<template>
  <div class="gacha-page">
    <el-card>
      <template #header>
        <div class="header">
          <span>Quản lý Cấu hình Gacha</span>
          <div>
            <el-button type="primary" @click="logDialogVisible = true">Xem Log</el-button>
            <el-button type="success" @click="openAddDialog">Thêm mới</el-button>
          </div>
        </div>
      </template>

      <el-table :data="configs" border v-loading="loading">
        <el-table-column prop="id" label="ID" width="60" />
        <el-table-column prop="reward_type" label="Loại" />
        <el-table-column prop="amount" label="Số lượng" />
        <el-table-column prop="weight" label="Tỉ lệ" />
        <el-table-column prop="description" label="Mô tả" />
        <el-table-column label="Thao tác" width="180">
          <template #default="{ row }">
            <el-button size="small" type="warning" @click="openEditDialog(row)">Sửa</el-button>
            <el-button size="small" type="danger" @click="deleteConfig(row.id)">Xóa</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog v-model="dialogVisible" :title="isEdit ? 'Cập nhật' : 'Thêm mới'" width="500px">
      <el-form :model="form" label-width="100px">
        <el-form-item label="Loại thưởng"><el-input v-model="form.reward_type" /></el-form-item>
        <el-form-item label="Số lượng"><el-input-number v-model="form.amount" :min="0" style="width:100%" /></el-form-item>
        <el-form-item label="Tỉ lệ"><el-input-number v-model="form.weight" :min="1" style="width:100%" /></el-form-item>
        <el-form-item label="Mô tả"><el-input v-model="form.description" /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">Hủy</el-button>
        <el-button type="primary" @click="saveConfig">Lưu</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="logDialogVisible" title="Nhật ký thay đổi" width="900px">
      <el-table :data="logs" border>
        <el-table-column prop="created_at" label="Thời gian" width="160" />
        <el-table-column prop="action" label="Hành động" width="100" />
        <el-table-column prop="admin_name" label="Admin" width="120" />
        <el-table-column label="Dữ liệu cũ"><template #default="{row}"><pre>{{ formatJson(row.old_value) }}</pre></template></el-table-column>
        <el-table-column label="Dữ liệu mới"><template #default="{row}"><pre>{{ formatJson(row.new_value) }}</pre></template></el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';

// Lưu ý: Đảm bảo baseURL được cấu hình đúng trong axios
const configs = ref([]);
const logs = ref([]);
const loading = ref(false);
const dialogVisible = ref(false);
const logDialogVisible = ref(false);
const isEdit = ref(false);
const form = ref({});

const fetchData = async () => {
  loading.value = true;
  try {
    // Gọi API đã được route thông qua prefix 'admin/gacha-config'
    const [resC, resL] = await Promise.all([
      axios.get('/api/admin/gacha-config'),
      axios.get('/api/admin/gacha-config/logs')
    ]);
    configs.value = resC.data;
    logs.value = resL.data.data;
  } catch (e) { ElMessage.error('Lỗi tải dữ liệu'); }
  finally { loading.value = false; }
};

const openAddDialog = () => {
  isEdit.value = false;
  form.value = { reward_type: 'gold', amount: 100, weight: 10, description: '' };
  dialogVisible.value = true;
};

const openEditDialog = (row) => {
  isEdit.value = true;
  form.value = { ...row };
  dialogVisible.value = true;
};

const saveConfig = async () => {
  try {
    if (isEdit.value) await axios.put(`/api/admin/gacha-config/${form.value.id}`, form.value);
    else await axios.post('/api/admin/gacha-config', form.value);
    
    ElMessage.success('Lưu thành công');
    dialogVisible.value = false;
    fetchData();
  } catch (e) { ElMessage.error('Thao tác thất bại'); }
};

const deleteConfig = async (id) => {
  try {
    await ElMessageBox.confirm('Xóa cấu hình này?', 'Cảnh báo', { type: 'warning' });
    await axios.delete(`/api/admin/gacha-config/${id}`);
    ElMessage.success('Đã xóa');
    fetchData();
  } catch (e) { if(e !== 'cancel') ElMessage.error('Lỗi xóa'); }
};

const formatJson = (val) => {
  try { return JSON.stringify(JSON.parse(val), null, 2); }
  catch { return val || 'None'; }
};

onMounted(fetchData);
</script>