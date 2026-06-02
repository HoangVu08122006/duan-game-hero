<template>
  <div style="padding: 24px; max-width: 1200px; margin: 0 auto; font-family: sans-serif;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
      <div>
        <h2 style="margin: 0; color: #303133;">🛡️ HỆ THỐNG QUẢN TRỊ GAME - HERO SLASH</h2>
        <p style="margin: 4px 0 0 0; color: #909399; font-size: 14px;">Quản lý thông tin dữ liệu người chơi trong server</p>
      </div>
      <div>
        <el-button type="primary" :loading="loading" @click="fetchPlayers">
          🔄 Làm Mới Dữ Liệu
        </el-button>
      </div>
    </div>

    <el-card shadow="never">
      <el-table 
        v-loading="loading"
        :data="players" 
        border 
        stripe
        style="width: 100%"
        element-loading-text="Đang tải dữ liệu player..."
      >
        <el-table-column prop="id" label="ID" width="100" align="center" />
        <el-table-column prop="username" label="Tên Tài Khoản" min-width="150" />
        <el-table-column prop="level" label="Cấp Độ (Level)" width="130" align="center">
          <template #default="scope">
            <el-tag type="info">Lv. {{ scope.row.level }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="gold" label="Số Vàng (Gold)" width="150">
          <template #default="scope">
            <span style="color: #e6a23c; font-weight: bold;">
              {{ formatNumber(scope.row.gold) }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="Trạng Thái" width="130" align="center">
          <template #default="scope">
            <el-tag :type="scope.row.status === 'banned' ? 'danger' : 'success'">
              {{ scope.row.status === 'banned' ? 'Bị Khóa' : 'Hoạt Động' }}
            </el-tag>
          </template>
        </el-table-column>
        
        <el-table-column label="Thao Tác Quản Trị" width="250" align="center">
          <template #default="scope">
            <el-button size="small" type="warning" @click="handleEdit(scope.row)">
              Cộng Tiền
            </el-button>
            <el-button 
              size="small" 
              :type="scope.row.status === 'banned' ? 'success' : 'danger'"
              @click="toggleBan(scope.row)"
            >
              {{ scope.row.status === 'banned' ? 'Mở Khóa' : 'Ban Acc' }}
            </el-button>
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

// Khai báo state lưu danh sách và trạng thái loading
const players = ref([])
const loading = ref(false)

// Hàm gọi API lấy danh sách người chơi từ Backend của bạn
const fetchPlayers = async () => {
  loading.value = true
  try {
    const response = await axios.get('http://26.103.188.167:8000/api/list/players')
    
    // Gán dữ liệu trả về vào mảng players (giả định API trả về trực tiếp mảng hoặc response.data.data)
    if (Array.isArray(response.data)) {
      players.value = response.data
    } else if (response.data && Array.isArray(response.data.data)) {
      players.value = response.data.data
    } else {
      players.value = []
    }
    
    ElMessage.success('Tải danh sách player thành công!')
  } catch (error) {
    console.error('Lỗi khi gọi API:', error)
    ElMessage.error('Không thể kết nối tới Server API backend!')
  } finally {
    loading.value = false
  }
}

// Chạy hàm fetchPlayers ngay khi trang web vừa được load xong
onMounted(() => {
  fetchPlayers()
})

// Các hàm xử lý sự kiện phụ (Bản demo)
const handleEdit = (player) => {
  ElMessage.info(`Mở chức năng sửa thông tin cho: ${player.username}`)
}

const toggleBan = (player) => {
  player.status = player.status === 'banned' ? 'active' : 'banned'
  ElMessage.warning(`Đã thay đổi trạng thái tài khoản ${player.username}`)
}

// Hàm format hiển thị số cho đẹp (Ví dụ: 1000000 -> 1,000,000)
const formatNumber = (num) => {
  if (!num) return 0
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
}
</script>