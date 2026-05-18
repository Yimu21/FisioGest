import { createRouter, createWebHistory } from 'vue-router'
import Login from '../components/Login.vue'
import Dashboard from '../components/Dashboard.vue'
import Inventario from '../components/Inventario.vue'
import Fisioterapeutas from '../components/Fisioterapeutas.vue'

const routes = [
  { path: '/', component: Login },
  { path: '/dashboard', component: Dashboard },
  { path: '/inventario', component: Inventario },
  { path: '/fisioterapeutas', component: Fisioterapeutas },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

export default createRouter({
  history: createWebHistory(),
  routes,
})
