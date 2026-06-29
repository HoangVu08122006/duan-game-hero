<template>
  <div class="p-6 max-w-4xl mx-auto" v-loading="loading">
    <h2 class="text-2xl font-bold mb-6 text-center">Điểm danh nhận quà</h2>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
      <div 
        v-for="reward in rewards" 
        :key="reward.day"
        :class="['p-4 border rounded-lg text-center', statusClass(reward.status)]"
      >
        <p class="font-bold text-sm">Ngày {{ reward.day }}</p>
        <div class="my-2 text-2xl">🎁</div>
        <p class="text-xs font-semibold">{{ reward.name }}</p>
        <p class="text-xs text-gray-500">{{ reward.amount }} {{ reward.type }}</p>
        
        <el-button 
          v-if="reward.status === 'available'"
          type="primary" 
          size="small"
          class="mt-3 w-full"
          @click="claimReward"
          :loading="loading"
        >
          Nhận quà
        </el-button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus'; // Dùng Element Plus

const rewards = ref([]);
const loading = ref(false);
const playerId = 51;

const fetchStatus = async () => {
  loading.value = true;
  try {
    const res = await axios.get(`/api/daily-reward/status/${playerId}`);
    rewards.value = res.data.data.rewards_wheel;
  } finally {
    loading.value = false;
  }
};

const claimReward = async () => {
  loading.value = true;
  try {
    const res = await axios.post(`/api/daily-reward/claim/${playerId}`);
    
    // Hiển thị thông báo đẹp hơn
    ElMessageBox.alert(`Chúc mừng! Bạn đã nhận được ${res.data.reward.amount} ${res.data.reward.reward_type}`, 'Thành công', {
      confirmButtonText: 'OK',
    });
    
    fetchStatus();
  } catch (err) {
    ElMessage.error(err.response?.data?.message || "Có lỗi xảy ra");
  } finally {
    loading.value = false;
  }
};

const statusClass = (status) => ({
  'bg-gray-100 border-gray-200': status === 'locked',
  'bg-green-100 border-green-500 shadow-lg': status === 'available',
  'bg-blue-50 border-blue-200': status === 'claimed'
});

onMounted(fetchStatus);
</script>