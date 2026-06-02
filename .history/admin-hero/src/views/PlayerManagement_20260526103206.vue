<template>
  <div class="management-container">
    <div class="page-header">
      <div>
        <h2 class="title">
          👥 QUẢN LÝ NGƯỜI CHƠI 
          <el-badge :value="players.length" type="primary" class="total-badge">
            <span class="total-label">Tổng số tài khoản</span>
          </el-badge>
        </h2>
        <p class="subtitle">Xem danh sách, kiểm tra chi tiết trang bị, Pet và thông số Game Master</p>
      </div>
      <div class="action-buttons">
        <button class="btn-add" @click="openRegisterDialog">➕ Tạo Tài Khoản Mới</button>
        <el-button type="primary" :loading="loading" @click="fetchPlayers" round>🔄 Làm Mới</el-button>
      </div>
    </div>

    <el-card shadow="never" class="table-card">
      <el-table v-loading="loading" :data="players" border stripe style="width: 100%">
        <el-table-column type="index" label="STT" width="80" align="center" />
        <el-table-column prop="name" label="Tên Nhân Vật" min-width="150" />
        <el-table-column label="Đẳng Cấp" width="120" align="center">
          <template #default="scope"><el-tag type="success">Lv. {{ scope.row.level || 1 }}</el-tag></template>
        </el-table-column>
        <el-table-column prop="gold" label="Vàng" width="130">
          <template #default="scope"><span class="gold-text">💰 {{ formatNumber(scope.row.gold) }}</span></template>
        </el-table-column>
        <el-table-column prop="total_power" label="Tổng Lực Chiến" width="150">
          <template #default="scope"><span class="power-text">🔥 {{ formatNumber(scope.row.total_power) }}</span></template>
        </el-table-column>
        <el-table-column label="Hành Động" width="220" align="center" fixed="right">
          <template #default="scope">
            <el-button size="small" type="primary" plain @click="fetchPlayerDetail(scope.row.id)">👁️ Chi Tiết</el-button>
            <el-button size="small" type="danger" @click="handleDeletePlayer(scope.row)">❌ Xóa</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog v-model="detailDialogVisible" title="🪪 THÔNG TIN CHI TIẾT GAME MASTER" width="850px" destroy-on-close>
      <div v-if="detailLoading" v-loading="true" style="height: 300px;"></div>
      
      <div v-else-if="selectedPlayer" class="detail-wrapper">
        <el-tabs type="border-card">
          <el-tab-pane label="📊 Thông Số Gốc vs Hiện Tại">
            <el-descriptions :column="2" border size="small">
              <el-descriptions-item label="HP (Gốc/Hiện tại)">
                <el-tag type="info">{{ formatNumber(selectedPlayer.base_hp) }}</el-tag> ➡️ 
                <el-tag type="danger">{{ formatNumber(currentCalculatedStats.hp) }}</el-tag>
              </el-descriptions-item>
              <el-descriptions-item label="ATK (Gốc/Hiện tại)">
                <el-tag type="info">{{ formatNumber(selectedPlayer.base_attack) }}</el-tag> ➡️ 
                <el-tag type="warning">{{ formatNumber(currentCalculatedStats.atk) }}</el-tag>
              </el-descriptions-item>
              <el-descriptions-item label="Tốc độ">{{ selectedPlayer.base_speed }} ➡️ <b>{{ currentCalculatedStats.speed }}</b></el-descriptions-item>
              <el-descriptions-item label="Chí mạng">{{ selectedPlayer.base_crit_rate }}% ➡️ <b>{{ currentCalculatedStats.critRate }}%</b></el-descriptions-item>
            </el-descriptions>

            <h4 class="sub-section-title">⚡ BẢNG ĐIỀU CHỈNH CẤP ĐỘ</h4>
            <div class="stats-edit-box">
              <el-form :model="statsForm" inline size="small">
                <el-form-item label="💪 Cấp HP:"><el-input-number v-model="statsForm.upgraded_hp_lv" :min="0" /></el-form-item>
                <el-form-item label="⚔️ Cấp ATK:"><el-input-number v-model="statsForm.upgraded_attack_lv" :min="0" /></el-form-item>
                <el-form-item label="⚡ Tốc Độ:"><el-input-number v-model="statsForm.upgraded_speed_lv" :min="0" /></el-form-item>
                <el-button type="success" :loading="statsUpdateLoading" @click="updatePlayerStats">⚙️ Áp Dụng</el-button>
              </el-form>
            </div>
          </el-tab-pane>
        </el-tabs>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'

const players = ref([])
const loading = ref(false)
const detailDialogVisible = ref(false)
const detailLoading = ref(false)
const selectedPlayer = ref(null)
const statsUpdateLoading = ref(false)

const statsForm = reactive({
  upgraded_hp_lv: 0,
  upgraded_attack_lv: 0,
  upgraded_speed_lv: 0,
  upgraded_crit_rate_lv: 0
})

// Tính toán thời gian thực
const currentCalculatedStats = computed(() => {
  if (!selectedPlayer.value) return { hp: 0, atk: 0, speed: 0, critRate: 0 }
  const p = selectedPlayer.value
  const s = statsForm
  return {
    hp: Math.round(p.base_hp * Math.pow(1.05, s.upgraded_hp_lv)),
    atk: Math.round(p.base_attack * Math.pow(1.08, s.upgraded_attack_lv)),
    speed: p.base_speed + s.upgraded_speed_lv,
    critRate: (parseFloat(p.base_crit_rate || 0) + (s.upgraded_crit_rate_lv * 0.5)).toFixed(2)
  }
})

const getAuthHeaders = () => ({ Authorization: `Bearer ${localStorage.getItem('admin_token')}` })

const fetchPlayers = async () => {
  loading.value = true
  try {
    const res = await axios.get('http://26.103.188.167:8000/api/list/players', { headers: getAuthHeaders() })
    players.value = res.data.data
  } catch (e) { ElMessage.error('Lỗi tải danh sách') }
  finally { loading.value = false }
}

const fetchPlayerDetail = async (id) => {
  detailDialogVisible.value = true
  detailLoading.value = true
  try {
    const res = await axios.get(`http://26.103.188.167:8000/api/list/players/${id}`, { headers: getAuthHeaders() })
    selectedPlayer.value = res.data.player
    // Gán dữ liệu vào form
    statsForm.upgraded_hp_lv = selectedPlayer.value.upgraded_hp_lv
    statsForm.upgraded_attack_lv = selectedPlayer.value.upgraded_attack_lv
    statsForm.upgraded_speed_lv = selectedPlayer.value.upgraded_speed_lv
  } finally { detailLoading.value = false }
}

const updatePlayerStats = async () => {
  statsUpdateLoading.value = true
  try {
    await axios.patch(`http://26.103.188.167:8000/api/list/players/${selectedPlayer.value.id}/stats`, statsForm, { headers: getAuthHeaders() })
    ElMessage.success('Cập nhật thành công!')
    fetchPlayers()
  } catch (e) { ElMessage.error('Cập nhật thất bại') }
  finally { statsUpdateLoading.value = false }
}

const formatNumber = (num) => (num || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")

onMounted(fetchPlayers)
</script>

<style scoped>
/* Thêm CSS của bạn tại đây */
.gold-text { color: #e6a23c; font-weight: bold; }
.power-text { color: #f56c6c; font-weight: bold; }
.sub-section-title { margin: 20px 0 10px; font-weight: bold; }
.stats-edit-box { background: #f0f9eb; padding: 15px; border-radius: 6px; }
</style>