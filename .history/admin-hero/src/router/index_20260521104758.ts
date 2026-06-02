import { createRouter, createWebHistory } from 'vue-router'
import PlayerManagement from '../views/PlayerManagement.vue'
import GiftcodeManagement from '../views/GiftcodeManagement.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/players' // Mặc định vừa vào trang web sẽ nhảy thẳng vào quản lý player
    },
    {
      path: '/players',
      name: 'players',
      component: PlayerManagement
    },
    {
      path: '/giftcodes',
      name: 'giftcodes',
      component: GiftcodeManagement
    }
  ]
})

export default router