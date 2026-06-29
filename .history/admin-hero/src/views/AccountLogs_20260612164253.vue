<template>
  <div class="stats-container">
    <el-card class="chart-card">
      <el-radio-group v-model="timeRange" @change="fetchStats">
        <el-radio-button label="day">Ngày</el-radio-button>
        <el-radio-button label="week">Tuần</el-radio-button>
        <el-radio-button label="month">Tháng</el-radio-button>
      </el-radio-group>
      <v-chart class="chart" :option="option" autoresize />
    </el-card>

    <div class="container mt-5">
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
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue'; // Gộp tất cả vào đây
import axios from 'axios';
import VChart from 'vue-echarts';
import * as echarts from 'echarts';
import $ from 'jquery';
import 'datatables.net-bs5';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';

// --- Logic Bảng ---
const players = ref([]);
const formatDate = (dateString) => new Date(dateString).toLocaleString('vi-VN');

const fetchData = async () => {
  try {
    const response = await axios.get('http://26.103.188.167:8000/api/list/players');
    if (response.data.status === 'success') {
      players.value = response.data.data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      
      await nextTick();
      if ($.fn.DataTable.isDataTable('#playerTable')) {
        $('#playerTable').DataTable().destroy();
      }
      $('#playerTable').DataTable({
        paging: true,
        pageLength: 10,
        language: { search: "Tìm kiếm:", paginate: { next: "Tiếp", previous: "Trước" } }
      });
    }
  } catch (error) { console.error("Lỗi tải dữ liệu:", error); }
};

// --- Logic Biểu đồ ---
const timeRange = ref('week');
const rawData = ref([]);

const option = computed(() => ({
  xAxis: { type: 'category', data: rawData.value.map(item => item.date) },
  yAxis: { type: 'value' },
  series: [{ data: rawData.value.map(item => item.count), type: 'bar', itemStyle: { color: '#409EFF' } }],
  tooltip: { trigger: 'axis' }
}));

const fetchStats = async () => {
  try {
    const res = await axios.get(`/api/stats/registrations?range=${timeRange.value}`);
    rawData.value = res.data;
  } catch (e) { console.error("Lỗi tải biểu đồ:", e); }
};


onMounted(() => {
  fetchData();
  fetchStats();
});
</script>

<style scoped>
.stats-container { padding: 20px; }
.chart-card { margin-bottom: 20px; padding: 10px; }
.chart { height: 400px; }
.container { background: #ffffff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); }
</style>