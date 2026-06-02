import { createRouter, createWebHistory } from 'vue-router'
// Sử dụng @/views giúp chỉ định chính xác vị trí file trong thư mục src
import PlayerManagement from '@/views/PlayerManagement.vue'
import GiftcodeManagement from '@/views/GiftcodeManagement.vue'

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

  ]
})

export default router