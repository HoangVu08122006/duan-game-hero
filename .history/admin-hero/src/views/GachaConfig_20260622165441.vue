<template>
  <div class="container">
    <h1>Quản lý Cấu hình Gacha</h1>
    
    <div class="form-section">
      <h3>{{ editingId ? 'Sửa cấu hình' : 'Thêm mới' }}</h3>
      <input v-model="form.reward_type" placeholder="Reward Type">
      <input v-model.number="form.reward_id" placeholder="Reward ID" type="number">
      <input v-model.number="form.amount" placeholder="Amount" type="number">
      <input v-model.number="form.weight" placeholder="Weight" type="number">
      <input v-model="form.description" placeholder="Description">
      <button @click="saveConfig">{{ editingId ? 'Cập nhật' : 'Thêm' }}</button>
    </div>

    <table>
      <thead>
        <tr>
          <th>ID</th><th>Type</th><th>ID</th><th>Amount</th><th>Weight</th><th>Desc</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in configs" :key="item.id">
          <td>{{ item.id }}</td>
          <td>{{ item.reward_type }}</td>
          <td>{{ item.reward_id }}</td>
          <td>{{ item.amount }}</td>
          <td>{{ item.weight }}</td>
          <td>{{ item.description }}</td>
          <td>
            <button @click="editItem(item)">Sửa</button>
            <button @click="deleteItem(item.id)">Xóa</button>
          </td>
        </tr>
      </tbody>
    </table>

    <h3>Nhật ký thay đổi (Logs)</h3>
    <table>
      <tr v-for="log in logs" :key="log.id">
        <td>{{ log.created_at }}</td>
        <td>{{ log.action }}</td>
        <td>{{ log.admin_name }}</td>
      </tr>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const api = axios.create({ baseURL: 'http://localhost:8000/admin/gacha-config' });

const configs = ref([]);
const logs = ref([]);
const editingId = ref(null);
const form = ref({ reward_type: '', reward_id: null, amount: 0, weight: 0, description: '' });

const fetchData = async () => {
  const { data } = await api.get('/');
  configs.value = data.data;
  const logRes = await api.get('/logs');
  logs.value = logRes.data.data;
};

const saveConfig = async () => {
  if (editingId.value) {
    await api.put(`/${editingId.value}`, form.value);
  } else {
    await api.post('/', form.value);
  }
  editingId.value = null;
  fetchData();
};

const deleteItem = async (id) => {
  if(confirm('Bạn chắc chắn muốn xóa?')) {
    await api.delete(`/${id}`);
    fetchData();
  }
};

const editItem = (item) => {
  editingId.value = item.id;
  form.value = { ...item };
};

onMounted(fetchData);
</script>