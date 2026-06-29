<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Cấu hình Phần thưởng (Chu kỳ: {{ rewards.length }} ngày)</h2>
      <el-button type="primary" @click="openDialog()">Thêm ngày mới</el-button>
    </div>

    <el-table :data="rewards" border stripe v-loading="loading">
      <el-table-column prop="day_index" label="Ngày thứ" width="100" />
      <el-table-column prop="name" label="Tên quà" />
      <el-table-column prop="reward_type" label="Loại (gold/exp)" />
      <el-table-column prop="amount" label="Số lượng" />
      <el-table-column label="Hành động" width="200">
        <template #default="scope">
          <el-button size="small" @click="openDialog(scope.row)">Sửa</el-button>
          <el-button size="small" type="danger" @click="deleteReward(scope.row.day_index)">Xóa</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="dialogVisible" :title="isEdit ? 'Sửa phần thưởng' : 'Thêm phần thưởng'">
      <el-form :model="form">
        <el-form-item label="Ngày thứ">
          <el-input-number v-model="form.day_index" :disabled="isEdit" />
        </el-form-item>
        <el-form-item label="Tên quà">
          <el-input v-model="form.name" />
        </el-form-item>
        <el-form-item label="Loại quà">
          <el-select v-model="form.reward_type">
            <el-option label="Gold" value="gold" />
            <el-option label="EXP" value="exp" />
          </el-select>
        </el-form-item>
        <el-form-item label="Số lượng">
          <el-input-number v-model="form.amount" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">Hủy</el-button>
        <el-button type="primary" @click="saveReward">Lưu</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { ElMessage } from 'element-plus';

const rewards = ref([]);
const loading = ref(false);
const dialogVisible = ref(false);
const isEdit = ref(false);
const form = ref({ day_index: 0, name: '', reward_type: 'gold', amount: 0 });

const fetchRewards = async () => {
  loading.value = true;
  const res = await axios.get('/api/admin/rewards'); // Gọi API lấy danh sách
  rewards.value = res.data;
  loading.value = false;
};

const openDialog = (row = null) => {
  isEdit.value = !!row;
  form.value = row ? { ...row } : { day_index: rewards.value.length + 1, name: '', reward_type: 'gold', amount: 0 };
  dialogVisible.value = true;
};

const saveReward = async () => {
  try {
    if (isEdit.value) {
      await axios.put(`/api/admin/rewards/${form.value.day_index}`, form.value);
    } else {
      await axios.post('/api/admin/rewards/store', form.value);
    }
    ElMessage.success('Lưu thành công!');
    dialogVisible.value = false;
    fetchRewards();
  } catch (err) {
    ElMessage.error('Có lỗi xảy ra');
  }
};

const deleteReward = async (day) => {
  await axios.delete(`/api/admin/rewards/${day}`);
  fetchRewards();
};

onMounted(fetchRewards);
</script>