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
      
    </el-menu>
  </el-aside>
</template>

<script setup>
// Đã thêm 'Gift' và 'DataLine', 'Coordinate' vào danh sách import
import { Monitor, User, Grid, Gift, DataLine, Coordinate } from '@element-plus/icons-vue' 
import { useRoute } from 'vue-router'

const route = useRoute()
</script>