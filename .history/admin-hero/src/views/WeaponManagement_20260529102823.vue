<template>
  <div class="weapon-container" v-loading="loading">
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between;">
      <h2>Quản lý vũ khí</h2>
      <el-button type="primary" @click="showDialog()">+ Thêm Vũ Khí</el-button>
    </div>
    
    <el-table :data="weapons" border style="width: 100%">
      <el-table-column label="Hình ảnh" width="100">
        <template #default="scope">
          <el-image 
            :src="'http://26.103.188.167:8000' + scope.row.image_weapon" 
            fit="cover" 
            style="width: 50px; height: 50px; border-radius: 4px;"
          >
            <template #error>
              <div style="background: #e0e0e0; height: 100%; display: flex; align-items: center; justify-content: center;">N/A</div>
            </template>
          </el-image>
        </template>
      </el-table-column>
      <el-table-column prop="name" label="Tên Vũ Khí" />
      <el-table-column prop="base_attack" label="Sức mạnh" />
      <el-table-column prop="required_hero_level" label="Cấp độ" />
      <el-table-column label="Hành động" width="220">
        <template #default="scope">
          <el-button size="small" @click="viewDetail(scope.row)">Xem</el-button>
          <el-button size="small" @click="showDialog(scope.row)">Sửa</el-button>
          <el-button size="small" type="danger" @click="confirmDelete(scope.row.id)">Xóa</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="dialogVisible" :title="form.id ? 'Sửa Vũ Khí' : 'Thêm Vũ Khí'" width="400px">
      <el-form :model="form" label-position="top">
        <el-form-item label="Tên vũ khí"><el-input v-model="form.name" /></el-form-item>
        <el-form-item label="Ảnh vũ khí">
          <input type="text" @change="onFileChange" accept="image/*" />
        </el-form-item>
        <el-form-item label="Sức mạnh"><el-input-number v-model="form.base_attack" style="width: 100%" /></el-form-item>
        <el-form-item label="Cấp độ yêu cầu"><el-input-number v-model="form.required_hero_level" style="width: 100%" /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">Hủy</el-button>
        <el-button type="primary" :loading="saving" @click="saveWeapon">Lưu thay đổi</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="detailVisible" title="Chi tiết" width="300px">
      <div style="text-align: center;">
        <img :src="'http://26.103.188.167:8000' + detailForm.image_weapon" style="width: 150px; border-radius: 8px;">
        <h3>{{ detailForm.name }}</h3>
        <p>Sức mạnh: {{ detailForm.base_attack }}</p>
        <p>Cấp độ yêu cầu: {{ detailForm.required_hero_level }}</p>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';
import '@/echo';

const weapons = ref([]);
const dialogVisible = ref(false);
const detailVisible = ref(false);
const loading = ref(false);
const saving = ref(false);
const selectedFile = ref(null);
const form = ref({ id: null, name: '', base_attack: 0, required_hero_level: 1 });
const detailForm = ref({});
const API_URL = 'http://26.103.188.167:8000/api/weapons';

const onFileChange = (e) => { selectedFile.value = e.target.files[0]; };

const fetchWeapons = async () => {
  loading.value = true;
  try {
    const res = await axios.get(API_URL);
    weapons.value = res.data;
  } catch (e) { ElMessage.error('Lỗi tải dữ liệu!'); } finally { loading.value = false; }
};

const showDialog = (row = null) => {
  form.value = row ? { ...row } : { id: null, name: '', base_attack: 0, required_hero_level: 1 };
  selectedFile.value = null;
  dialogVisible.value = true;
};

const viewDetail = (row) => {
  detailForm.value = row;
  detailVisible.value = true;
};

const saveWeapon = async () => {
  saving.value = true;
  const formData = new FormData();
  Object.keys(form.value).forEach(k => formData.append(k, form.value[k]));
  if (selectedFile.value) formData.append('image_weapon', selectedFile.value);
  if (form.value.id) formData.append('_method', 'PUT');

  try {
    await axios.post(form.value.id ? `${API_URL}/${form.value.id}` : API_URL, formData, { headers: {'Content-Type': 'multipart/form-data'} });
    ElMessage.success('Thành công!');
    dialogVisible.value = false;
    await fetchWeapons();
  } catch (e) { ElMessage.error('Có lỗi xảy ra!'); } finally { saving.value = false; }
};

const confirmDelete = (id) => {
  ElMessageBox.confirm('Xóa vũ khí này?', 'Cảnh báo', { type: 'warning' }).then(async () => {
    await axios.delete(`${API_URL}/${id}`);
    await fetchWeapons();
  });
};

onMounted(() => {
  fetchWeapons();
  window.Echo.channel('weapons-channel').listen('.WeaponUpdatedEvent', () => fetchWeapons());
});
</script>