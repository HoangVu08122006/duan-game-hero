<template>
  <div class="reward-config-page p-6">
  <div class="p-6">
    <el-tabs type="card">
      <el-tab-pane label="Danh sách Gói">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">Quản lý Gói phần thưởng</h2>
          <el-button type="primary" @click="openConfigModal()">+ Tạo Gói mới</el-button>
        </div>

        <el-table :data="configs" border v-loading="loading">
          <el-table-column prop="name" label="Tên Gói" />
          <el-table-column prop="duration" label="Số ngày" />
          <el-table-column prop="status" label="Trạng thái">
            <template #default="scope">
              <el-tag :type="scope.row.status === 'active' ? 'success' : 'info'">{{ scope.row.status }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="Hành động" width="280">
            <template #default="scope">
              <el-button size="small" @click="editConfig(scope.row)">Sửa</el-button>
              <el-button v-if="scope.row.status !== 'active'" size="small" type="danger" @click="deleteConfig(scope.row.id)">Xóa</el-button>
              <el-button v-if="scope.row.status !== 'active'" size="small" type="success" @click="activateConfig(scope.row.id)">Active</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-tab-pane>

        <el-tab-pane label="Nhật ký thay đổi">
            <el-table :data="auditLogs" border>
                <el-table-column prop="created_at" label="Thời gian" width="180" />
                <el-table-column prop="action" label="Hành động" width="120" />
                <el-table-column prop="config_id" label="ID Gói" width="100" />
                <el-table-column label="Chi tiết">
                <template #default="scope">
                    <el-popover placement="left" width="500" trigger="click">
                    <template #reference>
                        <el-button size="small" type="primary" plain>Xem chi tiết</el-button>
                    </template>
                    <div class="log-detail-box">
                        <h4 class="font-bold text-red-600 mb-1">Dữ liệu cũ:</h4>
                        <pre class="log-pre">{{ formatLog(scope.row.old_data) }}</pre>
                        <h4 class="font-bold text-green-600 mt-3 mb-1">Dữ liệu mới:</h4>
                        <pre class="log-pre">{{ formatLog(scope.row.new_data) }}</pre>
                    </div>
                    </el-popover>
                </template>
                </el-table-column>
            </el-table>
            </el-tab-pane>
</el-tabs>
    <el-dialog v-model="dialogVisible" :title="isEdit ? 'Chỉnh sửa Gói' : 'Tạo Gói mới'" width="70%">
      <el-form :model="form" label-width="100px">
        <el-form-item label="Tên Gói"> <el-input v-model="form.name" /> </el-form-item>
        <el-form-item label="Số ngày"> <el-input-number v-model="form.duration" @change="generateItems" /> </el-form-item>
      </el-form>

      <el-table :data="form.items" border height="300px">
        <el-table-column prop="day_index" label="Ngày" width="80" />
        <el-table-column label="Tên quà">
          <template #default="scope"> <el-input v-model="scope.row.name" /> </template>
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
          <template #default="scope"> <el-input-number v-model="scope.row.amount" /> </template>
        </el-table-column>
      </el-table>

      <template #footer>
        <el-button @click="dialogVisible = false">Hủy</el-button>
        <el-button type="primary" @click="saveConfig">Lưu cấu hình</el-button>
      </template>
    </el-dialog>
  </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';

const configs = ref([]);
const auditLogs = ref([]);
const loading = ref(false);
const dialogVisible = ref(false);
const isEdit = ref(false);
const form = ref({ id: null, name: '', duration: 7, items: [] });

const fetchData = async () => {
  loading.value = true;
  // Đồng thời lấy cả danh sách gói và log
  const [resConfigs, resLogs] = await Promise.all([
    axios.get('/api/admin/configs'),
    axios.get('/api/admin/logs') // Đảm bảo bạn đã tạo route này
  ]);
  configs.value = resConfigs.data;
  auditLogs.value = resLogs.data;
  loading.value = false;
};

const generateItems = () => {
  if (form.value.items.length === 0 || isEdit.value === false) {
    form.value.items = Array.from({ length: form.value.duration }, (_, i) => ({
      day_index: i + 1, name: '', reward_type: 'gold', amount: 0
    }));
  }
};

const formatLog = (jsonString) => {
  if (!jsonString) return 'Không có dữ liệu';
  try {
    const data = typeof jsonString === 'string' ? JSON.parse(jsonString) : jsonString;
    return JSON.stringify(data, null, 2); // Định dạng đẹp với 2 khoảng trắng
  } catch (e) {
    return jsonString;
  }
};

const openConfigModal = () => {
  isEdit.value = false;
  form.value = { id: null, name: '', duration: 7, items: [] };
  generateItems();
  dialogVisible.value = true;
};

const editConfig = async (row) => {
  isEdit.value = true;
  const res = await axios.get(`/api/admin/configs/${row.id}`);
  form.value = res.data;
  dialogVisible.value = true;
};

const saveConfig = async () => {
  await axios.post('/api/admin/configs', form.value);
  ElMessage.success('Lưu thành công!');
  dialogVisible.value = false;
  fetchData();
};

const activateConfig = async (id) => {
  await axios.post(`/api/admin/configs/${id}/activate`);
  ElMessage.success('Đã kích hoạt!');
  fetchData();
};

const deleteConfig = async (id) => {
  try {
    await ElMessageBox.confirm('Bạn có chắc muốn xóa?', 'Cảnh báo', { type: 'warning' });
    await axios.delete(`/api/admin/configs/${id}`);
    ElMessage.success('Đã xóa!');
    fetchData();
  } catch (err) { if (err !== 'cancel') ElMessage.error('Lỗi khi xóa!'); }
};



onMounted(fetchData);
</script>

<style scoped>
.reward-config-page {
  min-height: 100vh;
  background: #f8fafc;
}

/* Header */
.reward-config-page h2 {
  color: #1e293b;
  font-size: 24px;
  font-weight: 700;
}

/* Card Tabs */
.reward-config-page .el-tabs__header {
  margin-bottom: 20px;
}

.reward-config-page .el-tabs--card > .el-tabs__header .el-tabs__item {
  border-radius: 10px 10px 0 0;
  font-weight: 600;
}

.reward-config-page .el-tabs__item.is-active {
  color: #409eff;
}

/* Table */
.reward-config-page .el-table {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(15, 23, 42, 0.08);
}

.reward-config-page .el-table th {
  background: #f1f5f9 !important;
  color: #475569 !important;
  font-weight: 700;
}

.reward-config-page .el-table td {
  color: #334155;
}

.reward-config-page .el-table__row:hover td {
  background: #f8fafc !important;
}

/* Button */
.reward-config-page .el-button {
  border-radius: 8px;
  font-weight: 600;
}

.reward-config-page .el-button--primary {
  background: #3b82f6;
  border: none;
}

.reward-config-page .el-button--success {
  background: #10b981;
  border: none;
}

.reward-config-page .el-button--danger {
  background: #ef4444;
  border: none;
}

/* Dialog */
.reward-config-page .el-dialog {
  border-radius: 16px;
  overflow: hidden;
}

.reward-config-page .el-dialog__header {
  border-bottom: 1px solid #e2e8f0;
}

.reward-config-page .el-dialog__title {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
}

/* Form */
.reward-config-page .el-form-item__label {
  font-weight: 600;
  color: #475569;
}

.reward-config-page .el-input__wrapper,
.reward-config-page .el-textarea__inner,
.reward-config-page .el-select__wrapper,
.reward-config-page .el-input-number {
  border-radius: 10px;
}

/* Popover log */
.log-detail-box {
  max-height: 400px;
  overflow-y: auto;
}

.log-pre {
  background: #0f172a;
  color: #e2e8f0;
  padding: 12px;
  border-radius: 10px;
  font-size: 13px;
  white-space: pre-wrap;
  word-break: break-word;
}

/* Status Tag */
.el-tag--success {
  border: none;
  background: #dcfce7;
  color: #166534;
}

.el-tag--info {
  border: none;
  background: #e2e8f0;
  color: #475569;
}

/* Scroll */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 20px;
}
</style>