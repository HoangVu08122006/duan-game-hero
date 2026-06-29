<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
      <h1 class="text-3xl font-bold mb-6 text-gray-800">Quản lý Thực thể Game</h1>

      <div class="flex gap-2 mb-6">
        <button v-for="type in ['monster', 'boss']" :key="type"
          @click="changeType(type)"
          :class="currentType === type ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border'"
          class="px-6 py-2 rounded-lg font-semibold capitalize transition">
          {{ type }}s
        </button>
      </div>

      <button @click="openModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded mb-6 transition">
        + Thêm {{ currentType }} mới
      </button>

      <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-left">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-4">ID</th>
              <th class="p-4">Tên</th>
              <th class="p-4">HP</th>
              <th class="p-4">Atk</th>
              <th class="p-4">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in items" :key="item.id" class="border-t hover:bg-gray-50">
              <td class="p-4">{{ item.id }}</td>
              <td class="p-4 font-medium">{{ item.name }}</td>
              <td class="p-4">{{ item.base_hp }}</td>
              <td class="p-4">{{ item.base_atk || item.base_attack }}</td>
              <td class="p-4 flex gap-2">
                <button @click="openModal(item)" class="text-blue-600 hover:underline">Sửa</button>
                <button @click="deleteItem(item.id)" class="text-red-600 hover:underline">Xóa</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4">
      <div class="bg-white p-8 rounded-2xl w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">{{ formData.id ? 'Sửa' : 'Thêm' }} {{ currentType }}</h2>
        <div class="space-y-4">
          <input v-model="formData.name" placeholder="Tên" class="w-full border p-2 rounded" />
          <input v-model="formData.base_hp" type="number" placeholder="Base HP" class="w-full border p-2 rounded" />
          <input v-model="formData.base_atk" type="number" placeholder="Base ATK" class="w-full border p-2 rounded" />
          <div class="flex justify-end gap-3 pt-4">
            <button @click="showModal = false" class="px-4 py-2 text-gray-600">Hủy</button>
            <button @click="saveDraft" class="bg-blue-600 text-white px-6 py-2 rounded-lg">Lưu vào nháp</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const currentType = ref('monster');
const items = ref([]);
const showModal = ref(false);
const formData = ref({ id: null, name: '', base_hp: '', base_atk: '' });

const fetchData = async () => {
  try {
    const { data } = await axios.get(`/api/admin/entity/${currentType.value}`);
    items.value = data.data;
  } catch (err) { alert('Không thể tải dữ liệu!'); }
};

const changeType = (type) => {
  currentType.value = type;
  fetchData();
};

const openModal = (item = null) => {
  formData.value = item ? { ...item } : { name: '', base_hp: '', base_atk: '' };
  showModal.value = true;
};

const saveDraft = async () => {
  try {
    await axios.post(`/api/admin/entity/${currentType.value}/draft`, formData.value);
    alert('Đã gửi yêu cầu lưu bản nháp!');
    showModal.value = false;
  } catch (err) { alert('Lỗi: ' + (err.response?.data.message || 'Có lỗi xảy ra')); }
};

const deleteItem = async (id) => {
  if (!confirm('Xác nhận xóa?')) return;
  await axios.delete(`/api/admin/entity/${currentType.value}/${id}`);
  fetchData();
};

onMounted(fetchData);
</script>