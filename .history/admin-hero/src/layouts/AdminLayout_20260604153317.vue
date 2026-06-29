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