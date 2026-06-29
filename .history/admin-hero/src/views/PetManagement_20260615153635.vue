<template>
  <div class="pet-management" style="padding: 20px;">
    <el-card>
      <template #header>
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
          <span>Quản lý danh sách Pet & Lịch sử cập nhật</span>
          <el-button type="success" @click="openAddDialog">Thêm Pet Mới</el-button>
        </div>
      </template>

      <el-table v-loading="loading" :data="petList" border style="width: 100%">
        <el-table-column prop="id" label="ID" width="70" />
        <el-table-column prop="name" label="Tên Thú" width="150" />
        <el-table-column prop="prefab_name" label="Prefab" width="150" />
        <el-table-column prop="base_dps" label="DPS" width="100" />
        <el-table-column prop="growth_rate" label="Growth" width="100" />
        <el-table-column prop="skill_1_name" label="Skill 1" />
        <el-table-column prop="skill_2_name" label="Skill 2" />
        <el-table-column prop="skill_3_name" label="Skill 3" />
        
        <el-table-column label="Thao tác" width="180" fixed="right">
          <template #default="scope">
            <el-button type="primary" size="small" @click="openEditDialog(scope.row)">Sửa</el-button>
            <el-button size="small" @click="openLogs(scope.row.id)">Log</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog v-model="dialogVisible" :title="isEdit ? 'Chỉnh sửa thông tin Pet' : 'Thêm Pet mới'" width="600px">
      <el-form :model="editForm" label-width="140px">
        <el-form-item label="Tên Pet"><el-input v-model="editForm.name" /></el-form-item>
        <el-form-item label="Prefab Name"><el-input v-model="editForm.prefab_name" /></el-form-item>
        <el-form-item label="DPS Gốc"><el-input-number v-model="editForm.base_dps" :precision="2" /></el-form-item>
        <el-form-item label="Growth Rate"><el-input-number v-model="editForm.growth_rate" :step="0.01" :precision="2" /></el-form-item>
        
        <el-divider>Kỹ năng</el-divider>
        <el-form-item label="Tên Skill 1"><el-input v-model="editForm.skill_1_name" /></el-form-item>
        <el-form-item label="Mô tả S1"><el-input v-model="editForm.skill_1_description" type="textarea" /></el-form-item>
        <el-form-item label="Tên Skill 2"><el-input v-model="editForm.skill_2_name" /></el-form-item>
        <el-form-item label="Mô tả S2"><el-input v-model="editForm.skill_2_description" type="textarea" /></el-form-item>
        <el-form-item label="Tên Skill 3"><el-input v-model="editForm.skill_3_name" /></el-form-item>
        <el-form-item label="Mô tả S3"><el-input v-model="editForm.skill_3_description" type="textarea" /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">Hủy</el-button>
        <el-button type="primary" @click="submitForm" :loading="submitLoading">Xác nhận</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="logDialogVisible" title="Lịch sử thay đổi" width="700px">
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
const isEdit = ref(false); // Trạng thái: false = thêm mới, true = chỉnh sửa

const defaultForm = {
  id: null, name: '', prefab_name: '', base_dps: 0, growth_rate: 0,
  skill_1_name: '', skill_1_description: '',
  skill_2_name: '', skill_2_description: '',
  skill_3_name: '', skill_3_description: ''
};

const editForm = ref({ ...defaultForm });

const fetchPets = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/pets');
    petList.value = res.data.data;
  } finally { loading.value = false; }
};

// Mở form Thêm mới
const openAddDialog = () => {
  isEdit.value = false;
  editForm.value = { ...defaultForm };
  dialogVisible.value = true;
};

const detailDialogVisible = ref(false);
const petDetail = ref({});
//
const viewDetails = async (id) => {
  try {
    const res = await axios.get(`/api/pets/${id}`);
    petDetail.value = res.data.data;
    detailDialogVisible.value = true;
  } catch (e) {
    ElMessage.error('Không tải được thông tin chi tiết');
  }
};

// Mở form Chỉnh sửa
const openEditDialog = (row) => {
  isEdit.value = true;
  editForm.value = { ...row };
  dialogVisible.value = true;
};

const openLogs = async (id) => {
  try {
    const res = await axios.get(`/api/pets/${id}/logs`);
    petLogs.value = res.data.data;
    logDialogVisible.value = true;
  } catch (e) { ElMessage.error('Không tải được log'); }
};

const submitForm = async () => {
  submitLoading.value = true;
  try {
    if (isEdit.value) {
      // Logic cập nhật
      await axios.put(`/api/pets/${editForm.value.id}`, editForm.value);
      ElMessage.success('Cập nhật thành công!');
    } else {
      // Logic thêm mới
      await axios.post('/api/pets', editForm.value);
      ElMessage.success('Thêm mới thành công!');
    }
    
    dialogVisible.value = false;
    fetchPets();
  } catch (e) {
    const errorMsg = e.response?.data?.message || 'Có lỗi xảy ra khi lưu';
    ElMessage.error(errorMsg);
  } finally {
    submitLoading.value = false;
  }
};

onMounted(fetchPets);
</script>

<style scoped>
.pet-management {
  padding: 20px;
  background: #f5f7fa;
  min-height: 100vh;
}

/* Card */
:deep(.el-card) {
  border-radius: 15px;
  border: none;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.card-header {
  font-size: 20px;
  font-weight: 700;
  color: #303133;
  display: flex;
  align-items: center;
}

.card-header::before {
  content: "🐾";
  margin-right: 10px;
  font-size: 24px;
}

/* Table */
:deep(.el-table) {
  border-radius: 12px;
  overflow: hidden;
}

:deep(.el-table th) {
  background: linear-gradient(90deg, #409eff, #67c23a);
  color: white;
  font-weight: bold;
  font-size: 14px;
}

:deep(.el-table tr:hover td) {
  background: #ecf5ff !important;
}

:deep(.el-table td) {
  transition: all 0.3s ease;
}

/* Buttons */
:deep(.el-button--primary) {
  border-radius: 8px;
  font-weight: 600;
}

:deep(.el-button) {
  border-radius: 8px;
}

/* Dialog */
:deep(.el-dialog) {
  border-radius: 16px;
}

:deep(.el-dialog__header) {
  border-bottom: 1px solid #ebeef5;
  padding-bottom: 15px;
}

:deep(.el-dialog__title) {
  font-size: 18px;
  font-weight: bold;
}

/* Form */
:deep(.el-form-item__label) {
  font-weight: 600;
}

:deep(.el-input__wrapper) {
  border-radius: 8px;
}

:deep(.el-textarea__inner) {
  border-radius: 8px;
}

:deep(.el-input-number) {
  width: 100%;
}

/* Divider */
:deep(.el-divider__text) {
  font-weight: bold;
  color: #409eff;
}

/* Log Dialog */
:deep(.el-table .warning-row) {
  background: #fdf6ec;
}

/* Scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-thumb {
  background: #409eff;
  border-radius: 10px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}


</style>