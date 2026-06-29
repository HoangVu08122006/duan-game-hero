<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Quản lý Phần thưởng (Tổng: {{ rewards.length }} ngày)</h2>
      <el-button type="primary" @click="handleOpenDialog()">+ Thêm phần thưởng</el-button>
    </div>

    <el-table :data="rewards" border v-loading="loading">
      <el-table-column prop="day_index" label="Ngày" width="80" />
      <el-table-column prop="name" label="Tên quà" />
      <el-table-column prop="reward_type" label="Loại" />
      <el-table-column prop="amount" label="Số lượng" />
      <el-table-column label="Thao tác" width="200">
        <template #default="scope">
          <el-button size="small" @click="handleOpenDialog(scope.row)">Sửa</el-button>
          <el-button size="small" type="danger" @click="handleDelete(scope.row.day_index)">Xóa</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="dialogVisible" :title="isEdit ? 'Sửa phần thưởng' : 'Thêm mới'">
      <el-form :model="form" label-width="100px">
        <el-form-item label="Ngày thứ">
          <el-input-number v-model="form.day_index" :disabled="isEdit" />
        </el-form-item>
        <el-form-item label="Tên quà">
          <el-input v-model="form.name" />
        </el-form-item>
        <el-form-item label="Loại quà">
          <el-select v-model="form.reward_type" class="w-full">
            <el-option label="Gold" value="gold" />
            <el-option label="EXP" value="exp" />
          </el-select>
        </el-form-item>
        <el-form-item label="Số lượng">
          <el-input-number v-model="form.amount" class="w-full" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">Hủy</el-button>
        <el-button type="primary" @click="handleSubmit">Lưu thay đổi</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';

const rewards = ref([]);
const loading = ref(false);
const dialogVisible = ref(false);
const isEdit = ref(false);
const form = ref({ day_index: 1, name: '', reward_type: 'gold', amount: 0 });

// Lấy danh sách
const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/admin/rewards');
    rewards.value = res.data;
  } catch (err) {
    ElMessage.error('Lỗi tải dữ liệu');
  } finally {
    loading.value = false;
  }
};

// Mở Dialog
const handleOpenDialog = (row = null) => {
  isEdit.value = !!row;
  form.value = row ? { ...row } : { day_index: rewards.value.length + 1, name: '', reward_type: 'gold', amount: 0 };
  dialogVisible.value = true;
};

// Lưu (Thêm/Sửa)
const handleSubmit = async () => {
  try {
    if (isEdit.value) {
      await axios.put(`/api/admin/rewards/${form.value.day_index}`, form.value);
    } else {
      await axios.post('/api/admin/rewards/store', form.value);
    }
    ElMessage.success('Thành công!');
    dialogVisible.value = false;
    fetchData();
  } catch (err) {
    ElMessage.error(err.response?.data?.message || 'Có lỗi xảy ra');
  }
};

// Xóa
const handleDelete = (day) => {
  ElMessageBox.confirm('Bạn có chắc muốn xóa ngày này?', 'Xác nhận', { type: 'warning' })
    .then(async () => {
      await axios.delete(`/api/admin/rewards/${day}`);
      fetchData();
    });
};

onMounted(fetchData);
</script>