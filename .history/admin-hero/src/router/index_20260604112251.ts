import { createRouter, createWebHistory } from 'vue-router'
// Sử dụng @/views giúp chỉ định chính xác vị trí file trong thư mục src
import PlayerManagement from '@/views/PlayerManagement.vue'
import WeaponManagement from '@/views/WeaponManagement.vue'
import AccountLogs from '@/views/AccountLogs.vue'
import LoginAdmin from '../LoginAdmin.vue';
import App from '../App.vue';
const routes = [
  { path: '/login', component: LoginAdmin },
  { path: '/dashboard', name: 'dashboard', component: App } 
];
const routes = [
  { path: '/login', component: () => import('../LoginAdmin.vue') }, // Trang đăng nhập
  { 
    path: '/', 
    component: () => import('@/layouts/AdminLayout.vue'), // Trang sau khi đăng nhập
    children: [
        { path: 'players', name: 'players', component: PlayerManagement },
        { path: 'weapons', name: 'weapons', component: WeaponManagement },
        { path: 'account-logs', name: 'AccountLogs', component: AccountLogs }
      ]
    }
  ]
})

export default router