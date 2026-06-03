<template>
  <FisioLayout>

    <!-- Header -->
    <div class="dash-header">
      <div>
        <h2 class="page-title">Dashboard</h2>
        <p class="page-sub">Bienvenido, <strong>{{ userName }}</strong> — {{ weekLabel }}</p>
      </div>
      <button class="btn-add" @click="showModal = true">+ Nueva Sesión</button>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Mis Citas Hoy</span>
          <span class="stat-value">{{ misCitasHoy }}</span>
        </div>
        <div class="stat-icon green">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Mis Pacientes</span>
          <span class="stat-value">{{ misPacientes }}</span>
        </div>
        <div class="stat-icon blue">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
          </svg>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Completadas</span>
          <span class="stat-value">{{ citasCompletadas }}</span>
        </div>
        <div class="stat-icon green">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
        </div>
      </div>

      <div class="stat-card stat-warn">
        <div class="stat-info">
          <span class="stat-label">Pendientes</span>
          <span class="stat-value">{{ citasPendientes }}</span>
        </div>
        <div class="stat-icon yellow">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Calendario Semanal -->
    <div class="cal-card">
      <div class="cal-header">
        <div>
          <h3 class="cal-title">Agenda Semanal</h3>
          <p class="cal-sub">{{ weekLabel }}</p>
        </div>
        <div class="cal-nav">
          <button class="nav-btn" @click="weekOffset--">&#8249; Anterior</button>
          <button class="nav-btn today-btn" :class="{ active: weekOffset === 0 }" @click="weekOffset = 0">Hoy</button>
          <button class="nav-btn" @click="weekOffset++">Siguiente &#8250;</button>
        </div>
      </div>

      <div v-if="loading" class="cal-empty">
        <p>Cargando citas...</p>
      </div>

      <template v-else>
        <div class="cal-scroll">
          <table class="cal-table">
            <thead>
              <tr>
                <th class="time-col"></th>
                <th
                  v-for="d in weekDays"
                  :key="d.date"
                  class="day-col"
                  :class="{ 'today-col': d.isToday }"
                >
                  {{ d.label }}<span v-if="d.isToday" class="today-dot">&#9679;</span>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="slot in slots" :key="slot">
                <td class="time-cell">{{ slot }}</td>
                <td
                  v-for="d in weekDays"
                  :key="d.date"
                  class="slot-cell"
                  :class="{ 'today-slot': d.isToday }"
                >
                  <div
                    v-for="cita in citasEnSlot(d.date, slot)"
                    :key="cita.cita_id"
                    class="appt"
                    :class="colorMap[cita.estado] || 'appt-blue'"
                  >
                    <span class="appt-name">{{ nombrePaciente(cita.paciente_id) }}</span>
                    <span class="appt-info">{{ (cita.fecha_hora || '').slice(11,16) }} &middot; {{ estadoLabel[cita.estado] || cita.estado }}</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="citasEnSemana === 0" class="cal-empty">
          <span class="cal-empty-icon">📅</span>
          <p>No hay citas para esta semana</p>
        </div>

        <div class="cal-legend">
          <span class="legend-item"><span class="legend-dot appt-blue"></span> Programada</span>
          <span class="legend-item"><span class="legend-dot appt-green"></span> Completada</span>
          <span class="legend-item"><span class="legend-dot appt-yellow"></span> Reprogramada</span>
          <span class="legend-item"><span class="legend-dot appt-red"></span> Cancelada</span>
        </div>
      </template>
    </div>

    <!-- Modal sesión -->
    <Teleport to="body">
      <div class="modal-overlay" v-if="showModal" @click.self="showModal = false">
        <div class="modal">
          <div class="modal-header">
            <h3>Registrar Nueva Sesión</h3>
            <button class="modal-close" @click="showModal = false">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <form class="modal-form" @submit.prevent="guardarSesion">
            <div class="form-group">
              <label>Paciente *</label>
              <select v-model="sesionForm.paciente_id" required>
                <option value="">-- Seleccione un paciente --</option>
                <option v-for="p in misPacientesLista" :key="p.paciente_id" :value="p.paciente_id">
                  {{ p.nombre }} {{ p.apellido }}
                </option>
              </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Fecha *</label>
                <input v-model="sesionForm.fecha" type="date" required />
              </div>
              <div class="form-group">
                <label>Hora *</label>
                <input v-model="sesionForm.hora" type="time" required />
              </div>
            </div>
            <div class="form-group">
              <label>Tipo de Sesión</label>
              <select v-model="sesionForm.tipo">
                <option value="terapia">Sesión de Terapia</option>
                <option value="evaluacion">Evaluación</option>
                <option value="seguimiento">Seguimiento</option>
              </select>
            </div>
            <div class="form-group">
              <label>Notas</label>
              <textarea v-model="sesionForm.notas" rows="3" placeholder="Observaciones y avances..."></textarea>
            </div>
            <div class="modal-actions">
              <button type="submit" class="btn-guardar" :disabled="guardando">
                {{ guardando ? 'Guardando...' : 'Guardar Sesión' }}
              </button>
              <button type="button" class="btn-cancelar" @click="showModal = false">Cancelar</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

  </FisioLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import FisioLayout from '@/components/FisioLayout.vue'
import { citaService, fisioService, getUser, saveUser } from '@/services/api'
import axios from 'axios'

const loading    = ref(true)
const showModal  = ref(false)
const guardando  = ref(false)
const weekOffset = ref(0)

const misCitas          = ref([])
const misPacientesLista = ref([])

const currentUser = computed(() => getUser())
const userName    = computed(() => currentUser.value?.nombre ?? 'Fisioterapeuta')
const fisioId     = computed(() => currentUser.value?.fisioterapeuta_id ?? null)

const sesionForm = ref({ paciente_id: '', fecha: '', hora: '', tipo: 'terapia', notas: '' })

// ── Stats ─────────────────────────────────────────────────────────────────────
const misPacientes     = computed(() => misPacientesLista.value.length)
const misCitasHoy      = computed(() => {
  const today = new Date().toISOString().slice(0, 10)
  return misCitas.value.filter(c => (c.fecha_hora ?? '').startsWith(today)).length
})
const citasCompletadas = computed(() => misCitas.value.filter(c => c.estado === 'atendida').length)
const citasPendientes  = computed(() => misCitas.value.filter(c => c.estado === 'programada').length)

// ── Calendario semanal ────────────────────────────────────────────────────────
const DAY_NAMES = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']
const MESES     = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic']

const slots = [
  '08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30',
  '12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30',
  '16:00','16:30','17:00','17:30','18:00','18:30','19:00','19:30',
  '20:00',
]

const colorMap = {
  programada:   'appt-blue',
  atendida:     'appt-green',
  cancelada:    'appt-red',
  reprogramada: 'appt-yellow',
}
const estadoLabel = {
  programada:   'Programada',
  atendida:     'Completada',
  cancelada:    'Cancelada',
  reprogramada: 'Reprogramada',
}

function getMonday(offset) {
  const d   = new Date()
  const day = d.getDay()
  const mon = new Date(d)
  mon.setDate(d.getDate() + (day === 0 ? -6 : 1 - day) + offset * 7)
  mon.setHours(0, 0, 0, 0)
  return mon
}

const weekDays = computed(() => {
  const mon   = getMonday(weekOffset.value)
  const today = new Date().toISOString().slice(0, 10)
  return DAY_NAMES.map((name, i) => {
    const d   = new Date(mon)
    d.setDate(mon.getDate() + i)
    const iso = d.toISOString().slice(0, 10)
    return { label: `${name} ${d.getDate()}`, date: iso, isToday: iso === today }
  })
})

const weekLabel = computed(() => {
  const days  = weekDays.value
  const first = days[0].date
  const last  = days[6].date
  const fmt   = iso => {
    const [, m, d] = iso.split('-')
    return `${parseInt(d)} ${MESES[parseInt(m) - 1]}`
  }
  const year = last.slice(0, 4)
  return `Semana del ${fmt(first)} al ${fmt(last)} de ${year}`
})

const agendaSemanal = computed(() => {
  const agenda = {}
  for (const d of weekDays.value) agenda[d.date] = {}

  for (const cita of misCitas.value) {
    const fh = cita.fecha_hora
    if (!fh) continue
    const date = fh.slice(0, 10)
    if (!(date in agenda)) continue
    const h    = parseInt(fh.slice(11, 13))
    const m    = parseInt(fh.slice(14, 16))
    const slot = String(h).padStart(2, '0') + ':' + (m < 30 ? '00' : '30')
    if (!agenda[date][slot]) agenda[date][slot] = []
    agenda[date][slot].push(cita)
  }
  return agenda
})

const citasEnSemana = computed(() =>
  weekDays.value.reduce((sum, d) =>
    sum + Object.values(agendaSemanal.value[d.date] || {}).reduce((s, arr) => s + arr.length, 0), 0)
)

function citasEnSlot(date, slot) {
  return agendaSemanal.value[date]?.[slot] ?? []
}

function nombrePaciente(id) {
  const p = misPacientesLista.value.find(p => p.paciente_id === id)
  return p ? `${p.nombre} ${p.apellido}` : `Paciente #${id}`
}

// ── Acciones ──────────────────────────────────────────────────────────────────
async function guardarSesion() {
  guardando.value = true
  try {
    await citaService.create({
      paciente_id:       sesionForm.value.paciente_id,
      fisioterapeuta_id: fisioId.value,
      fecha_hora:        `${sesionForm.value.fecha} ${sesionForm.value.hora}:00`,
      motivo:            `${sesionForm.value.tipo}: ${sesionForm.value.notas}`,
    })
    await cargar()
    showModal.value  = false
    sesionForm.value = { paciente_id: '', fecha: '', hora: '', tipo: 'terapia', notas: '' }
  } catch {} finally {
    guardando.value = false
  }
}

async function cargar() {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    if (token) {
      try {
        const meRes = await axios.get('/api/me', {
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
        })
        saveUser(meRes.data, token)
      } catch {}
    }
    const [cr, pr] = await Promise.allSettled([
      fisioService.misCitas(),
      fisioService.misPacientes(),
    ])
    if (cr.status === 'fulfilled') misCitas.value          = cr.value.data
    if (pr.status === 'fulfilled') misPacientesLista.value = pr.value.data
  } finally {
    loading.value = false
  }
}

onMounted(cargar)
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

/* Header */
.dash-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; }
.page-title  { color: #ffffff; font-size: 1.4rem; font-weight: 700; margin-bottom: 0.2rem; }
.page-sub    { color: #6b7280; font-size: 0.82rem; }
.page-sub strong { color: #4ade80; }

.btn-add { background: #074434; color: #ffffff; border: none; border-radius: 8px; padding: 0.55rem 1rem; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: background 0.15s; white-space: nowrap; }
.btn-add:hover { background: #0a5c46; }

/* Stats */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.9rem; margin-bottom: 1.25rem; }

.stat-card { background: #111111; border: 1px solid #1c1c1c; border-radius: 10px; padding: 1.1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; }
.stat-card.stat-warn { background: #1a1408; border-color: #2e2008; }

.stat-info  { display: flex; flex-direction: column; gap: 0.5rem; }
.stat-label { color: #6b7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.stat-value { color: #ffffff; font-size: 2.4rem; font-weight: 700; line-height: 1; }

.stat-icon { width: 44px; height: 44px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-icon.green  { background: rgba(74,222,128,0.12); color: #4ade80; }
.stat-icon.blue   { background: rgba(56,189,248,0.12); color: #38bdf8; }
.stat-icon.yellow { background: rgba(251,191,36,0.12); color: #fbbf24; }

/* ── Calendario ──────────────────────────────────────────────────────────────── */
.cal-card { background: #111111; border: 1px solid #1c1c1c; border-radius: 10px; overflow: hidden; }

.cal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid #1c1c1c; }
.cal-title  { color: #ffffff; font-size: 0.95rem; font-weight: 600; margin: 0 0 0.15rem; }
.cal-sub    { color: #6b7280; font-size: 0.78rem; margin: 0; }

.cal-nav { display: flex; gap: 0.5rem; }
.nav-btn { background: #1c1c1c; border: 1px solid #2a2a2a; color: #9ca3af; padding: 0.35rem 0.8rem; border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer; transition: background 0.15s, color 0.15s; }
.nav-btn:hover { background: #2a2a2a; color: #d1d5db; }
.nav-btn.today-btn.active { background: rgba(74,222,128,0.12); color: #4ade80; border-color: rgba(74,222,128,0.3); }

.cal-scroll { overflow-x: auto; }

.cal-table { width: 100%; border-collapse: collapse; min-width: 700px; font-size: 0.82rem; }

.cal-table thead tr { border-bottom: 1px solid #1c1c1c; }
.cal-table thead th { padding: 0.6rem 0.4rem; color: #9ca3af; font-size: 0.75rem; font-weight: 600; text-align: center; border-right: 1px solid #161616; }
.cal-table thead th:last-child { border-right: none; }

.time-col { width: 52px; }
.day-col  { min-width: 110px; }
.today-col { color: #4ade80 !important; background: rgba(74,222,128,0.04); }
.today-dot { margin-left: 3px; font-size: 7px; vertical-align: middle; }

.cal-table tbody tr { border-bottom: 1px solid #0f0f0f; }
.cal-table tbody tr:hover td { background: rgba(255,255,255,0.015); }

.time-cell { padding: 3px 6px; color: #4b5563; font-size: 0.7rem; text-align: center; border-right: 1px solid #1c1c1c; white-space: nowrap; vertical-align: top; }

.slot-cell { padding: 2px 3px; border-right: 1px solid #0f0f0f; vertical-align: top; min-height: 26px; }
.slot-cell:last-child { border-right: none; }
.today-slot { background: rgba(74,222,128,0.02); }

/* Cita card */
.appt { display: flex; flex-direction: column; padding: 3px 6px; border-radius: 4px; margin-bottom: 2px; border-left: 3px solid; cursor: pointer; transition: opacity 0.15s; }
.appt:hover { opacity: 0.8; }

.appt-blue   { background: #1e3a8a; border-color: #3b82f6; }
.appt-green  { background: #14532d; border-color: #22c55e; }
.appt-red    { background: #7f1d1d; border-color: #dc2626; }
.appt-yellow { background: #78350f; border-color: #f59e0b; }

.appt-name { font-size: 0.7rem; font-weight: 600; color: #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px; }
.appt-info { font-size: 0.62rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Vacío */
.cal-empty { text-align: center; padding: 2.5rem 1rem; color: #4b5563; }
.cal-empty-icon { font-size: 2rem; display: block; margin-bottom: 0.5rem; }
.cal-empty p { margin: 0; font-size: 0.875rem; }

/* Leyenda */
.cal-legend { display: flex; gap: 1.2rem; flex-wrap: wrap; padding: 0.75rem 1.25rem; border-top: 1px solid #1c1c1c; }
.legend-item { display: flex; align-items: center; gap: 0.4rem; font-size: 0.75rem; color: #6b7280; }
.legend-dot { display: inline-block; width: 12px; height: 12px; border-radius: 2px; border-left: 3px solid; flex-shrink: 0; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal { background: #111111; border: 1px solid #1c1c1c; border-radius: 12px; padding: 1.5rem; width: 100%; max-width: 460px; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.modal-header h3 { color: #ffffff; font-size: 1rem; font-weight: 700; }
.modal-close { background: none; border: none; color: #6b7280; cursor: pointer; padding: 0.25rem; border-radius: 5px; display: flex; transition: color 0.15s; }
.modal-close:hover { color: #d1d5db; }
.modal-form { display: flex; flex-direction: column; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.form-group label { color: #9ca3af; font-size: 0.8rem; font-weight: 600; }
.form-group input, .form-group select, .form-group textarea { background: #0d0d0d; border: 1px solid #1c1c1c; border-radius: 7px; padding: 0.6rem 0.75rem; color: #d1d5db; font-size: 0.875rem; outline: none; width: 100%; transition: border-color 0.15s; font-family: inherit; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #074434; box-shadow: 0 0 0 3px rgba(7,68,52,0.2); }
.form-group textarea { resize: none; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.modal-actions { display: flex; gap: 0.6rem; margin-top: 0.25rem; }
.btn-guardar { flex: 1; background: #074434; color: #ffffff; border: none; border-radius: 7px; padding: 0.65rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: background 0.15s; }
.btn-guardar:hover:not(:disabled) { background: #0a5c46; }
.btn-guardar:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-cancelar { flex: 1; background: #1c1c1c; color: #9ca3af; border: 1px solid #2a2a2a; border-radius: 7px; padding: 0.65rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: background 0.15s, color 0.15s; }
.btn-cancelar:hover { background: #2a2a2a; color: #d1d5db; }
</style>
