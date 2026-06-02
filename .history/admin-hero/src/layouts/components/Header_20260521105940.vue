<template>
  <el-header class="app-header">
    <div class="logo-area">
      <img src="https://element-plus.org/images/element-plus-logo.svg" alt="Logo Game" class="logo-img" />
      <span class="logo-text">HERO SLASH - ADMIN PANEL</span>
    </div>
    <div class="header-right">
      <el-tag type="danger" effect="dark">SERVER 1 - GM</el-tag>
    </div>
  </el-header>
</template>

<style scoped>
.app-header {
  background-color: #ffffff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e6e6e6;
  height: 60px;
  padding: 0 20px;
}
.logo-area {
  display: flex;
  align-items: center;
  gap: 12px;
}
.logo-img {
  height: 30px;
}
.logo-text {
  font-size: 18px;
  font-weight: bold;
  color: #409eff;
  letter-spacing: 0.5px;
}
</style>