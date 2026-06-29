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
      <el-table-column label="Hành động" width="250">
        <template #default="scope">
          <el-button size="small" @click="editConfig(scope.row)">Chi tiết/Sửa</el-button>
          <el-button v-if="scope.row.status !== 'active'" size="small" type="success" @click="activateConfig(scope.row.id)">Active</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="dialogVisible" :title="isEdit ? 'Chỉnh sửa Gói' : 'Tạo Gói mới'" width="70%">
      <el-form :model="form" label-width="100px">
        <el-form-item label="Tên Gói">
          <el-input v-model="form.name" />
        </el-form-item>
        <el-form-item label="Số ngày">
          <el-input-number v-model="form.duration" @change="generateItems" />
        </el-form-item>
      </el-form>

      <el-table :data="form.items" border height="400px">
        <el-table-column prop="day_index" label="Ngày" width="80" />
        <el-table-column label="Tên quà">
          <template #default="scope">
            <el-input v-model="scope.row.name" placeholder="VD: 100 Vàng" />
          </template>
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
          <template #default="scope">
            <el-input-number v-model="scope.row.amount" :min="0" />
          </template>
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
import { ElMessage } from 'element-plus';

const configs = ref([]);
const dialogVisible = ref(false);
const isEdit = ref(false);
const form = ref({ name: '', duration: 7, items: [] });

// Tạo bảng trống khi chọn số ngày
const generateItems = () => {
  form.value.items = Array.from({ length: form.value.duration }, (_, i) => ({
    day_index: i + 1,
    name: '',
    reward_type: 'gold',
    amount: 0
  }));
};

const saveConfig = async () => {
  // Gửi cả config và danh sách items lên backend
  await axios.post('/api/admin/configs', form.value);
  ElMessage.success('Đã lưu cấu hình!');
  dialogVisible.value = false;
  fetchConfigs();
};

const activateConfig = async (id) => {
  await axios.post(`/api/admin/configs/${id}/activate`);
  ElMessage.success('Đã kích hoạt gói quà!');
  fetchConfigs();
};

onMounted(fetchConfigs);
</script>