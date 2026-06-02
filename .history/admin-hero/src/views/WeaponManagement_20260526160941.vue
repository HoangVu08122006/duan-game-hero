<template>
  <div class="weapon-container" v-loading="loading">
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between;">
      <h2>Quản lý vũ khí</h2>
      <el-button type="primary" @click="showDialog()">+ Thêm Vũ Khí</el-button>
    </div>
    
    <el-table :data="weapons" border style="width: 100%">
      <el-table-column label="Hình ảnh" width="100">
        <template #default="scope">
          <el-image :src="'http://26.103.188.167:8000' + scope.row.image_weapon" fit="cover" style="width: 50px; height: 50px; border-radius: 4px;">
            <template #error><div style="background: #e0e0e0; display:flex; align-items:center; justify-content:center; height:100%">N/A</div></template>
          </el-image>
        </template>
      </el-table-column>
      <el-table-column prop="name" label="Tên" />
      <el-table-column label="Hành động" width="250">
        <template #default="scope">
          <el-button size="small" @click="viewDetail(scope.row)">Xem</el-button>
          <el-button size="small" @click="showDialog(scope.row)">Sửa</el-button>
          <el-button size="small" type="danger" @click="confirmDelete(scope.row.id)">Xóa</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="dialogVisible" :title="form.id ? 'Sửa Vũ Khí' : 'Thêm Vũ Khí'" width="400px">
      <el-form label-position="top">
        <el-form-item label="Tên"><el-input v-model="form.name" /></el-form-item>
        <el-form-item label="Ảnh vũ khí">
          <input type="file" @change="onFileChange" accept="image/*" />
        </el-form-item>
        <el-form-item label="Sức mạnh"><el-input-number v-model="form.base_attack" style="width: 100%" /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="saveWeapon" type="primary">Lưu</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="detailVisible" title="Chi tiết vũ khí" width="300px">
      <div style="text-align: center;">
        <img :src="'http://26.103.188.167:8000' + detailForm.image_weapon" style="width: 100px; border-radius: 8px;">
        <h3>{{ detailForm.name }}</h3>
        <p>Sức mạnh: {{ detailForm.base_attack }}</p>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import '@/echo';

const weapons = ref([]);
const dialogVisible = ref(false);
const detailVisible = ref(false);
const form = ref({});
const detailForm = ref({});
const selectedFile = ref(null);
const loading = ref(false);

const fetchWeapons = async () => {
  const res = await axios.get('http://26.103.188.167:8000/api/weapons');
  weapons.value = res.data;
};

const onFileChange = (e) => { selectedFile.value = e.target.files[0]; };

const showDialog = (row = null) => {
  form.value = row ? { ...row } : { name: '', base_attack: 0 };
  dialogVisible.value = true;
};

const viewDetail = (row) => {
  detailForm.value = row;
  detailVisible.value = true;
};

const saveWeapon = async () => {
  const fd = new FormData();
  Object.keys(form.value).forEach(k => fd.append(k, form.value[k]));
  if (selectedFile.value) fd.append('image_weapon', selectedFile.value);
  if (form.value.id) fd.append('_method', 'PUT');

  await axios.post('http://26.103.188.167:8000/api/weapons' + (form.value.id ? `/${form.value.id}` : ''), fd);
  dialogVisible.value = false;
  fetchWeapons();
};

const confirmDelete = async (id) => {
  await axios.delete(`http://26.103.188.167:8000/api/weapons/${id}`);
  fetchWeapons();
};

onMounted(() => {
  fetchWeapons();
  window.Echo.channel('weapons-channel').listen('.WeaponUpdatedEvent', () => fetchWeapons());
});
</script>