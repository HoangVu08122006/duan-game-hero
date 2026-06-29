<template>
  <div class="pet-management" style="padding: 20px;">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>Quản lý danh sách Pet (Gốc)</span>
        </div>
      </template>

      <el-table v-loading="loading" :data="petList" border style="width: 100%">
        <el-table-column prop="id" label="ID" width="70" />
        <el-table-column prop="name" label="Tên Thú" width="150" />
        <el-table-column prop="prefab_name" label="Prefab" width="150" />
        <el-table-column prop="base_dps" label="DPS Gốc" width="100" />
        <el-table-column prop="growth_rate" label="Growth" width="100" />
        <el-table-column prop="skill_1_name" label="Kỹ năng 1" />
        <el-table-column prop="skill_2_name" label="Kỹ năng 2" />
        <el-table-column prop="skill_3_name" label="Kỹ năng 3" />
        
        <el-table-column label="Thao tác" width="120" fixed="right">
          <template #default="scope">
            <el-button type="primary" size="small" @click="openEditDialog(scope.row)">
              Sửa
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog v-model="dialogVisible" title="Chỉnh sửa thông tin Pet" width="500px">
      <el-form :model="editForm" label-width="120px">
        <el-form-item label="Tên Pet">
          <el-input v-model="editForm.name" />
        </el-form-item>
        <el-form-item label="Prefab Name">
          <el-input v-model="editForm.prefab_name" />
        </el-form-item>
        <el-form-item label="DPS Gốc">
          <el-input-number v-model="editForm.base_dps" />
        </el-form-item>
        <el-form-item label="Growth Rate">
          <el-input-number v-model="editForm.growth_rate" :step="0.01" />
        </el-form-item>
        <el-form-item label="Kỹ năng 1">
          <el-input v-model="editForm.skill_1_name" />
        </el-form-item>
        <el-form-item label="Kỹ năng 2">
          <el-input v-model="editForm.skill_2_name" />
        </el-form-item>
        <el-form-item label="Kỹ năng 3">
          <el-input v-model="editForm.skill_3_name" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">Hủy</el-button>
        <el-button type="primary" @click="submitEdit" :loading="submitLoading">Lưu lại</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { ElMessage } from 'element-plus';

const petList = ref([]);
const loading = ref(false);
const dialogVisible = ref(false);
const submitLoading = ref(false);

// Form chứa dữ liệu đang sửa
const editForm = ref({
  id: null,
  name: '',
  prefab_name: '',
  base_dps: 0,
  growth_rate: 0,
  skill_1_name: '',
  skill_2_name: '',
  skill_3_name: '',
});

const fetchPets = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/pets');
    petList.value = response.data.data;
  } catch (error) {
    ElMessage.error('Lỗi tải danh sách Pet');
  } finally {
    loading.value = false;
  }
};

// Khi bấm nút Sửa
const openEditDialog = (row) => {
  // Copy dữ liệu từ hàng vào form để tránh sửa trực tiếp lên bảng khi chưa lưu
  editForm.value = { ...row };
  dialogVisible.value = true;
};

// Khi bấm Lưu trong Dialog
const submitEdit = async () => {
  submitLoading.value = true;
  try {
    const response = await axios.put(`/api/pets/${editForm.value.id}`, editForm.value);
    if (response.data.success) {
      ElMessage.success('Cập nhật thành công!');
      dialogVisible.value = false;
      fetchPets(); // Load lại danh sách
    }
  } catch (error) {
    ElMessage.error(error.response?.data?.message || 'Có lỗi xảy ra');
  } finally {
    submitLoading.value = false;
  }
};

onMounted(fetchPets);
</script>