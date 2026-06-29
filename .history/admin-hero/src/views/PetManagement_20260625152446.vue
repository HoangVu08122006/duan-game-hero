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
            <el-button type="danger" size="small" @click="viewDetails(scope.row.id)">Chi tiết</el-button>
            <el-button type="primary" size="small" @click="openEditDialog(scope.row)">Sửa</el-button>
            <el-button size="small" @click="openLogs(scope.row.id)">Log</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog v-model="detailDialogVisible" title="Thông tin chi tiết Pet" width="500px">
      <el-descriptions :column="1" border>
        <el-descriptions-item label="Tên Pet">{{ petDetail.name }}</el-descriptions-item>
        <el-descriptions-item label="DPS">{{ petDetail.base_dps }}</el-descriptions-item>
        <el-descriptions-item label="Ngày tạo">
          {{ new Date(petDetail.created_at).toLocaleString() }}
        </el-descriptions-item>
        <el-descriptions-item label="Cập nhật lần cuối">
          {{ new Date(petDetail.updated_at).toLocaleString() }}
        </el-descriptions-item>
      </el-descriptions>
    </el-dialog>

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
const detailDialogVisible = ref(false);
const petDetail = ref({});

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

//Xem chi tiết
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
  min-height: 100vh;
  padding: 24px;
  background: linear-gradient(
    135deg,
    #0f172a 0%,
    #111827 50%,
    #1e293b 100%
  );
}

/* Card */
.pet-management .el-card {
  background: rgba(20, 25, 40, 0.85);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 20px;
  box-shadow:
    0 10px 30px rgba(0,0,0,0.3),
    0 0 30px rgba(59,130,246,0.08);
}

/* Header */
.pet-management .el-card__header {
  background: transparent;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}

.card-header span {
  color: #fff;
  font-size: 22px;
  font-weight: 700;
}

/* Table */
.pet-management .el-table {
  background: transparent !important;
  color: #fff;
}

.pet-management .el-table th {
  background: #1e293b !important;
  color: #94a3b8 !important;
  border: none !important;
  font-size: 13px;
  text-transform: uppercase;
}

.pet-management .el-table td {
  background: #111827 !important;
  color: #e2e8f0;
  border-color: rgba(255,255,255,0.05) !important;
}

.pet-management .el-table__row:hover td {
  background: #172033 !important;
}

/* Buttons */
.pet-management .el-button {
  border-radius: 10px;
  font-weight: 600;
  border: none;
}

.pet-management .el-button--success {
  background: linear-gradient(
    135deg,
    #22c55e,
    #16a34a
  );
}

.pet-management .el-button--primary {
  background: linear-gradient(
    135deg,
    #3b82f6,
    #2563eb
  );
}

.pet-management .el-button--danger {
  background: linear-gradient(
    135deg,
    #ef4444,
    #dc2626
  );
}

.pet-management .el-button:hover {
  transform: translateY(-2px);
}

/* Dialog */
.el-dialog {
  background: #111827 !important;
  border-radius: 20px !important;
  overflow: hidden;
}

.el-dialog__header {
  border-bottom: 1px solid rgba(255,255,255,0.08);
}

.el-dialog__title {
  color: white !important;
  font-size: 18px;
  font-weight: 700;
}

.el-dialog__body {
  background: #111827;
  color: white;
}

/* Input */
.el-input__wrapper,
.el-textarea__inner,
.el-input-number {
  background: #1e293b !important;
  border: 1px solid #334155 !important;
  color: white !important;
  border-radius: 10px;
}

.el-input__inner,
.el-textarea__inner {
  color: white !important;
}

/* Label */
.el-form-item__label {
  color: #cbd5e1 !important;
  font-weight: 600;
}

/* Divider */
.el-divider__text {
  background: #111827 !important;
  color: #60a5fa !important;
  font-weight: 700;
}

/* Description */
.el-descriptions {
  background: #111827;
}

.el-descriptions__label {
  background: #1e293b !important;
  color: #60a5fa !important;
}

.el-descriptions__content {
  background: #0f172a !important;
  color: white !important;
}

/* Scroll */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-thumb {
  background: #475569;
  border-radius: 999px;
}

/* Glow effect */
.pet-management .el-card:hover {
  box-shadow:
    0 10px 30px rgba(0,0,0,0.3),
    0 0 35px rgba(59,130,246,0.2);
}

</style>