import { createRouter, createWebHistory } from 'vue-router'

import LoginAdmin from '@/LoginAdmin.vue'
import AdminLayout from '@/layouts/AdminLayout.vue'

import PlayerManagement from '@/views/PlayerManagement.vue'
import WeaponManagement from '@/views/WeaponManagement.vue'
import AccountLogs from '@/views/AccountLogs.vue'
import PetManagement from '@/views/PetManagement.vue'
import Leaderboard from '@/views/PlayerRank.vue'
import RewardManagement from '@/views/RewardManagement.vue'
import EntityManagement from '@/views/EntityManagement.vue'
import GachaConfig from '@/views/GachaConfig.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      component: LoginAdmin
    },
    {
      path: '/dashboard',
      component: AdminLayout,
      children: [
        {
          path: '',
          redirect: '/dashboard/players'
        },
        {
          path: 'players',
          name: 'players',
          component: PlayerManagement
        },
        // ĐÃ THÊM ĐƯỜNG DẪN NÀY
        {
          path: 'leaderboard',
          name: 'leaderboard',
          component: Leaderboard
        },
        {
          path: 'weapons',
          name: 'weapons',
          component: WeaponManagement
        },
        {
          path: 'pets',
          name: 'pets',
          component: PetManagement
        },
        {
          path: 'account-logs',
          name: 'account-logs',
          component: AccountLogs
        },
        {
          path: 'daily-rewards',
          name: 'daily-rewards',
          component: RewardManagement
        },
        {
          path: 'entity-management',
          name: 'entity-management',
          component: EntityManagement
        },
        
      ]
    }
  ]
})

export default router