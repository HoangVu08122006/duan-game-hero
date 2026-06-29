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
          v-for="player in players"
          :key="player.id"
          :class="{
            gold: player.rank === 1,
            silver: player.rank === 2,
            bronze: player.rank === 3
          }"
        >
          <td>
            <span v-if="player.rank === 1">🥇</span>
            <span v-else-if="player.rank === 2">🥈</span>
            <span v-else-if="player.rank === 3">🥉</span>
            <span v-else>{{ player.rank }}</span>
          </td>

          <td>{{ player.name }}</td>
          <td>{{ player.total_power.toLocaleString() }}</td>
          <td>{{ player.highest_floor }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>