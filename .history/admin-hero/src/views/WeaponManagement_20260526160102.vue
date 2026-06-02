<template>
  <div class="weapon-container">
    <el-table :data="weapons" border>
      <el-table-column label="Ảnh" width="100">
        <template #default="scope">
          <el-image :src="scope.row.image_weapon" fit="cover" style="width:50px; height:50px">
            <template #error>
              <div style="background:#e0e0e0; width:100%; height:100%; display:flex; align-items:center; justify-content:center;">
                <el-icon><Picture /></el-icon>
              </div>
            </template>
          </el-image>
        </template>
      </el-table-column>
      <el-table-column prop="name" label="Tên" />
      <el-table-column label="Hành động">
        <template #default="scope">
          <el-button @click="showDialog(scope.row)">Sửa</el-button>
          <el-button type="danger" @click="confirmDelete(scope.row.id)">Xóa</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="dialogVisible" title="Vũ khí">
      <el-form :model="form">
        <el-form-item label="Tên"> <el-input v-model="form.name" /> </el-form-item>
        <el-form-item label="Link ảnh"> <el-input v-model="form.image_weapon" /> </el-form-item>
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
import { Picture } from '@element-plus/icons-vue';

const weapons = ref([]);
const dialogVisible = ref(false);
const form = ref({});

const fetchWeapons = async () => {
  const res = await axios.get('http://26.103.188.167:8000/api/weapons');
  weapons.value = res.data;
};

const saveWeapon = async () => {
  if (form.value.id) {
    await axios.put(`http://26.103.188.167:8000/api/weapons/${form.value.id}`, form.value);
  } else {
    await axios.post('http://26.103.188.167:8000/api/weapons', form.value);
  }
  dialogVisible.value = false;
  fetchWeapons();
};

onMounted(() => {
  fetchWeapons();
  // Lắng nghe real-time
  window.Echo.channel('weapons-channel')
    .listen('.WeaponUpdatedEvent', (e) => {
      fetchWeapons(); // Cập nhật lại toàn bộ bảng khi Admin thêm mới
    });
});
</script>

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

  window.Echo.channel('weapons-channel')
    .listen('.WeaponUpdatedEvent', (e) => { // CÓ DẤU CHẤM ở đầu

    console.log(e);

});
});


</script>