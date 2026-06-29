<template>
  <div class="container mt-5">
    <h2>Danh sách người chơi</h2>

    <table id="playerTable" class="table table-striped table-bordered" style="width:100%">
      <thead>
        <tr>
          <th>STT</th>
          <th>Tên tài khoản</th>
          <th>Thời gian tạo</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(player, index) in players" :key="player.id">
          <td>{{ index + 1 }}</td>
          <td>{{ player.name }}</td>
          <td>{{ formatDate(player.created_at) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import * as $ from 'jquery'; // Cách import này thường ổn định hơn với Vite
import 'datatables.net-bs5';


const players = ref([]);

// Hàm định dạng ngày tháng
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString('vi-VN');
};

// Gọi API
const fetchData = async () => {
  try {
    const response = await fetch('http://26.103.188.167:8000/api/list/players');
    const res = await response.json();
    
    if (res.status === 'success') {
      // Sắp xếp: Mới nhất lên đầu
      players.value = res.data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      
      // Chờ Vue render xong DOM rồi mới khởi tạo DataTables
      await nextTick();
      $('#playerTable').DataTable({
        pageLength: 10,
        language: {
          search: "Tìm kiếm:",
          paginate: { first: "Đầu", last: "Cuối", next: "Tiếp", previous: "Trước" }
        }
      });
    }
  } catch (error) {
    console.error("Lỗi tải dữ liệu:", error);
  }
};

onMounted(() => {
  fetchData();
});
</script>

<style>

</style>