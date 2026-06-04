import { createRouter, createWebHistory } from 'vue-router'
import { isAuthenticated, getUserRole, getUser, clearUser } from '@/services/api'

import Login            from '../components/Login.vue'
import Dashboard        from '../components/Dashboard.vue'
import Citas            from '../components/Citas.vue'
import Pacientes        from '../components/Pacientes.vue'
import Inventario       from '../components/Inventario.vue'
import Fisioterapeutas  from '../components/Fisioterapeutas.vue'
import Configuracion    from '../components/Configuracion.vue'

import FisioDashboard   from '../components/FisioDashboard.vue'
import FisioPacientes   from '../components/FisioPacientes.vue'
import FisioAsignaciones from '../components/FisioAsignaciones.vue'
import FisioCitas       from '../components/FisioCitas.vue'
import FisioAgenda          from '../components/FisioAgenda.vue'
import FisioConfiguracion   from '../components/FisioConfiguracion.vue'

const routes = [
  // ── Pública ──────────────────────────────────────────────────────────────
  { path: '/', component: Login },

  // ── Admin ─────────────────────────────────────────────────────────────────
  { path: '/dashboard',       component: Dashboard,       meta: { requiresAuth: true, role: 'admin' } },
  { path: '/citas',           component: Citas,           meta: { requiresAuth: true, role: 'admin' } },
  { path: '/pacientes',       component: Pacientes,       meta: { requiresAuth: true, role: 'admin' } },
  { path: '/inventario',      component: Inventario,      meta: { requiresAuth: true, role: 'admin' } },
  { path: '/fisioterapeutas', component: Fisioterapeutas, meta: { requiresAuth: true, role: 'admin' } },
  { path: '/configuracion',   component: Configuracion,   meta: { requiresAuth: true, role: 'admin' } },

  // ── Fisioterapeuta ────────────────────────────────────────────────────────
  { path: '/fisio/dashboard',    component: FisioDashboard,    meta: { requiresAuth: true, role: 'fisioterapeuta' } },
  { path: '/fisio/pacientes',    component: FisioPacientes,    meta: { requiresAuth: true, role: 'fisioterapeuta' } },
  { path: '/fisio/asignaciones', component: FisioAsignaciones, meta: { requiresAuth: true, role: 'fisioterapeuta' } },
  { path: '/fisio/citas',        component: FisioCitas,        meta: { requiresAuth: true, role: 'fisioterapeuta' } },
  { path: '/fisio/agenda',         component: FisioAgenda,         meta: { requiresAuth: true, role: 'fisioterapeuta' } },
  { path: '/fisio/configuracion',  component: FisioConfiguracion,  meta: { requiresAuth: true, role: 'fisioterapeuta' } },

  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// ── Guardia global ────────────────────────────────────────────────────────────
router.beforeEach((to) => {
  const auth = isAuthenticated()
  const role = getUserRole()
  const user = getUser()

  // Si el fisioterapeuta fue desactivado mientras tenía sesión → cerrar sesión
  if (auth && role === 'fisioterapeuta' && user?.activo === false) {
    clearUser()
    return '/'
  }

  // Si la ruta requiere autenticación y no hay token → login
  if (to.meta.requiresAuth && !auth) return '/'

  // Si la ruta requiere un rol específico y no coincide → redirigir a su home
  if (to.meta.role && to.meta.role !== role) {
    if (role === 'admin')            return '/dashboard'
    if (role === 'fisioterapeuta')   return '/fisio/dashboard'
    return '/'
  }

  // Si ya está autenticado y va al login → redirigir a su home
  if (to.path === '/' && auth) {
    if (role === 'admin')            return '/dashboard'
    if (role === 'fisioterapeuta')   return '/fisio/dashboard'
  }
})

export default router
