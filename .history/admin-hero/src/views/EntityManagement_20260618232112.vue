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
          <span>Quản lý Quái & Boss</span>

          <div>
            <el-button type="success" @click="openAddDialog">
              Thêm mới
            </el-button>

            <el-button type="warning" @click="publishDialogVisible = true">
              Publish Draft
            </el-button>
          </div>
        </div>
      </template>

      <el-tabs
        v-model="entityType"
        @tab-change="fetchEntities"
      >
        <el-tab-pane label="Quái vật" name="monster" />
        <el-tab-pane label="Boss" name="boss" />
      </el-tabs>

      <el-table
        :data="entityList"
        border
        v-loading="loading"
        style="width:100%"
      >
        <el-table-column prop="id" label="ID" width="70" />

        <el-table-column prop="name" label="Tên" min-width="180" />

        <el-table-column
          v-if="entityType === 'monster'"
          prop="type"
          label="Loại"
          width="120"
        />

        <el-table-column
          prop="prefab_name"
          label="Prefab"
          width="180"
        />

        <el-table-column
          prop="base_hp"
          label="HP"
          width="100"
        />

        <el-table-column
          :prop="entityType === 'monster'
            ? 'base_atk'
            : 'base_attack'"
          label="ATK"
          width="100"
        />

        <el-table-column
          prop="base_gold"
          label="Gold"
          width="100"
        />

        <el-table-column
          prop="base_exp"
          label="EXP"
          width="100"
        />

        <el-table-column label="Thao tác" width="180">
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
              @click="deleteEntity(row)"
            >
              Xóa
            </el-button>

          </template>
        </el-table-column>
      </el-table>
    </el-card>

    

    <!-- Dialog Thêm/Sửa -->

    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? 'Chỉnh sửa' : 'Thêm mới'"
      width="700px"
    >
      <el-form
        :model="form"
        label-width="140px"
      >
        <el-form-item label="Tên">
          <el-input v-model="form.name" />
        </el-form-item>

        <el-form-item
          v-if="entityType === 'monster'"
          label="Loại Quái"
        >
          <el-input v-model="form.type" />
        </el-form-item>

        <el-form-item label="Prefab Name">
          <el-input v-model="form.prefab_name" />
        </el-form-item>

        <el-form-item label="Image URL">
          <el-input v-model="form.image_url" />
        </el-form-item>

        <el-divider>Chỉ số</el-divider>

        <el-form-item label="HP">
          <el-input-number
            v-model="form.base_hp"
            :min="1"
            style="width:100%"
          />
        </el-form-item>

        <el-form-item label="ATK">
          <el-input-number
            v-if="entityType === 'monster'"
            v-model="form.base_atk"
            :min="0"
            style="width:100%"
          />

          <el-input-number
            v-else
            v-model="form.base_attack"
            :min="0"
            style="width:100%"
          />
        </el-form-item>

        <el-form-item label="Gold">
          <el-input-number
            v-model="form.base_gold"
            :min="0"
            style="width:100%"
          />
        </el-form-item>

        <el-form-item label="EXP">
          <el-input-number
            v-model="form.base_exp"
            :min="0"
            style="width:100%"
          />
        </el-form-item>

        <el-form-item
          v-if="entityType === 'boss'"
          label="Skills"
        >
          <el-input
            v-model="form.skills"
            type="textarea"
            :rows="5"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">
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

    <!-- Publish Draft -->

    <el-dialog
      v-model="publishDialogVisible"
      title="Publish Draft"
      width="400px"
    >
      <el-input
        v-model="draftId"
        placeholder="Nhập Draft ID"
      />

      <template #footer>
        <el-button
          @click="publishDialogVisible = false"
        >
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'
const draftList = ref([])
const draftLoading = ref(false)
const draftDialogVisible = ref(false)
const draftDetail = ref({})

const entityType = ref('monster')
const entityList = ref([])
const loading = ref(false)

const dialogVisible = ref(false)
const publishDialogVisible = ref(false)

const isEdit = ref(false)
const draftId = ref('')

const defaultForm = () => ({
  id: null,

  name: '',
  type: '',

  prefab_name: '',
  image_url: '',

  base_hp: 1,
  base_atk: 0,
  base_attack: 0,

  base_gold: 0,
  base_exp: 0,

  skills: ''
})

const form = ref(defaultForm())

const fetchEntities = async () => {
  loading.value = true

  try {
    const res = await axios.get(
      `/api/admin/entity/${entityType.value}`
    )

    entityList.value = res.data.data
  } catch (e) {
    ElMessage.error('Không tải được dữ liệu')
  } finally {
    loading.value = false
  }
}

const openAddDialog = () => {
  isEdit.value = false
  form.value = defaultForm()
  dialogVisible.value = true
}

const openEditDialog = (row) => {
  isEdit.value = true
  form.value = { ...row }
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

  } catch (e) {
    ElMessage.error(
      e.response?.data?.message ||
      'Lưu draft thất bại'
    )
  }
}

const publishDraft = async () => {
  try {
    const res = await axios.post(
      `/api/admin/entity/publish/${draftId.value}`
    )

    ElMessage.success(res.data.message)

    publishDialogVisible.value = false
    fetchEntities()

  } catch (e) {
    ElMessage.error('Publish thất bại')
  }
}

const deleteEntity = async (row) => {
  try {
    await ElMessageBox.confirm(
      `Xóa ${row.name}?`,
      'Xác nhận'
    )

    const res = await axios.delete(
      `/api/admin/entity/${entityType.value}/${row.id}`
    )

    ElMessage.success(res.data.message)

    fetchEntities()

  } catch {}
}

const fetchDrafts = async () => {
  draftLoading.value = true

  try {
    const res = await axios.get('/api/admin/entity/drafts')
    draftList.value = res.data.data
  } catch (e) {
    ElMessage.error('Không tải được danh sách draft')
  } finally {
    draftLoading.value = false
  }
}

const viewDraft = (row) => {
  draftDetail.value = row
  draftDialogVisible.value = true
}

onMounted(() => {
  fetchEntities()
})
</script>