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
                  <template v-for="item in citasEnSlot(d.date, slot)" :key="item._tipo === 'cita' ? 'c'+item.cita_id : 'e'+item.evento_id">
                    <!-- Cita (clickeable para cambiar estado) -->
                    <div
                      v-if="item._tipo === 'cita'"
                      class="appt appt-clickable"
                      :class="colorMap[item.estado] || 'appt-blue'"
                      @click="abrirModalEstado(item)"
                      :title="'Click para cambiar estado'"
                    >
                      <span class="appt-name">{{ nombrePaciente(item.paciente_id) }}</span>
                      <span class="appt-info">{{ (item.fecha_hora || '').slice(11,16) }} &middot; {{ estadoLabel[item.estado] || item.estado }}</span>
                    </div>
                    <!-- Evento personal -->
                    <div v-else class="appt appt-evento">
                      <span class="appt-name">{{ item.titulo }}</span>
                      <span class="appt-info">{{ item.hora_inicio ? item.hora_inicio.slice(0,5) : '' }} &middot; {{ item.tipo }}</span>
                    </div>
                  </template>
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
          <span class="legend-item"><span class="legend-dot appt-evento"></span> Evento personal</span>
        </div>
      </template>
    </div>

    <!-- Modal: Cambiar estado de cita -->
    <Teleport to="body">
      <div class="modal-overlay" v-if="showEstadoModal" @click.self="cerrarModalEstado">
        <div class="modal modal-sm">
          <div class="modal-header">
            <h3>Actualizar Estado de Cita</h3>
            <button class="modal-close" @click="cerrarModalEstado">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <div class="estado-info" v-if="citaSeleccionada">
            <div class="info-row">
              <span class="info-label">Paciente</span>
              <span class="info-val">{{ nombrePaciente(citaSeleccionada.paciente_id) }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Fecha y hora</span>
              <span class="info-val info-fecha">{{ formatFecha(citaSeleccionada.fecha_hora) }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Estado actual</span>
              <span class="estado-badge" :class="citaSeleccionada.estado">
                {{ estadoLabel[citaSeleccionada.estado] || citaSeleccionada.estado }}
              </span>
            </div>
          </div>

          <div class="estado-opciones">
            <button
              v-for="op in opcionesEstado.filter(o => o.value !== citaSeleccionada?.estado)"
              :key="op.value"
              class="btn-estado-op"
              :class="[op.cls, { activo: nuevoEstado === op.value }]"
              @click="nuevoEstado = op.value"
            >
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline v-if="op.value === 'atendida'" points="20 6 9 17 4 12"/>
                <line v-else-if="op.value === 'cancelada'" x1="18" y1="6" x2="6" y2="18"/>
                <line v-if="op.value === 'cancelada'" x1="6" y1="6" x2="18" y2="18"/>
                <circle v-if="op.value === 'programada'" cx="12" cy="12" r="10"/>
              </svg>
              {{ op.label }}
            </button>
          </div>

          <p v-if="errorEstado" class="error-msg">{{ errorEstado }}</p>

          <div class="modal-actions" style="margin-top:1rem;">
            <button class="btn-guardar" @click="guardarEstado" :disabled="actualizando || !nuevoEstado">
              {{ actualizando ? 'Guardando...' : 'Confirmar cambio' }}
            </button>
            <button class="btn-cancelar" @click="cerrarModalEstado">Cancelar</button>
          </div>

          <p class="notif-hint">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            El administrador recibirá una notificación con este cambio.
          </p>
        </div>
      </div>
    </Teleport>

    <!-- Toast -->
    <Teleport to="body">
      <Transition name="toast-slide">
        <div v-if="toast.visible" class="fisio-toast" :class="toast.type">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline v-if="toast.type === 'ok'" points="20 6 9 17 4 12"/>
            <circle v-else cx="12" cy="12" r="10"/>
          </svg>
          <span>{{ toast.msg }}</span>
        </div>
      </Transition>
    </Teleport>

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
import { citaService, fisioService, agendaService, getUser, saveUser } from '@/services/api'
import axios from 'axios'

const loading    = ref(true)
const showModal  = ref(false)
const guardando  = ref(false)
const weekOffset = ref(0)

const misCitas          = ref([])
const misPacientesLista = ref([])
const misEventos        = ref([])

const currentUser = computed(() => getUser())
const userName    = computed(() => currentUser.value?.nombre ?? 'Fisioterapeuta')
const fisioId     = computed(() => currentUser.value?.fisioterapeuta_id ?? null)

const sesionForm = ref({ paciente_id: '', fecha: '', hora: '', tipo: 'terapia', notas: '' })

// ── Stats ─────────────────────────────────────────────────────────────────────
const misPacientes     = computed(() => misPacientesLista.value.length)
const misCitasHoy      = computed(() => {
  const today = localDateISO()
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
  const mon = new Date(d.getFullYear(), d.getMonth(), d.getDate())
  mon.setDate(mon.getDate() + (day === 0 ? -6 : 1 - day) + offset * 7)
  return mon
}

function localDateISO(d = new Date()) {
  const y  = d.getFullYear()
  const m  = String(d.getMonth() + 1).padStart(2, '0')
  const dd = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${dd}`
}

const weekDays = computed(() => {
  const mon   = getMonday(weekOffset.value)
  const today = localDateISO()
  return DAY_NAMES.map((name, i) => {
    const d   = new Date(mon)
    d.setDate(mon.getDate() + i)
    const iso = localDateISO(d)
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
    agenda[date][slot].push({ _tipo: 'cita', ...cita })
  }

  for (const ev of misEventos.value) {
    if (!ev.fecha) continue
    const date = ev.fecha.slice(0, 10)
    if (!(date in agenda)) continue
    const hora = ev.hora_inicio ?? '00:00'
    const h    = parseInt(hora.slice(0, 2))
    const m    = parseInt(hora.slice(3, 5))
    const slot = String(h).padStart(2, '0') + ':' + (m < 30 ? '00' : '30')
    if (!agenda[date][slot]) agenda[date][slot] = []
    agenda[date][slot].push({ _tipo: 'evento', ...ev })
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

// ── Modal cambio de estado ────────────────────────────────────────────────────
const showEstadoModal  = ref(false)
const citaSeleccionada = ref(null)
const nuevoEstado      = ref('')
const actualizando     = ref(false)
const errorEstado      = ref('')
const toast            = ref({ visible: false, type: 'ok', msg: '' })
let   toastTimer       = null

const opcionesEstado = [
  { value: 'atendida',   label: 'Marcar como atendida',  cls: 'op-green'  },
  { value: 'cancelada',  label: 'Cancelar cita',          cls: 'op-red'    },
  { value: 'programada', label: 'Restablecer programada', cls: 'op-blue'   },
]

function formatFecha(fechaHora) {
  if (!fechaHora) return '—'
  return new Date(fechaHora).toLocaleString('es-ES', { dateStyle: 'medium', timeStyle: 'short' })
}

function abrirModalEstado(cita) {
  citaSeleccionada.value = cita
  nuevoEstado.value      = ''   // sin preselección; el estado actual queda oculto en las opciones
  errorEstado.value      = ''
  showEstadoModal.value  = true
}

function cerrarModalEstado() {
  showEstadoModal.value  = false
  citaSeleccionada.value = null
  errorEstado.value      = ''
}

function mostrarToast(type, msg) {
  clearTimeout(toastTimer)
  toast.value = { visible: true, type, msg }
  toastTimer  = setTimeout(() => { toast.value.visible = false }, 3500)
}

async function guardarEstado() {
  if (!citaSeleccionada.value || nuevoEstado.value === citaSeleccionada.value.estado) return
  actualizando.value = true
  errorEstado.value  = ''
  try {
    await axios.patch(
      `/api/fisio/citas/${citaSeleccionada.value.cita_id}/estado`,
      { estado: nuevoEstado.value },
      { headers: { Authorization: `Bearer ${localStorage.getItem('token')}`, Accept: 'application/json' } }
    )
    await cargar()
    cerrarModalEstado()
    mostrarToast('ok', `Cita marcada como "${estadoLabel[nuevoEstado.value] || nuevoEstado.value}". El administrador fue notificado.`)
  } catch (e) {
    errorEstado.value = e?.response?.data?.message || 'Error al actualizar. Intenta de nuevo.'
  } finally {
    actualizando.value = false
  }
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
    const [cr, pr, er] = await Promise.allSettled([
      fisioService.misCitas(),
      fisioService.misPacientes(),
      agendaService.getEventos(),
    ])
    if (cr.status === 'fulfilled') misCitas.value          = cr.value.data
    if (pr.status === 'fulfilled') misPacientesLista.value = pr.value.data
    if (er.status === 'fulfilled') misEventos.value        = er.value.data
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
.appt { display: flex; flex-direction: column; padding: 3px 6px; border-radius: 4px; margin-bottom: 2px; border-left: 3px solid; }
.appt-clickable { cursor: pointer; transition: filter 0.15s, transform 0.1s; }
.appt-clickable:hover { filter: brightness(1.2); transform: scale(1.02); }

.appt-blue   { background: #1e3a8a; border-color: #3b82f6; }
.appt-green  { background: #14532d; border-color: #22c55e; }
.appt-red    { background: #7f1d1d; border-color: #dc2626; }
.appt-yellow { background: #78350f; border-color: #f59e0b; }
.appt-evento { background: #2e1065; border-color: #a855f7; }

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

/* Modal cambio de estado */
.modal-sm { max-width: 380px; }

.estado-info {
  background: #0d0d0d;
  border: 1px solid #1c1c1c;
  border-radius: 8px;
  padding: 0.85rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
  margin-bottom: 1rem;
}
.info-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.82rem; }
.info-label { color: #6b7280; font-weight: 600; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.4px; }
.info-val { color: #d1d5db; }
.info-fecha { color: #38bdf8; }

.estado-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
.estado-badge.programada { background: rgba(59,130,246,0.15); color: #93c5fd; }
.estado-badge.atendida   { background: rgba(74,222,128,0.15); color: #4ade80; }
.estado-badge.cancelada  { background: rgba(239,68,68,0.15);  color: #f87171; }

.estado-opciones { display: flex; flex-direction: column; gap: 0.45rem; margin-bottom: 0.5rem; }

.btn-estado-op {
  display: flex; align-items: center; gap: 0.6rem;
  padding: 0.6rem 0.9rem; border-radius: 8px;
  border: 1px solid #2a2a2a; background: #0d0d0d;
  color: #9ca3af; font-size: 0.85rem; font-weight: 500;
  cursor: pointer; text-align: left; font-family: inherit;
  transition: background 0.15s, border-color 0.15s, color 0.15s;
}
.btn-estado-op:hover { background: #1a1a1a; color: #e5e7eb; }
.btn-estado-op.activo.op-green  { background: rgba(34,197,94,0.12);  border-color: #22c55e; color: #4ade80; }
.btn-estado-op.activo.op-red    { background: rgba(239,68,68,0.12);  border-color: #dc2626; color: #f87171; }
.btn-estado-op.activo.op-blue   { background: rgba(59,130,246,0.12); border-color: #3b82f6; color: #93c5fd; }

.error-msg { color: #f87171; font-size: 0.82rem; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); border-radius: 6px; padding: 0.5rem 0.75rem; margin-top: 0.5rem; }

.notif-hint {
  display: flex; align-items: center; gap: 0.4rem;
  color: #4b5563; font-size: 0.72rem; margin-top: 0.75rem;
}

/* Toast */
.fisio-toast {
  position: fixed; bottom: 1.75rem; right: 1.75rem; z-index: 9999;
  display: flex; align-items: center; gap: 0.6rem;
  padding: 0.75rem 1.1rem; border-radius: 10px;
  font-size: 0.82rem; font-weight: 500; max-width: 360px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.5);
}
.fisio-toast.ok  { background: #052e16; border: 1px solid rgba(74,222,128,0.3); color: #4ade80; }
.fisio-toast.err { background: #2d1111; border: 1px solid rgba(239,68,68,0.3);  color: #f87171; }

.toast-slide-enter-active { animation: toastIn 0.3s ease; }
.toast-slide-leave-active { animation: toastIn 0.25s ease reverse; }
@keyframes toastIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
</style>
