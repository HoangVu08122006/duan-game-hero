<template>
  <div class="p-6 max-w-6xl mx-auto">
    <h2 class="text-3xl font-bold mb-6 text-gray-800">Quản lý Thực thể Game</h2>

    <div class="flex border-b mb-6">
      <button v-for="tab in ['monster', 'boss']" :key="tab"
        @click="activeTab = tab"
        :class="['px-8 py-3 font-bold capitalize transition', activeTab === tab ? 'border-b-4 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-blue-400']">
        {{ tab }}s
      </button>
    </div>

    <div class="bg-white p-6 shadow-lg rounded-lg mb-8">
      <div class="flex justify-between mb-4">
        <h3 class="text-xl font-semibold">Danh sách {{ activeTab }}s</h3>
        <button @click="showModal = true" class="bg-blue-600 text-white px-4 py-2 rounded shadow">Thêm mới</button>
      </div>

      <table class="w-full text-left border-collapse">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-3 border">ID</th>
            <th class="p-3 border">Tên</th>
            <th class="p-3 border">HP</th>
            <th class="p-3 border">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="p-3 border">{{ item.id }}</td>
            <td class="p-3 border">{{ item.name }}</td>
            <td class="p-3 border">{{ item.base_hp }}</td>
            <td class="p-3 border">
              <button @click="editItem(item)" class="text-blue-600 mr-4">Sửa</button>
              <button @click="deleteItem(item.id)" class="text-red-600">Xóa</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="bg-gray-800 text-white p-6 rounded-lg">
      <h3 class="text-lg font-bold mb-4 text-green-400">📜 Nhật ký hệ thống (Admin Logs)</h3>
      <div class="h-40 overflow-y-auto text-sm font-mono">
        <p v-for="log in logs" :key="log.id" class="mb-1">
          [{{ log.created_at }}] {{ log.action }} - {{ log.description }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';

const activeTab = ref('monster');
const items = ref([]);
const logs = ref([]);

// Cấu hình Axios
const api = axios.create({
  baseURL: '/api/admin',
  headers: { 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` }
});

const fetchData = async () => {
  const [res, logRes] = await Promise.all([
    api.get(`/entity/${activeTab.value}`),
    api.get(`/logs/${activeTab.value}`) // Bạn cần tạo route này trả về AdminLog::latest()->get()
  ]);
  items.value = res.data.data;
  logs.value = logRes.data.data;
};

const deleteItem = async (id) => {
  if (confirm('Xóa thực thể này?')) {
    await api.delete(`/entity/${activeTab.value}/${id}`);
    fetchData();
  }
};

watch(activeTab, fetchData, { immediate: true });
</script>