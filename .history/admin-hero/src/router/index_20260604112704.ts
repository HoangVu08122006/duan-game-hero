import { createRouter, createWebHistory } from 'vue-router'
import LoginAdmin from '../LoginAdmin.vue'
import App from '../App.vue' // Layout chính
import PlayerManagement from '@/views/PlayerManagement.vue'
import WeaponManagement from '@/views/WeaponManagement.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { 
      path: '/login', 
      name: 'Login', 
      component: LoginAdmin 
    },
    {
      path: '/',
      component: App, // App.vue là layout bao bên ngoài
      redirect: '/players', 
      children: [
        { path: 'players', name: 'players', component: PlayerManagement },
        { path: 'weapons', name: 'weapons', component: WeaponManagement },
        // ... các trang khác
      ]
    }
  ]
})