<template>
  <div class="weapon-container" v-loading="loading">
    <!-- Header Section -->
    <div class="header-section">
      <h2 style="margin: 0; display: flex; align-items: center; gap: 10px;">
        ⚔️ Quản lý vũ khí 🛡️
      </h2>
      <el-button type="primary" :icon="Plus" @click="showDialog()">Thêm Vũ Khí</el-button>
    </div>

    <!-- Table Section -->
    <el-table :data="weapons" border style="width: 100%" class="custom-table">
      <el-table-column label="📸 Hình ảnh" width="110" align="center">
        <template #default="scope">
          <el-image :src="'http://26.103.188.167:8000' + scope.row.image_weapon" fit="cover" class="weapon-image">
            <template #error><div class="image-error">🚫</div></template>
          </el-image>
        </template>
      </el-table-column>
      <el-table-column prop="name" label="🗡️ Tên Vũ Khí" />
      <el-table-column prop="base_attack" label="🔥 Sức mạnh" align="center" />
      <el-table-column prop="required_hero_level" label="📈 Cấp độ" align="center" />
      <el-table-column label="⚙️ Hành động" width="280" align="center">
        <template #default="scope">
          <el-button size="small" :icon="View" @click="viewDetail(scope.row)">Xem</el-button>
          <el-button size="small" type="primary" plain :icon="Edit" @click="showDialog(scope.row)">Sửa</el-button>
          <el-button size="small" type="danger" plain :icon="Delete" @click="confirmDelete(scope.row.id)">Xóa</el-button>
        </template>
      </el-table-column>
    </el-table>

    <!-- Dialog Form -->
    <el-dialog v-model="dialogVisible" :title="form.id ? '✏️ Sửa Vũ Khí' : '➕ Thêm Vũ Khí mới'" width="450px">
      <el-form :model="form" label-position="top">
        <el-form-item label="📝 Tên vũ khí">
          <el-input v-model="form.name" placeholder="Ví dụ: Kiếm thánh..." />
        </el-form-item>
        
        <el-form-item label="🖼️ Ảnh vũ khí">
          <input type="file" @change="onFileChange" accept="image/*" />
          <div v-if="previewImage" class="preview-container">
            <img :src="previewImage" />
          </div>
        </el-form-item>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="🔥 Sức mạnh">
              <el-input-number v-model="form.base_attack" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="📈 Cấp độ yêu cầu">
              <el-input-number v-model="form.required_hero_level" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">❌ Hủy</el-button>
        <el-button type="primary" :loading="saving" @click="saveWeapon">✅ Lưu thay đổi</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';
import { Plus, Edit, Delete, View } from '@element-plus/icons-vue'; // Import icons
import '@/echo';

// ... (phần logic giữ nguyên như cũ)
</script>

<style scoped>
/* Thêm chút hiệu ứng cho các biểu tượng */
.weapon-image { transition: transform 0.3s; cursor: pointer; }
.weapon-image:hover { transform: scale(1.2); }
/* ... các style cũ giữ nguyên ... */
</style>