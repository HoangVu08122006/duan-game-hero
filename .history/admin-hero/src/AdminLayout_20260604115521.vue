<template>
         
          <el-main class="app-main">
            <router-view />
          </el-main>

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