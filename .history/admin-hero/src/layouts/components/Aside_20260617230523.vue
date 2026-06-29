<template>
  <el-aside width="240px" class="app-aside">
    <el-menu
      :router="true"
      :default-active="route.path"
      class="side-menu"
      background-color="#304156"
      text-color="#bfcbd9"
      active-text-color="#409EFF"
    >
      <el-menu-item index="/dashboard/account-logs">
        <el-icon><Monitor /></el-icon>
        <span>Dashboard</span>
      </el-menu-item>

      <el-menu-item index="/dashboard/leaderboard">
        <el-icon><DataLine /></el-icon>
        <span>Bảng Xếp Hạng</span>
      </el-menu-item>

      <el-menu-item index="/dashboard/players">
        <el-icon><User /></el-icon>
        <span>Quản Lý Người Chơi</span>
      </el-menu-item>

      <el-menu-item index="/dashboard/weapons">
        <el-icon><Grid /></el-icon> <span>Quản Lý Vũ Khí</span>
      </el-menu-item>

      <el-menu-item index="/dashboard/pets">
        <el-icon><Coordinate /></el-icon> 
        <span>Quản Lý Pet</span>
      </el-menu-item>

      <el-menu-item index="/dashboard/daily-rewards">
        <el-icon><Gift /></el-icon> 
        <span>Quản Lý Phần Thưởng</span>
      </el-menu-item>
      
      <el-menu-item index="/dashboard/entity-management">
        <el-icon><Gift /></el-icon> 
        <span>Quản Lý Phần Thưởng</span>
      </el-menu-item>
    </el-menu>
  </el-aside>
</template>

<script setup>
// Cần import tất cả các icon bạn dùng vào đây
import { Monitor, User, Grid } from '@element-plus/icons-vue' 
import { useRoute } from 'vue-router'

const route = useRoute()
</script>



<style scoped>
.app-aside {
  background: linear-gradient(
    180deg,
    #1f2937 0%,
    #111827 100%
  );
  box-shadow: 4px 0 15px rgba(0, 0, 0, 0.2);
}

.side-menu {
  height: 100%;
  border-right: none;
  background: transparent;
}

/* Item menu */
:deep(.el-menu-item) {
  height: 55px;
  font-size: 15px;
  font-weight: 500;
  margin: 8px;
  border-radius: 10px;
  transition: all 0.3s ease;
}

/* Hover */
:deep(.el-menu-item:hover) {
  background: rgba(64, 158, 255, 0.15) !important;
  color: #fff !important;
  transform: translateX(5px);
}

/* Active */
:deep(.el-menu-item.is-active) {
  background: linear-gradient(
    90deg,
    #409eff,
    #67c23a
  ) !important;

  color: white !important;

  box-shadow: 0 4px 12px rgba(64, 158, 255, 0.4);
}

/* Icon */
:deep(.el-menu-item .el-icon) {
  font-size: 18px;
  margin-right: 10px;
}

/* Active icon */
:deep(.el-menu-item.is-active .el-icon) {
  color: white;
}

/* Text */
:deep(.el-menu-item span) {
  letter-spacing: 0.3px;
}
</style>