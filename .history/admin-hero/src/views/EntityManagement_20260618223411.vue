<el-form
  :model="form"
  label-width="130px"
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

  <el-form-item
    :label="entityType === 'monster'
      ? 'ATK'
      : 'Attack'"
  >
    <el-input-number
      v-model="
        entityType === 'monster'
          ? form.base_atk
          : form.base_attack
      "
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
      placeholder="Json hoặc mô tả skill boss"
    />
  </el-form-item>

</el-form>

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