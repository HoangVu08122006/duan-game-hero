<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
      <h1 class="text-3xl font-bold mb-6 text-gray-800 capitalize">Quản lý {{ currentType }}</h1>

      <div class="flex gap-2 mb-6">
        <button v-for="type in ['monster', 'boss']" :key="type"
          @click="changeType(type)"
          :class="currentType === type ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border'"
          class="px-6 py-2 rounded-lg font-semibold capitalize transition shadow-sm">
          {{ type === 'monster' ? 'Quái vật' : 'Boss' }}
        </button>
      </div>

      <button @click="openModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg mb-6 shadow-sm transition font-medium">
        + Thêm {{ currentType === 'monster' ? 'quái vật' : 'boss' }} mới
      </button>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="p-4 text-gray-600 font-semibold">ID</th>
              <th class="p-4 text-gray-600 font-semibold">Tên</th>
              <th class="p-4 text-gray-600 font-semibold">HP</th>
              <th class="p-4 text-gray-600 font-semibold">Atk</th>
              <th class="p-4 text-gray-600 font-semibold">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50/50 transition">
              <td class="p-4 text-gray-700">{{ item.id }}</td>
              <td class="p-4 font-semibold text-gray-800">{{ item.name }}</td>
              <td class="p-4 text-gray-600">{{ item.base_hp }}</td>
              <td class="p-4 text-gray-600">{{ item.base_atk || '—' }}</td>
              <td class="p-4 flex gap-3">
                <button @click="openModal(item)" class="text-blue-600 hover:underline font-medium">Sửa</button>
                <button @click="deleteItem(item.id)" class="text-red-600 hover:underline font-medium">Xóa</button>
              </td>
            </tr>
            <tr v-if="items.length === 0">
              <td colspan="5" class="p-6 text-center text-gray-400">Không có dữ liệu</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-white p-8 rounded-2xl w-full max-w-md shadow-xl animate-fade-in">
        <h2 class="text-xl font-bold mb-6 text-gray-800">{{ formData.id ? 'Sửa' : 'Thêm' }} {{ currentType === 'monster' ? 'Quái vật' : 'Boss' }}</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tên</label>
            <input v-model="formData.name" placeholder="Nhập tên..." class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Base HP</label>
            <input v-model="formData.base_hp" type="number" placeholder="Nhập HP..." class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Base ATK</label>
            <input v-model="formData.base_atk" type="number" placeholder="Nhập ATK..." class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" />
          </div>
          <div class="flex justify-end gap-3 pt-6">
            <button @click="showModal = false" class="px-5 py-2.5 text-gray-700 font-medium hover:bg-gray-100 rounded-lg transition">Hủy</button>
            <button @click="saveDraft" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium transition shadow-sm">Lưu bản nháp</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Khởi tạo cấu hình axios kết nối trực tiếp tới Laravel (port 8000)
const apiClient = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
});

const currentType = ref('monster');
const items = ref([]);
const showModal = ref(false);
const formData = ref({ id: null, name: '', base_hp: '', base_atk: '' });

// Lấy danh sách ghi nhận log ngầm từ Backend
const fetchData = async () => {
  try {
    // Lấy cookie xác thực Sanctum trước
    await apiClient.get('/sanctum/csrf-cookie');
    
    const { data } = await apiClient.get(`/api/admin/entity/${currentType.value}`);
    items.value = data.data;
  } catch (err) {
    if (err.response?.status === 401) {
      alert('Phiên làm việc hết hạn hoặc bạn chưa đăng nhập. Vui lòng đăng nhập hệ thống trước!');
    } else {
      alert('Không thể tải dữ liệu thực thể!');
    }
  }
};

const changeType = (type) => {
  currentType.value = type;
  fetchData();
};

const openModal = (item = null) => {
  formData.value = item ? { ...item } : { name: '', base_hp: '', base_atk: '' };
  showModal.value = true;
};

// Gửi bản nháp (log CREATE_DRAFT hoặc UPDATE_DRAFT tự động lưu ở server)
const saveDraft = async () => {
  try {
    await apiClient.post(`/api/admin/entity/${currentType.value}/draft`, formData.value);
    alert('Đã tạo bản nháp thành công và log đã được lưu lại!');
    showModal.value = false;
    fetchData();
  } catch (err) { 
    alert('Lỗi: ' + (err.response?.data.message || 'Có lỗi xảy ra')); 
  }
};

// Xóa dữ liệu trực tiếp (log DELETE tự động lưu ở server)
const deleteItem = async (id) => {
  if (!confirm('Xác nhận xóa chính thức bản ghi này? Hành động này sẽ lưu lại log hệ thống!')) return;
  try {
    await apiClient.delete(`/api/admin/entity/${currentType.value}/${id}`);
    alert('Đã xóa thành công!');
    fetchData();
  } catch (err) { 
    alert('Không thể xóa dữ liệu!'); 
  }
};

onMounted(fetchData);
</script>