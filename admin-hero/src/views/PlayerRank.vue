<template>
  <div class="leaderboard-container">
    <h2 class="title">🏆 BẢNG XẾP HẠNG TOÀN SERVER 🏆</h2>

    <table class="leaderboard-table">
      <thead>
        <tr>
          <th>Hạng</th>
          <th>Tên người chơi</th>
          <th>Lực chiến</th>
          <th>Tầng cao nhất</th>
          <th>Thời gian đạt tầng</th>
        </tr>
      </thead>

      <tbody>
        <tr
          v-for="(player, index) in players"
          :key="player.id"
          :class="{
            gold: player.rank === 1 || index === 0,
            silver: player.rank === 2 || index === 1,
            bronze: player.rank === 3 || index === 2
          }"
        >
          <td>
            <span v-if="player.rank === 1 || index === 0">🥇</span>
            <span v-else-if="player.rank === 2 || index === 1">🥈</span>
            <span v-else-if="player.rank === 3 || index === 2">🥉</span>
            <span v-else class="rank-number">{{ player.rank || index + 1 }}</span>
          </td>

          <td><strong>{{ player.name }}</strong></td>
          <td>{{ Number(player.total_power).toLocaleString() }}</td>
          <td><span class="floor-tag">{{ player.highest_floor }}</span></td>
          
          <td class="date-cell">{{ formatDate(player.reached_highest_floor_at) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Leaderboard',

  data() {
    return {
      players: []
    }
  },

  mounted() {
    this.fetchLeaderboard()
  },

  methods: {
    formatDate(dateString) {
      if (!dateString) return 'Chưa xác định';
      const options = { 
        year: 'numeric', 
        month: '2-digit', 
        day: '2-digit', 
        hour: '2-digit', 
        minute: '2-digit' 
      };
      return new Date(dateString).toLocaleDateString('vi-VN', options);
    },

    async fetchLeaderboard() {
      try {
        const response = await axios.get('/api/leaderboard/rankings')
        // Xử lý dữ liệu từ Laravel Pagination hoặc Collection
        this.players = response.data?.data?.data || response.data?.data || []
      } catch (error) {
        console.error('Lỗi khi tải bảng xếp hạng:', error)
      }
    }
  }
}
</script>

<style scoped>
.leaderboard-container {
  max-width: 1100px;
  margin: 40px auto;
  padding: 20px;
}

.title {
  text-align: center;
  color: #ffd700;
  font-size: 32px;
  margin-bottom: 25px;
  text-shadow: 0 0 10px #ffd700;
}

.leaderboard-table {
  width: 100%;
  border-collapse: collapse;
  border-radius: 15px;
  background: #1e1e2f;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.4);
  overflow: hidden;
}

.leaderboard-table th {
  background: #2d2d44;
  color: #a0a0c0;
  padding: 18px;
  font-size: 16px;
  text-transform: uppercase;
}

.leaderboard-table td {
  padding: 16px;
  text-align: center;
  color: white;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.floor-tag {
  background: #409EFF;
  padding: 4px 10px;
  border-radius: 12px;
  font-weight: bold;
}

.date-cell {
  color: #888;
  font-size: 0.9em;
}

.leaderboard-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.08);
}

/* Hiệu ứng Top 3 */
.gold { background: linear-gradient(90deg, rgba(255, 215, 0, 0.15), transparent); }
.silver { background: linear-gradient(90deg, rgba(192, 192, 192, 0.15), transparent); }
.bronze { background: linear-gradient(90deg, rgba(205, 127, 50, 0.15), transparent); }
</style>