<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold mb-4">Danh sách {{ type === 'monster' ? 'Quái' : 'Boss' }}</h2>

    <table class="w-full border-collapse border border-gray-300">
      <thead class="bg-gray-100">
        <tr>
          <th class="border p-2">ID</th>
          <th class="border p-2">Tên</th>
          <th class="border p-2">HP Gốc</th>
          <th class="border p-2">Prefab</th>
          <th class="border p-2">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="item.id">
          <td class="border p-2 text-center">{{ item.id }}</td>
          <td class="border p-2">{{ item.name }}</td>
          <td class="border p-2">{{ item.base_hp || item.base_hp }}</td>
          <td class="border p-2">{{ item.prefab_name }}</td>
          <td class="border p-2 text-center">
            <button @click="editItem(item)" class="text-blue-600 mr-2">Sửa</button>
            <button @click="deleteItem(item.id)" class="text-red-600">Xóa</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps(['type']);
const items = ref([]);

// Hàm lấy danh sách từ API
const fetchData = async () => {
  try {
    const res = await axios.get(`/api/admin/entity/${props.type}`);
    items.value = res.data.data;
  } catch (error) {
    console.error("Lỗi khi tải danh sách:", error);
  }
};

// Gọi lại khi type thay đổi (khi chuyển giữa trang Quái và Boss)
watch(() => props.type, fetchData, { immediate: true });

onMounted(fetchData);
</script>