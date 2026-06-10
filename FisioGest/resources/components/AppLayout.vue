<template>
  <div class="app-shell">

    <!-- ── Sidebar ── -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.2">
          <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
        </svg>
        <span><span class="brand-fisio">Fisio</span><span class="brand-gest">Gest</span></span>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/dashboard" class="nav-item" active-class="active">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
          Dashboard
        </router-link>

        <router-link to="/citas" class="nav-item" active-class="active">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          Citas
        </router-link>

        <router-link to="/pacientes" class="nav-item" active-class="active">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
          </svg>
          Pacientes
        </router-link>

        <router-link to="/fisioterapeutas" class="nav-item" active-class="active">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
          </svg>
          Fisioterapeutas
        </router-link>

        <router-link to="/inventario" class="nav-item" active-class="active">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
          </svg>
          Inventario
        </router-link>

      

        <router-link to="/configuracion" class="nav-item" active-class="active">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
          </svg>
          Configuración
        </router-link>

       <router-link to="/contactos" class="nav-item" active-class="active">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
          Contactos
        </router-link>
      </nav>

      <button class="logout-btn" @click="logout">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        Cerrar sesión
      </button>
    </aside>

    <!-- ── Right column ── -->
    <div class="right-col">

      <!-- Top bar -->
      <header class="top-bar">
        <div class="topbar-right">

          <!-- Campana de notificaciones -->
          <div class="notif-wrapper" ref="notifRef">
            <button class="icon-btn notif-btn" title="Notificaciones" @click="toggleNotif">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
              <span v-if="noLeidas > 0" class="notif-badge">{{ noLeidas > 9 ? '9+' : noLeidas }}</span>
            </button>

            <!-- Dropdown -->
            <div v-if="showNotif" class="notif-dropdown">
              <div class="notif-header">
                <span class="notif-title">Notificaciones</span>
                <button v-if="noLeidas > 0" class="notif-mark-all" @click="marcarTodas">
                  Marcar todas como leídas
                </button>
              </div>

              <div v-if="notificaciones.length === 0" class="notif-empty">
                No hay notificaciones
              </div>

              <div v-else class="notif-list">
                <div
                  v-for="n in notificaciones"
                  :key="n.notificacion_id"
                  class="notif-item"
                  :class="{ unread: !n.leida }"
                  @click="marcarLeida(n)"
                >
                  <div class="notif-icon-wrap">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="10"/>
                      <polyline points="12 6 12 12 16 14"/>
                    </svg>
                  </div>
                  <div class="notif-body">
                    <p class="notif-item-title">{{ n.titulo }}</p>
                    <p class="notif-item-msg">{{ n.mensaje }}</p>
                    <p class="notif-item-time">{{ formatNotifTime(n.created_at) }}</p>
                  </div>
                  <span v-if="!n.leida" class="notif-dot"></span>
                </div>
              </div>
            </div>
          </div>

          <button class="icon-btn" title="Ayuda">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </button>
          <div class="user-chip">
            <div class="user-avatar">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
            </div>
            <span>{{ userName }}</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main class="page-content">
        <slot />
      </main>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService, clearUser, getUser, getUserRole, notificacionesService, fisioNotificacionesService } from '@/services/api'

const router      = useRouter()
const open        = ref({ progresos: false })
const currentUser = computed(() => getUser())
const userName    = computed(() => currentUser.value?.nombre ?? 'Administrador')

function toggle(key) {
  open.value[key] = !open.value[key]
}

async function logout() {
  try { await authService.logout() } catch {}
  clearUser()
  router.push('/')
}

// ── Notificaciones ────────────────────────────────────────────────────────
const notificaciones = ref([])
const showNotif      = ref(false)
const notifRef       = ref(null)

const noLeidas = computed(() => notificaciones.value.filter(n => !n.leida).length)

// Seleccionar servicio según rol del usuario
const esFisio = computed(() => getUserRole() === 'fisioterapeuta')
const notifSvc = computed(() => esFisio.value ? fisioNotificacionesService : notificacionesService)

async function cargarNotificaciones() {
  try {
    const res = await notifSvc.value.getAll()
    notificaciones.value = res.data
  } catch {}
}

function toggleNotif() {
  showNotif.value = !showNotif.value
  if (showNotif.value) cargarNotificaciones()
}

async function marcarLeida(n) {
  if (n.leida) return
  try {
    await notifSvc.value.marcarLeida(n.notificacion_id)
    n.leida = true
  } catch {}
}

async function marcarTodas() {
  try {
    await notifSvc.value.marcarTodas()
    notificaciones.value.forEach(n => { n.leida = true })
  } catch {}
}

function formatNotifTime(ts) {
  if (!ts) return ''
  try {
    const iso = ts.includes('T') ? ts : ts.replace(' ', 'T') + 'Z'
    return new Date(iso).toLocaleString('es', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
  } catch { return ts }
}

function handleClickOutside(e) {
  if (notifRef.value && !notifRef.value.contains(e.target)) {
    showNotif.value = false
  }
}

onMounted(() => {
  cargarNotificaciones()
  document.addEventListener('click', handleClickOutside)
  // Polling cada 60s para notificaciones nuevas
  const interval = setInterval(cargarNotificaciones, 60000)
  onUnmounted(() => {
    clearInterval(interval)
    document.removeEventListener('click', handleClickOutside)
  })
})
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.app-shell {
  display: flex;
  height: 100vh;
  overflow: hidden;
  background: #0a0a0a;
  font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
}

/* ── Sidebar ── */
.sidebar {
  width: 210px;
  flex-shrink: 0;
  background: #111111;
  border-right: 1px solid #1c1c1c;
  display: flex;
  flex-direction: column;
  padding: 1.25rem 0.75rem;
  gap: 0;
  overflow-y: auto;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0 0.5rem 1.25rem;
  border-bottom: 1px solid #1c1c1c;
  margin-bottom: 0.75rem;
  font-size: 1rem;
  font-weight: 700;
}
.brand-fisio { color: #4ade80; }
.brand-gest  { color: #ffffff; }

/* Nav */
.sidebar-nav {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.52rem 0.65rem;
  border-radius: 7px;
  color: #8a8f94;
  text-decoration: none;
  font-size: 0.82rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  user-select: none;
  position: relative;
}

.nav-item:hover {
  background: rgba(255,255,255,0.05);
  color: #e0e0e0;
}

.nav-item.active {
  background: rgba(7, 68, 52, 0.35);
  color: #ffffff;
}

.nav-item.active svg { color: #4ade80; }

.chevron {
  margin-left: auto;
  transition: transform 0.2s;
}
.chevron.open { transform: rotate(180deg); }

.nav-group { display: flex; flex-direction: column; }

.nav-sub {
  display: flex;
  flex-direction: column;
  gap: 1px;
  padding-left: 2.1rem;
  margin-bottom: 2px;
}

.sub-item {
  font-size: 0.78rem;
  color: #6b7280;
  padding: 0.35rem 0.5rem;
  border-radius: 5px;
  cursor: pointer;
  transition: color 0.15s, background 0.15s;
}
.sub-item:hover { color: #d1d5db; background: rgba(255,255,255,0.04); }

/* Logout */
.logout-btn {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  background: none;
  border: 1px solid #1c1c1c;
  border-radius: 7px;
  color: #6b7280;
  padding: 0.52rem 0.65rem;
  font-size: 0.82rem;
  cursor: pointer;
  transition: background 0.15s, color 0.15s, border-color 0.15s;
  width: 100%;
  margin-top: 0.75rem;
}
.logout-btn:hover {
  background: rgba(220,38,38,0.1);
  color: #f87171;
  border-color: rgba(220,38,38,0.3);
}

/* ── Right column ── */
.right-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Top bar */
.top-bar {
  height: 52px;
  background: #111111;
  border-bottom: 1px solid #1c1c1c;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 0 1.5rem;
  flex-shrink: 0;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.icon-btn {
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 0.4rem;
  border-radius: 6px;
  display: flex;
  align-items: center;
  transition: background 0.15s, color 0.15s;
}
.icon-btn:hover { background: rgba(255,255,255,0.06); color: #d1d5db; }

.user-chip {
  display: flex;
  align-items: center;
  gap: 0.45rem;
  background: rgba(255,255,255,0.05);
  border: 1px solid #1c1c1c;
  border-radius: 8px;
  padding: 0.35rem 0.65rem;
  color: #d1d5db;
  font-size: 0.82rem;
  font-weight: 500;
  cursor: pointer;
  margin-left: 0.25rem;
  transition: background 0.15s;
}
.user-chip:hover { background: rgba(255,255,255,0.08); }

.user-avatar {
  width: 26px;
  height: 26px;
  background: rgba(7,68,52,0.4);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4ade80;
}

/* Page content */
.page-content {
  flex: 1;
  overflow-y: auto;
  padding: 1.75rem;
}

/* ── Notificaciones ── */
.notif-wrapper {
  position: relative;
}

.notif-btn {
  position: relative;
}

.notif-badge {
  position: absolute;
  top: 1px;
  right: 1px;
  background: #ef4444;
  color: #fff;
  font-size: 0.58rem;
  font-weight: 700;
  min-width: 16px;
  height: 16px;
  border-radius: 99px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 3px;
  line-height: 1;
  pointer-events: none;
}

.notif-dropdown {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: 340px;
  background: #141414;
  border: 1px solid #222;
  border-radius: 12px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.6);
  z-index: 500;
  overflow: hidden;
  animation: fadeDown 0.15s ease;
}

@keyframes fadeDown {
  from { opacity: 0; transform: translateY(-6px); }
  to   { opacity: 1; transform: none; }
}

.notif-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid #1c1c1c;
}

.notif-title {
  font-size: 0.85rem;
  font-weight: 700;
  color: #e4e4e7;
}

.notif-mark-all {
  background: none;
  border: none;
  color: #4ade80;
  font-size: 0.72rem;
  cursor: pointer;
  padding: 0;
  font-family: inherit;
}
.notif-mark-all:hover { text-decoration: underline; }

.notif-empty {
  padding: 2rem;
  text-align: center;
  color: #4b5563;
  font-size: 0.82rem;
}

.notif-list {
  max-height: 360px;
  overflow-y: auto;
}

.notif-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid #1a1a1a;
  cursor: pointer;
  transition: background 0.12s;
  position: relative;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: rgba(255,255,255,0.03); }
.notif-item.unread { background: rgba(74,222,128,0.04); }

.notif-icon-wrap {
  flex-shrink: 0;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: rgba(74,222,128,0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4ade80;
  margin-top: 1px;
}

.notif-body { flex: 1; min-width: 0; }

.notif-item-title {
  font-size: 0.8rem;
  font-weight: 600;
  color: #e4e4e7;
  margin-bottom: 2px;
}

.notif-item-msg {
  font-size: 0.75rem;
  color: #9ca3af;
  line-height: 1.4;
  margin-bottom: 4px;
}

.notif-item-time {
  font-size: 0.68rem;
  color: #4b5563;
}

.notif-dot {
  flex-shrink: 0;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #4ade80;
  margin-top: 5px;
}
</style>
