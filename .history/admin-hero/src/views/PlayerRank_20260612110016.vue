<template>
  <div>
    <table>
      <tr v-for="player in players" :key="player.id">
        <td>{{ player.rank || '...' }}</td>
        <td>{{ player.name }}</td>
        <td>{{ player.total_power }}</td>
        <td>{{ player.highest_floor }}</td>
      </tr>
    </table>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      players: [],
    };
  },
  mounted() {
    this.fetchLeaderboard();
  },
  methods: {
    async fetchLeaderboard() {
      try {
        // Nếu là Admin, bạn nên truyền thêm headers Authorization để server nhận diện
        const response = await axios.get('/api/leaderboard/rankings');
        
        // Nếu là Admin (phân trang), dữ liệu nằm trong response.data.data.data
        // Nếu là Người chơi (top 100), dữ liệu nằm trong response.data.data
        this.players = response.data.data.data || response.data.data;
      } catch (error) {
        console.error("Lỗi khi tải bảng xếp hạng:", error);
      }
    }
  }
};
</script>