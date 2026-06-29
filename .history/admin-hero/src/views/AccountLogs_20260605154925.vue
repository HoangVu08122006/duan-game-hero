<template>
  <div class="container mt-5">
    <h2 class="mb-4">Lịch sử tạo & Thao tác tài khoản</h2>

    <div class="table-responsive">
      <table id="playerTable" class="table table-hover align-middle">
        <thead>
          <tr>
            <th>STT</th>
            <th>Tên tài khoản</th>
            <th>Thời gian</th>
            <th>Trạng thái</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(player, index) in players" :key="player.id" 
              :class="isDeleted(player) ? 'row-deleted' : 'row-created'">
            <td>{{ index + 1 }}</td>
            <td>{{ player.name }}</td>
            <td>{{ formatDate(player) }}</td>
            <td>
              <span :class="isDeleted(player) ? 'badge bg-danger' : 'badge bg-success'">
                {{ isDeleted(player) ? 'Bị xóa' : 'Đã tạo' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import $ from 'jquery';
import 'datatables.net-bs5';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';

const players = ref([]);

// Logic kiểm tra: nếu updated_at khác created_at thì coi là đã bị thao tác (xóa)
const isDeleted = (player) => {
  return new Date(player.updated_at).getTime() !== new Date(player.created_at).getTime();
};

const formatDate = (player) => {
  const dateToDisplay = isDeleted(player) ? player.updated_at : player.created_at;
  return new Date(dateToDisplay).toLocaleString('vi-VN');
};

const fetchData = async () => {
  try {
    const response = await fetch('http://26.103.188.167:8000/api/list/players');
    const res = await response.json();
    
    if (res.status === 'success') {
      players.value = res.data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      
      await nextTick();

      if ($.fn.DataTable.isDataTable('#playerTable')) {
        $('#playerTable').DataTable().destroy();
      }

      $('#playerTable').DataTable({
        paging: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50],
        language: {
          search: "Tìm kiếm:",
          lengthMenu: "Hiển thị _MENU_ mục",
          info: "Tổng: _TOTAL_ dòng",
          paginate: { first: "Đầu", last: "Cuối", next: "Tiếp", previous: "Trước" }
        }
      });
    }
  } catch (error) {
    console.error("Lỗi:", error);
  }
};

onMounted(() => {
  fetchData();
});
</script>

<style>
.container { background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.row-created { background-color: #f0fff4 !important; color: #198754; }
.row-deleted { background-color: #fff5f5 !important; color: #dc3545; }
.badge { padding: 0.5rem 1rem; border-radius: 20px; font-weight: 500; }
#playerTable { border-collapse: separate !important; border-spacing: 0; }
.dataTables_wrapper .dataTables_filter input { border-radius: 20px; }
</style>