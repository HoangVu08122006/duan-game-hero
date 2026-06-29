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
/* ==========================
   GAME ADMIN THEME
========================== */

.reward-config-page {
  min-height: 100vh;
  padding: 24px;

  background:
    radial-gradient(circle at top left,
      rgba(59,130,246,.15),
      transparent 30%),
    radial-gradient(circle at top right,
      rgba(16,185,129,.15),
      transparent 30%),
    linear-gradient(
      135deg,
      #0f172a 0%,
      #111827 40%,
      #1e293b 100%
    );

  color: #fff;
}

/* ==========================
   HEADER
========================== */

.reward-config-page h2 {
  color: white;
  font-size: 28px;
  font-weight: 700;
  margin: 0;
}

.reward-config-page h2::before {
  content: "🎁 ";
}

/* ==========================
   CARD
========================== */

.reward-config-page .el-card {
  background: rgba(17, 24, 39, 0.8);
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 20px;
  backdrop-filter: blur(15px);

  box-shadow:
    0 8px 30px rgba(0,0,0,.3),
    0 0 30px rgba(59,130,246,.08);
}

.reward-config-page .el-card:hover {
  box-shadow:
    0 12px 40px rgba(0,0,0,.4),
    0 0 35px rgba(59,130,246,.15);
}

/* ==========================
   TABS
========================== */

.reward-config-page .el-tabs__header {
  border: none !important;
}

.reward-config-page .el-tabs__nav {
  border: none !important;
}

.reward-config-page .el-tabs__item {
  color: #94a3b8;
  border: none !important;
  background: rgba(255,255,255,.05);
  margin-right: 10px;
  border-radius: 12px 12px 0 0;
}

.reward-config-page .el-tabs__item.is-active {
  color: white;
  background: linear-gradient(
    135deg,
    #3b82f6,
    #2563eb
  );
}

/* ==========================
   TABLE
========================== */

.reward-config-page .el-table {
  background: transparent !important;
  color: white;
  border-radius: 16px;
  overflow: hidden;
}

.reward-config-page .el-table::before {
  display: none;
}

.reward-config-page .el-table th {
  background: #1e293b !important;
  color: #cbd5e1 !important;
  border: none !important;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.reward-config-page .el-table td {
  background: rgba(17,24,39,.8) !important;
  color: #e2e8f0;
  border-color: rgba(255,255,255,.05) !important;
}

.reward-config-page .el-table__row:hover td {
  background: rgba(59,130,246,.08) !important;
}

/* ==========================
   BUTTONS
========================== */

.reward-config-page .el-button {
  border-radius: 10px;
  font-weight: 600;
  border: none;
}

.reward-config-page .el-button--primary {
  background: linear-gradient(
    135deg,
    #3b82f6,
    #2563eb
  );
}

.reward-config-page .el-button--success {
  background: linear-gradient(
    135deg,
    #10b981,
    #059669
  );
}

.reward-config-page .el-button--danger {
  background: linear-gradient(
    135deg,
    #ef4444,
    #dc2626
  );
}

.reward-config-page .el-button:hover {
  transform: translateY(-2px);
}

/* ==========================
   TAG
========================== */

.reward-config-page .el-tag--success {
  background: rgba(16,185,129,.15);
  color: #34d399;
  border: 1px solid rgba(16,185,129,.3);
}

.reward-config-page .el-tag--info {
  background: rgba(148,163,184,.15);
  color: #cbd5e1;
  border: 1px solid rgba(148,163,184,.3);
}

/* ==========================
   DIALOG
========================== */

.el-dialog {
  background: #111827 !important;
  border-radius: 20px !important;
  overflow: hidden;
}

.el-dialog__header {
  background: #1e293b;
  padding: 20px;
  border-bottom: 1px solid rgba(255,255,255,.08);
}

.el-dialog__title {
  color: white !important;
  font-size: 18px;
  font-weight: 700;
}

.el-dialog__body {
  background: #111827;
}

.el-dialog__footer {
  background: #111827;
}

/* ==========================
   INPUT
========================== */

.el-input__wrapper,
.el-select__wrapper,
.el-textarea__inner,
.el-input-number {
  background: #1e293b !important;
  border: 1px solid #334155 !important;
  border-radius: 12px !important;
}

.el-input__inner,
.el-textarea__inner {
  color: white !important;
}

.el-form-item__label {
  color: #cbd5e1 !important;
  font-weight: 600;
}

/* ==========================
   LOG POPUP
========================== */

.log-detail-box {
  max-height: 450px;
  overflow-y: auto;
}

.log-pre {
  background: #0f172a;
  color: #e2e8f0;
  padding: 14px;
  border-radius: 10px;
  font-size: 12px;
  border: 1px solid rgba(255,255,255,.08);
}

/* ==========================
   SCROLLBAR
========================== */

::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #111827;
}

::-webkit-scrollbar-thumb {
  background: #475569;
  border-radius: 999px;
}

/* ==========================
   FLOATING GLOW
========================== */

.reward-config-page::before {
  content: "";
  position: fixed;
  width: 350px;
  height: 350px;
  background: rgba(59,130,246,.15);
  filter: blur(120px);
  top: -100px;
  left: -100px;
  pointer-events: none;
}

.reward-config-page::after {
  content: "";
  position: fixed;
  width: 350px;
  height: 350px;
  background: rgba(16,185,129,.15);
  filter: blur(120px);
  bottom: -100px;
  right: -100px;
  pointer-events: none;
}
</style>