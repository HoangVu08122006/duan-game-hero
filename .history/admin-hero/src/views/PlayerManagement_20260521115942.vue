<template>
  <div class="management-container">
    
    <div class="page-header">
      <div>
        <h2 class="title">👥 QUẢN LÝ NGƯỜI CHƠI</h2>
        <p class="subtitle">Xem danh sách, kiểm tra chi tiết trang bị, Pet và thông số Game Master</p>
      </div>
      <div class="action-buttons">
        <button class="btn-add" @click="openRegisterDialog">
          ➕ Tạo Tài Khoản Mới
        </button>
        <el-button type="primary" :loading="loading" @click="fetchPlayers" round>
          🔄 Làm Mới
        </el-button>
      </div>
    </div>

    <el-card shadow="never" class="table-card">
      <el-table 
        v-loading="loading"
        :data="players" 
        border 
        stripe
        style="width: 100%"
      >
        <el-table-column prop="id" label="ID" width="80" align="center" />
        <el-table-column prop="name" label="Tên Nhân Vật" min-width="150" />
        <el-table-column label="Đẳng Cấp" width="120" align="center">
          <template #default="scope">
            <el-tag type="success">Lv. {{ scope.row.level || 1 }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="gold" label="Vàng" width="130">
          <template #default="scope">
            <span class="gold-text">💰 {{ formatNumber(scope.row.gold) }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="total_power" label="Tổng Lực Chiến" width="150">
          <template #default="scope">
            <span class="power-text">🔥 {{ formatNumber(scope.row.total_power) }}</span>
          </template>
        </el-table-column>
        
        <el-table-column label="Hành Động" width="220" align="center" fixed="right">
          <template #default="scope">
            <el-button 
              size="small" 
              type="primary" 
              plain
              @click="fetchPlayerDetail(scope.row.id)"
            >
              👁️ Chi Tiết
            </el-button>
            <el-button 
              size="small" 
              type="danger" 
              @click="handleDeletePlayer(scope.row)"
            >
              ❌ Xóa V
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog v-model="dialogVisible" title="➕ ĐĂNG KÝ TÀI KHOẢN MỚI" width="500px" destroy-on-close>
      <el-form :model="registerForm" :rules="rules" ref="registerFormRef" label-position="top">
        <el-form-item label="Tên Nhân Vật (Username)" prop="name">
          <el-input v-model="registerForm.name" placeholder="Nhập tên nhân vật..." clearable />
        </el-form-item>
        <el-form-item label="Mật Khẩu" prop="password">
          <el-input v-model="registerForm.password" type="password" placeholder="Nhập mật khẩu..." show-password />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">Hủy</el-button>
          <el-button type="primary" :loading="submitLoading" @click="submitRegisterForm(registerFormRef)">Khởi Tạo</el-button>
        </span>
      </template>
    </el-dialog>

    <el-dialog
      v-model="detailDialogVisible"
      title="🪪 THÔNG TIN CHI TIẾT GAME MASTER PLAYER"
      width="750px"
      destroy-on-close
    >
      <div v-if="detailLoading" v-loading="true" style="height: 300px;"></div>
      
      <div v-else-if="selectedPlayer" class="detail-wrapper">
        <div class="detail-header">
          <div class="avatar-box">👑</div>
          <div class="user-meta">
            <h3>{{ selectedPlayer.name }} <el-tag size="small" type="warning">ID: {{ selectedPlayer.id }}</el-tag></h3>
            <p>Tổng Lực Chiến: <span class="power-text">🔥 {{ formatNumber(selectedPlayer.total_power) }}</span></p>
          </div>
        </div>

        <el-tabs type="border-card" class="detail-tabs">
          
          <el-tab-pane label="📊 Thông Số & Công Pháp">
            <el-descriptions :column="2" border size="small">
              <el-descriptions-item label="Cấp Độ"><el-tag type="success">Lv. {{ selectedPlayer.level }}</el-tag></el-descriptions-item>
              <el-descriptions-item label="Kinh Nghiệm (EXP)">{{ selectedPlayer.exp }}</el-descriptions-item>
              <el-descriptions-item label="Tài Sản Vàng">💰 {{ formatNumber(selectedPlayer.gold) }}</el-descriptions-item>
              <el-descriptions-item label="Quái Đã Diệt">💀 {{ selectedPlayer.kill_count }}</el-descriptions-item>
              <el-descriptions-item label="Ải Hiện Tại">🏰 Tầng {{ selectedPlayer.current_floor }}</el-descriptions-item>
              <el-descriptions-item label="Ải Cao Nhất">🏆 Tầng {{ selectedPlayer.highest_floor }}</el-descriptions-item>
              <el-descriptions-item label="Máu hiện tại (HP)">❤️ {{ selectedPlayer.current_hp }} / {{ selectedPlayer.base_hp }}</el-descriptions-item>
              <el-descriptions-item label="Tấn Công (ATK)">⚔️ {{ selectedPlayer.current_attack }} (Gốc: {{ selectedPlayer.base_attack }})</el-descriptions-item>
            </el-descriptions>

            <h4 class="sub-section-title">✨ Cấp Độ Nâng Cấp Công Pháp</h4>
            <div class="upgrade-grid">
              <div>💪 Cấp HP: <el-tag size="small">Lv.{{ selectedPlayer.upgraded_hp_lv }}</el-tag></div>
              <div>⚔️ Cấp ATK: <el-tag size="small">Lv.{{ selectedPlayer.upgraded_attack_lv }}</el-tag></div>
              <div>⚡ Cấp Tốc Độ: <el-tag size="small">Lv.{{ selectedPlayer.upgraded_speed_lv }}</el-tag></div>
              <div>🎯 Cấp Chí Mạng: <el-tag size="small">Lv.{{ selectedPlayer.upgraded_crit_rate_lv }}</el-tag></div>
            </div>
          </el-tab-pane>

          <el-tab-pane label="⚔️ Vũ Khí Sở Hữu">
            <el-table :data="selectedPlayer.weapons" border size="small" style="width: 100%">
              <el-table-column prop="weapon.name" label="Tên Vũ Khí" min-width="120" />
              <el-table-column label="Trạng Thái Vận Sức" width="130" align="center">
                <template #default="wScope">
                  <el-tag v-if="wScope.row.is_equipped === 1" type="danger" effect="dark">Đang Trang Bị</el-tag>
                  <el-tag v-else type="info">Trong Kho</el-tag>
                </template>
              </el-table-column>
              <el-table-column label="Cấp Cường Hóa" width="110" align="center">
                <template #default="wScope">
                  ➕ {{ wScope.row.level }}
                </template>
              </el-table-column>
              <el-table-column label="Sát Thương" width="110" align="center">
                <template #default="wScope">
                  💥 {{ formatNumber(wScope.row.current_damage) }}
                </template>
              </el-table-column>
              <el-table-column label="Phí Nâng Cấp tiếp" width="120" align="center">
                <template #default="wScope">
                  🪙 {{ formatNumber(wScope.row.upgrade_cost) }}
                </template>
              </el-table-column>
            </el-table>
          </el-tab-pane>

          <el-tab-pane label="🐉 Trận Doanh Thú Cưng">
            <div v-if="selectedPlayer.pets && selectedPlayer.pets.length > 0">
              <div v-for="pet in selectedPlayer.pets" :key="pet.id" class="pet-card">
                <div class="pet-info-main">
                  <div>
                    <span class="pet-title">🐉 {{ pet.name }}</span> 
                    <el-tag size="small" type="success" style="margin-left: 8px;">Cấp {{ pet.pivot?.level }}</el-tag>
                    <el-tag v-if="pet.pivot?.is_equipped === 1" size="small" type="danger" style="margin-left: 5px;">Xuất Trận</el-tag>
                  </div>
                  <div class="pet-power">Lực chiến Pet: ⚡ {{ selectedPlayer.pet_skills?.total_power || pet.base_dps }}</div>
                </div>
                
                <div class="pet-skills-list">
                  <h5>🔥 Kỹ Năng Pet Có Sẵn:</h5>
                  <ul>
                    <li v-if="pet.skill_1_name">🔹 <b>{{ pet.skill_1_name }}</b> (Sát thương chính: {{ selectedPlayer.pet_skills?.main_dame || 0 }})</li>
                    <li v-if="pet.skill_2_name">🔹 <b>{{ pet.skill_2_name }}</b></li>
                    <li v-if="pet.skill_3_name">🔹 <b>{{ pet.skill_3_name }}</b></li>
                  </ul>
                </div>
              </div>
            </div>
            <el-empty v-else description="Player này chưa thuần hóa Thú Cưng nào!" />
          </el-tab-pane>
        </el-tabs>
      </div>
      
      <template #footer>
        <el-button type="primary" @click="detailDialogVisible = false">Đóng Giao Diện</el-button>
      </template>
    </el-dialog>

  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import axios from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'

// --- BIẾN ĐIỀU KHIỂN BẢNG TỔNG QUAN ---
const players = ref([])
const loading = ref(false)

// --- BIẾN ĐIỀU KHIỂN XEM CHI TIẾT ---
const detailDialogVisible = ref(false)
const detailLoading = ref(false)
const selectedPlayer = ref(null)

// --- BIẾN ĐIỀU KHIỂN FORM REGISTER ---
const dialogVisible = ref(false)
const submitLoading = ref(false)
const registerFormRef = ref(null)
const registerForm = reactive({ name: '', password: '' })
const rules = reactive({
  name: [{ required: true, message: 'Vui lòng điền tên nhân vật!', trigger: 'blur' }],
  password: [{ required: true, message: 'Vui lòng nhập mật khẩu!', trigger: 'blur' }]
})

// 1. Lấy danh sách người chơi tổng quan đổ ra bảng
const fetchPlayers = async () => {
  loading.value = true
  try {
    const response = await axios.get('http://26.103.188.167:8000/api/list/players')
    if (response.data && response.data.status === 'success') {
      players.value = response.data.data
    }
  } catch (error) {
    ElMessage.error('Không thể lấy danh sách người chơi!')
  } finally { loading.value = false }
}

// 2. Gọi API chi tiết của từng Player theo Route Param chuẩn (/api/list/players/{id})
const fetchPlayerDetail = async (playerId) => {
  detailDialogVisible.value = true
  detailLoading.value = true
  selectedPlayer.value = null
  
  try {
    const response = await axios.get(`http://26.103.188.167:8000/api/list/players/${playerId}`)
    console.log("Dữ liệu chi tiết nhận được từ Backend:", response.data)

    if (response.data) {
      selectedPlayer.value = response.data.player || response.data.data || response.data
    } else {
      ElMessage.error('API trả về dữ liệu trống!')
    }
  } catch (error) {
    console.error("Lỗi chi tiết khi gọi API:", error.response)
    const errorMsg = error.response?.data?.message || 'Không thể kết nối đến API chi tiết!'
    ElMessage.error(`Lỗi tải chi tiết: ${errorMsg}`)
  } finally {
    detailLoading.value = false
  }
}

// 3. Gửi lệnh XÓA TÀI KHOẢN vĩnh viễn lên Backend (API DELETE: /api/list/players/{id})
const handleDeletePlayer = (row) => {
  ElMessageBox.confirm(
    `Hành động này sẽ xóa hoàn toàn nhân vật [${row.name}] khỏi hệ thống. Bạn có chắc chắn muốn tiếp tục?`,
    '⚠️ CẢNH BÁO XÓA TÀI KHOẢN VĨNH VIỄN',
    {
      confirmButtonText: 'Đồng Ý Xóa',
      cancelButtonText: 'Hủy Bỏ',
      type: 'danger',
      center: true,
    }
  )
    .then(async () => {
      try {
        await axios.delete(`http://26.103.188.167:8000/api/list/players/${row.id}`)
        ElMessage.success(`Đã xóa thành công tài khoản [${row.name}] khỏi cơ sở dữ liệu!`)
        fetchPlayers() // Tự động cập nhật lại danh sách bảng sau khi xóa thành công
      } catch (error) {
        console.error("Lỗi khi xóa tài khoản:", error.response)
        const errorMsg = error.response?.data?.message || 'Không thể thực hiện lệnh xóa!'
        ElMessage.error(`Xóa thất bại: ${errorMsg}`)
      }
    })
    .catch(() => {
      ElMessage.info('Đã hủy thao tác xóa tài khoản vĩnh viễn.')
    })
}

// 4. Xử lý Form Đăng ký tài khoản mới
const openRegisterDialog = () => { dialogVisible.value = true }
const submitRegisterForm = async (formEl) => {
  if (!formEl) return
  await formEl.validate(async (valid) => {
    if (valid) {
      submitLoading.value = true
      try {
        await axios.post('http://26.103.188.167:8000/api/register', registerForm)
        ElMessage.success(`Tạo tài khoản thành công!`)
        dialogVisible.value = false
        fetchPlayers()
      } catch (error) { ElMessage.error('Lỗi đăng ký!') } 
      finally { submitLoading.value = false }
    }
  })
}

const formatNumber = (num) => num ? num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") : 0

onMounted(() => { fetchPlayers() })
</script>

<style scoped>
.management-container { background-color: #ffffff; padding: 20px; border-radius: 6px; box-shadow: 0 2px 12px 0 rgba(0,0,0,0.05); }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.action-buttons { display: flex; gap: 10px; }
.title { margin: 0; color: #303133; font-size: 22px; }
.subtitle { margin: 4px 0 0 0; color: #909399; font-size: 13px; }
.btn-add { background-color: #67c23a; color: white; border: none; padding: 0 20px; font-size: 14px; font-weight: bold; border-radius: 20px; cursor: pointer; }
.btn-add:hover { background-color: #5daf34; }
.table-card { border: none; }
.gold-text { color: #e6a23c; font-weight: bold; }
.power-text { color: #f56c6c; font-weight: bold; }

/* CSS Khu vực Giao diện chi tiết trong Popup */
.detail-wrapper { padding: 5px; }
.detail-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 5px solid #409eff; }
.avatar-box { font-size: 32px; background: #eecfcd; padding: 10px; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; }
.user-meta h3 { margin: 0 0 5px 0; font-size: 18px; color: #2c3e50; }
.user-meta p { margin: 0; font-size: 14px; color: #606266; }
.detail-tabs { margin-top: 10px; }
.sub-section-title { margin: 20px 0 10px 0; font-size: 14px; color: #303133; border-bottom: 1px dashed #dcdfe6; padding-bottom: 5px; }
.upgrade-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; background: #f4f4f5; padding: 12px; border-radius: 6px; font-size: 13px; }

/* CSS Thú cưng */
.pet-card { background: #fafafa; border: 1px solid #e4e7ed; padding: 15px; border-radius: 6px; margin-bottom: 15px; }
.pet-info-main { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ebeef5; padding-bottom: 8px; margin-bottom: 10px; }
.pet-title { font-weight: bold; color: #e6a23c; font-size: 15px; }
.pet-power { font-size: 13px; font-weight: 500; color: #409eff; }
.pet-skills-list h5 { margin: 0 0 5px 0; color: #606266; font-size: 13px; }
.pet-skills-list ul { margin: 0; padding-left: 15px; font-size: 13px; color: #909399; list-style-type: none; }
.pet-skills-list li { margin-bottom: 3px; }
</style>