<template>
  <div class="weapon-container" v-loading="loading">
    <div class="header-section">
      <h2 style="margin: 0;">Quản lý vũ khí</h2>
      <el-button type="primary" @click="showDialog()">+ Thêm Vũ Khí</el-button>
    </div>

    <el-table :data="weapons" border style="width: 100%" class="custom-table">
      <el-table-column label="Hình ảnh" width="100" align="center">
        <template #default="scope">
          <el-image 
            :src="'http://26.103.188.167:8000' + scope.row.image_weapon" 
            fit="cover" 
            class="weapon-image"
          >
            <template #error>
              <div class="image-error">N/A</div>
            </template>
          </el-image>
        </template>
      </el-table-column>
      <el-table-column prop="name" label="Tên Vũ Khí" />
      <el-table-column prop="base_attack" label="Sức mạnh" align="center" />
      <el-table-column prop="required_hero_level" label="Cấp độ" align="center" />
      <el-table-column label="Hành động" width="250" align="center">
        <template #default="scope">
          <el-button size="small" @click="viewDetail(scope.row)">Xem</el-button>
          <el-button size="small" type="primary" plain @click="showDialog(scope.row)">Sửa</el-button>
          <el-button size="small" type="danger" plain @click="confirmDelete(scope.row.id)">Xóa</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="dialogVisible" :title="form.id ? 'Sửa Vũ Khí' : 'Thêm Vũ Khí'" width="450px" destroy-on-close>
      <el-form :model="form" label-position="top">
        <el-form-item label="Tên vũ khí">
          <el-input v-model="form.name" placeholder="Nhập tên vũ khí" />
        </el-form-item>
        
        <el-form-item label="Ảnh vũ khí">
          <input type="file" @change="onFileChange" accept="image/*" />
          <div v-if="previewImage" class="preview-container">
            <img :src="previewImage" />
          </div>
        </el-form-item>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Sức mạnh">
              <el-input-number v-model="form.base_attack" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Cấp độ yêu cầu">
              <el-input-number v-model="form.required_hero_level" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">Hủy</el-button>
        <el-button type="primary" :loading="saving" @click="saveWeapon">Lưu thay đổi</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="detailVisible" title="Chi tiết" width="350px">
      <div class="detail-content">
        <img :src="'http://26.103.188.167:8000' + detailForm.image_weapon" class="detail-img">
        <h3>{{ detailForm.name }}</h3>
        <p>Sức mạnh: <strong>{{ detailForm.base_attack }}</strong></p>
        <p>Cấp độ yêu cầu: <strong>{{ detailForm.required_hero_level }}</strong></p>
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
const previewImage = ref(null);
const form = ref({ id: null, name: '', base_attack: 0, required_hero_level: 1 });
const detailForm = ref({});
const API_URL = 'http://26.103.188.167:8000/api/weapons';

const onFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedFile.value = file;
    previewImage.value = URL.createObjectURL(file);
  }
};

const fetchWeapons = async () => {
  loading.value = true;
  try {
    const res = await axios.get(API_URL);
    weapons.value = res.data;
  } catch (e) { ElMessage.error('Lỗi tải dữ liệu!'); } finally { loading.value = false; }
};

const showDialog = (row = null) => {
  form.value = row ? { ...row } : { id: null, name: '', base_attack: 0, required_hero_level: 1 };
  previewImage.value = row ? 'http://26.103.188.167:8000' + row.image_weapon : null;
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
  formData.append('name', form.value.name);
  formData.append('base_attack', form.value.base_attack);
  formData.append('required_hero_level', form.value.required_hero_level);
  if (selectedFile.value) formData.append('image_weapon', selectedFile.value);

  try {
    const url = form.value.id ? `${API_URL}/${form.value.id}` : API_URL;
    await axios.post(url, formData, { headers: { 'Content-Type': 'multipart/form-data' } });
    ElMessage.success('Thành công!');
    dialogVisible.value = false;
    await fetchWeapons();
  } catch (e) { ElMessage.error('Lỗi server'); } finally { saving.value = false; }
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

<style scoped>
.weapon-container { padding: 20px; }
.header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 15px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.weapon-image { width: 50px; height: 50px; border-radius: 4px; }
.image-error { background: #f5f7fa; height: 50px; display: flex; align-items: center; justify-content: center; color: #999; }
.preview-container img { width: 100px; height: 100px; object-fit: cover; margin-top: 10px; border-radius: 4px; border: 1px solid #ddd; }
.detail-content { text-align: center; }
.detail-img { width: 150px; border-radius: 8px; margin-bottom: 10px; }
</style>