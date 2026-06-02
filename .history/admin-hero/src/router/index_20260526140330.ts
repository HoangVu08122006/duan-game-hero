import { createRouter, createWebHistory } from 'vue-router'
// Sử dụng @/views giúp chỉ định chính xác vị trí file trong thư mục src
import PlayerManagement from '@/views/PlayerManagement.vue'


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/players'
    },
    {
      path: '/players',
      name: 'players',
      component: PlayerManagement
    },
    {
      path: '/weapons',
      name: 'weapons',
      component: WeaponManagement
    }
  ]
})

export default router