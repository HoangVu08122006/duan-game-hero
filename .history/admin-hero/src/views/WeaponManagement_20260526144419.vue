<template>
  <div class="weapon-container" v-loading="loading">
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between;">
      <h2>Quản lý vũ khí</h2>
      <el-button type="primary" @click="showDialog()">+ Thêm Vũ Khí</el-button>
    </div>
    
    <el-table :data="weapons" border style="width: 100%">
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="name" label="Tên Vũ Khí" />
      <el-table-column prop="base_attack" label="Sức mạnh" />
      <el-table-column prop="required_hero_level" label="Cấp độ yêu cầu" />
      <el-table-column label="Hành động" width="200">
        <template #default="scope">
          <el-button size="small" @click="showDialog(scope.row)">Sửa</el-button>
          <el-button size="small" type="danger" @click="confirmDelete(scope.row.id)">Xóa</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="dialogVisible" :title="form.id ? 'Sửa Vũ Khí' : 'Thêm Vũ Khí'" width="400px">
      <el-form :model="form" label-position="top">
        <el-form-item label="Tên vũ khí">
          <el-input v-model="form.name" placeholder="Nhập tên..." />
        </el-form-item>
        <el-form-item label="Sức mạnh">
          <el-input-number v-model="form.base_attack" :min="0" style="width: 100%" />
        </el-form-item>
        <el-form-item label="Cấp độ yêu cầu">
          <el-input-number v-model="form.required_hero_level" :min="1" style="width: 100%" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">Hủy</el-button>
        <el-button type="primary" :loading="saving" @click="saveWeapon">Lưu thay đổi</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';
import '@/echo'; // Đảm bảo đã import cấu hình Echo

const weapons = ref([]);
const dialogVisible = ref(false);
const loading = ref(false);
const saving = ref(false);
const form = ref({ id: null, name: '', base_attack: 0, required_hero_level: 1 });
const API_URL = 'http://26.103.188.167:8000/api/weapons';

const fetchWeapons = async () => {
  loading.value = true;
  try {
    const res = await axios.get(API_URL);
    weapons.value = res.data;
  } catch (e) {
    ElMessage.error('Không thể tải dữ liệu!');
  } finally {
    loading.value = false;
  }
};

const showDialog = (row = null) => {
  form.value = row ? { ...row } : { id: null, name: '', base_attack: 0, required_hero_level: 1 };
  dialogVisible.value = true;
};

const saveWeapon = async () => {
  if (!form.value.name) return ElMessage.warning('Vui lòng nhập tên vũ khí!');
  
  saving.value = true;
  try {
    if (form.value.id) {
      await axios.put(`${API_URL}/${form.value.id}`, form.value);
      ElMessage.success('Cập nhật thành công!');
    } else {
      await axios.post(API_URL, form.value);
      ElMessage.success('Thêm mới thành công!');
    }
    dialogVisible.value = false;
    await fetchWeapons();
  } catch (e) {
    ElMessage.error('Có lỗi xảy ra!');
  } finally {
    saving.value = false;
  }
};

const confirmDelete = (id) => {
  ElMessageBox.confirm('Bạn có chắc chắn muốn xóa?', 'Cảnh báo', { type: 'warning' })
    .then(async () => {
      await axios.delete(`${API_URL}/${id}`);
      ElMessage.success('Đã xóa!');
      fetchWeapons();
    });
};

onMounted(() => {
  fetchWeapons();
  
  // Lắng nghe sự kiện Real-time
  (window as any).Echo.channel('weapons-channel')
    .listen('.WeaponUpdatedEvent', (e: any) => {
      console.log('Update từ Admin:', e);
      fetchWeapons();
    });
});
</script>