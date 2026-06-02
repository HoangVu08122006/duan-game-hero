<template>
  <div class="management-container">
    
    <div class="page-header">
      <div>
        <h2 class="title">👥 QUẢN LÝ NGƯỜI CHƠI</h2>
        <p class="subtitle">Xem danh sách, kiểm tra lực chiến và quản lý tài khoản người chơi</p>
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
        element-loading-text="Đang tải dữ liệu player..."
      >
        <el-table-column prop="id" label="ID" width="80" align="center" />
        <el-table-column prop="name" label="Tên Nhân Vật" min-width="150" />
        <el-table-column label="Đẳng Cấp" width="120" align="center">
          <template #default="scope">
            <el-tag type="success">Lv. {{ scope.row.level || 1 }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="gold" label="Vàng (Gold)" width="150">
          <template #default="scope">
            <span class="gold-text">💰 {{ formatNumber(scope.row.gold) }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="total_power" label="Lực Chiến" width="150">
          <template #default="scope">
            <span class="power-text">🔥 {{ formatNumber(scope.row.total_power) }}</span>
          </template>
        </el-table-column>
        <el-table-column label="Hành Động" width="120" align="center">
          <template #default="scope">
            <el-button size="small" type="danger" @click="handleKick(scope.row)">Kick</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog
      v-model="dialogVisible"
      title="➕ ĐĂNG KÝ TÀI KHOẢN NGƯỜI CHƠI MỚI"
      width="500px"
      destroy-on-close
      class="register-dialog"
    >
      <el-form 
        :model="registerForm" 
        :rules="rules" 
        ref="registerFormRef"
        label-position="top"
      >
        <el-form-item label="Tên Nhân Vật (Username)" prop="name">
          <el-input 
            v-model="registerForm.name" 
            placeholder="Nhập tên nhân vật muốn tạo..." 
            clearable
          />
        </el-form-item>

        <el-form-item label="Mật Khẩu" prop="password">
          <el-input 
            v-model="registerForm.password" 
            type="password" 
            placeholder="Nhập mật khẩu bảo mật..." 
            show-password
          />
        </el-form-item>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
          <el-form-item label="Vàng Khởi Tạo" prop="gold">
            <el-input-number v-model="registerForm.gold" :min="0" style="width: 100%" />
          </el-form-item>
          <el-form-item label="Cấp Độ Đầu" prop="level">
            <el-input-number v-model="registerForm.level" :min="1" style="width: 100%" />
          </el-form-item>
        </div>
      </el-form>

      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">Hủy Bỏ</el-button>
          <el-button 
            type="primary" 
            :loading="submitLoading" 
            @click="submitRegisterForm(registerFormRef)"
          >
            🚀 Khởi Tạo Tài Khoản
          </el-button>
        </span>
      </template>
    </el-dialog>

  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import axios from 'axios'
import { ElMessage } from 'element-plus'

// --- BIẾN ĐIỀU KHIỂN BẢNG ---
const players = ref([])
const loading = ref(false)

// --- BIẾN ĐIỀU KHIỂN FORM REGISTER ---
const dialogVisible = ref(false)
const submitLoading = ref(false)
const registerFormRef = ref(null)

// Dữ liệu Form đăng ký
const registerForm = reactive({
  name: '',
  password: '',
  gold: 1000, // Giá trị mặc định khi tạo mới
  level: 1
})

// Kiểm tra dữ liệu đầu vào (Validation)
const rules = reactive({
  name: [
    { required: true, message: 'Vui lòng điền tên nhân vật!', trigger: 'blur' },
    { min: 3, message: 'Tên nhân vật phải có ít nhất 3 ký tự!', trigger: 'blur' }
  ],
  password: [
    { required: true, message: 'Vui lòng nhập mật khẩu!', trigger: 'blur' },
    { min: 6, message: 'Mật khẩu phải dài từ 6 ký tự trở lên!', trigger: 'blur' }
  ]
})

// --- CÁC HÀM XỬ LÝ LOGIC ---

// 1. Lấy danh sách người chơi
const fetchPlayers = async () => {
  loading.value = true
  try {
    const response = await axios.get('http://26.103.188.167:8000/api/list/players')
    if (response.data && response.data.status === 'success') {
      players.value = response.data.data
    }
  } catch (error) {
    ElMessage.error('Không thể kết nối đến API lấy danh sách!')
  } finally {
    loading.value = false
  }
}

// 2. Mở popup form đăng ký và reset form về trắng
const openRegisterDialog = () => {
  registerForm.name = ''
  registerForm.password = ''
  registerForm.gold = 1000
  registerForm.level = 1
  dialogVisible.value = true
}

// 3. Hàm kích hoạt gửi dữ liệu Form lên API /api/register
const submitRegisterForm = async (formEl) => {
  if (!formEl) return
  
  // Kiểm tra xem admin đã điền đúng quy định của form chưa
  await formEl.validate(async (valid) => {
    if (valid) {
      submitLoading.value = true
      try {
        // Gọi API Đăng ký tài khoản thực tế của bạn
        const response = await axios.post('http://26.103.188.167:8000/api/register', {
          name: registerForm.name,
          password: registerForm.password,
          gold: registerForm.gold,
          level: registerForm.level
        })

        // Tùy theo cấu trúc Backend trả về để check thành công (Ví dụ: response.data.status === 'success')
        ElMessage.success(`Tạo tài khoản [${registerForm.name}] thành công!`)
        dialogVisible.value = false // Đóng popup form
        
        fetchPlayers() // Tự động load lại bảng dữ liệu để hiển thị player mới ngay lập tức
      } catch (error) {
        console.error(error)
        ElMessage.error(error.response?.data?.message || 'Lỗi hệ thống khi đăng ký tài khoản!')
      } finally {
        submitLoading.value = false
      }
    } else {
      ElMessage.warning('Vui lòng điền đầy đủ thông tin hợp lệ!')
    }
  })
}

const handleKick = (row) => { ElMessage.error(`Đã kick player: ${row.name}`) }
const formatNumber = (num) => num ? num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") : 0

onMounted(() => { fetchPlayers() })
</script>

<style scoped>
.management-container {
  background-color: #ffffff;
  padding: 20px;
  border-radius: 6px;
  box-shadow: 0 2px 12px 0 rgba(0,0,0,0.05);
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.action-buttons {
  display: flex;
  gap: 10px;
}
.title { margin: 0; color: #303133; font-size: 22px; }
.subtitle { margin: 4px 0 0 0; color: #909399; font-size: 13px; }

/* CSS nút Tạo Tài Khoản phá cách nổi bật */
.btn-add {
  background-color: #67c23a;
  color: white;
  border: none;
  padding: 0 20px;
  font-size: 14px;
  font-weight: bold;
  border-radius: 20px;
  cursor: pointer;
  transition: background-color 0.3s;
}
.btn-add:hover {
  background-color: #5daf34;
}

.table-card { border: none; }
.gold-text { color: #e6a23c; font-weight: bold; }
.power-text { color: #f56c6c; font-weight: bold; }
</style>