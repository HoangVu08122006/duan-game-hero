<template>
  <div class="p-6 max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 text-center">Điểm danh nhận quà</h2>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
      <div 
        v-for="reward in rewards" 
        :key="reward.day"
        :class="['p-4 border rounded-lg text-center', statusClass(reward.status)]"
      >
        <p class="font-bold text-sm">Ngày {{ reward.day }}</p>
        <div class="my-2 text-2xl">🎁</div>
        <p class="text-xs">{{ reward.name }}</p>
        
        <button 
          v-if="reward.status === 'available'"
          @click="claimReward(reward.day)"
          class="mt-3 bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700"
        >
          Nhận
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const rewards = ref([]);
const playerId = 51; // ID người chơi đang đăng nhập

const fetchStatus = async () => {
  const res = await axios.get(`/api/daily-reward/status/${playerId}`);
  rewards.value = res.data.data.rewards_wheel;
};

const claimReward = async () => {
  try {
    await axios.post(`/api/daily-reward/claim/${playerId}`);
    alert("Nhận quà thành công!");
    fetchStatus(); // Cập nhật lại giao diện sau khi nhận
  } catch (err) {
    alert(err.response?.data?.message || "Lỗi rồi!");
  }
};

const statusClass = (status) => ({
  'bg-gray-100 border-gray-200': status === 'locked',
  'bg-green-100 border-green-500': status === 'available',
  'bg-blue-50 border-blue-200': status === 'claimed'
});

onMounted(fetchStatus);
</script>