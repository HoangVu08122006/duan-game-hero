<template>
  <div class="leaderboard-container">
    <h2 class="title">🏆 BẢNG XẾP HẠNG 🏆</h2>

    <table class="leaderboard-table">
      <thead>
        <tr>
          <th>Hạng</th>
          <th>Tên người chơi</th>
          <th>Lực chiến</th>
          <th>Tầng cao nhất</th>
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
            <span v-else>
              {{ player.rank || index + 1 }}
            </span>
          </td>

          <td>{{ player.name }}</td>
          <td>{{ Number(player.total_power).toLocaleString() }}</td>
          <td>{{ player.highest_floor }}</td>
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
    async fetchLeaderboard() {
      try {
        const response = await axios.get('/api/leaderboard/rankings')

        this.players =
          response.data?.data?.data ||
          response.data?.data ||
          []
      } catch (error) {
        console.error('Lỗi khi tải bảng xếp hạng:', error)
      }
    }
  }
}
</script>

<style scoped>
.leaderboard-container {
  max-width: 1000px;
  margin: 40px auto;
  padding: 20px;
}

.title {
  text-align: center;
  color: #ffd700;
  font-size: 32px;
  margin-bottom: 20px;
  text-shadow: 0 0 10px #ffd700;
}

.leaderboard-table {
  width: 100%;
  border-collapse: collapse;
  overflow: hidden;
  border-radius: 15px;
  background: #1e1e2f;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.4);
}

.leaderboard-table th {
  background: #2d2d44;
  color: white;
  padding: 15px;
  font-size: 18px;
}

.leaderboard-table td {
  padding: 14px;
  text-align: center;
  color: white;
}

.leaderboard-table tbody tr {
  transition: all 0.3s ease;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.leaderboard-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.08);
}

.gold {
  background: linear-gradient(
    90deg,
    rgba(255, 215, 0, 0.25),
    rgba(255, 215, 0, 0.05)
  );
}

.silver {
  background: linear-gradient(
    90deg,
    rgba(192, 192, 192, 0.25),
    rgba(192, 192, 192, 0.05)
  );
}

.bronze {
  background: linear-gradient(
    90deg,
    rgba(205, 127, 50, 0.25),
    rgba(205, 127, 50, 0.05)
  );
}

@media (max-width: 768px) {
  .leaderboard-table th,
  .leaderboard-table td {
    padding: 10px;
    font-size: 14px;
  }

  .title {
    font-size: 24px;
  }
}
</style>