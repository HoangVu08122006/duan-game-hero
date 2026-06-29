<template>
  <div class="management-container">
    
    <div class="page-header">
      <div>
        <h2 class="title">
          👥 QUẢN LÝ NGƯỜI CHƠI 
          <el-badge :value="players.length" type="primary" class="total-badge">
            <span class="total-label">Tổng số tài khoản</span>
          </el-badge>
        </h2>
        <p class="subtitle">Xem danh sách, kiểm tra chi tiết trang bị, Pet và thông số Game Master</p>
      </div>
      <div class="action-buttons">
        <button class="btn-add" @click="openRegisterDialog">
          ➕ Tạo Tài Khoản Mới
        </button>
        <el-button type="primary" :loading="loading" @click="fetchPlayers" round>
          🔄 Làm Mới
        </el-button>
      </div>
    </div>
 <div style="margin-bottom: 15px; display: flex; justify-content: flex-end;">
  <el-input 
  v-model="searchQuery" 
  placeholder="Tìm kiếm theo tên nhân vật..." 
  clearable 
  @input="onSearch" 
/>

<el-table :data="pagedPlayers" border stripe style="width: 100%">
  </el-table>


</div>

<el-card shadow="never" class="table-card">
  <el-table 
    v-loading="loading"
    :data="pagedPlayers" 
    border 
    stripe
    style="width: 100%"
  >
    <el-table-column type="index" label="STT" width="80" align="center" />
    <el-table-column prop="name" label="Tên Nhân Vật" min-width="150" />
    <el-table-column label="Đẳng Cấp" width="120" align="center">
      <template #default="scope">
        <el-tag type="success">Lv. {{ scope.row.level || 1 }}</el-tag>
      </template>
    </el-table-column>
    <el-table-column prop="gold" label="Vàng" width="130">
      <template #default="scope">
        <span class="gold-text">💰 {{ formatNumber(scope.row.gold) }}</span>
      </template>
    </el-table-column>
    <el-table-column prop="total_power" label="Tổng Lực Chiến" width="150">
      <template #default="scope">
        <span class="power-text">🔥 {{ formatNumber(scope.row.total_power) }}</span>
      </template>
    </el-table-column>

    <el-table-column label="Trạng Thái" width="140" align="center">
  <template #default="scope">
    <div style="display: flex; flex-direction: column; align-items: center;">
      <el-tag :type="scope.row.is_online ? 'success' : 'info'" size="small">
        {{ scope.row.is_online ? '🟢 Online' : '🔴 Offline' }}
      </el-tag>
      
      <span v-if="!scope.row.is_online" style="font-size: 11px; color: #888; margin-top: 4px;">
        {{ formatLastSeen(scope.row.last_login_at) }}
      </span>
    </div>
  </template>
</el-table-column>
    
    <el-table-column label="Hành Động" width="220" align="center" fixed="right">
      <template #default="scope">
        <el-button size="small" type="primary" plain @click="fetchPlayerDetail(scope.row.id)">👁️ Chi Tiết</el-button>
        <el-button size="small" type="danger" @click="handleDeletePlayer(scope.row)">❌ Xóa TK</el-button>
      </template>
    </el-table-column>
  </el-table>
</el-card>

<div style="margin-top: 20px; display: flex; justify-content: center;">
  <el-pagination
    v-model:current-page="currentPage"
    v-model:page-size="pageSize"
    :page-sizes="[10, 20, 50, 100]"
    layout="total, sizes, prev, pager, next, jumper"
    :total="filteredPlayers.length"
    @current-change="handlePageChange"
  />
</div>
    <el-dialog v-model="dialogVisible" title="➕ ĐĂNG KÝ TÀI KHOẢN MỚI" width="500px" destroy-on-close>
      <el-form :model="registerForm" :rules="rules" ref="registerFormRef" label-position="top">
        <el-form-item label="Tên Nhân Vật (Username)" prop="name" :error="nameFieldError">
          <el-input 
            v-model="registerForm.name" 
            placeholder="Nhập tên nhân vật..." 
            clearable 
            @input="nameFieldError = ''" 
          />
        </el-form-item>
        <el-form-item label="Mật Khẩu" prop="password">
          <el-input v-model="registerForm.password" type="password" placeholder="Nhập mật khẩu..." show-password />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">Hủy</el-button>
          <el-button type="primary" :loading="submitLoading" @click="submitRegisterForm(registerFormRef)">Khởi Tạo</el-button>
        </span>
      </template>
    </el-dialog>

    <el-dialog
      v-model="detailDialogVisible"
      title="🪪 THÔNG TIN CHI TIẾT GAME MASTER PLAYER"
      width="800px"
      destroy-on-close
    >
      <div v-if="detailLoading" v-loading="true" style="height: 300px;"></div>
      
      <div v-else-if="selectedPlayer" class="detail-wrapper">
        <div class="detail-header">
          <div class="avatar-box">👑</div>
          <div class="user-meta">
            <h3>{{ selectedPlayer.name }} <el-tag size="small" type="warning">ID: {{ selectedPlayer.id }}</el-tag></h3>
            <p>Tổng Lực Chiến: <span class="power-text">🔥 {{ formatNumber(powerDetails?.calculated_total_power || selectedPlayer.total_power) }}</span></p>
          </div>
        </div>

        <el-tabs type="border-card" class="detail-tabs" @tab-click="handleTabClick">
          <el-tab-pane label="📊 Thông Số & Công Pháp">
  <el-descriptions :column="3" border size="small">
    <el-descriptions-item label="Cấp Độ"><el-tag type="success">Lv. {{ selectedPlayer.level }}</el-tag></el-descriptions-item>
    <el-descriptions-item label="Kinh Nghiệm">{{ selectedPlayer.exp }}</el-descriptions-item>
    <el-descriptions-item label="Vàng">💰 {{ formatNumber(selectedPlayer.gold) }}</el-descriptions-item>
    
    <el-descriptions-item label="Ải Hiện Tại">🏰 {{ selectedPlayer.current_floor }}</el-descriptions-item>
    <el-descriptions-item label="Ải Cao Nhất">🏆 {{ selectedPlayer.highest_floor }}</el-descriptions-item>
    <el-descriptions-item label="Quái Đã Diệt">💀 {{ selectedPlayer.kill_count }}</el-descriptions-item>

    <el-descriptions-item label="Máu Cơ Bản">{{ formatNumber(selectedPlayer.base_hp) }}</el-descriptions-item>
    <el-descriptions-item label="Máu Hiện Tại">❤️ {{ formatNumber(selectedPlayer.current_hp) }}</el-descriptions-item>
    <el-descriptions-item label="Cấp HP">❤️ Lv. {{ selectedPlayer.upgraded_hp_lv }}</el-descriptions-item>

    <el-descriptions-item label="Công Cơ Bản">{{ formatNumber(selectedPlayer.base_attack) }}</el-descriptions-item>
    <el-descriptions-item label="Công Hiện Tại">⚔️ {{ formatNumber(selectedPlayer.current_attack) }}</el-descriptions-item>
    <el-descriptions-item label="Cấp Công">⚔️ Lv. {{ selectedPlayer.upgraded_attack_lv }}</el-descriptions-item>

    <el-descriptions-item label="Tốc Độ Cơ Bản">{{ selectedPlayer.base_speed }}</el-descriptions-item>
    <el-descriptions-item label="Tốc Độ Hiện Tại">⚡ {{ selectedPlayer.current_speed }}</el-descriptions-item>
    <el-descriptions-item label="Cấp Tốc Độ">⚡ Lv. {{ selectedPlayer.upgraded_speed_lv }}</el-descriptions-item>

    <el-descriptions-item label="Tỉ Lệ CM (B/H)">🎯 {{ selectedPlayer.base_crit_rate }}% / {{ selectedPlayer.current_crit_rate }}%</el-descriptions-item>
    <el-descriptions-item label="Sát Thương CM (B/H)">💥 {{ selectedPlayer.base_crit_damage }}% / {{ selectedPlayer.current_crit_damage }}%</el-descriptions-item>
    <el-descriptions-item label="Cấp Tỉ Lệ CM">🎯 Lv. {{ selectedPlayer.upgraded_crit_rate_lv }}</el-descriptions-item>
    <el-descriptions-item label="Cấp Sát Thương CM">💥 Lv. {{ selectedPlayer.upgraded_crit_damage_lv }}</el-descriptions-item>
  </el-descriptions>

            <h4 class="sub-section-title">📊 Chi Tiết Thuộc Tính Sức Mạnh (Lực Chiến)</h4>
            <div v-if="powerDetails" class="power-detail-box">
              <el-row :gutter="20">
                <el-col :span="8">
                  <div class="power-item">
                    <span class="label">👤 Lực chiến gốc:</span>
                    <span class="value">{{ formatNumber(powerDetails.damage_details?.hero_base) }}</span>
                  </div>
                </el-col>
                <el-col :span="8">
                  <div class="power-item">
                    <span class="label">⚔️ Vũ khí trang bị:</span>
                    <span class="value">+{{ formatNumber(powerDetails.damage_details?.equipped_weapon) }}</span>
                  </div>
                </el-col>
                <el-col :span="8">
                  <div class="power-item">
                    <span class="label">🐉 Thú cưng xuất trận:</span>
                    <span class="value">+{{ formatNumber(powerDetails.damage_details?.equipped_pet) }}</span>
                  </div>
                </el-col>
              </el-row>
            </div>

            <h4 class="sub-section-title">⚡ BẢNG ĐIỀU CHỈNH CẤP ĐỘ CÔNG PHÁP (GAME MASTER)</h4>
            <div class="stats-edit-box">
              <el-form :model="statsForm" inline size="small" label-position="left">
                <div class="stats-grid-inputs">
                  <el-form-item label="💪 Cấp HP:">
                    <el-input-number v-model="statsForm.upgraded_hp_lv" :min="0"  />
                  </el-form-item>
                  <el-form-item label="⚔️ Cấp ATK:">
                    <el-input-number v-model="statsForm.upgraded_attack_lv" :min="0"  />
                  </el-form-item>
                  <el-form-item label="⚡ Cấp Tốc Độ:">
                    <el-input-number v-model="statsForm.upgraded_speed_lv" :min="0" :max="100" />
                  </el-form-item>
                  <el-form-item label="🎯 Cấp Chí Mạng:">
                    <el-input-number v-model="statsForm.upgraded_crit_rate_lv" :min="0" :max="100" />
                  </el-form-item>
                  <el-form-item label="💥 Sát Thương CM:">
                    <el-input-number v-model="statsForm.upgraded_crit_damage_lv" :min="0" :max="1000" />
                  </el-form-item>
                  <el-form-item label="💰 Vàng:">
                    <el-input-number v-model="statsForm.gold" :min="0" :step="10000" />
                  </el-form-item>
                  <el-form-item label="✨ EXP:">
                    <el-input-number v-model="statsForm.exp" :min="0" :step="1000" />
                  </el-form-item>
                </div>
                <div style="margin-top: 12px; text-align: right;">
                  <el-button type="success" :loading="statsUpdateLoading" @click="updatePlayerStats">
                    ⚙️ Áp Dụng Chỉ Số Mới
                  </el-button>
                </div>
              </el-form>
            </div>
          </el-tab-pane>

          <el-tab-pane label="⚔️ Vũ Khí Sở Hữu">
            <el-table :data="selectedPlayer.weapons" border size="small" style="width: 100%">
              <el-table-column prop="weapon.name" label="Tên Vũ Khí" min-width="120" />
              
              <el-table-column label="Trạng Thái" width="130" align="center">
                <template #default="wScope">
                  <el-tag v-if="wScope.row.is_equipped === 1" type="danger" effect="dark">Đang Trang Bị</el-tag>
                  <el-tag v-else type="info">Trong Kho</el-tag>
                </template>
              </el-table-column>

              <el-table-column label="Cấp Cường Hóa" width="160" align="center">
                <template #default="wScope">
                  <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                    <span>Lv {{ wScope.row.level }}</span>
                    <el-input-number 
                      v-model="wScope.row.upgrade_input" 
                      :min="1" 
                      :max="100" 
                      size="small" 
                      style="width: 80px;"
                      placeholder="Cấp"
                    />
                  </div>
                </template>
              </el-table-column>

              <el-table-column label="Thao Tác Admin" width="150" align="center" fixed="right">
                <template #default="wScope">
                  <el-button 
                    type="success" 
                    size="small" 
                    @click="handleAdminUpgrade(wScope.row)"
                  >
                    Nâng cấp
                  </el-button>
                </template>
              </el-table-column>

              <el-table-column label="Sát Thương" width="110" align="center">
                <template #default="wScope">💥 {{ formatNumber(wScope.row.current_damage) }}</template>
              </el-table-column>

              <el-table-column label="Phí Nâng Cấp tiếp" width="120" align="center">
                <template #default="wScope">
                  🪙 {{ formatNumber(wScope.row.upgrade_cost) }}
                </template>
              </el-table-column>

              <el-table-column label="Thao Tác" width="120" align="center" fixed="right">
                <template #default="wScope">
                  <el-button 
                    v-if="wScope.row.is_equipped === 0"
                    type="primary" 
                    size="small"  :loading="equipLoading === wScope.row.id"
                    @click="handleEquipWeapon(wScope.row)"
                  >
                    Trang bị
                  </el-button>
                </template>
              </el-table-column>
            </el-table>
          </el-tab-pane>

         <el-tab-pane label="🐉 Trận Doanh Thú Cưng">
          <div v-if="selectedPlayer.pets && selectedPlayer.pets.length > 0">
            <div v-for="pet in selectedPlayer.pets" :key="pet.id" class="pet-card">
              <div class="pet-info-main">
                <div>
                  <span class="pet-title">🐉 {{ pet.name }}</span> 
                  <el-tag size="small" type="success" style="margin-left: 8px;">Cấp {{ pet.pivot?.level }}</el-tag>
                  <el-tag v-if="pet.pivot?.is_equipped === 1" size="small" type="danger" style="margin-left: 5px;">Xuất Trận</el-tag>
                </div>
                
                <div class="admin-control" style="margin-top: 10px; padding: 10px; border: 1px dashed #e6a23c; border-radius: 4px;">
                  <span style="font-size: 12px; font-weight: bold; color: #e6a23c;">Điều chỉnh cấp độ:</span>
                  
                  <el-input-number 
                    v-model="pet.pivot.edit_level" 
                    :min="1" 
                    :max="999" 
                    size="small" 
                    style="width: 120px; margin: 0 10px;"
                    controls-position="right"
                  />
                  
                 <el-button 
                type="warning" 
                  size="small" 
                  @click="handleAdminUpgradePet(pet)" 
                >
                    Áp dụng
                  </el-button>

                  <el-button 
                    v-if="pet.pivot?.is_equipped !== 1" 
                    type="primary" 
                    size="small" 
                    plain
                    @click="handleAdminEquipPet(pet)"
                    style="margin-left: 10px;"
                  >
                    <el-icon><Check /></el-icon> Xuất trận
                  </el-button>

                  <el-tag v-else type="danger" effect="dark" size="small">
                    Đang xuất trận
                  </el-tag>
                  </div>
                <div class="pet-power">Lực chiến Pet: ⚡ {{ formatNumber(pet.total_power) }}</div>
              </div>
                            
              <div class="pet-skills-list">
                <h5>🔥 Kỹ Năng Thần Thú:</h5>
                <ul>
                  <li>🔹 <b>{{ pet.skill_1_name }}</b> (Sát thương: <span class="power-text">{{ formatNumber(selectedPlayer.pet_skills?.skill_1_dame) }}</span>)</li>
                  <li>🔹 <b>{{ pet.skill_2_name }}</b> (Sát thương: <span class="power-text">{{ formatNumber(selectedPlayer.pet_skills?.skill_2_dame) }}</span>)</li>
                  <li>🔹 <b>{{ pet.skill_3_name }}</b> (Sát thương: <span class="power-text">{{ formatNumber(selectedPlayer.pet_skills?.skill_3_dame) }}</span>)</li>
                </ul>
              </div>
            </div>
          </div>
          <el-empty v-else description="Player này chưa thuần hóa Thú Cưng nào!" />
        </el-tab-pane>

          <el-tab-pane label="🔐 Tài Khoản & Bảo Mật">
            <div v-loading="accountLoading" class="account-settings-box">
              <h4 class="sub-section-title" style="margin-top: 0;">🛠️ Thay Đổi Cấu Hình Xác Thực</h4>
              
              <el-form :model="accountForm" label-position="top" size="default">
                <el-form-item label="Tên Tài Khoản Hiện Tại / Mới">
                  <el-input 
                    v-model="accountForm.name" 
                    placeholder="Nhập tên tài khoản mới nếu muốn đổi..." 
                    clearable
                  />
                </el-form-item>
                
                <el-form-item label="Mật Khẩu Mới (Bỏ trống nếu không muốn giữ nguyên)">
                  <el-input 
                    v-model="accountForm.password" 
                    type="password" 
                    placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)..." 
                    show-password
                    clearable
                  />
                </el-form-item>

                <el-form-item style="margin-bottom: 0; margin-top: 20px;">
                  <el-button 
                    type="warning" 
                    :loading="accountUpdateLoading" 
                    @click="updatePlayerAccount"
                  >
                    💾 Lưu Cập Nhật Tài Khoản
                  </el-button>
                </el-form-item>
              </el-form>
            </div>
          </el-tab-pane>

          <el-tab-pane name="logs">
            <template #label>
              <el-badge :value="unreadLogsCount" :hidden="unreadLogsCount === 0" class="badge-item">
                <span>📜 Nhật Ký Thay Đổi</span>
              </el-badge>
            </template>
            <div class="log-container" style="max-height: 400px; overflow-y: auto;">
              <el-timeline v-if="auditLogs.length > 0">
                <el-timeline-item v-for="log in auditLogs" :key="log.id" :timestamp="log.created_at">
                  Đã đổi <b>{{ log.field_name }}</b>: 
                  <el-tag size="mini" type="info">{{ log.old_value }}</el-tag> ➡️ 
                  <el-tag size="mini" type="success">{{ log.new_value }}</el-tag>
                </el-timeline-item>
              </el-timeline>
              <el-empty v-else description="Chưa có dữ liệu thay đổi nào"></el-empty>
            </div>
          </el-tab-pane>
        </el-tabs>
      </div>
      
      <template #footer>
        <el-button type="primary" @click="detailDialogVisible = false">Đóng Giao Diện</el-button>
      </template>
    </el-dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import axios from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'

// --- CẤU HÌNH HEADERS ĐỂ FIX LỖI 401 UNAUTHENTICATED ---
const getAuthHeaders = () => {
  const token = localStorage.getItem('admin_token') || sessionStorage.getItem('admin_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

const players = ref([])
const loading = ref(false)
const handleTabClick = (tab) => {
  // tab.paneName hoặc tab.props.name chứa giá trị name="logs" bạn đã đặt
  if (tab.paneName === 'logs') {
    unreadLogsCount.value = 0; // Reset thông báo
    if (selectedPlayer.value) {
      loadLogs(selectedPlayer.value.id);
    }
  }
};
const detailDialogVisible = ref(false)
const detailLoading = ref(false)
const selectedPlayer = ref(null)
const powerDetails = ref(null)

// State quản lý sửa tài khoản
const accountLoading = ref(false)
const accountUpdateLoading = ref(false)
const accountForm = reactive({ id: null, name: '', password: '' })

// State quản lý sửa chỉ số Stats (PATCH API)
const statsUpdateLoading = ref(false)
const statsForm = reactive({
  gold: 0,
  exp: 0,
  upgraded_hp_lv: 0,
  upgraded_attack_lv: 0,
  upgraded_speed_lv: 0,
  upgraded_crit_rate_lv: 0,
  upgraded_crit_damage_lv: 0
})
const unreadLogsCount = ref(0);
// Hàm xử lý khi click vào tab
const handleLogTabClick = () => {
  unreadLogsCount.value = 0; // Reset số lượng về 0
  loadLogs(selectedPlayer.value.id);
};

// State form đăng ký mới
// Thêm các biến state
const searchQuery = ref('');
const currentPage = ref(1);
const pageSize = ref(10);
const auditLogs = ref([]);
// Hàm tải nhật ký từ Server
const loadLogs = async (playerId) => {
  try {
    const response = await axios.get(`http://26.103.188.167:8000/api/players/${playerId}/logs`, { 
      headers: getAuthHeaders() 
    });
    auditLogs.value = response.data;
  } catch (error) {
    ElMessage.error("Không thể tải nhật ký thay đổi!");
  }
};
// 1. Tính toán danh sách đã lọc theo tên
const filteredPlayers = computed(() => {
  if (!players.value || players.value.length === 0) return [];
  
  return players.value.filter(player => 
    player.name && player.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

// 2. Tính toán danh sách cho trang hiện tại
const pagedPlayers = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredPlayers.value.slice(start, end);
});

// Hàm reset trang khi tìm kiếm hoặc thay đổi filter
const handlePageChange = (val) => {
  currentPage.value = val;
};



const dialogVisible = ref(false)
const submitLoading = ref(false)
const registerFormRef = ref(null)
const registerForm = reactive({ name: '', password: '' })
const rules = reactive({
  name: [{ required: true, message: 'Vui lòng điền tên nhân vật!', trigger: 'blur' }],
  password: [{ required: true, message: 'Vui lòng nhập mật khẩu!', trigger: 'blur' }]
})
const nameFieldError = ref('')

// 1. Lấy danh sách người chơi tổng quan
const fetchPlayers = async () => {
  loading.value = true
  
  try {
    const response = await axios.get('http://26.103.188.167:8000/api/list/players', { headers: getAuthHeaders() })
    if (response.data && response.data.status === 'success') {
      players.value = response.data.data
    }

  } catch (error) {
    ElMessage.error('Không thể lấy danh sách người chơi!')
  } finally { 
    loading.value = false 
  }
}

  // Khai báo biến state loading
const equipLoading = ref(null);

// Hàm xử lý trang bị
const handleEquipWeapon = async (weapon) => {
  equipLoading.value = weapon.id;
  try {
    const response = await axios.post(`http://26.103.188.167:8000/api/admin/weapon/equip`, {
      player_id: selectedPlayer.value.id,
      id: weapon.id 
    }, { headers: getAuthHeaders() });

    if (response.data.status === 'success') {
      ElMessage.success('Đã trang bị vũ khí thành công!');
      await fetchPlayerDetail(selectedPlayer.value.id);
    }
  } catch (error) {
    ElMessage.error(error.response?.data?.message || 'Có lỗi xảy ra!');
  } finally {
    equipLoading.value = null;
  }
};

// Hàm nâng cấp vũ khí cho Admin
const handleAdminUpgrade = async (row) => {
  if (!row.upgrade_input || row.upgrade_input < 1) {
    ElMessage.warning('Vui lòng nhập số cấp hợp lệ!');
    return;
  }

  try {
    const response = await axios.post('http://26.103.188.167:8000/api/admin/weapon/upgrade', {
      player_id: selectedPlayer.value.id,
      weapon_id: row.id,
      new_level: row.upgrade_input
    }, { headers: getAuthHeaders() });

    ElMessage.success(response.data.message);
    
    // Cập nhật lại dữ liệu vũ khí sau khi nâng cấp
    await fetchPlayerDetail(selectedPlayer.value.id);
    
    // Tải lại log để cập nhật lịch sử nâng cấp cho Admin thấy
    await loadLogs(selectedPlayer.value.id);
    
  } catch (error) {
    ElMessage.error('Nâng cấp thất bại: ' + (error.response?.data?.message || 'Lỗi server'));
  }
};

// Khai báo state để quản lý loading cho từng Pet
// ... các phần code trước đó của bạn ...

const petUpgradeLoading = ref(null);
const handleAdminUpgradePet = async (pet) => {
  try {
    const payload = {
      player_id: selectedPlayer.value.id,
      player_pet_id: pet.pivot.id,
      new_level: pet.pivot.edit_level
    };

    const res = await axios.post('/api/admin/pet/upgrade', payload);
    ElMessage.success(res.data.message);

    // CẬP NHẬT DỮ LIỆU CỤ THỂ CHO PET NÀY
    pet.pivot.level = res.data.data.new_level;
    pet.calculated_stats = res.data.data.new_stats;
    
    // Gán các chỉ số sát thương mới vào đúng pet
    pet.skill_1_dame = res.data.data.skill_1_dame;
    pet.skill_2_dame = res.data.data.skill_2_dame;
    pet.skill_3_dame = res.data.data.skill_3_dame;

  } catch (err) {
    return ElMessage.error(err.response?.data?.message || 'Cập nhật thất bại');
  }
}

const handleAdminEquipPet = async (pet) => {
  try {
    // 1. Tạo payload gửi lên server
    const payload = {
      player_id: selectedPlayer.value.id,
      player_pet_id: pet.pivot.id // ID trong bảng player_pets
    };

    // 2. Gọi API (Sử dụng endpoint đã khai báo trong route)
    const res = await axios.post('/api/admin/pets/equip', payload);

    // 3. Thông báo thành công
    ElMessage.success(res.data.message);

    // 4. Cập nhật trạng thái hiển thị ngay lập tức (không cần load lại toàn bộ nếu không cần)
    // Hoặc có thể đợi loadPlayerDetail để cập nhật chính xác từ DB
  } catch (err) {
    return ElMessage.error(
      err.response?.data?.message || 'Không thể chuyển pet xuất trận'
    );
  }

  // 5. Reload lại chi tiết người chơi để làm mới giao diện (cập nhật tag "Xuất Trận")
  try {
    await loadPlayerDetail(selectedPlayer.value.id);
  } catch (err) {
    console.error('Lỗi reload dữ liệu sau khi trang bị:', err);
  }
};


// 2. Lấy chi tiết thông tin và điền dữ liệu vào form sửa
const fetchPlayerDetail = async (playerId) => {
  detailDialogVisible.value = true
  detailLoading.value = true
  selectedPlayer.value = null
  powerDetails.value = null
  
  accountForm.id = playerId
  accountForm.name = ''
  accountForm.password = ''
  accountLoading.value = true


  try {
    const headers = { headers: getAuthHeaders() }
    const [detailRes, powerRes, accountRes] = await Promise.all([
      axios.get(`http://26.103.188.167:8000/api/list/players/${playerId}`, headers),
      axios.get(`http://26.103.188.167:8000/api/list/players/${playerId}/total-power`, headers),
      axios.get(`http://26.103.188.167:8000/api/list/players/${playerId}/account`, headers)
    ])
    

    if (detailRes.data) {
      const pData = detailRes.data.player || detailRes.data.data || detailRes.data
      selectedPlayer.value = pData
      
      // Gán dữ liệu chỉ số cũ vào Form sửa chỉ số
      statsForm.gold = pData.gold || 0;
      statsForm.exp = pData.exp || 0;
      statsForm.upgraded_hp_lv = pData.upgraded_hp_lv || 0
      statsForm.upgraded_attack_lv = pData.upgraded_attack_lv || 0
      statsForm.upgraded_speed_lv = pData.upgraded_speed_lv || 0
      statsForm.upgraded_crit_rate_lv = pData.upgraded_crit_rate_lv || 0
      statsForm.upgraded_crit_damage_lv = pData.upgraded_crit_damage_lv || 0
    }

    if (powerRes.data && powerRes.data.status === 'success') {
      powerDetails.value = powerRes.data
      const targetPlayer = players.value.find(p => p.id === playerId)
      if (targetPlayer) {
        targetPlayer.total_power = powerRes.data.calculated_total_power
      }
    }

    if (accountRes.data && accountRes.data.status === 'success' && accountRes.data.data) {
      accountForm.name = accountRes.data.data.current_name || ''
    } else {
      accountForm.name = selectedPlayer.value?.name || ''
    }

  } catch (error) {
    ElMessage.error('Không thể lấy đầy đủ thông tin tài khoản!')
    console.error(error)
  } finally {
    detailLoading.value = false
    accountLoading.value = false
  }
}

// Thay thế hàm updatePlayerStats cũ bằng hàm này
const updatePlayerStats = async () => {
  if (!selectedPlayer.value) return;

  statsUpdateLoading.value = true;
  try {
    const response = await axios.patch(
      `http://26.103.188.167:8000/api/list/players/${selectedPlayer.value.id}/stats`, 
      statsForm, 
      { headers: getAuthHeaders() }
    );
    if (response.data.status === 'success') {
        unreadLogsCount.value++; // Tăng biến đếm
        await loadLogs(selectedPlayer.value.id);
      }
    if (response.data.status === 'success') {
      ElMessage.success('Đã cập nhật chỉ số nhân vật thành công!');
      await fetchPlayerDetail(selectedPlayer.value.id);
      // GỌI HÀM NÀY ĐỂ LÀM MỚI LOG SAU KHI SỬA
      await loadLogs(selectedPlayer.value.id); 
  }
  } catch (error) {
    ElMessage.error('Cập nhật chỉ số thất bại: ' + (error.response?.data?.message || 'Lỗi server'));
  } finally {
    statsUpdateLoading.value = false;
  }
};

// 4. API PUT: Cập nhật thông tin tài khoản (Name, Password)
const updatePlayerAccount = async () => {
  if (!accountForm.name.trim()) {
    ElMessage.warning('Tên tài khoản không được để trống!')
    return
  }

  accountUpdateLoading.value = true
  try {
    const payload = { name: accountForm.name }
    if (accountForm.password && accountForm.password.trim() !== '') {
      if (accountForm.password.length < 6) {
        ElMessage.warning('Mật khẩu mới phải có tối thiểu 6 ký tự!')
        accountUpdateLoading.value = false
        return
      }
      payload.password = accountForm.password
    }

    const response = await axios.put(
      `http://26.103.188.167:8000/api/list/players/${accountForm.id}/account`, 
      payload,
      { headers: getAuthHeaders() }
    )
    
    if (response.data && response.data.status === 'success') {
      ElMessage.success('Admin đã cập nhật thông tin tài khoản thành công!')
      if (selectedPlayer.value) selectedPlayer.value.name = accountForm.name
      const targetPlayer = players.value.find(p => p.id === accountForm.id)
      if (targetPlayer) targetPlayer.name = accountForm.name
      accountForm.password = ''
    }
  } catch (error) {
    ElMessage.error(error.response?.data?.message || 'Cập nhật tài khoản thất bại.')
  } finally {
    accountUpdateLoading.value = false
  }
}

// 5. Xóa tài khoản
const handleDeletePlayer = (row) => {
  ElMessageBox.confirm(
    `Hành động này sẽ xóa hoàn toàn nhân vật [${row.name}] khỏi hệ thống. Bạn có chắc chắn muốn tiếp tục?`,
    '⚠️ CẢNH BÁO XÓA TÀI KHOẢN',
    { confirmButtonText: 'Đồng Ý Xóa', cancelButtonText: 'Hủy Bỏ', type: 'danger', center: true }
  )
    .then(async () => {
      try {
        await axios.delete(`http://26.103.188.167:8000/api/list/players/${row.id}`, { headers: getAuthHeaders() })
        ElMessage.success(`Đã xóa thành công tài khoản [${row.name}]!`)
        fetchPlayers() 
      } catch (error) { ElMessage.error('Xóa thất bại!') }
    })
    .catch(() => { ElMessage.info('Đã hủy thao tác xóa.') })
}

const openRegisterDialog = () => { 
  nameFieldError.value = ''
  dialogVisible.value = true 
}

const submitRegisterForm = async (formEl) => {
  if (!formEl) return;
  
  // 1. Chặn click đúp ngay từ đầu
  if (submitLoading.value) return; 

  try {
    // 2. Validate trực tiếp bằng Promise
    await formEl.validate(); 
    
    submitLoading.value = true;
    nameFieldError.value = '';

    await axios.post('http://26.103.188.167:8000/api/register', registerForm, { 
      headers: getAuthHeaders() 
    });

    ElMessage.success('Tạo tài khoản thành công!');
    dialogVisible.value = false;
    fetchPlayers();
  } catch (error) {
    // 3. Kiểm tra lỗi thông minh hơn
    const data = error.response?.data;
    const status = error.response?.status;
    
    // Ưu tiên lấy message từ server trả về, nếu không có mới dùng text mặc định
    const serverMsg = data?.error || data?.message || '';
    
    if (status === 400 || status === 422 || serverMsg.toLowerCase().includes('tồn tại')) {
      nameFieldError.value = 'Tên nhân vật đã tồn tại!';
      ElMessage.error('Đăng ký thất bại: Tên nhân vật đã tồn tại!');
    } else {
      ElMessage.error('Lỗi hệ thống: ' + (serverMsg || 'Không thể kết nối API'));
    }
  } finally {
    submitLoading.value = false;
  }


};
const formatLastSeen = (dateString) => {
  if (!dateString) return 'Chưa từng online';
  
  const lastSeen = new Date(dateString);
  const now = new Date();
  const diffInMinutes = Math.floor((now - lastSeen) / (1000 * 60));
  
  if (diffInMinutes < 1) return 'Vừa xong';
  if (diffInMinutes < 60) return `${diffInMinutes} phút trước`;
  
  const diffInHours = Math.floor(diffInMinutes / 60);
  if (diffInHours < 24) return `${diffInHours} giờ trước`;
  
  const diffInDays = Math.floor(diffInHours / 24);
  if (diffInDays > 7) return '> 7 ngày trước';
  return `${diffInDays} ngày trước`;
};

const formatNumber = (num) => num ? num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") : 0

onMounted(() => { fetchPlayers() })
</script>

<style scoped>
.management-container {
  padding: 25px;
  min-height: 100vh;
  background:
    radial-gradient(circle at top, #1d2845 0%, #0f172a 40%, #020617 100%);
  color: #fff;
}

/* HEADER */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;

  padding: 20px;
  border-radius: 20px;

  background: rgba(255,255,255,0.05);
  backdrop-filter: blur(12px);

  border: 1px solid rgba(255,255,255,0.08);
  box-shadow: 0 0 30px rgba(255,215,0,0.15);
}

.title {
  font-size: 28px;
  font-weight: 800;
  color: #ffd700;
  text-shadow: 0 0 12px rgba(255,215,0,0.5);
}

.subtitle {
  margin-top: 6px;
  color: #cbd5e1;
}

/* BUTTON */
.btn-add {
  background: linear-gradient(135deg,#f59e0b,#f97316);
  border: none;
  color: white;
  padding: 12px 22px;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: .3s;
}

.btn-add:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(245,158,11,.4);
}

/* CARD */
.table-card {
  background: rgba(255,255,255,0.04) !important;
  border-radius: 20px !important;
  border: 1px solid rgba(255,255,255,.08);
  overflow: hidden;
}

/* TABLE */
:deep(.el-table) {
  background: transparent !important;
  color: white;
}

:deep(.el-table th) {
  background: linear-gradient(
    90deg,
    #1e293b,
    #334155
  ) !important;
  color: #ffd700 !important;
  font-weight: bold;
  font-size: 14px;
}

:deep(.el-table tr) {
  background: rgba(255,255,255,0.02);
}

:deep(.el-table tr:hover td) {
  background: rgba(59,130,246,0.15) !important;
}

:deep(.el-table td) {
  border-color: rgba(255,255,255,.05);
}

/* POWER */
.power-text {
  color: #ff4d4f;
  font-weight: bold;
  text-shadow: 0 0 8px rgba(255,77,79,.5);
}

.gold-text {
  color: #facc15;
  font-weight: bold;
  text-shadow: 0 0 8px rgba(250,204,21,.5);
}

/* SEARCH */
:deep(.el-input__wrapper) {
  background: rgba(255,255,255,.05);
  border-radius: 12px;
  box-shadow: none !important;
}

/* DETAIL PLAYER */
.detail-wrapper {
  color: white;
}

.detail-header {
  display: flex;
  align-items: center;
  gap: 20px;

  margin-bottom: 20px;
  padding: 20px;

  border-radius: 18px;

  background: linear-gradient(
    135deg,
    rgba(59,130,246,.15),
    rgba(168,85,247,.15)
  );
}

.avatar-box {
  width: 90px;
  height: 90px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 50%;

  font-size: 40px;

  background: linear-gradient(
    135deg,
    #f59e0b,
    #ef4444
  );

  box-shadow: 0 0 20px rgba(245,158,11,.5);
}

/* PET CARD */
.pet-card {
  margin-bottom: 18px;
  padding: 18px;

  border-radius: 16px;

  background: linear-gradient(
    135deg,
    rgba(30,41,59,.8),
    rgba(15,23,42,.9)
  );

  border: 1px solid rgba(255,215,0,.2);

  transition: .3s;
}

.pet-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 0 25px rgba(255,215,0,.2);
}

.pet-title {
  font-size: 18px;
  font-weight: 700;
  color: #facc15;
}

.pet-power {
  margin-top: 10px;
  font-size: 15px;
  color: #22c55e;
  font-weight: bold;
}

/* SKILL BOX */
.pet-skills-list {
  margin-top: 15px;
  padding: 15px;
  border-radius: 12px;

  background: rgba(255,255,255,.03);
}

.pet-skills-list h5 {
  color: #fb923c;
  margin-bottom: 10px;
}

/* POWER BOX */
.power-detail-box {
  margin-top: 15px;
  padding: 20px;
  border-radius: 15px;

  background: rgba(255,255,255,.04);

  border: 1px solid rgba(255,255,255,.08);
}

.power-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.power-item .label {
  color: #94a3b8;
}

.power-item .value {
  color: #facc15;
  font-size: 20px;
  font-weight: bold;
}

/* TAB */
:deep(.el-tabs__item) {
  color: #cbd5e1;
  font-weight: 600;
}

:deep(.el-tabs__item.is-active) {
  color: #ffd700 !important;
}

/* PAGINATION */
:deep(.el-pagination) {
  background: transparent;
  color: white;
}

/* SCROLLBAR */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-thumb {
  background: #475569;
  border-radius: 20px;
}

::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}
</style>