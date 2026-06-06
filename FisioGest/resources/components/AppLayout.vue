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

        <div class="nav-group">
          <div class="nav-item" @click="toggle('progresos')">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
              <line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
            Progresos
            <svg class="chevron" :class="{ open: open.progresos }" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </div>
          <div class="nav-sub" v-show="open.progresos">
            <span class="sub-item">Ver progresos</span>
            <span class="sub-item">Nuevo progreso</span>
          </div>
        </div>

        <div class="nav-item">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
          </svg>
          Configuración
        </div>

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
          <button class="icon-btn" title="Notificaciones">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
          </button>
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
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { authService, clearUser, getUser } from '@/services/api'

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
</style>
