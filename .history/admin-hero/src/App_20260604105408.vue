<template>
  <div class="common-layout">
    <el-container class="main-container">
      
      <el-header class="app-header">
        <div class="logo-area">
          <img src="https://element-plus.org/images/element-plus-logo.svg" alt="Logo" class="logo-img" />
          <span class="logo-text">HERO SLASH - GM PANEL</span>
        </div>
        <el-tag type="danger" effect="dark">Admin Server</el-tag>
      </el-header>

      <el-container class="body-container">
        
        <el-aside width="240px" class="app-aside">
          <el-menu
            :router="true"
            :default-active="route.path"
            class="el-menu-vertical-demo"
            background-color="#304156"
            text-color="#bfcbd9"
            active-text-color="#409EFF"
          >

            <el-menu-item index="/account-logs">
              <el-icon><Monitor /></el-icon>
              <span>Lịch Sử Tạo TK</span>
            </el-menu-item>

            <el-menu-item index="/players">
              <el-icon><User /></el-icon>
              <span>Quản Lý Người Chơi</span>
            </el-menu-item>

            <el-menu-item index="/weapons">
            <el-icon><Knife /></el-icon>
            <span>Quản Lý Vũ Khí</span>
            </el-menu-item>
            
            <el-menu-item index="/giftcodes">
              <el-icon><Ticket /></el-icon>
              <span>Quản Lý Giftcode</span>
            </el-menu-item>
          </el-menu>
        </el-aside>

        <el-container class="content-container">
          
          <el-main class="app-main">
            <router-view />
          </el-main>

          <el-footer class="app-footer">
            <span>© 2026 HERO SLASH Studio. Backend Team.</span>
          </el-footer>

        </el-container>

      </el-container>
    </el-container>
  </div>
</template>

<script setup>
import { User, Ticket } from '@element-plus/icons-vue'
import { useRoute } from 'vue-router'

const route = useRoute()
</script>

<style scoped>
/* Giữ nguyên toàn bộ phần CSS cũ của App.vue để đảm bảo khung giao diện đẹp */
.common-layout { height: 100vh; width: 100vw; overflow: hidden; }
.main-container { height: 100vh; display: flex; flex-direction: column; }
.app-header { background-color: #ffffff; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e6e6e6; height: 60px; padding: 0 20px; }
.logo-area { display: flex; align-items: center; gap: 12px; }
.logo-img { height: 30px; }
.logo-text { font-size: 18px; font-weight: bold; color: #409eff; }
.body-container { flex: 1; display: flex; overflow: hidden; }
.app-aside { background-color: #304156; }
.el-menu-vertical-demo { border-right: none; height: 100%; }
.content-container { display: flex; flex-direction: column; flex: 1; background-color: #f0f2f5; overflow: hidden; }
.app-main { flex: 1; padding: 20px; overflow-y: auto; }
.app-footer { background-color: #ffffff; color: #606266; border-top: 1px solid #e6e6e6; display: flex; align-items: center; justify-content: center; font-size: 13px; height: 40px !important; }
</style>