<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold mb-6">Quản lý Thực thể Game</h2>

    <div class="flex border-b mb-4">
      <button 
        v-for="tab in ['monster', 'boss']" :key="tab"
        @click="activeTab = tab" 
        :class="['px-6 py-2 capitalize font-semibold', activeTab === tab ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500']"
      >
        {{ tab }}s
      </button>
    </div>

    <div class="bg-white p-4 shadow rounded">
      <table class="w-full border-collapse border border-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="border p-2">ID</th>
            <th class="border p-2">Tên</th>
            <th class="border p-2">HP</th>
            <th class="border p-2">Prefab</th>
            <th class="border p-2">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td class="border p-2 text-center">{{ item.id }}</td>
            <td class="border p-2">{{ item.name }}</td>
            <td class="border p-2">{{ item.base_hp }}</td>
            <td class="border p-2">{{ item.prefab_name }}</td>
            <td class="border p-2 text-center">
              <button @click="editItem(item)" class="text-blue-500 mr-3">Sửa</button>
              <button @click="deleteItem(item.id)" class="text-red-500">Xóa</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps(['type']);
const activeTab = ref('monster');
const items = ref([]);

// Tạo instance axios có sẵn Token
const apiClient = axios.create({
  baseURL: '/api/admin',
  headers: { 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` }
});

const fetchData = async () => {
  try {
    const res = await apiClient.get(`/entity/${activeTab.value}`);
    items.value = res.data.data;
  } catch (error) {
    console.error("Lỗi 401: Kiểm tra lại Token hoặc đăng nhập!");
  }
};

watch(activeTab, fetchData, { immediate: true });
</script>