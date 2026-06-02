<template>
  <div style="padding: 20px; max-width: 1000px; margin: 0 auto;">
    <h2>🛡️ HỆ THỐNG QUẢN TRỊ GAME - HERO SLASH</h2>
    <hr style="margin-bottom: 20px; border: 1px solid #eee;" />

    <div style="margin-bottom: 20px;">
      <el-button type="primary">Thêm Người Chơi Mới</el-button>
      <el-button type="success">Phát Quà Toàn Server (Giftcode)</el-button>
    </div>

    <el-table :data="tableData" border style="width: 100%">
      <el-table-column prop="uid" label="UID" width="100" />
      <el-table-column prop="username" label="Tên Tài Khoản" width="180" />
      <el-table-column prop="level" label="Cấp Độ" width="100" />
      <el-table-column prop="gold" label="Số Vàng (Gold)" width="150" />
      
      <el-table-column label="Hành Động">
        <template #default="scope">
          <el-button size="small" type="warning" @click="editPlayer(scope.row)">
            Cộng Vàng/Kim Cương
          </el-button>
          <el-button size="small" type="danger">
            Ban Tài Khoản
          </el-button>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// Dữ liệu giả lập (Sau này bạn sẽ dùng Axios để gọi API lấy dữ liệu này từ Backend Game)
const tableData = ref([
  { uid: '10001', username: 'slayer_pro', level: 45, gold: 150000 },
  { uid: '10002', username: 'shadow_ninja', level: 12, gold: 3500 },
  { uid: '10003', username: 'dragon_born', level: 89, gold: 4500000 },
])

const editPlayer = (row) => {
  alert(`Chuẩn bị gọi API Backend để chỉnh sửa cho nhân vật: ${row.username}`)
}
</script>