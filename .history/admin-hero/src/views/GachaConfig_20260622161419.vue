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
import { ref, onMounted } from "vue";
import axios from "axios";
import { ElMessage, ElMessageBox } from "element-plus";

const configs = ref([]);
const logs = ref([]);
const loading = ref(false);

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

const API_URL =
  "http://127.0.0.1:8000/api/admin/gacha-config";

const authHeader = () => ({
  headers: {
    Authorization: `Bearer ${localStorage.getItem("token")}`,
  },
});

const fetchConfigs = async () => {
  loading.value = true;

  try {
    const res = await axios.get(
      API_URL,
      authHeader()
    );

    configs.value = res.data.data;
  } catch (error) {
    ElMessage.error("Không tải được dữ liệu");
  }

  loading.value = false;
};

const fetchLogs = async () => {
  try {
    const res = await axios.get(
      `${API_URL}/logs`,
      authHeader()
    );

    logs.value = res.data.data;
    logDialogVisible.value = true;
  } catch (error) {
    ElMessage.error("Không tải được log");
  }
};

const openAddDialog = () => {
  isEdit.value = false;

  form.value = {
    id: null,
    reward_type: "",
    reward_id: null,
    amount: 1,
    weight: 1,
    description: "",
  };

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
  try {
    if (isEdit.value) {
      await axios.put(
        `${API_URL}/${form.value.id}`,
        form.value,
        authHeader()
      );

      ElMessage.success("Cập nhật thành công");
    } else {
      await axios.post(
        API_URL,
        form.value,
        authHeader()
      );

      ElMessage.success("Thêm thành công");
    }

    dialogVisible.value = false;
    fetchConfigs();
  } catch (error) {
    ElMessage.error(
      error.response?.data?.message ||
        "Có lỗi xảy ra"
    );
  }
};

const deleteConfig = async (id) => {
  try {
    await ElMessageBox.confirm(
      "Bạn chắc chắn muốn xóa?",
      "Cảnh báo",
      {
        type: "warning",
      }
    );

    await axios.delete(
      `${API_URL}/${id}`,
      authHeader()
    );

    ElMessage.success("Xóa thành công");

    fetchConfigs();
  } catch (error) {}
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
  fetchConfigs();
});
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