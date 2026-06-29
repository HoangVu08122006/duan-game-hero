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

    <el-card style="margin-top:20px">
  <template #header>
    <span>Danh sách Draft</span>
  </template>

  <el-table
    :data="draftList"
    border
    v-loading="draftLoading"
  >
    <el-table-column
      prop="id"
      label="Draft ID"
      width="100"
    />

    <el-table-column
      prop="entity_type"
      label="Loại"
      width="120"
    />

    <el-table-column
      prop="target_id"
      label="Entity ID"
      width="120"
    />

    <el-table-column
      prop="status"
      label="Trạng thái"
      width="120"
    >
      <template #default="{ row }">
        <el-tag
          v-if="row.status === 'pending'"
          type="warning"
        >
          Chờ duyệt
        </el-tag>

        <el-tag
          v-else
          type="success"
        >
          Đã duyệt
        </el-tag>
      </template>
    </el-table-column>

    <el-table-column
      prop="created_at"
      label="Ngày tạo"
      width="180"
    />

    <el-table-column
      label="Thao tác"
      width="250"
    >
      <template #default="{ row }">

        <el-button
          size="small"
          @click="viewDraft(row)"
        >
          Xem
        </el-button>

        <el-button
          type="success"
          size="small"
          @click="publishDraft(row.id)"
        >
          Publish
        </el-button>

        <el-button
      type="danger"
      size="small"
      @click="deleteDraft(row)"
    >
      Xóa
    </el-button>

      </template>
    </el-table-column>
  </el-table>
</el-card>

<el-dialog
  v-model="draftDialogVisible"
  title="Chi tiết Draft"
  width="700px"
>
  <el-descriptions
    border
    :column="1"
  >
    <el-descriptions-item label="Draft ID">
      {{ draftDetail.id }}
    </el-descriptions-item>

    <el-descriptions-item label="Loại">
      {{ draftDetail.entity_type }}
    </el-descriptions-item>

    <el-descriptions-item label="Trạng thái">
      {{ draftDetail.status }}
    </el-descriptions-item>
  </el-descriptions>

  <el-divider />

  <pre style="white-space: pre-wrap">
{{ JSON.stringify(JSON.parse(draftDetail.payload || '{}'), null, 2) }}
  </pre>
</el-dialog>

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

  <el-card style="margin-top:20px">
  <template #header>
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <span>Lịch sử thao tác (Logs)</span>
      <el-button size="small" @click="fetchLogs">Làm mới</el-button>
    </div>

  </template>

  <el-table :data="logList" border v-loading="logLoading" style="width: 100%">
    <el-table-column prop="id" label="Log ID" width="80" />
    <el-table-column prop="action" label="Hành động" width="150" />
    <el-table-column prop="target_type" label="Loại" width="100" />
    <el-table-column prop="target_id" label="Entity ID" width="100" />
    <el-table-column prop="description" label="Mô tả" min-width="200" />
    <el-table-column prop="created_at" label="Thời gian" width="180" />
  </el-table>
</el-card>
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

// Thêm các biến này vào phần khai báo state
const logList = ref([])
const logLoading = ref(false)

// Thêm hàm lấy dữ liệu log
const fetchLogs = async () => {
  logLoading.value = true
  try {
    // Giả sử bạn có route này ở Backend (hoặc tạo mới)
    const res = await axios.get('/api/admin/logs') 
    logList.value = res.data.data
  } catch (e) {
    ElMessage.error('Không tải được lịch sử log')
  } finally {
    logLoading.value = false
  }
}

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

const publishDraft = async (id = null) => {
  try {
    const targetId = id || draftId.value

    const res = await axios.post(
      `/api/admin/entity/publish/${targetId}`
    )

    ElMessage.success(res.data.message)

    publishDialogVisible.value = false

    fetchEntities()
    fetchDrafts()

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

// Hàm gọi API xóa
const deleteDraft = async (row) => {
  try {
    await ElMessageBox.confirm('Bạn có chắc chắn muốn xóa bản nháp này?', 'Cảnh báo', {
      confirmButtonText: 'Xóa',
      cancelButtonText: 'Hủy',
      type: 'warning'
    });

    // Gọi đúng đường dẫn mới đã sửa ở trên
    await axios.delete(`/api/admin/delete-draft/${row.id}`);
    
    ElMessage.success('Đã xóa thành công');
    fetchDrafts(); 
  } catch (e) {
    if (e !== 'cancel') {
      ElMessage.error('Xóa thất bại');
    }
  }
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
  fetchDrafts()
  fetchLogs();
})
</script>

<style scoped>

/* ===========================
   HERO SLASH - MONSTER ADMIN
=========================== */

.monster-admin{
  min-height:100vh;
  padding:20px;
  background:
    radial-gradient(circle at top,#3b0764 0%,#111827 35%,#020617 100%);
}

/* CARD */

:deep(.el-card){
  background:rgba(15,23,42,.95);
  border:1px solid rgba(251,191,36,.25);
  border-radius:20px;
  overflow:hidden;

  box-shadow:
    0 0 15px rgba(251,191,36,.1),
    0 0 40px rgba(168,85,247,.08);
}

:deep(.el-card__header){
  background:
    linear-gradient(
      90deg,
      #581c87,
      #7e22ce,
      #9333ea
    );

  color:white;
  font-weight:700;
  font-size:18px;

  border-bottom:
    2px solid rgba(251,191,36,.4);
}

/* TABS */

:deep(.el-tabs__item){
  color:#cbd5e1;
  font-weight:600;
}

:deep(.el-tabs__item.is-active){
  color:#fbbf24;
}

:deep(.el-tabs__active-bar){
  background:#fbbf24;
}

/* TABLE */

:deep(.el-table){
  background:transparent;
  color:white;
}

:deep(.el-table th.el-table__cell){
  background:#1e293b !important;
  color:#fbbf24 !important;
  font-weight:700;
}

:deep(.el-table tr){
  background:#0f172a;
}

:deep(.el-table td){
  background:#0f172a;
  color:#e2e8f0;
}

:deep(.el-table__row:hover td){
  background:
    rgba(168,85,247,.15) !important;
}

/* BORDER */

:deep(.el-table),
:deep(.el-table td),
:deep(.el-table th){
  border-color:
    rgba(251,191,36,.12);
}

/* BUTTONS */

:deep(.el-button--success){
  background:
    linear-gradient(
      45deg,
      #16a34a,
      #22c55e
    );

  border:none;
  font-weight:700;

  box-shadow:
    0 0 10px rgba(34,197,94,.35);
}

:deep(.el-button--success:hover){
  transform:translateY(-2px);
}

:deep(.el-button--warning){
  background:
    linear-gradient(
      45deg,
      #ea580c,
      #f59e0b
    );

  border:none;
  color:white;
  font-weight:700;
}

:deep(.el-button--primary){
  background:
    linear-gradient(
      45deg,
      #2563eb,
      #3b82f6
    );

  border:none;
}

:deep(.el-button--danger){
  background:
    linear-gradient(
      45deg,
      #b91c1c,
      #ef4444
    );

  border:none;
}

/* DIALOG */

:deep(.el-dialog){
  border-radius:18px;
  overflow:hidden;
  background:#111827;
}

:deep(.el-dialog__header){
  background:
    linear-gradient(
      90deg,
      #581c87,
      #9333ea
    );
}

:deep(.el-dialog__title){
  color:white;
  font-weight:700;
}

:deep(.el-dialog__body){
  background:#111827;
  color:white;
}

/* FORM */

:deep(.el-form-item__label){
  color:#fbbf24;
  font-weight:600;
}

:deep(.el-input__wrapper){
  background:#1e293b;
}

:deep(.el-input__inner){
  color:white;
}

:deep(.el-textarea__inner){
  background:#1e293b;
  color:white;
}

:deep(.el-input-number){
  width:100%;
}

/* DIVIDER */

:deep(.el-divider__text){
  color:#fbbf24;
  background:#111827;
  font-weight:700;
}

/* TAG */

:deep(.el-tag--warning){
  font-weight:700;
}

:deep(.el-tag--success){
  font-weight:700;
}

/* LOG JSON */

pre{
  background:#020617;
  color:#22c55e;

  padding:15px;

  border-radius:12px;

  border:
    1px solid rgba(34,197,94,.2);

  max-height:450px;

  overflow:auto;
}

/* HEADER TITLE */

.page-title{
  color:white;
  font-size:22px;
  font-weight:700;

  text-shadow:
    0 0 5px #fbbf24,
    0 0 15px #fbbf24;
}

/* SCROLLBAR */

::-webkit-scrollbar{
  width:8px;
  height:8px;
}

::-webkit-scrollbar-thumb{
  background:#7e22ce;
  border-radius:10px;
}

::-webkit-scrollbar-track{
  background:#0f172a;
}

/* BOSS ROW */

.boss-row{
  background:
    rgba(234,88,12,.15);
}

/* MONSTER ROW */

.monster-row{
  background:
    rgba(59,130,246,.08);
}

</style>