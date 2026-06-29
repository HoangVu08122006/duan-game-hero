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

const renderChart = (labels, data) => {
  const ctx = document.getElementById('playerChart').getContext('2d');

  // Xóa chart cũ
  if (window.myChart) {
    window.myChart.destroy();
  }

  window.myChart = new Chart(ctx, {
    data: {
      labels,
      datasets: [
        {
          type: 'bar',
          label: 'Số tài khoản',
          data,
          backgroundColor: '#3b82f6',
          borderRadius: 8,
          borderSkipped: false,
          barThickness: 40
        },
        {
          type: 'line',
          label: 'Xu hướng',
          data,
          borderColor: '#ef4444',
          borderWidth: 3,
          tension: 0.4,
          pointRadius: 5,
          pointHoverRadius: 8,
          pointBackgroundColor: '#ef4444',
          fill: false
        }
      ]
    },

    options: {
      responsive: true,
      maintainAspectRatio: false,

      plugins: {
        legend: {
          position: 'top'
        },

        tooltip: {
          backgroundColor: '#111827',
          padding: 12,
          cornerRadius: 8
        }
      },

      scales: {
        x: {
          grid: {
            display: false
          }
        },

        y: {
          beginAtZero: true,
          ticks: {
            precision: 0
          },
          grid: {
            color: 'rgba(0,0,0,0.08)'
          }
        }
      },

      animation: {
        duration: 1200
      }
    },

    plugins: [
      {
        id: 'valueLabels',
        afterDatasetsDraw(chart) {
          const { ctx } = chart;

          chart.data.datasets.forEach((dataset, datasetIndex) => {
            if (dataset.type !== 'bar') return;

            const meta = chart.getDatasetMeta(datasetIndex);

            meta.data.forEach((bar, index) => {
              const value = dataset.data[index];

              ctx.save();
              ctx.fillStyle = '#111827';
              ctx.font = 'bold 12px sans-serif';
              ctx.textAlign = 'center';

              ctx.fillText(
                value,
                bar.x,
                bar.y - 10
              );

              ctx.restore();
            });
          });
        }
      }
    ]
  });
};

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
.chart-container {
  height: 450px;
  background: #fff;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}
</style>