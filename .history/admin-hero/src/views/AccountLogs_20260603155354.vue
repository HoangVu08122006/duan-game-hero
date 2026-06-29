<template>
  <div class="logs-container">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>📋 Lịch sử Tạo Tài Khoản</span>
          <el-button type="primary" @click="fetchLogs" :loading="loading">Làm mới</el-button>
        </div>
      </template>

      <el-table :data="logs" v-loading="loading" style="width: 100%">
        <el-table-column prop="created_at" label="Thời gian" width="180" />
        <el-table-column prop="player.name" label="Tên nhân vật" />
        <el-table-column label="Nguồn tạo">
          <template #default="scope">
            <el-tag :type="scope.row.created_by_admin_id ? 'danger' : 'success'">
              {{ scope.row.created_by_admin_id ? 'Admin: ' + scope.row.admin?.name : 'Người chơi' }}
            </el-tag>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const logs = ref([]);
const loading = ref(false);

const fetchLogs = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/logs/all'); // Bạn cần tạo route API này ở Laravel
    logs.value = data;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchLogs);
</script>

<style scoped>
.card-header { display: flex; justify-content: space-between; align-items: center; }
</style>