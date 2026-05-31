<template>
  <FisioLayout>

    <div class="pag-header">
      <h2 class="page-title">Mis Pacientes</h2>
      <span class="badge-count">{{ totalPacientes }} asignados</span>
    </div>

    <div v-if="loading" class="empty-state">Cargando pacientes...</div>

    <div v-else-if="misPacientes.length === 0" class="empty-state">
      No tienes pacientes asignados aún.
    </div>

    <div v-else class="panel-wrapper">

      <!-- ── Lista de pacientes ── -->
      <div class="list-panel">
        <div
          v-for="p in misPacientes"
          :key="p.paciente_id"
          class="patient-row"
          :class="{ active: selectedId === p.paciente_id }"
          @click="selectedId = p.paciente_id"
        >
          <div class="p-avatar" :style="{ background: avatarColor(p.nombre) }">
            {{ initials(p.nombre, p.apellido) }}
          </div>
          <div class="p-info">
            <span class="p-name">{{ p.nombre }} {{ p.apellido }}</span>
            <span class="p-diag">{{ p.diagnostico ?? (p.genero ? capitalize(p.genero) : 'Sin diagnóstico') }}</span>
          </div>
        </div>
      </div>

      <!-- ── Detalle del paciente ── -->
      <div class="detail-panel">
        <template v-if="selected">

          <!-- Aviso solo lectura -->
          <div class="readonly-notice">
            🔒 Vista de solo lectura — no modificable por fisioterapeuta.
          </div>

          <!-- Header del paciente -->
          <div class="detail-card">
            <div class="detail-top">
              <div class="d-avatar" :style="{ background: avatarColor(selected.nombre) }">
                {{ initials(selected.nombre, selected.apellido) }}
              </div>
              <div class="d-main">
                <div class="d-name-row">
                  <h3 class="d-name">{{ selected.nombre }} {{ selected.apellido }}</h3>
                  <span class="estado-badge active">Activo</span>
                </div>
                <p class="d-sub">Atendida por <strong>{{ userName }}</strong></p>
                <div class="d-fields">
                  <div class="d-field">
                    <span class="df-label">Nombre completo</span>
                    <span class="df-val">{{ selected.nombre }} {{ selected.apellido }}</span>
                  </div>
                  <div class="d-field">
                    <span class="df-label">Género</span>
                    <span class="df-val">{{ capitalize(selected.genero ?? '—') }}</span>
                  </div>
                  <div class="d-field">
                    <span class="df-label">Teléfono</span>
                    <span class="df-val">{{ selected.telefono ?? '—' }}</span>
                  </div>
                  <div class="d-field">
                    <span class="df-label">Nacimiento</span>
                    <span class="df-val">{{ formatFecha(selected.fecha_nacimiento) }}</span>
                  </div>
                  <div class="d-field">
                    <span class="df-label">Fisioterapeuta</span>
                    <span class="df-val green">{{ userName }}</span>
                  </div>
                  <div class="d-field">
                    <span class="df-label">ID Paciente</span>
                    <span class="df-val mono">{{ selected.paciente_id }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Historial de citas del paciente -->
          <div class="history-card">
            <h4 class="section-title">Historial de Citas</h4>
            <div v-if="citasPaciente.length === 0" class="td-empty">Sin citas registradas para este paciente.</div>
            <div v-for="c in citasPaciente" :key="c.cita_id" class="history-row">
              <div class="hr-dot" :class="c.estado"></div>
              <div class="hr-info">
                <span class="hr-fecha">{{ formatFecha(c.fecha_hora) }}</span>
                <span class="hr-motivo">{{ c.motivo || 'Sesión de terapia' }}</span>
              </div>
              <span class="estado-badge sm" :class="c.estado">{{ c.estado }}</span>
            </div>
          </div>

        </template>

        <div v-else class="empty-detail">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="1.5">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
          </svg>
          <p>Selecciona un paciente para ver su detalle</p>
        </div>
      </div>

    </div>

  </FisioLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import FisioLayout from '@/components/FisioLayout.vue'
import { fisioService, getUser } from '@/services/api'

const loading      = ref(true)
const misPacientes = ref([])
const todasCitas   = ref([])
const selectedId   = ref(null)

const currentUser = computed(() => getUser())
const userName    = computed(() => currentUser.value?.nombre ?? 'Fisioterapeuta')

const selected = computed(() => misPacientes.value.find(p => p.paciente_id === selectedId.value) ?? null)

const citasPaciente = computed(() => {
  if (!selected.value) return []
  return todasCitas.value.filter(c => c.paciente_id === selected.value.paciente_id)
})

const totalPacientes = computed(() => misPacientes.value.length)

function initials(nombre = '', apellido = '') {
  return (nombre[0] ?? 'P').toUpperCase() + (apellido[0] ?? '').toUpperCase()
}

function capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : '—' }

const colors = ['#1e3a8a','#4c1d95','#78350f','#14532d','#7f1d1d','#0c4a6e']
function avatarColor(nombre = '') {
  return colors[nombre.charCodeAt(0) % colors.length]
}

function formatFecha(fh) {
  if (!fh) return '—'
  try { return new Date(fh).toLocaleDateString('es-ES', { dateStyle: 'medium' }) } catch { return fh }
}

async function cargar() {
  loading.value = true
  try {
    const [pr, cr] = await Promise.allSettled([
      fisioService.misPacientes(),
      fisioService.misCitas(),
    ])
    if (pr.status === 'fulfilled') {
      misPacientes.value = pr.value.data
      if (misPacientes.value.length) selectedId.value = misPacientes.value[0].paciente_id
    }
    if (cr.status === 'fulfilled') todasCitas.value = cr.value.data
  } finally {
    loading.value = false
  }
}

onMounted(cargar)
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

.pag-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; }
.page-title { color: #ffffff; font-size: 1.4rem; font-weight: 700; }
.badge-count { background: rgba(74,222,128,0.12); color: #4ade80; font-size: 0.72rem; font-weight: 600; padding: 0.2rem 0.65rem; border-radius: 20px; }

.empty-state { color: #4b5563; text-align: center; padding: 3rem; font-size: 0.9rem; }

.panel-wrapper {
  display: flex;
  gap: 1rem;
  height: calc(100vh - 180px);
  overflow: hidden;
}

/* Lista */
.list-panel {
  width: 260px;
  flex-shrink: 0;
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 10px;
  overflow-y: auto;
}

.patient-row {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid #161616;
  cursor: pointer;
  transition: background 0.15s;
}
.patient-row:last-child { border-bottom: none; }
.patient-row:hover { background: rgba(255,255,255,0.03); }
.patient-row.active { background: rgba(7,68,52,0.2); border-left: 3px solid #22c55e; }

.p-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 12px; font-weight: 700; flex-shrink: 0;
}

.p-info { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
.p-name { color: #d1d5db; font-size: 0.82rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.p-diag { color: #52525b; font-size: 0.72rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Detalle */
.detail-panel {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding-right: 0.25rem;
}

.readonly-notice {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 7px;
  padding: 0.6rem 1rem;
  font-size: 0.78rem;
  color: #6b7280;
}

.detail-card {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 10px;
  padding: 1.25rem;
}

.detail-top { display: flex; gap: 1rem; }

.d-avatar {
  width: 64px; height: 64px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 22px; font-weight: 700; flex-shrink: 0;
}

.d-main { flex: 1; min-width: 0; }

.d-name-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem; }
.d-name { color: #ffffff; font-size: 1.15rem; font-weight: 700; }
.d-sub { color: #6b7280; font-size: 0.78rem; margin-bottom: 1rem; }

.d-fields {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
}

.d-field { display: flex; flex-direction: column; gap: 2px; }
.df-label { color: #4b5563; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.4px; }
.df-val { color: #d1d5db; font-size: 0.82rem; font-weight: 500; }
.df-val.green { color: #4ade80; }
.df-val.mono  { font-family: monospace; color: #9ca3af; }

/* Historia de citas */
.history-card {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 10px;
  padding: 1.25rem;
}

.section-title { color: #e4e4e7; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.85rem; }

.history-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0;
  border-bottom: 1px solid #161616;
}
.history-row:last-child { border-bottom: none; }

.hr-dot {
  width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.hr-dot.programada { background: #3b82f6; }
.hr-dot.atendida   { background: #22c55e; }
.hr-dot.cancelada  { background: #ef4444; }

.hr-info { flex: 1; display: flex; flex-direction: column; gap: 1px; }
.hr-fecha  { color: #38bdf8; font-size: 0.78rem; }
.hr-motivo { color: #9ca3af; font-size: 0.75rem; }

/* Estado badges */
.estado-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
.estado-badge.sm { font-size: 0.7rem; padding: 0.15rem 0.5rem; }
.estado-badge.active     { background: rgba(74,222,128,0.12);  color: #4ade80; }
.estado-badge.programada { background: rgba(59,130,246,0.15);  color: #93c5fd; }
.estado-badge.atendida   { background: rgba(74,222,128,0.15);  color: #4ade80; }
.estado-badge.cancelada  { background: rgba(239,68,68,0.15);   color: #f87171; }

.td-empty { color: #4b5563; font-size: 0.82rem; padding: 0.75rem 0; }

.empty-detail {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  gap: 0.75rem;
  color: #374151;
  font-size: 0.9rem;
}
</style>
