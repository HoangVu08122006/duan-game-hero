<template>
  <div class="login-container">
    <el-card class="login-card">
      <h2>🔑 ĐĂNG NHẬP ADMIN</h2>
      <el-form :model="loginForm">
        <el-form-item>
          <el-input v-model="loginForm.username" placeholder="Username" />
        </el-form-item>
        <el-form-item>
          <el-input v-model="loginForm.password" type="password" placeholder="Password" show-password />
        </el-form-item>
        <el-button type="primary" style="width: 100%" @click="handleLogin" :loading="loading">Đăng Nhập</el-button>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';

const loginForm = ref({ username: '', password: '' });
const loading = ref(false);
const router = useRouter();

const handleLogin = async () => {
  loading.value = true;
  try {
    const { data } = await axios.post('http://26.103.188.167:8000/api/admin/login', loginForm.value);
    localStorage.setItem('admin_token', data.token); // Lưu token
    ElMessage.success('Chào mừng Admin!');
    router.push('/dashboard'); // Chuyển hướng sau khi thành công
  } catch (error) {
    ElMessage.error('Đăng nhập thất bại!');
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.login-container { display: flex; justify-content: center; align-items: center; height: 100vh; }
.login-card { width: 350px; }
</style>