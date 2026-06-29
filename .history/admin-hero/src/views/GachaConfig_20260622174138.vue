<template>
  <div style="padding:20px">
    <el-card>
      <template #header>
        <div
          style="
            display:flex;
            justify-content:space-between;
            align-items:center;
          "
        >
          <span class="gacha-title">
            🎲 Hero Slash - Gacha Config
        </span>

          <div>
            <el-button type="success" @click="openAddDialog">
              Thêm mới
            </el-button>

            <el-button type="primary" @click="openLogDialog">
              Xem Log
            </el-button>
          </div>
        </div>
      </template>

      <!-- Bảng dữ liệu -->
       
      <el-table
        :data="configs"
        border
        v-loading="loading"
        style="width:100%"
      >
        <el-table-column prop="id" label="ID" width="80" />

        <el-table-column
          prop="reward_type"
          label="Loại thưởng"
        />

        <el-table-column
          prop="reward_id"
          label="Reward ID"
        />

        <el-table-column
          prop="amount"
          label="Số lượng"
        />

        <el-table-column
          prop="weight"
          label="Tỷ lệ"
        />

        <el-table-column
          prop="description"
          label="Mô tả"
        />

        <el-table-column
          label="Thao tác"
          width="220"
        >
          <template #default="{ row }">
            <el-button
              size="small"
              type="primary"
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

    <!-- Dialog Thêm / Sửa -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? 'Cập nhật Config' : 'Thêm Config'"
      width="600px"
    >
      <el-form
        :model="form"
        label-width="120px"
      >
        <el-form-item label="Reward Type">
          <el-input v-model="form.reward_type" />
        </el-form-item>

        <el-form-item label="Reward ID">
          <el-input-number
            v-model="form.reward_id"
            :min="0"
          />
        </el-form-item>

        <el-form-item label="Amount">
          <el-input-number
            v-model="form.amount"
            :min="1"
          />
        </el-form-item>

        <el-form-item label="Weight">
          <el-input-number
            v-model="form.weight"
            :min="1"
          />
        </el-form-item>

        <el-form-item label="Description">
          <el-input
            v-model="form.description"
            type="textarea"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible=false">
          Hủy
        </el-button>

        <el-button
          type="primary"
          :loading="submitLoading"
          @click="saveConfig"
        >
          Lưu
        </el-button>
      </template>
    </el-dialog>

    <!-- Dialog Log -->
    <el-dialog
      v-model="logDialogVisible"
      title="Lịch sử thay đổi"
      width="90%"
    >
      <el-table
        :data="logs"
        border
        max-height="600"
      >
        <el-table-column
          prop="created_at"
          label="Thời gian"
          width="180"
        />

        <el-table-column
          prop="admin_name"
          label="Admin"
          width="120"
        />

        <el-table-column
          prop="action"
          label="Hành động"
          width="120"
        />

        <el-table-column
          prop="target_id"
          label="ID Config"
          width="100"
        />

        <el-table-column label="Giá trị cũ">
          <template #default="{ row }">
            <pre>{{ formatJson(row.old_value) }}</pre>
          </template>
        </el-table-column>

        <el-table-column label="Giá trị mới">
          <template #default="{ row }">
            <pre>{{ formatJson(row.new_value) }}</pre>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { ElMessage, ElMessageBox } from "element-plus";

const API = "http://localhost:8000/api/admin";

const loading = ref(false);
const submitLoading = ref(false);

const configs = ref([]);
const logs = ref([]);

const dialogVisible = ref(false);
const logDialogVisible = ref(false);

const isEdit = ref(false);

const form = ref({
  id: null,
  reward_type: "",
  reward_id: null,
  amount: 1,
  weight: 1,
  description: "",
});

const getConfigs = async () => {
  loading.value = true;

  try {
    const res = await axios.getget(`${API}/gacha`);
    configs.value = res.data.data;
  } catch (error) {
    ElMessage.error("Không tải được dữ liệu");
  }

  loading.value = false;
};

const getLogs = async () => {
  try {
    const res = await axios.get(`${API}/gacha/logs`);
    logs.value = res.data.data;
  } catch (error) {
    ElMessage.error("Không tải được log");
  }
};

const resetForm = () => {
  form.value = {
    id: null,
    reward_type: "",
    reward_id: null,
    amount: 1,
    weight: 1,
    description: "",
  };
};

const openAddDialog = () => {
  resetForm();
  isEdit.value = false;
  dialogVisible.value = true;
};

const openEditDialog = (row) => {
  isEdit.value = true;

  form.value = {
    ...row,
  };

  dialogVisible.value = true;
};

const saveConfig = async () => {
  submitLoading.value = true;

  try {
    if (isEdit.value) {
      await axios.put(
        `${API}/gacha/${form.value.id}`,
        form.value
      );

      ElMessage.success("Cập nhật thành công");
    } else {
      await axios.post(`${API}/gacha`, form.value);

      ElMessage.success("Thêm thành công");
    }

    dialogVisible.value = false;

    await getConfigs();
  } catch (error) {
    console.log(error);

    ElMessage.error("Có lỗi xảy ra");
  }

  submitLoading.value = false;
};

const deleteConfig = async (id) => {
  try {
    await ElMessageBox.confirm(
      "Bạn có chắc muốn xóa?",
      "Cảnh báo",
      {
        type: "warning",
      }
    );

    await axios.delete(`${API}/gacha/${id}`);

    ElMessage.success("Xóa thành công");

    getConfigs();
  } catch (error) {}
};

const openLogDialog = async () => {
  await getLogs();
  logDialogVisible.value = true;
};

const formatJson = (value) => {
  if (!value) return "";

  try {
    return JSON.stringify(
      JSON.parse(value),
      null,
      2
    );
  } catch {
    return value;
  }
};

onMounted(() => {
  getConfigs();
});
</script>

<style scoped>
/* ===========================
   HERO SLASH GACHA THEME
=========================== */

div {
  box-sizing: border-box;
}

:deep(body) {
  background: #0f172a;
}

/* Background */
.gacha-page {
  min-height: 100vh;
  background:
    radial-gradient(circle at top, #312e81 0%, #0f172a 50%),
    linear-gradient(180deg, #111827, #0f172a);
}

/* Card */
:deep(.el-card) {
  border-radius: 20px;
  border: 2px solid #facc15;
  background: rgba(17, 24, 39, 0.95);
  box-shadow:
    0 0 20px rgba(250, 204, 21, 0.2),
    0 0 40px rgba(168, 85, 247, 0.15);
}

/* Header */
:deep(.el-card__header) {
  background: linear-gradient(
    90deg,
    #4c1d95,
    #7c3aed
  );

  color: white;
  font-size: 22px;
  font-weight: bold;
  border-bottom: 2px solid #facc15;
}

/* Table */
:deep(.el-table) {
  background: transparent;
  color: white;
}

:deep(.el-table th.el-table__cell) {
  background: #1e1b4b !important;
  color: #facc15 !important;
  font-size: 14px;
  font-weight: 700;
}

:deep(.el-table tr) {
  background: #111827;
}

:deep(.el-table td) {
  background: #111827;
  color: #f3f4f6;
}

:deep(.el-table__row:hover td) {
  background: rgba(124, 58, 237, 0.2) !important;
}

/* Viền */
:deep(.el-table),
:deep(.el-table th),
:deep(.el-table td) {
  border-color: rgba(250, 204, 21, 0.15);
}

/* Scroll */
:deep(.el-scrollbar__bar) {
  opacity: 0.4;
}

/* Button Add */
:deep(.el-button--success) {
  background: linear-gradient(
    45deg,
    #22c55e,
    #16a34a
  );
  border: none;
  font-weight: bold;
  box-shadow: 0 0 12px rgba(34, 197, 94, 0.4);
}

:deep(.el-button--success:hover) {
  transform: translateY(-2px);
}

/* Button Log */
:deep(.el-button--primary) {
  background: linear-gradient(
    45deg,
    #7c3aed,
    #9333ea
  );
  border: none;
  font-weight: bold;
  box-shadow: 0 0 12px rgba(147, 51, 234, 0.4);
}

/* Button Delete */
:deep(.el-button--danger) {
  background: linear-gradient(
    45deg,
    #dc2626,
    #ef4444
  );
  border: none;
}

/* Dialog */
:deep(.el-dialog) {
  border-radius: 20px;
  overflow: hidden;
  background: #111827;
}

:deep(.el-dialog__header) {
  background: linear-gradient(
    90deg,
    #4c1d95,
    #7c3aed
  );
  color: white;
  padding: 18px;
}

:deep(.el-dialog__title) {
  color: white;
  font-weight: bold;
}

:deep(.el-dialog__body) {
  background: #111827;
  color: white;
}

/* Input */
:deep(.el-input__wrapper),
:deep(.el-textarea__inner),
:deep(.el-input-number) {
  background: #1f2937;
  color: white;
  border-radius: 10px;
}

:deep(.el-input__inner),
:deep(.el-textarea__inner) {
  color: white;
}

/* Label Form */
:deep(.el-form-item__label) {
  color: #facc15;
  font-weight: 600;
}

/* Log JSON */
pre {
  margin: 0;
  color: #e5e7eb;
  background: #0f172a;
  padding: 10px;
  border-radius: 10px;
  font-size: 12px;
  max-height: 180px;
  overflow-y: auto;
  border: 1px solid rgba(250, 204, 21, 0.2);
}

/* Loading */
:deep(.el-loading-mask) {
  background: rgba(15, 23, 42, 0.8);
}

/* Glow title */
.gacha-title {
  font-size: 24px;
  font-weight: bold;
  color: white;

  text-shadow:
    0 0 5px #facc15,
    0 0 15px #facc15,
    0 0 25px #facc15;
}
</style>