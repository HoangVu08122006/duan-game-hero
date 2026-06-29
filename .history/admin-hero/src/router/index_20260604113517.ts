import { createRouter, createWebHistory } from 'vue-router'

import LoginAdmin from '@/LoginAdmin.vue'
import AdminLayout from 'App.vue'

import PlayerManagement from '@/views/PlayerManagement.vue'
import WeaponManagement from '@/views/WeaponManagement.vue'
import AccountLogs from '@/views/AccountLogs.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // Login
    {
      path: '/',
      name: 'login',
      component: LoginAdmin
    },

    // Layout Admin
    {
      path: '/admin',
      component: AdminLayout,
      children: [
        {
          path: 'players',
          component: PlayerManagement
        },
        {
          path: 'weapons',
          component: WeaponManagement
        },
        {
          path: 'account-logs',
          component: AccountLogs
        }
      ]
    }
  ]
})

export default router