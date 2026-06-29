<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold mb-6">Quản lý Thực thể</h2>

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
            <th class="border p-2">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td class="border p-2 text-center">{{ item.id }}</td>
            <td class="border p-2">{{ item.name }}</td>
            <td class="border p-2">{{ item.base_hp }}</td>
            <td class="border p-2 text-center">
              <button @click="deleteItem(item.id)" class="text-red-500 hover:text-red-700">Xóa</button>
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

const activeTab = ref('monster');
const items = ref([]);

// Cấu hình axios với interceptor để tự động thêm token
const apiClient = axios.create({
  baseURL: '/api/admin',
  headers: { 'Accept': 'application/json' }
});

// Thêm token vào mỗi request
apiClient.interceptors.request.use(config => {
  const token = localStorage.getItem('admin_token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

const fetchData = async () => {
  try {
    // Gọi đúng route đã định nghĩa trong Laravel: /api/admin/entity/monster hoặc /boss
    const res = await apiClient.get(`/entity/${activeTab.value}`);
    items.value = res.data.data;
  } catch (error) {
    if (error.response?.status === 401) {
      alert("Phiên đăng nhập hết hạn!");
    } else {
      console.error("Lỗi:", error);
    }
  }
};

const deleteItem = async (id) => {
  if (!confirm('Bạn có chắc chắn muốn xóa?')) return;
  try {
    await apiClient.delete(`/entity/${activeTab.value}/${id}`);
    fetchData(); // Tải lại bảng sau khi xóa
  } catch (err) {
    alert("Xóa thất bại!");
  }
};

watch(activeTab, fetchData, { immediate: true });
</script>