<template>
  <div class="pet-management">
    <h2>Quản lý danh sách Pet</h2>
    <el-table v-loading="loading" :data="petList" style="width: 100%">
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="name" label="Tên Thú" />
      <el-table-column prop="base_dps" label="DPS Gốc" />
      <el-table-column prop="growth_rate" label="Tỷ lệ tăng trưởng" />
    </el-table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { ElMessage } from 'element-plus';

const petList = ref([]);
const loading = ref(false);

const fetchPets = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/pets'); // Gọi API vừa tạo
    petList.value = response.data.data;
  } catch (error) {
    ElMessage.error('Không thể tải danh sách thú cưng');
  } finally {
    loading.value = false;
  }
};

onMounted(fetchPets);
</script>