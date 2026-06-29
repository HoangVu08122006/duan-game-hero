<template>
<el-table :data="logs" style="width: 100%">
  <el-table-column prop="stt" label="STT" width="60" />
  
  <el-table-column prop="player_name" label="Tên tài khoản" />
  
  <el-table-column prop="created_at" label="Thời gian tạo" />
  
  <el-table-column prop="creation_method" label="Nguồn tạo">
    <template #default="scope">
      <el-tag :type="scope.row.creation_method === 'Admin tạo' ? 'danger' : 'success'">
        {{ scope.row.creation_method }}
      </el-tag>
    </template>
  </el-table-column>
</el-table>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const logs = ref([]); // Mảng rỗng mặc định
const loading = ref(false);

const fetchLogs = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/logs/all');
    // Đảm bảo dữ liệu là mảng, nếu không sẽ gán mảng rỗng để không bị lỗi table
    logs.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error("Lỗi lấy log:", error);
    logs.value = []; // Reset về mảng rỗng khi lỗi
  } finally {
    loading.value = false;
  }
};

onMounted(fetchLogs);
</script>

<style scoped>
.card-header { display: flex; justify-content: space-between; align-items: center; }
</style>