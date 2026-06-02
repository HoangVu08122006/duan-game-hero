<template>
  <div style="padding: 24px; max-width: 1400px; margin: 0 auto; font-family: sans-serif;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
      <div>
        <h2 style="margin: 0; color: #303133; letter-spacing: 0.5px;">🛡️ HERO SLASH - GAME MASTER TOOL</h2>
        <p style="margin: 6px 0 0 0; color: #909399; font-size: 14px;">Quản lý và giám sát chỉ số thời gian thực của người chơi</p>
      </div>
      <div>
        <el-button type="primary" :loading="loading" @click="fetchPlayers" round>
          🔄 Làm Mới Dữ Liệu
        </el-button>
      </div>
    </div>

    <el-row :gutter="20" style="margin-bottom: 20px;">
      <el-col :span="6">
        <el-card shadow="hover" style="border-left: 4px solid #409eff;">
          <div style="color: #909399; font-size: 13px;">Tổng Số Tài Khoản</div>
          <div style="font-size: 24px; font-weight: bold; margin-top: 5px; color: #303133;">
            {{ players.length }}
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-card shadow="never" style="border-radius: 8px;">
      <el-table 
        v-loading="loading"
        :data="players" 
        border 
        stripe
        style="width: 100%"
        element-loading-text="Đang kết nối API lấy danh sách người chơi..."
      >
        <el-table-column prop="id" label="ID" width="80" align="center" />
        
        <el-table-column prop="name" label="Tên Nhân Vật" min-width="140">
          <template #default="scope">
            <span style="font-weight: bold; color: #2c3e50;">{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        
        <el-table-column label="Đẳng Cấp" width="130" align="center">
          <template #default="scope">
            <el-tag type="success" effect="dark">Lv. {{ scope.row.level }}</el-tag>
            <div style="font-size: 11px; color: #909399; margin-top: 4px;">EXP: {{ scope.row.exp }}</div>
          </template>
        </el-table-column>

        <el-table-column prop="total_power" label="Lực Chiến" width="120" align="center">
          <template #default="scope">
            <span style="color: #f56c6c; font-weight: bold; font-size: 15px;">
              🔥 {{ formatNumber(scope.row.total_power) }}
            </span>
          </template>
        </el-table-column>
        
        <el-table-column prop="gold" label="Vàng (Gold)" width="150">
          <template #default="scope">
            <span style="color: #e6a23c; font-weight: bold;">
              💰 {{ formatNumber(scope.row.gold) }}
            </span>
          </template>
        </el-table-column>

        <el-table-column label="Vượt Ải (Floor)" width="140" align="center">
          <template #default="scope">
            <div style="font-size: 13px;">Hiện tại: <b>{{ scope.row.current_floor }}</b></div>
            <div style="font-size: 11px; color: #909399;">Cao nhất: {{ scope.row.highest_floor }}</div>
          </template>
        </el-table-column>

        <el-table-column label="Thông Số & Cấp Công Pháp (Upgrades)" min-width="260">
          <template #default="scope">
            <div style="font-size: 12px; line-height: 1.6;">
              ⚔️ <b>ATK:</b> {{ scope.row.base_attack }} <span style="color:#409EFF">(Lv.{{ scope.row.upgraded_attack_lv }})</span><br />
              ❤️ <b>HP:</b> {{ scope.row.current_hp }}/{{ scope.row.base_hp }} <span style="color:#67C23A">(Lv.{{ scope.row.upgraded_hp_lv }})</span><br />
              ⚡ <b>Tốc độ:</b> <span style="color:#E6A23C">Lv.{{ scope.row.upgraded_speed_lv }}</span> | 
              🎯 <b>Chí mạng:</b> <span style="color:#909399">Lv.{{ scope.row.upgraded_crit_rate_lv }}</span>
            </div>
          </template>
        </el-table-column>
        
        <el-table-column label="Hành Động" width="160" align="center" fixed="right">
          <template #default="scope">
            <el-dropdown trigger="click">
              <el-button type="warning" size="small">
                Thao Tác <i class="el-icon-arrow-down el-icon--right"></i>
              </el-button>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item @click="adjustGold(scope.row)">✨ Cộng/Trừ Vàng</el-dropdown-item>
                  <el-dropdown-item @click="adjustLevel(scope.row)">🆙 Thay Đổi Level</el-dropdown-item>
                  <el-dropdown-item divided style="color: #f56c6c;" @click="kickPlayer(scope.row)">
                    ❌ Kick Khỏi Server
                  </el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { ElMessage } from 'element-plus'

// Khởi tạo các biến Reactive
const players = ref([])
const loading = ref(false)

// Hàm gọi API lấy danh sách người chơi thực tế từ IP của bạn
const fetchPlayers = async () => {
  loading.value = true
  try {
    const response = await axios.get('http://26.103.188.167:8000/api/list/players')
    
    // Check cấu trúc JSON: Nhận diện trường dữ liệu nằm trong mảng "data"
    if (response.data && response.data.status === 'success' && Array.isArray(response.data.data)) {
      players.value = response.data.data
      ElMessage.success(response.data.message || 'Tải danh sách player thành công!')
    } else {
      players.value = []
      ElMessage.error('Cấu trúc dữ liệu API trả về không đúng mong đợi.')
    }
  } catch (error) {
    console.error('Lỗi kết nối API Backend:', error)
    ElMessage.error('Không thể kết nối đến API Backend (Kiểm tra lại Server hoặc lỗi CORS)!')
  } finally {
    loading.value = false
  }
}

// Tự động kích hoạt gọi dữ liệu khi tải xong trang
onMounted(() => {
  fetchPlayers()
})

// Các hàm xử lý tương tác Admin nhanh
const adjustGold = (player) => {
  ElMessage.info(`Chức năng cộng/trừ vàng cho [${player.name}] đang được cấu hình API điều chỉnh...`)
}

const adjustLevel = (player) => {
  ElMessage.info(`Tính năng thay đổi Level / Exp cho [${player.name}]`)
}

const kickPlayer = (player) => {
  ElMessage.error(`Đã gửi lệnh ngắt kết nối trực tuyến đối với Player: ${player.name}`)
}

// Hàm định dạng hiển thị số tiền/lực chiến cho đẹp (Ví dụ: 2871305 -> 2,871,305)
const formatNumber = (num) => {
  if (num === undefined || num === null) return 0
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
}
</script>

<style scoped>
/* Bạn có thể tùy chỉnh thêm CSS riêng tại đây nếu cần */
.el-table {
  --el-table-header-bg-color: #f5f7fa;
  --el-table-header-text-color: #303133;
}
</style>