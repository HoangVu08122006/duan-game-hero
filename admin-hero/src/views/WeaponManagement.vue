<template>
  <div class="weapon-container" v-loading="loading">
    <div class="header-card">
      <h2 style="margin: 0; display: flex; align-items: center; gap: 10px;">
        ⚔️ Quản lý kho vũ khí 🛡️
      </h2>
      <el-button type="primary" :icon="Plus" @click="showDialog()">Thêm vũ khí mới</el-button>
    </div>

    <el-table :data="weapons" border stripe class="weapon-table">
      <el-table-column label="📸 Ảnh" width="100" align="center">
        <template #default="scope">
          <el-image 
            :src="'http://26.103.188.167:8000' + scope.row.image_weapon" 
            fit="cover" 
            class="table-img"
            :preview-src-list="['http://26.103.188.167:8000' + scope.row.image_weapon]"
          >
            <template #error><div class="image-error">🚫</div></template>
          </el-image>
        </template>
      </el-table-column>
      <el-table-column prop="name" label="🗡️ Tên Vũ Khí" />
      <el-table-column prop="base_attack" label="🔥 Sức mạnh" align="center" width="150" />
      <el-table-column prop="required_hero_level" label="📈 Cấp độ yêu cầu" align="center" width="150" />
      <el-table-column label="⚙️ Hành động" width="250" align="center">
        <template #default="scope">
          <el-button size="small" type="primary" plain :icon="Edit" @click="showDialog(scope.row)">Sửa</el-button>
          <el-button size="small" type="danger" plain :icon="Delete" @click="confirmDelete(scope.row.id)">Xóa</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="dialogVisible" :title="form.id ? '✏️ Chỉnh sửa vũ khí' : '➕ Thêm vũ khí mới'" width="450px">
      <el-form :model="form" label-position="top">
        <el-form-item label="📝 Tên vũ khí">
          <el-input v-model="form.name" placeholder="Nhập tên..." />
        </el-form-item>
        
        <el-form-item label="🖼️ Ảnh vũ khí">
          <input type="file" @change="onFileChange" accept="image/*" />
          <div v-if="previewImage" class="preview-box">
            <img :src="previewImage" />
          </div>
        </el-form-item>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="🔥 Sức mạnh">
              <el-input-number v-model="form.base_attack" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="📈 Cấp độ yêu cầu">
              <el-input-number v-model="form.required_hero_level" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">❌ Hủy</el-button>
        <el-button type="primary" :loading="saving" @click="saveWeapon">✅ Lưu dữ liệu</el-button>
      </template>
    </el-dialog>

    
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';
import { Plus, Edit, Delete, View } from '@element-plus/icons-vue';
import '@/echo';

const weapons = ref([]);
const dialogVisible = ref(false);
const loading = ref(false);
const saving = ref(false);
const selectedFile = ref(null);
const previewImage = ref(null);
const form = ref({ id: null, name: '', base_attack: 0, required_hero_level: 1 });
const API_URL = 'http://26.103.188.167:8000/api/weapons';

const onFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedFile.value = file;
    previewImage.value = URL.createObjectURL(file);
  }
};

const showDialog = (row = null) => {
  form.value = row ? { ...row } : { id: null, name: '', base_attack: 0, required_hero_level: 1 };
  previewImage.value = row ? 'http://26.103.188.167:8000' + row.image_weapon : null;
  selectedFile.value = null;
  dialogVisible.value = true;
};

const fetchWeapons = async () => {
  loading.value = true;
  try {
    const res = await axios.get(API_URL);
    weapons.value = res.data;
  } catch (e) { ElMessage.error('Lỗi tải dữ liệu!'); } finally { loading.value = false; }
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
    ElMessage.success('Cập nhật thành công! ✨');
    dialogVisible.value = false;
    await fetchWeapons();
  } catch (e) { ElMessage.error('Có lỗi xảy ra!'); } finally { saving.value = false; }
};

const confirmDelete = (id) => {
  ElMessageBox.confirm('Bạn có chắc muốn xóa vũ khí này không? 🗑️', 'Cảnh báo', { type: 'warning' })
    .then(async () => {
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
.weapon-container { padding: 20px; background-color: #f4f7f6; min-height: 100vh; }
.header-card { 
  display: flex; justify-content: space-between; align-items: center; 
  background: white; padding: 20px; border-radius: 12px; 
  box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; 
}
.weapon-table { border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.table-img { width: 50px; height: 50px; border-radius: 8px; cursor: pointer; transition: 0.3s; }
.table-img:hover { transform: scale(1.1); }
.image-error { background: #eee; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 8px; }
.preview-box img { width: 120px; height: 120px; object-fit: cover; border-radius: 8px; margin-top: 10px; border: 2px solid #ddd; }
</style>