<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Quản lý Thực thể Game</h1>

    <div class="flex gap-4 mb-6">
      <button @click="currentType = 'monster'" :class="currentType === 'monster' ? 'bg-blue-600 text-white' : 'bg-gray-200'" class="px-4 py-2 rounded">Quái vật</button>
      <button @click="currentType = 'boss'" :class="currentType === 'boss' ? 'bg-blue-600 text-white' : 'bg-gray-200'" class="px-4 py-2 rounded">Boss</button>
    </div>

    <button @click="openModal()" class="bg-green-600 text-white px-4 py-2 rounded mb-4">Thêm mới {{ currentType }}</button>

    <table class="w-full border-collapse border">
      <thead>
        <tr class="bg-gray-100">
          <th class="border p-2">Tên</th>
          <th class="border p-2">HP</th>
          <th class="border p-2">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="item.id">
          <td class="border p-2">{{ item.name }}</td>
          <td class="border p-2">{{ item.base_hp }}</td>
          <td class="border p-2 flex gap-2">
            <button @click="openModal(item)" class="text-blue-500">Sửa</button>
            <button @click="deleteItem(item.id)" class="text-red-500">Xóa</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
      <div class="bg-white p-6 rounded w-96">
        <h2 class="text-lg font-bold mb-4">{{ formData.id ? 'Sửa' : 'Thêm' }} {{ currentType }}</h2>
        <input v-model="formData.name" placeholder="Tên" class="w-full border p-2 mb-2" />
        <input v-model="formData.base_hp" type="number" placeholder="Base HP" class="w-full border p-2 mb-2" />
        
        <div class="flex justify-end gap-2">
          <button @click="showModal = false" class="px-4 py-2 bg-gray-400 text-white rounded">Hủy</button>
          <button @click="saveDraft" class="px-4 py-2 bg-yellow-600 text-white rounded">Lưu Bản Nháp</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';

const currentType = ref('monster');
const items = ref([]);
const showModal = ref(false);
const formData = ref({ id: null, name: '', base_hp: '' });

// Tải dữ liệu
const fetchData = async () => {
  const res = await axios.get(`/api/admin/entity/${currentType.value}`);
  items.value = res.data.data;
};

// Lưu bản nháp (Draft)
const saveDraft = async () => {
  try {
    await axios.post(`/api/admin/entity/${currentType.value}/draft`, formData.value);
    alert('Đã lưu bản nháp thành công!');
    showModal.value = false;
  } catch (e) { alert('Lỗi xác thực!'); }
};

// Xóa
const deleteItem = async (id) => {
  if (confirm('Bạn có chắc muốn xóa?')) {
    await axios.delete(`/api/admin/entity/${currentType.value}/${id}`);
    fetchData();
  }
};

const openModal = (item = null) => {
  formData.value = item ? { ...item } : { name: '', base_hp: '' };
  showModal.value = true;
};

watch(currentType, fetchData);
onMounted(fetchData);
</script>