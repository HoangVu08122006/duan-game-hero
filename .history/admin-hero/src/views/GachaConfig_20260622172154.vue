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

            <el-button type="primary" @click="openLogDialog">
              Xem Log
            </el-button>
          </div>
        </div>
      </template>

      <!-- Bảng dữ liệu -->
       <div class="gacha-page">
  <el-card class="gacha-card">
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
    </el-card>
</div>

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
    const res = await axios.get(API);
    configs.value = res.data.data;
  } catch (error) {
    ElMessage.error("Không tải được dữ liệu");
  }

  loading.value = false;
};

const getLogs = async () => {
  try {
    const res = await axios.get(`${API}/logs`);
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
        `${API}/${form.value.id}`,
        form.value
      );

      ElMessage.success("Cập nhật thành công");
    } else {
      await axios.post(API, form.value);

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

    await axios.delete(`${API}/${id}`);

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
pre {
  margin: 0;
  white-space: pre-wrap;
  word-break: break-word;
}
</style>