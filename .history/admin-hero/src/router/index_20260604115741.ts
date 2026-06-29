import { createRouter, createWebHistory } from 'vue-router'

import LoginAdmin from '@/LoginAdmin.vue'
import AdminLayout from '@'

import PlayerManagement from '@/views/PlayerManagement.vue'
import WeaponManagement from '@/views/WeaponManagement.vue'
import AccountLogs from '@/views/AccountLogs.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),

  routes: [
    // Trang đăng nhập
    {
      path: '/',
      name: 'login',
      component: LoginAdmin
    },

    // Layout admin
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
        {
          path: 'weapons',
          name: 'weapons',
          component: WeaponManagement
        },
        {
          path: 'account-logs',
          name: 'account-logs',
          component: AccountLogs
        }
      ]
    }
  ]
})

export default router