<template>
  <div style="padding:20px">
    <el-card>
      <template #header>
        <div
          style="
            display:flex;
            justify-content:space-between;
            align-items:center
          "
        >
          <span>Quản lý Quái & Boss</span>

          <el-button
            type="success"
            @click="openAddDialog"
          >
            Thêm mới
          </el-button>
        </div>
      </template>

      <el-tabs
        v-model="entityType"
        @tab-change="fetchEntities"
      >
        <el-tab-pane
          label="Quái vật"
          name="monster"
        />

        <el-tab-pane
          label="Boss"
          name="boss"
        />
      </el-tabs>

      <el-table
        :data="entityList"
        border
        v-loading="loading"
      >
        <el-table-column
          prop="id"
          label="ID"
          width="80"
        />

        <el-table-column
          prop="name"
          label="Tên"
        />

        <el-table-column
          prop="base_hp"
          label="HP"
          width="120"
        />

        <el-table-column
          label="Thao tác"
          width="220"
        >
          <template #default="scope">

            <el-button
              type="primary"
              size="small"
              @click="openEditDialog(scope.row)"
            >
              Sửa
            </el-button>

            <el-button
              type="danger"
              size="small"
              @click="deleteEntity(scope.row)"
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
      :title="isEdit ? 'Cập nhật' : 'Thêm mới'"
      width="500px"
    >
      <el-form
        :model="form"
        label-width="120px"
      >
        <el-form-item label="Tên">
          <el-input v-model="form.name" />
        </el-form-item>

        <el-form-item label="HP Gốc">
          <el-input-number
            v-model="form.base_hp"
            :min="1"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible=false">
          Hủy
        </el-button>

        <el-button
          type="primary"
          @click="saveDraft"
        >
          Lưu Draft
        </el-button>
      </template>
    </el-dialog>

    <!-- Publish -->

    <el-card style="margin-top:20px">
      <el-input
        v-model="draftId"
        placeholder="Nhập Draft ID"
      />

      <el-button
        style="margin-top:10px"
        type="success"
        @click="publishDraft"
      >
        Publish Draft
      </el-button>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'

const entityType = ref('monster')
const entityList = ref([])
const loading = ref(false)

const dialogVisible = ref(false)
const isEdit = ref(false)

const draftId = ref('')

const form = ref({
  id: null,
  name: '',
  base_hp: 100
})

const fetchEntities = async () => {
  loading.value = true

  try {
    const res = await axios.get(
      `/api/admin/entity/${entityType.value}`
    )

    entityList.value = res.data.data
  } catch (e) {
    ElMessage.error('Không tải được dữ liệu')
  }

  loading.value = false
}

const openAddDialog = () => {
  isEdit.value = false

  form.value = {
    id: null,
    name: '',
    base_hp: 100
  }

  dialogVisible.value = true
}

const openEditDialog = (row) => {
  isEdit.value = true

  form.value = {
    id: row.id,
    name: row.name,
    base_hp: row.base_hp
  }

  dialogVisible.value = true
}

const saveDraft = async () => {
  try {

    const res = await axios.post(
      `/api/admin/entity/${entityType.value}/draft`,
      form.value
    )

    ElMessage.success(res.data.message)

    dialogVisible.value = false

    fetchEntities()

  } catch (e) {
    ElMessage.error('Lưu draft thất bại')
  }
}

const publishDraft = async () => {
  try {

    const res = await axios.post(
      `/api/admin/entity/publish/${draftId.value}`
    )

    ElMessage.success(res.data.message)

    fetchEntities()

  } catch (e) {
    ElMessage.error('Publish thất bại')
  }
}

const deleteEntity = async (row) => {
  try {

    await ElMessageBox.confirm(
      `Xóa ${row.name}?`,
      'Cảnh báo'
    )

    const res = await axios.delete(
      `/api/admin/entity/${entityType.value}/${row.id}`
    )

    ElMessage.success(res.data.message)

    fetchEntities()

  } catch (e) {}
}

onMounted(() => {
  fetchEntities()
})
</script>