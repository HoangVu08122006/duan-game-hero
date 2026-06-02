<template>
  <div class="page-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <div>
        <h3 style="margin: 0;">👥 QUẢN LÝ NGƯỜI CHƠI</h3>
        <p style="margin: 4px 0 0 0; color: #909399; font-size: 13px;">Dữ liệu nạp từ API thực tế của server game</p>
      </div>
      <el-button type="primary" :loading="loading" @click="fetchPlayers" round>🔄 Làm Mới</el-button>
    </div>

    <el-table v-loading="loading" :data="players" border stripe style="width: 100%">
      <el-table-column prop="id" label="ID" width="80" align="center" />
      <el-table-column prop="name" label="Tên Nhân Vật" min-width="140" />
      <el-table-column prop="level" label="Level" width="100" align="center" />
      <el-table-column prop="total_power" label="Lực Chiến" width="120" />
      <el-table-column prop="gold" label="Vàng (Gold)" width="140" />
      <el-table-column label="Hành Động" width="150" align="center">
        <template #default="scope">
          <el-button size="small" type="warning" @click="editPlayer(scope.row)">Cộng Tiền</el-button>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { ElMessage } from 'element-plus'

const players = ref([])
const loading = ref(false)

const fetchPlayers = async () => {
  loading.value = true
  try {
    const response = await axios.get('http://26.103.188.167:8000/api/list/players')
    if (response.data && response.data.status === 'success') {
      players.value = response.data.data
    }
  } catch (error) {
    ElMessage.error('Lỗi kết nối API Backend!')
  } finally {
    loading.value = false
  }
}

onMounted(() => { fetchPlayers() })
const editPlayer = (row) => { ElMessage.info(`Sửa đổi cho: ${row.name}`) }
</script>