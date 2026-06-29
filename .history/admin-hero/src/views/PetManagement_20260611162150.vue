<template>
  <div class="pet-management" style="padding: 20px;">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>Quản Lý Pet & Lịch Sử Thay Đổi</span>
        </div>
      </template>

      <el-table v-loading="loading" :data="petList" border style="width: 100%">
        <el-table-column prop="id" label="ID" width="70" />
        <el-table-column prop="name" label="Tên Thú" width="150" />
        <el-table-column prop="base_dps" label="DPS" width="100" />
        <el-table-column label="Thao tác" width="200" fixed="right">
          <template #default="scope">
            <el-button type="primary" size="small" @click="openEditDialog(scope.row)">Sửa</el-button>
            <el-button size="small" @click="openLogs(scope.row.id)">Lịch sử</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog v-model="dialogVisible" title="Chỉnh sửa Pet">
      <el-form :model="editForm" label-width="120px">
        <el-form-item label="Tên Pet"><el-input v-model="editForm.name" /></el-form-item>
        <el-form-item label="DPS Gốc"><el-input-number v-model="editForm.base_dps" /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">Hủy</el-button>
        <el-button type="primary" @click="submitEdit" :loading="submitLoading">Lưu thay đổi</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="logDialogVisible" title="Lịch sử thay đổi" width="600px">
      <el-table :data="petLogs" border height="400px">
        <el-table-column prop="field_name" label="Trường" width="120" />
        <el-table-column prop="old_value" label="Cũ" />
        <el-table-column prop="new_value" label="Mới" />
        <el-table-column prop="created_at" label="Thời gian" width="160" />
      </el-table>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { ElMessage } from 'element-plus';

const petList = ref([]);
const petLogs = ref([]);
const loading = ref(false);
const dialogVisible = ref(false);
const logDialogVisible = ref(false);
const submitLoading = ref(false);

const editForm = ref({});

const fetchPets = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/pets');
    petList.value = res.data.data;
  } finally { loading.value = false; }
};

const openEditDialog = (row) => {
  editForm.value = { ...row };
  dialogVisible.value = true;
};

const openLogs = async (id) => {
  try {
    const res = await axios.get(`/api/pets/${id}/logs`);
    petLogs.value = res.data.data;
    logDialogVisible.value = true;
  } catch (e) { ElMessage.error('Không thể tải log'); }
};

const submitEdit = async () => {
  submitLoading.value = true;
  try {
    await axios.put(`/api/pets/${editForm.value.id}`, editForm.value);
    ElMessage.success('Cập nhật thành công!');
    dialogVisible.value = false;
    fetchPets();
  } catch (e) { ElMessage.error('Lỗi khi lưu'); }
  finally { submitLoading.value = false; }
};

onMounted(fetchPets);
</script>