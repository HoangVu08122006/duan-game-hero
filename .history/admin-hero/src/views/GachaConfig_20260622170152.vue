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
          <span>Quản lý Gacha Config</span>

          <div>
            <el-button type="success" @click="openAddDialog">
              Thêm mới
            </el-button>

            <el-button type="primary" @click="loadLogs">
              Xem Log
            </el-button>
          </div>
        </div>
      </template>

      <el-table
        :data="configs"
        border
        v-loading="loading"
      >
        <el-table-column prop="id" label="ID" width="80"/>

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
          label="Tỉ lệ"
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
              type="primary"
              size="small"
              @click="openEditDialog(row)"
            >
              Sửa
            </el-button>

            <el-button
              type="danger"
              size="small"
              @click="deleteConfig(row)"
            >
              Xóa
            </el-button>

          </template>
        </el-table-column>

      </el-table>

    </el-card>

    <!-- Dialog thêm/sửa -->

    <el-dialog
      :title="isEdit ? 'Cập nhật cấu hình' : 'Thêm cấu hình'"
      v-model="dialogVisible"
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

        <el-button @click="dialogVisible = false">
          Hủy
        </el-button>

        <el-button
          type="primary"
          :loading="submitLoading"
          @click="submitForm"
        >
          Lưu
        </el-button>

      </template>

    </el-dialog>

    <!-- Dialog Log -->

    <el-dialog
      title="Nhật ký thay đổi"
      v-model="logDialogVisible"
      width="1200px"
    >

      <el-table
        :data="logs"
        border
      >
        <el-table-column
          prop="id"
          label="ID"
          width="70"
        />

        <el-table-column
          prop="action"
          label="Action"
          width="100"
        />

        <el-table-column
          prop="target_id"
          label="Target ID"
          width="100"
        />

        <el-table-column
          prop="admin_name"
          label="Admin"
          width="150"
        />

        <el-table-column
          prop="created_at"
          label="Thời gian"
          width="180"
        />

        <el-table-column
          label="Old Value"
        >
          <template #default="{ row }">
            <pre>{{ row.old_value }}</pre>
          </template>
        </el-table-column>

        <el-table-column
          label="New Value"
        >
          <template #default="{ row }">
            <pre>{{ row.new_value }}</pre>
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
const submitLoading = ref(false);

const dialogVisible = ref(false);
const logDialogVisible = ref(false);

const isEdit = ref(false);

const form = ref({
  id: null,
  reward_type: "",
  reward_id: null,
  amount: 1,
  weight: 1,
  description: ""
});

const token = localStorage.getItem("token");

const api = axios.create({
  baseURL: "http://127.0.0.1:8000/api",
  headers: {
    Authorization: `Bearer ${token}`
  }
});

const loadConfigs = async () => {
  loading.value = true;

  try {
    const res = await api.get("/admin");

    configs.value = res.data.data;
  } catch (e) {
    ElMessage.error("Không tải được dữ liệu");
  }

  loading.value = false;
};

const loadLogs = async () => {
  try {
    const res = await api.get("/admin/gacha-config/logs");

    logs.value = res.data.data;

    logDialogVisible.value = true;
  } catch (e) {
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
    description: ""
  };
};

const openAddDialog = () => {
  isEdit.value = false;

  resetForm();

  dialogVisible.value = true;
};

const openEditDialog = (row) => {
  isEdit.value = true;

  form.value = {
    ...row
  };

  dialogVisible.value = true;
};

const submitForm = async () => {
  submitLoading.value = true;

  try {

    if (isEdit.value) {

      await api.put(
        `/admin/gacha-config/${form.value.id}`,
        form.value
      );

      ElMessage.success("Cập nhật thành công");

    } else {

      await api.post(
        "/admin/gacha-config",
        form.value
      );

      ElMessage.success("Thêm thành công");
    }

    dialogVisible.value = false;

    loadConfigs();

  } catch (e) {

    ElMessage.error(
      e.response?.data?.message || "Có lỗi xảy ra"
    );
  }

  submitLoading.value = false;
};

const deleteConfig = async (row) => {

  try {

    await ElMessageBox.confirm(
      `Xóa cấu hình ID ${row.id}?`,
      "Cảnh báo",
      {
        type: "warning"
      }
    );

    await api.delete(
      `/admin/gacha-config/${row.id}`
    );

    ElMessage.success("Đã xóa");

    loadConfigs();

  } catch (e) {}
};

onMounted(() => {
  loadConfigs();
});
</script>

<style scoped>
pre {
  white-space: pre-wrap;
  word-break: break-word;
  margin: 0;
}
</style>