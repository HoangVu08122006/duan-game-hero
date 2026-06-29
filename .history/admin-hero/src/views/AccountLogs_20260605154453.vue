<template>
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

// Hàm định dạng ngày tháng
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString('vi-VN');
};

// Gọi API và khởi tạo DataTable
const fetchData = async () => {
  try {
    const response = await fetch('http://26.103.188.167:8000/api/list/players');
    const res = await response.json();
    
    if (res.status === 'success') {
      players.value = res.data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      
      // Chờ Vue render dữ liệu vào DOM
      await nextTick();

      // Kiểm tra và hủy instance cũ (nếu có) trước khi tạo mới
      if ($.fn.DataTable.isDataTable('#playerTable')) {
        $('#playerTable').DataTable().destroy();
      }

      // Khởi tạo DataTables với phân trang
      $('#playerTable').DataTable({
        paging: true,          // Bật tính năng phân trang
        pageLength: 10,        // Mặc định 10 dòng
        lengthMenu: [10, 25, 50], // Tùy chọn số dòng mỗi trang
        language: {
          search: "Tìm kiếm:",
          lengthMenu: "Hiển thị _MENU_ mục mỗi trang",
          info: "Hiển thị từ _START_ đến _END_ trong tổng số _TOTAL_ mục",
          paginate: { 
            first: "Đầu", 
            last: "Cuối", 
            next: "Tiếp", 
            previous: "Trước" 
          },
          emptyTable: "Không có dữ liệu trong bảng"
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

<!-- <style>
/* Đảm bảo bảng hiển thị đẹp trong Bootstrap 5 */
.container {
  margin-top: 2rem !important;
}
</style> -->