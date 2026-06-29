<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Quản lý Gói phần thưởng</h2>
      <el-button type="primary" @click="openConfigModal()">+ Tạo Gói mới</el-button>
    </div>

    <el-table :data="configs" border v-loading="loading">
      <el-table-column prop="name" label="Tên Gói" />
      <el-table-column prop="duration" label="Số ngày" />
      <el-table-column prop="status" label="Trạng thái">
        <template #default="scope">
          <el-tag :type="scope.row.status === 'active' ? 'success' : 'info'">
            {{ scope.row.status }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="Hành động" width="280">
        <template #default="scope">
          <el-button size="small" @click="editConfig(scope.row)">Sửa</el-button>
          <el-button size="small" type="info" plain @click="editConfig(scope.row)">Xem chi tiết</el-button>
          <el-button v-if="scope.row.status !== 'active'" size="small" type="danger" @click="deleteConfig(scope.row.id)">Xóa</el-button>
          <el-button v-if="scope.row.status !== 'active'" size="small" type="success" @click="activateConfig(scope.row.id)">Active</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="dialogVisible" :title="isEdit ? 'Chỉnh sửa Gói' : 'Tạo Gói mới'" width="70%">
      <el-form :model="form" label-width="100px">
        <el-form-item label="Tên Gói"> <el-input v-model="form.name" /> </el-form-item>
        <el-form-item label="Số ngày"> <el-input-number v-model="form.duration" @change="generateItems" /> </el-form-item>
      </el-form>

      <el-table :data="form.items" border height="300px">
        <el-table-column prop="day_index" label="Ngày" width="80" />
        <el-table-column label="Tên quà">
          <template #default="scope"> <el-input v-model="scope.row.name" /> </template>
        </el-table-column>
        <el-table-column label="Loại">
          <template #default="scope">
            <el-select v-model="scope.row.reward_type">
              <el-option label="Gold" value="gold" />
              <el-option label="EXP" value="exp" />
            </el-select>
          </template>
        </el-table-column>
        <el-table-column label="Số lượng">
          <template #default="scope"> <el-input-number v-model="scope.row.amount" /> </template>
        </el-table-column>
      </el-table>

      <template #footer>
        <el-button @click="dialogVisible = false">Hủy</el-button>
        <el-button type="primary" @click="saveConfig">Lưu cấu hình</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';

const configs = ref([]);
const loading = ref(false);
const dialogVisible = ref(false);
const isEdit = ref(false);
const form = ref({ id: null, name: '', duration: 7, items: [] });

const fetchConfigs = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/admin/configs');
    configs.value = res.data;
  } finally { loading.value = false; }
};

const generateItems = () => {
  // Reset items nếu thay đổi duration
  form.value.items = Array.from({ length: form.value.duration }, (_, i) => ({
    day_index: i + 1, name: '', reward_type: 'gold', amount: 0
  }));
};

const openConfigModal = () => {
  isEdit.value = false;
  form.value = { id: null, name: '', duration: 7, items: [] };
  generateItems();
  dialogVisible.value = true;
};

const editConfig = async (row) => {
  isEdit.value = true;
  // Lấy chi tiết từ API để đảm bảo dữ liệu mới nhất
  const res = await axios.get(`/api/admin/configs/${row.id}`);
  form.value = res.data;
  dialogVisible.value = true;
};

const saveConfig = async () => {
  await axios.post('/api/admin/configs', form.value);
  ElMessage.success('Lưu thành công!');
  dialogVisible.value = false;
  fetchConfigs();
};

const activateConfig = async (id) => {
  await axios.post(`/api/admin/configs/${id}/activate`);
  ElMessage.success('Đã kích hoạt!');
  fetchConfigs();
};

const deleteConfig = async (id) => {
  try {
    await ElMessageBox.confirm('Bạn có chắc muốn xóa cấu hình này?', 'Cảnh báo', { type: 'warning' });
    await axios.delete(`/api/admin/configs/${id}`);
    ElMessage.success('Đã xóa thành công!');
    fetchConfigs();
  } catch (err) {
    if (err !== 'cancel') ElMessage.error('Không thể xóa gói đang hoạt động!');
  }
};

onMounted(fetchConfigs);
</script>