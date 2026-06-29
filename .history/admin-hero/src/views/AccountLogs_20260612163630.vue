<template>

  <template>
  <div class="stats-container">
    <el-radio-group v-model="timeRange" @change="fetchStats">
      <el-radio-button label="day">Ngày</el-radio-button>
      <el-radio-button label="week">Tuần</el-radio-button>
      <el-radio-button label="month">Tháng</el-radio-button>
    </el-radio-group>

    <v-chart class="chart" :option="option" autoresize />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import VChart from 'vue-echarts';
import * as echarts from 'echarts';

const timeRange = ref('week');
const rawData = ref([]);

const option = computed(() => ({
  xAxis: { type: 'category', data: rawData.value.map(item => item.date) },
  yAxis: { type: 'value' },
  series: [{
    data: rawData.value.map(item => item.count),
    type: 'bar',
    itemStyle: { color: '#409EFF' }
  }],
  tooltip: { trigger: 'axis' }
}));

const fetchStats = async () => {
  const res = await axios.get(`/api/stats/registrations?range=${timeRange.value}`);
  rawData.value = res.data;
};

onMounted(fetchStats);
</script>

<style scoped>

</style>


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
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import $ from 'jquery';
import 'datatables.net-bs5';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';

const players = ref([]);

/

onMounted(() => {
  fetchData();
});
</script>

<!-- <style>
/* Đảm bảo bảng hiển thị đẹp trong Bootstrap 5 */
.container {
  margin-top: 2rem !important;
}
</style> -->
<style scoped>
/* 1. Tạo hiệu ứng đổ bóng cho container bảng */
.container {
  background: #ffffff;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

/* 2. Style cho tiêu đề */
h2 {
  color: #333;
  font-weight: 700;
  margin-bottom: 1.5rem;
  border-left: 5px solid #0d6efd;
  padding-left: 15px;
}

/* 3. Tăng khoảng cách và hover effect cho bảng */
#playerTable {
  border-collapse: separate !important;
  border-spacing: 0;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #dee2e6;
}

#playerTable thead th {
  background-color: #f8f9fa !important;
  color: #495057;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.85rem;
  padding: 12px;
}

#playerTable tbody tr:hover {
  background-color: #e9ecef !important;
  transition: all 0.2s;
  cursor: pointer;
}

/* 4. Tùy chỉnh ô tìm kiếm và phân trang */
.dataTables_wrapper .dataTables_filter input {
  border-radius: 20px;
  padding: 5px 15px;
  border: 1px solid #ced4da;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
  background: #0d6efd !important;
  border-color: #0d6efd !important;
  color: white !important;
  border-radius: 5px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
  background: #0b5ed7 !important;
  color: white !important;
}

.chart { height: 400px; }
</style>