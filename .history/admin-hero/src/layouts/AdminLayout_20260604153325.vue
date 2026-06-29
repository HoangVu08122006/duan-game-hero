<template>
  <div class="admin-layout">
    <el-container class="main-container">
      
      <Header />

      <el-container class="body-container">
        
        <Aside />

        <el-container class="content-container">
          
          <el-main class="app-main">
            <router-view />
          </el-main>

          <Footer />

        </el-container>

      </el-container>
    </el-container>
  </div>
</template>

<script setup>
// Import các component vừa tách ra
import Header from './components/Header.vue'
import Aside from './components/Aside.vue'
import Footer from './components/Footer.vue'
</script>

<style scoped>
.admin-layout {
  height: 100vh;
  width: 100vw;
  overflow: hidden;
}
.main-container {
  height: 100vh;
  display: flex;
  flex-direction: column;
}
.body-container {
  flex: 1;
  display: flex;
  overflow: hidden;
}
.content-container {
  display: flex;
  flex-direction: column;
  flex: 1;
  background-color: #f0f2f5;
  overflow: hidden;
}
.app-main {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
}
</style>