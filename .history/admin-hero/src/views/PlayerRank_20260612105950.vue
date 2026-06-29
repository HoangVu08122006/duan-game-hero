<template>
  <div class="leaderboard-container">
    <h2 class="title">Bảng Xếp Hạng</h2>
    <div class="table-wrapper">
      <table class="styled-table">
        <thead>
          <tr>
            <th>Hạng</th>
            <th>Tên Người Chơi</th>
            <th>Lực Chiến</th>
            <th>Tầng Cao Nhất</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(player, index) in players" :key="player.id" :class="{'top-three': index < 3}">
            <td>
              <span class="rank-badge" :class="'rank-' + index">{{ player.rank || index + 1 }}</span>
            </td>
            <td><strong>{{ player.name }}</strong></td>
            <td>{{ player.total_power.toLocaleString() }}</td>
            <td><span class="floor-tag">{{ player.highest_floor }}</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.leaderboard-container {
  padding: 20px;
  background: #f9fafc;
  border-radius: 8px;
}

.title {
  color: #304156;
  margin-bottom: 20px;
}

.table-wrapper {
  background: white;
  padding: 15px;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.styled-table {
  width: 100%;
  border-collapse: collapse;
  margin: 10px 0;
  font-size: 14px;
}

.styled-table thead tr {
  background-color: #304156;
  color: #ffffff;
  text-align: left;
}

.styled-table th, .styled-table td {
  padding: 12px 15px;
  border-bottom: 1px solid #dddddd;
}

.styled-table tbody tr:hover {
  background-color: #f1f1f1;
  cursor: pointer;
}

.rank-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: bold;
}

.rank-0 { background: #ffd700; color: #000; } /* Vàng cho top 1 */
.rank-1 { background: #c0c0c0; color: #000; } /* Bạc cho top 2 */
.rank-2 { background: #cd7f32; color: #fff; } /* Đồng cho top 3 */

.floor-tag {
  background: #409EFF;
  color: white;
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 12px;
}
</style>