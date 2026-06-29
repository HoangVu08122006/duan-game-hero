<template>
  <div class="pet-management" style="padding: 20px;">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>Quản lý danh sách Pet (Gốc) & Lịch sử cập nhật</span>
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

    <el-dialog v-model="dialogVisible" title="Chỉnh sửa thông tin Pet" width="600px">
      <el-form :model="editForm" label-width="140px">
        <el-form-item label="Tên Pet"><el-input v-model="editForm.name" /></el-form-item>
        <el-form-item label="Prefab Name"><el-input v-model="editForm.prefab_name" /></el-form-item>
        <el-form-item label="DPS Gốc"><el-input-number v-model="editForm.base_dps" /></el-form-item>
        <el-form-item label="Growth Rate"><el-input-number v-model="editForm.growth_rate" :step="0.01" /></el-form-item>
        
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
        <el-button type="primary" @click="submitEdit" :loading="submitLoading">Lưu thay đổi</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="logDialogVisible" title="Lịch sử thay đổi" width="700px">
      <el-table :data="petLogs" border height="400px">
        <el-table-column prop="field_name" label="Trường" width="120" />
        <el-table-column prop="old_value" label="Cũ" />
        <el-table-column prop="new_value" label="Mới" />
        <script setup>
import dayjs from 'dayjs';

// Hàm format thời gian
const formatDate = (row, column, cellValue) => {
  return dayjs(cellValue).format('DD/MM/YYYY HH:mm:ss');
};
</script>

<el-table-column prop="created_at" label="Thời gian" :formatter="formatDate" width="160" />
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

// Khởi tạo đầy đủ các field để form không bị lỗi undefined
const editForm = ref({
  id: null, name: '', prefab_name: '', base_dps: 0, growth_rate: 0,
  skill_1_name: '', skill_1_description: '',
  skill_2_name: '', skill_2_description: '',
  skill_3_name: '', skill_3_description: ''
});

const fetchPets = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/pets');
    petList.value = res.data.data;
  } finally { loading.value = false; }
};

const openEditDialog = (row) => {
  // Dùng JSON.parse/stringify để tách biệt hoàn toàn object mới với object cũ trong table
  editForm.value = JSON.parse(JSON.stringify(row));
  dialogVisible.value = true;
};

const openLogs = async (id) => {
  try {
    const res = await axios.get(`/api/pets/${id}/logs`);
    petLogs.value = res.data.data;
    logDialogVisible.value = true;
  } catch (e) { ElMessage.error('Không tải được log'); }
};

const submitEdit = async () => {
  submitLoading.value = true;
  try {
    // 1. Tạo một bản copy sạch, loại bỏ các trường không cần thiết
    const payload = {
      name: editForm.value.name,
      prefab_name: editForm.value.prefab_name,
      base_dps: editForm.value.base_dps,
      growth_rate: editForm.value.growth_rate,
      skill_1_name: editForm.value.skill_1_name,
      skill_2_name: editForm.value.skill_2_name,
      skill_3_name: editForm.value.skill_3_name,
      skill_1_description: editForm.value.skill_1_description,
      skill_2_description: editForm.value.skill_2_description,
      skill_3_description: editForm.value.skill_3_description,
    };

    // 2. Gửi payload sạch
    await axios.put(`/api/pets/${editForm.value.id}`, payload);
    
    ElMessage.success('Cập nhật thành công!');
    dialogVisible.value = false;
    fetchPets();
  } catch (e) {
    // 3. Hiện lỗi thực tế từ server thay vì chuỗi cố định
    const errorMsg = e.response?.data?.message || 'Có lỗi xảy ra khi lưu';
    ElMessage.error(errorMsg);
    console.error("Debug lỗi:", e.response?.data);
  } finally {
    submitLoading.value = false;
  }
};

onMounted(fetchPets);
</script>