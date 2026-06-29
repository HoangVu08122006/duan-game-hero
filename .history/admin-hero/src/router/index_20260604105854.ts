import { createRouter, createWebHistory } from 'vue-router'
import LoginAdmin from '@/views/LoginAdmin.vue' // Import trang login của bạn
import PlayerManagement from '@/views/PlayerManagement.vue'
import WeaponManagement from '@/views/WeaponManagement.vue'
import AccountLogs from '@/views/AccountLogs.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: LoginAdmin
    },
    {
      path: '/',
      redirect: '/players' // Mặc định chuyển về players, nhưng sẽ bị chặn bởi middleware bên dưới
    },
    {
      path: '/players',
      name: 'players',
      component: PlayerManagement,
      meta: { requiresAuth: true } // Đánh dấu cần đăng nhập
    },
    {
      path: '/weapons',
      name: 'weapons',
      component: WeaponManagement,
      meta: { requiresAuth: true }
    },
    {
      path: '/account-logs',
      name: 'AccountLogs',
      component: AccountLogs,
      meta: { requiresAuth: true }
    }
  ]
})

// Middleware kiểm tra đăng nhập
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('admin_token')
  
  if (to.meta.requiresAuth && !token) {
    // Nếu trang yêu cầu đăng nhập mà chưa có token -> đẩy về /login
    next('/login')
  } else if (to.path === '/login' && token) {
    // Nếu đã có token mà cố tình vào lại trang /login -> đẩy về trang chính
    next('/players')
  } else {
    next()
  }
})

export default router