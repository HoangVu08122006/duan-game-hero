<template>
  <div class="container mt-5">
    <div class="chart-container mb-5">
      <h2>Biểu đồ tăng trưởng người chơi</h2>
      <canvas id="playerChart"></canvas>
    </div>

    <h2>Lịch sử tạo tài khoản người chơi</h2>
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
import $ from 'jquery';
import axios from 'axios';
import { Chart, registerables } from 'chart.js';
import 'datatables.net-bs5';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';

Chart.register(...registerables);

const players = ref([]);

const formatDate = (dateString) => new Date(dateString).toLocaleString('vi-VN');

const renderChart(labels, data) {
    const ctx = document.getElementById('playerChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar', // Để kiểu bar làm chủ đạo
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Số tài khoản',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)', // Màu xanh dịu hơn
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    barThickness: 40, // Độ rộng cột cố định
                    order: 2
                },
                {
                    label: 'Xu hướng',
                    data: data,
                    type: 'line',
                    borderColor: '#ff6384',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4, // Đường cong mượt mà hơn
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    ticks: { precision: 0 } // Đảm bảo số lượng chỉ hiển thị số nguyên
                }
            }
        }
    });
}

const initDataTable = () => {
  if ($.fn.DataTable.isDataTable('#playerTable')) {
    $('#playerTable').DataTable().destroy();
  }
  $('#playerTable').DataTable({
    paging: true,
    pageLength: 10,
    language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json' }
  });
};

onMounted(async () => {
  try {
    // 1. Lấy dữ liệu bảng
    const listRes = await axios.get('http://26.103.188.167:8000/api/list/players');
    players.value = listRes.data.data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    
    // 2. Lấy dữ liệu biểu đồ
    const chartRes = await axios.get('/api/stats/player-growth');
    const labels = chartRes.data.map(item => item.date);
    const data = chartRes.data.map(item => item.count);

    await nextTick();
    renderChart(labels, data);
    initDataTable();
  } catch (error) {
    console.error("Lỗi tải dữ liệu:", error);
  }
});


</script>

<style>

</style>