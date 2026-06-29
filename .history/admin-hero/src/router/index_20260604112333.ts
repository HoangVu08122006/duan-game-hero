import { createRouter, createWebHistory } from 'vue-router'
import LoginAdmin from '../LoginAdmin.vue'
import PlayerManagement from '@/views/PlayerManagement.vue'
import WeaponManagement from '@/views/WeaponManagement.vue'
import AccountLogs from '@/views/AccountLogs.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/login', name: 'login', component: LoginAdmin },
    {
      path: '/',
      redirect: '/players', // Tự động vào trang quản lý khi vào gốc
      children: [
        { path: 'players', name: 'players', component: PlayerManagement },
        { path: 'weapons', name: 'weapons', component: WeaponManagement },
        { path: 'account-logs', name: 'AccountLogs', component: AccountLogs }
      ]
    }
  ]
})

// Navigation Guard: Bảo vệ route (Chưa đăng nhập thì bắt về login)
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('admin_token')
  if (to.path !== '/login' && !token) {
    next('/login')
  } else {
    next()
  }
})

export default router