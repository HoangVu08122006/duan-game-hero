<template>
  <div class="page-container">
    <el-card>
      <template #header>
        <div class="header">
          <h2>Quản lý Quái & Boss</h2>

          <el-button type="primary" @click="openCreateDialog">
            Thêm mới
          </el-button>
        </div>
      </template>

      <el-tabs v-model="activeType" @tab-change="loadData">
        <el-tab-pane label="Quái vật" name="monster" />
        <el-tab-pane label="Boss" name="boss" />
      </el-tabs>

      <el-table :data="entities" border v-loading="loading">
        <el-table-column prop="id" label="ID" width="80" />

        <el-table-column
          prop="name"
          label="Tên"
        />

        <el-table-column
          prop="base_hp"
          label="HP Gốc"
          width="150"
        />

        <el-table-column label="Thao tác" width="250">
          <template #default="{ row }">
            <el-button
              size="small"
              type="warning"
              @click="editEntity(row)"
            >
              Sửa
            </el-button>

            <el-button
              size="small"
              type="danger"
              @click="deleteEntity(row)"
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
      :title="form.id ? 'Sửa dữ liệu' : 'Thêm dữ liệu'"
      width="500px"
    >
      <el-form :model="form" label-width="120px">
        <el-form-item label="Tên">
          <el-input v-model="form.name" />
        </el-form-item>

        <el-form-item label="HP Gốc">
          <el-input-number
            v-model="form.base_hp"
            :min="1"
            style="width:100%"
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

    <!-- Dialog Publish -->
    <el-dialog
      v-model="publishDialog"
      title="Publish Draft"
      width="400px"
    >
      <el-input
        v-model="draftId"
        placeholder="Nhập Draft ID"
      />

      <template #footer>
        <el-button @click="publishDialog=false">
          Hủy
        </el-button>

        <el-button
          type="success"
          @click="publishDraft"
        >
          Publish
        </el-button>
      </template>
    </el-dialog>

    <el-button
      class="publish-btn"
      type="success"
      @click="publishDialog=true"
    >
      Publish Draft
    </el-button>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'

const activeType = ref('monster')
const entities = ref([])
const loading = ref(false)

const dialogVisible = ref(false)

const publishDialog = ref(false)
const draftId = ref('')

const form = reactive({
  id: null,
  name: '',
  base_hp: 100
})

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  headers: {
    Authorization: `Bearer ${localStorage.getItem('token')}`
  }
})

const loadData = async () => {
  loading.value = true

  try {
    const res = await api.get(
      `/admin/entity/${activeType.value}`
    )

    entities.value = res.data.data
  } catch (err) {
    ElMessage.error('Không tải được dữ liệu')
  }

  loading.value = false
}

const openCreateDialog = () => {
  form.id = null
  form.name = ''
  form.base_hp = 100

  dialogVisible.value = true
}

const editEntity = (row) => {
  form.id = row.id
  form.name = row.name
  form.base_hp = row.base_hp

  dialogVisible.value = true
}

const saveDraft = async () => {
  try {
    const payload = {
      id: form.id,
      name: form.name,
      base_hp: form.base_hp
    }

    const res = await api.post(
      `/admin/entity/${activeType.value}/draft`,
      payload
    )

    ElMessage.success(res.data.message)

    dialogVisible.value = false

    if (res.data.draft_id) {
      ElMessage.success(
        `Draft ID: ${res.data.draft_id}`
      )
    }
  } catch (err) {
    ElMessage.error('Lưu draft thất bại')
  }
}

const publishDraft = async () => {
  try {
    const res = await api.post(
      `/admin/entity/publish/${draftId.value}`
    )

    ElMessage.success(res.data.message)

    publishDialog.value = false

    loadData()
  } catch (err) {
    ElMessage.error('Publish thất bại')
  }
}

const deleteEntity = async (row) => {
  try {
    await ElMessageBox.confirm(
      `Xóa ${row.name}?`,
      'Cảnh báo'
    )

    const res = await api.delete(
      `/admin/entity/${activeType.value}/${row.id}`
    )

    ElMessage.success(res.data.message)

    loadData()
  } catch {}
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.page-container {
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.publish-btn {
  margin-top: 20px;
}
</style>