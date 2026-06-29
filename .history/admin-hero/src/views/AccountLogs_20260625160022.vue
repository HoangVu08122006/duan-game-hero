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

  if (window.myChart) {
    window.myChart.destroy();
  }

  const gradient = ctx.createLinearGradient(0, 0, 0, 400);
  gradient.addColorStop(0, '#60a5fa');
  gradient.addColorStop(1, '#2563eb');

  window.myChart = new Chart(ctx, {
    data: {
      labels,
      datasets: [
        {
          type: 'bar',
          label: 'Số tài khoản',

          data,

          backgroundColor: gradient,

          borderRadius: 10,
          borderSkipped: false,

          barThickness: 25,
          maxBarThickness: 25,

          categoryPercentage: 0.7,
          barPercentage: 0.8
        },
        {
          type: 'line',
          label: 'Xu hướng',

          data,

          borderColor: '#ef4444',
          borderWidth: 3,

          pointRadius: 5,
          pointHoverRadius: 8,

          pointBackgroundColor: '#ef4444',

          tension: 0.35,

          fill: false
        }
      ]
    },

    options: {
      responsive: true,
      maintainAspectRatio: false,

      interaction: {
        intersect: false,
        mode: 'index'
      },

      plugins: {
        legend: {
          position: 'top',

          labels: {
            font: {
              size: 14,
              weight: 'bold'
            }
          }
        },

        tooltip: {
          backgroundColor: '#111827',
          padding: 12,
          cornerRadius: 10
        }
      },

      scales: {
        x: {
          grid: {
            display: false
          },

          ticks: {
            color: '#6b7280'
          }
        },

        y: {
          beginAtZero: true,

          ticks: {
            precision: 0,
            color: '#6b7280'
          },

          grid: {
            color: 'rgba(107,114,128,0.15)'
          }
        }
      },

      animation: {
        duration: 1200,
        easing: 'easeOutQuart'
      }
    },

    plugins: [
      {
        id: 'valueLabels',

        afterDatasetsDraw(chart) {
          const { ctx } = chart;

          const meta = chart.getDatasetMeta(0);

          meta.data.forEach((bar, index) => {
            const value = data[index];

            ctx.save();

            ctx.fillStyle = '#111827';
            ctx.font = 'bold 12px Arial';
            ctx.textAlign = 'center';

            ctx.fillText(
              value,
              bar.x,
              bar.y - 10
            );

            ctx.restore();
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
.container {
  padding: 30px;
  min-height: 100vh;

  background:
    radial-gradient(circle at top,
      #1e293b 0%,
      #0f172a 40%,
      #020617 100%);
}

.chart-container {
  position: relative;

  height: 520px;

  padding: 25px;

  border-radius: 24px;

  background:
    rgba(15,23,42,.8);

  backdrop-filter: blur(20px);

  border: 1px solid rgba(255,255,255,.08);

  box-shadow:
    0 10px 40px rgba(0,0,0,.4),
    0 0 30px rgba(59,130,246,.15);

  overflow: hidden;
}

.chart-container::before {
  content: '';

  position: absolute;

  inset: 0;

  background:
    radial-gradient(
      circle at top right,
      rgba(59,130,246,.15),
      transparent 40%
    );

  pointer-events: none;
}

.chart-container h2 {
  color: white;

  font-size: 24px;

  font-weight: 700;

  margin-bottom: 20px;

  text-shadow: 0 0 15px rgba(96,165,250,.5);
}

#playerChart {
  width: 100% !important;
  height: 430px !important;
}

/* TABLE */

h2 {
  color: white;
  font-weight: 700;
  margin-bottom: 20px;
}

.table {
  background: rgba(255,255,255,.05);
  backdrop-filter: blur(12px);

  border-radius: 20px;
  overflow: hidden;

  color: white;
}

.table thead {
  background: linear-gradient(
    90deg,
    #2563eb,
    #1d4ed8
  );
}

.table thead th {
  color: white;
  border: none;
}

.table tbody tr {
  transition: .3s;
}

.table tbody tr:hover {
  background: rgba(59,130,246,.15);
}

.table td {
  color: #e2e8f0;
}

/* DataTable */

.dataTables_wrapper {
  color: white;
}

.dataTables_wrapper .dataTables_filter input,
.dataTables_wrapper .dataTables_length select {
  background: #1e293b;
  color: white;
  border: 1px solid #334155;
}

.page-item.active .page-link {
  background: #2563eb;
  border-color: #2563eb;
}
</style>