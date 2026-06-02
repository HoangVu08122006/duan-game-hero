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
      <el-menu-item index="/players">
        <el-icon><User /></el-icon>
        <span>Quản Lý Người Chơi</span>
      </el-menu-item>
      
<el-menu-item index="/weapons">
  <el-icon><Knife /></el-icon> <span>Quản Lý Vũ Khí</span>
</el-menu-item>
      
    </el-menu>
  </el-aside>
</template>

<script setup>
import { User, Ticket } from '@element-plus/icons-vue'
import { useRoute } from 'vue-router'

const route = useRoute()
</script>

<style scoped>
.app-aside {
  background-color: #304156;
}
.side-menu {
  border-right: none;
  height: 100%;
}
</style>