<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold mb-4 capitalize">Quản lý {{ type }}</h2>
    
    <div class="flex gap-4 mb-6">
      <button @click="view = 'list'" class="btn-primary">Danh sách chính</button>
      <button @click="view = 'draft'" class="btn-secondary">Bản nháp chờ duyệt</button>
    </div>

    <table v-if="view === 'list'" class="min-w-full bg-white shadow rounded">
      <thead>
        <tr><th>ID</th><th>Tên</th><th>HP</th><th>Hành động</th></tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="item.id">
          <td>{{ item.id }}</td>
          <td>{{ item.name }}</td>
          <td>{{ item.base_hp }}</td>
          <td>
            <button @click="editItem(item)" class="text-blue-500">Sửa</button>
            <button @click="deleteItem(item.id)" class="text-red-500 ml-2">Xóa</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-else class="bg-gray-100 p-4 rounded">
      <h3 class="font-bold">Tạo bản nháp mới</h3>
      <input v-model="formData.name" placeholder="Tên" class="block w-full mb-2 p-2">
      <input v-model="formData.base_hp" type="number" placeholder="HP" class="block w-full mb-2 p-2">
      <button @click="saveDraft" class="bg-green-600 text-white p-2">Lưu bản nháp</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps(['type']); // 'monster' hoặc 'boss'
const view = ref('list');
const items = ref([]);
const formData = ref({ name: '', base_hp: 0 });

// Lấy dữ liệu từ API
const fetchData = async () => {
  const res = await axios.get(`/api/admin/entity/${props.type}`);
  items.value = res.data.data;
};

// Gửi bản nháp về API
const saveDraft = async () => {
  await axios.post(`/api/admin/entity/${props.type}/draft`, {
    ...formData.value,
    id: formData.value.id || null
  });
  alert('Đã lưu bản nháp!');
};

onMounted(fetchData);
</script>