<template>
  <div class="weapon-container">
    <el-button type="primary" @click="showDialog()" style="margin-bottom: 20px;">Thêm Vũ Khí</el-button>
    
    <el-table :data="weapons" border style="width: 100%">
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="name" label="Tên Vũ Khí" />
      <el-table-column prop="base_attack" label="Sức mạnh" />
      <el-table-column prop="required_hero_level" label="Cấp độ yêu cầu" />
      <el-table-column label="Hành động" width="200">
        <template #default="scope">
          <el-button size="small" @click="showDialog(scope.row)">Sửa</el-button>
          <el-button size="small" type="danger" @click="deleteWeapon(scope.row.id)">Xóa</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="dialogVisible" :title="form.id ? 'Sửa Vũ Khí' : 'Thêm Vũ Khí'">
      <el-form :model="form">
        <el-form-item label="Tên vũ khí">
          <el-input v-model="form.name" />
        </el-form-item>
        <el-form-item label="Sức mạnh">
          <el-input-number v-model="form.base_attack" />
        </el-form-item>
        <el-form-item label="Cấp độ yêu cầu">
          <el-input-number v-model="form.required_hero_level" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">Hủy</el-button>
        <el-button type="primary" @click="saveWeapon">Lưu</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { ElMessage } from 'element-plus';

const weapons = ref([]);
const dialogVisible = ref(false);
const form = ref({ id: null, name: '', base_attack: 0, required_hero_level: 0 });
const API_URL = 'http://26.103.188.167:8000/api/weapons'; // Cập nhật đúng đường dẫn

const fetchWeapons = async () => {
  const res = await axios.get(API_URL);
  weapons.value = res.data;
};

const showDialog = (row = null) => {
  form.value = row ? { ...row } : { name: '', base_attack: 0, required_hero_level: 0 };
  dialogVisible.value = true;
};

const saveWeapon = async () => {
  if (form.value.id) {
    await axios.put(`${API_URL}/${form.value.id}`, form.value);
  } else {
    await axios.post(API_URL, form.value);
  }
  dialogVisible.value = false;
  fetchWeapons();
  ElMessage.success('Thành công!');
};

const deleteWeapon = async (id) => {
  await axios.delete(`${API_URL}/${id}`);
  fetchWeapons();
};

onMounted(fetchWeapons);
</script>