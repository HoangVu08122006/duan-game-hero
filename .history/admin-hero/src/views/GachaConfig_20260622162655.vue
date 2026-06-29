<template>
  <div class="gacha-page">
    <el-card>
      <template #header>
        <div class="header">
          <span>Quản lý Gacha Config</span>

          <div>
            <el-button type="primary" @click="fetchLogs">
              Xem Log
            </el-button>

            <el-button type="success" @click="openAddDialog">
              Thêm mới
            </el-button>
          </div>
        </div>
      </template>

      <el-table :data="configs" border v-loading="loading">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="reward_type" label="Loại thưởng" />
        <el-table-column prop="reward_id" label="Reward ID" />
        <el-table-column prop="amount" label="Số lượng" />
        <el-table-column prop="weight" label="Tỉ lệ" />
        <el-table-column prop="description" label="Mô tả" />

        <el-table-column label="Thao tác" width="180">
          <template #default="{ row }">
            <el-button
              size="small"
              type="warning"
              @click="openEditDialog(row)"
            >
              Sửa
            </el-button>

            <el-button
              size="small"
              type="danger"
              @click="deleteConfig(row.id)"
            >
              Xóa
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- Dialog thêm/sửa -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? 'Cập nhật cấu hình' : 'Thêm cấu hình'"
      width="600px"
    >
      <el-form :model="form" label-width="120px">
        <el-form-item label="Reward Type">
          <el-input v-model="form.reward_type" />
        </el-form-item>

        <el-form-item label="Reward ID">
          <el-input-number
            v-model="form.reward_id"
            :min="0"
            style="width:100%"
          />
        </el-form-item>

        <el-form-item label="Amount">
          <el-input-number
            v-model="form.amount"
            :min="1"
            style="width:100%"
          />
        </el-form-item>

        <el-form-item label="Weight">
          <el-input-number
            v-model="form.weight"
            :min="1"
            style="width:100%"
          />
        </el-form-item>

        <el-form-item label="Description">
          <el-input
            type="textarea"
            v-model="form.description"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">
          Hủy
        </el-button>

        <el-button type="primary" @click="saveConfig">
          Lưu
        </el-button>
      </template>
    </el-dialog>

    <!-- Dialog log -->
    <el-dialog
      v-model="logDialogVisible"
      title="Nhật ký thay đổi"
      width="1000px"
    >
      <el-table :data="logs" border>
        <el-table-column
          prop="created_at"
          label="Thời gian"
          width="180"
        />

        <el-table-column
          prop="action"
          label="Hành động"
          width="120"
        />

        <el-table-column
          prop="target_id"
          label="ID Config"
          width="120"
        />

        <el-table-column
          prop="admin_name"
          label="Admin"
          width="150"
        />

        <el-table-column label="Dữ liệu cũ">
          <template #default="{ row }">
            <pre>{{ formatJson(row.old_value) }}</pre>
          </template>
        </el-table-column>

        <el-table-column label="Dữ liệu mới">
          <template #default="{ row }">
            <pre>{{ formatJson(row.new_value) }}</pre>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';

// Trạng thái dữ liệu
const configs = ref([]);
const logs = ref([]);
const loading = ref(false);
const dialogVisible = ref(false);
const logDialogVisible = ref(false);
const isEdit = ref(false);

// Form dữ liệu
const form = ref({
  id: null,
  reward_type: 'gold',
  reward_id: 0,
  amount: 100,
  weight: 10,
  description: ''
});

// 1. Lấy dữ liệu (Cấu hình & Logs)
const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/admin/gacha'); // API trả về { configs: [], logs: [] }
    configs.value = res.data.configs;
    logs.value = res.data.logs;
  } catch (error) {
    ElMessage.error('Không thể tải dữ liệu!');
  } finally {
    loading.value = false;
  }
};

// 2. Xử lý UI Dialog
const openAddDialog = () => {
  isEdit.value = false;
  form.value = { reward_type: 'gold', reward_id: 0, amount: 100, weight: 10, description: '' };
  dialogVisible.value = true;
};

const openEditDialog = (row) => {
  isEdit.value = true;
  form.value = { ...row }; // Clone dữ liệu
  dialogVisible.value = true;
};

const fetchLogs = () => {
  logDialogVisible.value = true;
};

// 3. Logic Lưu (Thêm/Sửa)
const saveConfig = async () => {
  try {
    if (isEdit.value) {
      await axios.put(`/api/admin/gacha/${form.value.id}`, form.value);
      ElMessage.success('Cập nhật thành công!');
    } else {
      await axios.post('/api/admin/gacha', form.value);
      ElMessage.success('Thêm mới thành công!');
    }
    dialogVisible.value = false;
    fetchData(); // Tải lại danh sách
  } catch (error) {
    ElMessage.error('Có lỗi xảy ra!');
  }
};

// 4. Logic Xóa
const deleteConfig = async (id) => {
  try {
    await ElMessageBox.confirm('Bạn có chắc chắn muốn xóa cấu hình này?', 'Xác nhận', {
      type: 'warning'
    });
    await axios.delete(`/api/admin/gacha/${id}`);
    ElMessage.success('Đã xóa!');
    fetchData();
  } catch (err) {
    if (err !== 'cancel') ElMessage.error('Lỗi khi xóa!');
  }
};

// 5. Format JSON Log
const formatJson = (val) => {
  try {
    return JSON.stringify(JSON.parse(val), null, 2);
  } catch {
    return val || 'N/A';
  }
};

onMounted(fetchData);
</script>

<style scoped>
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

pre {
  white-space: pre-wrap;
  word-break: break-word;
  margin: 0;
}
</style>