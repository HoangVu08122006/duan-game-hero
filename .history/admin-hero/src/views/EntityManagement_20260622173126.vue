<template>
  <div class="monster-admin">
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

<!-- <style scoped>
/* =========================================
   HERO SLASH - DUNGEON MONSTER MANAGER
========================================= */

.monster-admin{
  min-height:100vh;
  padding:20px;

  background:
    radial-gradient(circle at center,
    #1d1d1d 0%,
    #0f0f0f 45%,
    #050505 100%);

  color:#d6d3d1;
}

/* CARD */

:deep(.el-card){
  background:#151515;
  border:1px solid #3a3126;
  border-radius:14px;

  box-shadow:
    0 0 30px rgba(0,0,0,.7),
    inset 0 0 20px rgba(255,255,255,.02);

  overflow:hidden;
}

:deep(.el-card__header){
  background:
    linear-gradient(
      180deg,
      #2a241d,
      #171411
    );

  border-bottom:1px solid #4d4030;

  color:#d7c4a1;

  font-weight:700;

  font-size:18px;
}

/* TABLE */

:deep(.el-table){
  background:#151515;
  color:#ddd;
}

:deep(.el-table th.el-table__cell){
  background:#211d18 !important;

  color:#cda86b !important;

  font-weight:700;

  border-bottom:
    1px solid #4a3d2e;
}

:deep(.el-table tr){
  background:#151515;
}

:deep(.el-table td){
  background:#151515;
  color:#ddd;
}

:deep(.el-table__row:hover td){
  background:#24201a !important;
}

:deep(.el-table),
:deep(.el-table td),
:deep(.el-table th){
  border-color:#30281f;
}

/* TABS */

:deep(.el-tabs__item){
  color:#b8b2a8;
  font-weight:600;
}

:deep(.el-tabs__item:hover){
  color:#d7c4a1;
}

:deep(.el-tabs__item.is-active){
  color:#e2bf81;
}

:deep(.el-tabs__active-bar){
  background:#cda86b;
}

/* BUTTONS */

:deep(.el-button){
  border-radius:8px;
  font-weight:600;
}

/* ADD */

:deep(.el-button--success){
  background:
    linear-gradient(
      180deg,
      #4e3b24,
      #2f2417
    );

  border:1px solid #82613a;

  color:#f3e6c9;
}

:deep(.el-button--success:hover){
  transform:translateY(-1px);
}

/* PUBLISH */

:deep(.el-button--warning){
  background:
    linear-gradient(
      180deg,
      #7f1d1d,
      #4b1010
    );

  border:1px solid #b91c1c;

  color:white;
}

/* EDIT */

:deep(.el-button--primary){
  background:
    linear-gradient(
      180deg,
      #393939,
      #232323
    );

  border:1px solid #555;
}

/* DELETE */

:deep(.el-button--danger){
  background:
    linear-gradient(
      180deg,
      #8b1e1e,
      #4a1010
    );

  border:1px solid #c62828;
}

/* DIALOG */

:deep(.el-dialog){
  background:#111111;
  border:1px solid #45392c;
  border-radius:14px;
  overflow:hidden;
}

:deep(.el-dialog__header){
  background:
    linear-gradient(
      180deg,
      #241d17,
      #151210
    );

  border-bottom:1px solid #46392c;
}

:deep(.el-dialog__title){
  color:#d7c4a1;
  font-weight:700;
}

:deep(.el-dialog__body){
  background:#111111;
  color:#ddd;
}

:deep(.el-dialog__footer){
  background:#111111;
}

/* FORM */

:deep(.el-form-item__label){
  color:#cda86b;
  font-weight:600;
}

:deep(.el-input__wrapper){
  background:#1a1a1a;
  box-shadow:none;
  border:1px solid #33291f;
}

:deep(.el-input__inner){
  color:#f5f5f5;
}

:deep(.el-textarea__inner){
  background:#1a1a1a;
  color:#f5f5f5;
  border:1px solid #33291f;
}

:deep(.el-input-number){
  width:100%;
}

/* DIVIDER */

:deep(.el-divider){
  border-color:#3b3126;
}

:deep(.el-divider__text){
  background:#111111;
  color:#cda86b;
  font-weight:700;
}

/* TAG */

:deep(.el-tag--warning){
  background:#5b3b00;
  border:none;
}

:deep(.el-tag--success){
  background:#1c4d28;
  border:none;
}

/* DESCRIPTIONS */

:deep(.el-descriptions){
  color:#ddd;
}

:deep(.el-descriptions__label){
  background:#1d1d1d !important;
  color:#cda86b !important;
}

:deep(.el-descriptions__content){
  background:#151515 !important;
  color:#ddd !important;
}

/* PREVIEW JSON */

pre{
  background:#0a0a0a;
  color:#9be58d;

  padding:15px;

  border-radius:10px;

  border:1px solid #2c2c2c;

  overflow:auto;

  max-height:500px;
}

/* LOG TABLE */

.log-title{
  color:#d7c4a1;
  font-weight:700;
}

/* SCROLLBAR */

::-webkit-scrollbar{
  width:8px;
  height:8px;
}

::-webkit-scrollbar-track{
  background:#111;
}

::-webkit-scrollbar-thumb{
  background:#4d4030;
  border-radius:10px;
}

::-webkit-scrollbar-thumb:hover{
  background:#6e5c47;
}

/* LOADING */

:deep(.el-loading-mask){
  background:rgba(0,0,0,.75);
}

/* HEADER TITLE */

.page-title{
  font-size:22px;
  font-weight:700;

  color:#d7c4a1;

  letter-spacing:1px;

  text-shadow:
    0 0 10px rgba(215,196,161,.2);
}
</style> -->