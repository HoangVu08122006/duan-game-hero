<template>
  <div class="management-container">
    
    <div class="page-header">
      <div>
        <h2 class="title">👥 QUẢN LÝ NGƯỜI CHƠI</h2>
        <p class="subtitle">Xem danh sách, kiểm tra lực chiến và thông số nâng cấp thời gian thực</p>
      </div>
      <div>
        <el-button type="primary" :loading="loading" @click="fetchPlayers" round>
          🔄 Làm Mới Dữ Liệu
        </el-button>
      </div>
    </div>

    <el-card shadow="never" class="table-card">
      <el-table 
        v-loading="loading"
        :data="players" 
        border 
        stripe
        style="width: 100%"
        element-loading-text="Đang tải dữ liệu player từ API..."
      >
        <el-table-column prop="id" label="ID" width="80" align="center" />
        
        <el-table-column prop="name" label="Tên Nhân Vật" min-width="150">
          <template #default="scope">
            <span class="player-name">{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        
        <el-table-column label="Đẳng Cấp" width="130" align="center">
          <template #default="scope">
            <el-tag type="success" effect="dark">Lv. {{ scope.row.level }}</el-tag>
            <div class="exp-text">EXP: {{ scope.row.exp }}</div>
          </template>
        </el-table-column>

        <el-table-column prop="total_power" label="Lực Chiến" width="130" align="center">
          <template #default="scope">
            <span class="power-text">🔥 {{ formatNumber(scope.row.total_power) }}</span>
          </template>
        </el-table-column>
        
        <el-table-column prop="gold" label="Vàng (Gold)" width="150">
          <template #default="scope">
            <span class="gold-text">💰 {{ formatNumber(scope.row.gold) }}</span>
          </template>
        </el-table-column>

        <el-table-column label="Vượt Ải (Floor)" width="140" align="center">
          <template #default="scope">
            <div style="font-size: 13px;">Hiện tại: <b>{{ scope.row.current_floor }}</b></div>
            <div class="highest-floor">Cao nhất: {{ scope.row.highest_floor }}</div>
          </template>
        </el-table-column>

        <el-table-column label="Cấp Công Pháp / Thuộc Tính Nâng Cấp" min-width="280">
          <template #default="scope">
            <div class="stats-box">
              <div>⚔️ <b>ATK:</b> {{ scope.row.base_attack }} <span class="upgrade-lv">(Lv.{{ scope.row.upgraded_attack_lv }})</span></div>
              <div>❤️ <b>HP:</b> {{ scope.row.current_hp }}/{{ scope.row.base_hp }} <span class="upgrade-lv">(Lv.{{ scope.row.upgraded_hp_lv }})</span></div>
              <div>⚡ <b>Tốc độ:</b> <span class="upgrade-tag">Lv.{{ scope.row.upgraded_speed_lv }}</span> | 🎯 <b>Chí mạng:</b> <span class="upgrade-tag">Lv.{{ scope.row.upgraded_crit_rate_lv }}</span></div>
            </div>
          </template>
        </el-table-column>
        
        <el-table-column label="Hành Động" width="150" align="center" fixed="right">
          <template #default="scope">
            <el-dropdown trigger="click">
              <el-button type="warning" size="small">
                Thao Tác <el-icon class="el-icon--right"><arrow-down /></el-icon>
              </el-button>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item @click="handleEditGold(scope.row)">✨ Cộng/Trừ Vàng</el-dropdown-item>
                  <el-dropdown-item @click="handleEditLevel(scope.row)">🆙 Sửa Cấp Độ</el-dropdown-item>
                  <el-dropdown-item divided style="color: #f56c6c;" @click="handleKick(scope.row)">
                    ❌ Kick / Ban Player
                  </el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { ElMessage } from 'element-plus'
import { ArrowDown } from '@element-plus/icons-vue' // Import icon mũi tên xuống cho dropdown

// Khởi tạo các State biến phản xạ
const players = ref([])
const loading = ref(false)

// Hàm gọi API thực tế lấy danh sách người chơi từ Backend
const fetchPlayers = async () => {
  loading.value = true
  try {
    const response = await axios.get('http://26.103.188.167:8000/api/list/players')
    
    // Kiểm tra cấu trúc JSON trả