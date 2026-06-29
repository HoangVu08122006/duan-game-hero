<template>
  <div class="pet-management">
    <h2>Quản lý danh sách Pet</h2>
    
    <el-table v-loading="loading" :data="petList" border style="width: 100%">
      <el-table-column 
        v-for="(column, key) in columns" 
        :key="key" 
        :prop="column" 
        :label="formatLabel(column)" 
        min-width="120" 
      />
    </el-table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const petList = ref([]);
const loading = ref(false);
const columns = ref([]); // Mảng chứa tên các cột

const fetchPets = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/pets');
    const data = response.data.data;
    
    if (data.length > 0) {
      petList.value = data;
      // Tự động lấy danh sách cột từ object đầu tiên
      columns.value = Object.keys(data[0]);
    }
  } catch (error) {
    console.error('Lỗi tải dữ liệu:', error);
  } finally {
    loading.value = false;
  }
};

// Hàm định dạng tên cột cho đẹp (ví dụ: base_dps -> Base Dps)
const formatLabel = (key) => {
  return key.replace(/_/g, ' ').toUpperCase();
};

onMounted(fetchPets);
</script>