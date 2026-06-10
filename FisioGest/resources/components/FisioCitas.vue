<template>
  <FisioLayout>

    <div class="pag-header">
      <div>
        <h2 class="page-title">Mis Citas</h2>
        <p class="page-sub">Gestiona las citas de <strong>{{ userName }}</strong>.</p>
      </div>
      <button class="btn-add" @click="abrirModalNueva">+ Agendar Cita</button>
    </div>

    <!-- Filtros -->
    <div class="filtros-row">
      <select v-model="filtroPaciente" class="filtro-select">
        <option value="">Todos los pacientes</option>
        <option v-for="p in todosPacientes" :key="p.paciente_id" :value="p.paciente_id">
          {{ p.nombre }} {{ p.apellido }}
        </option>
      </select>
      <select v-model="filtroEstado" class="filtro-select">
        <option value="">Todos los estados</option>
        <option value="programada">Programada</option>
        <option value="atendida">Atendida</option>
        <option value="cancelada">Cancelada</option>
      </select>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="mini-stat">
        <span class="ms-val">{{ misCitas.length }}</span>
        <span class="ms-label">Total</span>
      </div>
      <div class="mini-stat">
        <span class="ms-val green">{{ misCitas.filter(c=>c.estado==='atendida').length }}</span>
        <span class="ms-label">Completadas</span>
      </div>
      <div class="mini-stat">
        <span class="ms-val blue">{{ misCitas.filter(c=>c.estado==='programada').length }}</span>
        <span class="ms-label">Programadas</span>
      </div>
      <div class="mini-stat">
        <span class="ms-val red">{{ misCitas.filter(c=>c.estado==='cancelada').length }}</span>
        <span class="ms-label">Canceladas</span>
      </div>
    </div>

    <!-- Lista -->
    <div v-if="loading" class="empty-state">Cargando citas...</div>
    <div v-else-if="citasFiltradas.length === 0" class="empty-state">
      No hay citas que coincidan con los filtros.
    </div>

    <div v-else class="citas-list">
      <div v-for="c in citasFiltradas" :key="c.cita_id" class="cita-card">

        <div class="c-avatar" :style="{ background: avatarColor(nombrePaciente(c.paciente_id)) }">
          {{ inicialesPaciente(c.paciente_id) }}
        </div>

        <div class="c-info">
          <div class="c-name-row">
            <span class="c-nombre">{{ nombrePaciente(c.paciente_id) }}</span>
          </div>
          <p class="c-fecha">📅 <strong>{{ formatFecha(c.fecha_hora) }}</strong></p>
          <div class="c-diag" v-if="c.motivo">
            <span class="diag-label">Motivo:</span>
            <span class="diag-badge">{{ c.motivo }}</span>
          </div>
        </div>

        <div class="c-right">
          <span class="estado-badge" :class="c.estado">{{ estadoLabel(c.estado) }}</span>
          <button
            v-if="c.estado === 'programada' || c.estado === 'cancelada'"
            class="btn-reagendar"
            @click="abrirModalReagendar(c)"
          >
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
            </svg>
            Reagendar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal: Agendar / Reagendar -->
    <Teleport to="body">
      <div class="modal-overlay" v-if="showModal" @click.self="cerrarModal">
        <div class="modal">
          <div class="modal-header">
            <h3>{{ reagendando ? 'Reagendar Cita' : 'Agendar Nueva Cita' }}</h3>
            <button class="modal-close" @click="cerrarModal">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <form class="modal-form" @submit.prevent="guardarCita">

            <!-- Paciente (solo al crear) -->
            <div class="form-group" v-if="!reagendando">
              <label>Paciente *</label>
              <select v-model="form.paciente_id" required>
                <option value="">-- Selecciona un paciente --</option>
                <option v-for="p in todosPacientes" :key="p.paciente_id" :value="p.paciente_id">
                  {{ p.nombre }} {{ p.apellido }}
                </option>
              </select>
            </div>

            <!-- Paciente (solo al reagendar, solo lectura) -->
            <div class="info-readonly" v-else>
              <span class="ir-label">Paciente</span>
              <span class="ir-val">{{ nombrePaciente(form.paciente_id) }}</span>
            </div>

            <!-- Fecha -->
            <div class="form-group">
              <label>Fecha *</label>
              <input v-model="form.fecha" type="date" required @change="onFechaChange" :min="hoyISO" />
              <span v-if="advertenciaFecha" class="field-warn">{{ advertenciaFecha }}</span>
            </div>

            <!-- Slot picker -->
            <div class="form-group">
              <label>
                Hora *
                <span v-if="rangoHorario" class="label-hint">{{ rangoHorario }}</span>
              </label>

              <template v-if="slotsDelDia.length > 0">
                <div v-if="todoDiaBloqueado" class="slots-evento-aviso">
                  📅 Tienes un evento personal que bloquea todo este día.
                </div>
                <p v-else-if="slotsDelDia.every(s => s.ocupado || s.bloqueado)" class="slots-empty">
                  No hay horarios disponibles para este día.
                </p>
                <div v-else class="slots-grid">
                  <button
                    v-for="s in slotsDelDia"
                    :key="s.hora"
                    type="button"
                    class="slot-btn"
                    :class="{
                      'slot-selected':   form.hora === s.hora,
                      'slot-ocupado':    s.ocupado && !s.bloqueado,
                      'slot-bloqueado':  s.bloqueado,
                      'slot-disponible': !s.ocupado && !s.bloqueado,
                    }"
                    :disabled="s.ocupado || s.bloqueado"
                    @click="form.hora = s.hora"
                  >
                    {{ formatSlotLabel(s.hora) }}
                    <span v-if="s.bloqueado" class="slot-tag tag-bloqueado">Evento</span>
                    <span v-else-if="s.ocupado" class="slot-tag">Ocupado</span>
                  </button>
                </div>
                <span v-if="!form.hora && form.fecha && !todoDiaBloqueado" class="field-warn">Selecciona una hora disponible.</span>
              </template>

              <template v-else-if="form.fecha && advertenciaFecha">
                <!-- día no laborable: mensaje ya mostrado arriba -->
              </template>

              <template v-else>
                <p class="field-hint">
                  {{ form.fecha ? 'El fisioterapeuta no tiene horario configurado para este día.' : 'Selecciona una fecha para ver los horarios disponibles.' }}
                </p>
              </template>
            </div>

            <!-- Motivo -->
            <div class="form-group">
              <label>Motivo</label>
              <textarea v-model="form.motivo" rows="2" placeholder="Ej. Terapia de rehabilitación..."></textarea>
            </div>

            <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>

            <div class="modal-actions">
              <button
                type="submit"
                class="btn-guardar"
                :disabled="guardando || !form.hora || (slotsDelDia.length > 0 && !form.hora) || !!advertenciaFecha"
              >
                {{ guardando ? 'Guardando...' : (reagendando ? 'Confirmar reagenda' : 'Agendar cita') }}
              </button>
              <button type="button" class="btn-cancelar" @click="cerrarModal">Cancelar</button>
            </div>

            <p class="notif-hint" v-if="reagendando || !reagendando">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              El administrador recibirá una notificación con este cambio.
            </p>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Toast -->
    <Teleport to="body">
      <Transition name="toast-slide">
        <div v-if="toast.visible" class="fisio-toast" :class="toast.type">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline v-if="toast.type==='ok'" points="20 6 9 17 4 12"/>
            <circle v-else cx="12" cy="12" r="10"/>
          </svg>
          <span>{{ toast.msg }}</span>
        </div>
      </Transition>
    </Teleport>

  </FisioLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import FisioLayout from '@/components/FisioLayout.vue'
import { fisioService, agendaService, getUser } from '@/services/api'
import axios from 'axios'

const loading        = ref(true)
const misCitas       = ref([])
const todosPacientes = ref([])
const filtroPaciente = ref('')
const filtroEstado   = ref('')
const showModal      = ref(false)
const reagendando    = ref(false)
const citaEditando   = ref(null)
const guardando      = ref(false)
const errorMsg       = ref('')
const miHorario      = ref(null)

const form = ref({ paciente_id: '', fecha: '', hora: '', motivo: '' })

// ── Eventos de agenda propios del fisio ───────────────────────────────────────
const eventosDelDia    = ref([])
const todoDiaBloqueado = ref(false)

async function cargarEventosDia(fecha) {
  eventosDelDia.value    = []
  todoDiaBloqueado.value = false
  if (!fecha || !fisioId.value) return
  try {
    const res = await axios.get(`/api/eventos-fisio/${fisioId.value}/${fecha}`)
    eventosDelDia.value    = res.data
    todoDiaBloqueado.value = res.data.some(e => !e.hora_inicio)
  } catch {}
}

watch(() => form.value.fecha, (fecha) => {
  form.value.hora = ''
  cargarEventosDia(fecha)
})

function slotBloqueadoPorEvento(curMin) {
  const slotFin = curMin + 60
  for (const ev of eventosDelDia.value) {
    if (!ev.hora_inicio) return true
    const [eh, em] = ev.hora_inicio.split(':').map(Number)
    const evStart  = eh * 60 + em
    const evEnd    = ev.hora_fin
      ? (() => { const [fh, fm] = ev.hora_fin.split(':').map(Number); return fh * 60 + fm })()
      : 24 * 60
    if (curMin < evEnd && slotFin > evStart) return true
  }
  return false
}

const currentUser = computed(() => getUser())
const userName    = computed(() => currentUser.value?.nombre ?? 'Fisioterapeuta')
const fisioId     = computed(() => currentUser.value?.fisioterapeuta_id ?? null)

const hoyISO = new Date().toISOString().slice(0, 10)

// ── Toast ─────────────────────────────────────────────────────────────────────
const toast = ref({ visible: false, type: 'ok', msg: '' })
let toastTimer = null
function mostrarToast(type, msg) {
  clearTimeout(toastTimer)
  toast.value = { visible: true, type, msg }
  toastTimer  = setTimeout(() => { toast.value.visible = false }, 3500)
}

// ── Filtros ───────────────────────────────────────────────────────────────────
const citasFiltradas = computed(() => {
  let r = misCitas.value
  if (filtroPaciente.value) r = r.filter(c => c.paciente_id == filtroPaciente.value)
  if (filtroEstado.value)   r = r.filter(c => c.estado === filtroEstado.value)
  return r.slice().sort((a, b) => new Date(b.fecha_hora) - new Date(a.fecha_hora))
})

// ── Helpers ───────────────────────────────────────────────────────────────────
function nombrePaciente(id) {
  const p = todosPacientes.value.find(p => p.paciente_id === id)
  return p ? `${p.nombre} ${p.apellido}` : `Paciente #${id}`
}
function inicialesPaciente(id) {
  return nombrePaciente(id).split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
}
const colores = ['#1e3a8a','#4c1d95','#78350f','#14532d','#7f1d1d','#0c4a6e']
function avatarColor(nombre = '') { return colores[nombre.charCodeAt(0) % colores.length] }
function formatFecha(fh) {
  if (!fh) return '—'
  try { return new Date(fh).toLocaleString('es-ES', { dateStyle: 'medium', timeStyle: 'short' }) } catch { return fh }
}
function estadoLabel(estado) {
  return { programada: 'Programada', atendida: 'Completada', cancelada: 'Cancelada' }[estado] ?? estado
}
function formatSlotLabel(hora) {
  const [h, m] = hora.split(':').map(Number)
  return `${h % 12 || 12}:${String(m).padStart(2,'0')} ${h >= 12 ? 'p.m.' : 'a.m.'}`
}

// ── Slot picker ───────────────────────────────────────────────────────────────
const DIAS_KEY   = ['dom','lun','mar','mie','jue','vie','sab']
const MAPA_ADMIN = {'Lunes':'lun','Martes':'mar','Miércoles':'mie','Jueves':'jue','Viernes':'vie','Sábado':'sab','Domingo':'dom'}
const advertenciaFecha = ref('')
const rangoHorario     = ref('')

function confDia() {
  if (!form.value.fecha || !miHorario.value) return null
  const h = typeof miHorario.value === 'string' ? JSON.parse(miHorario.value) : miHorario.value
  const norm = {}
  for (const [k, v] of Object.entries(h)) {
    const clave = MAPA_ADMIN[k] ?? k
    norm[clave] = { activo: v.activo ?? false, entrada: v.entrada ?? v.inicio ?? null, salida: v.salida ?? v.fin ?? null }
  }
  const [y, m, d] = form.value.fecha.split('-').map(Number)
  const conf = norm[DIAS_KEY[new Date(y, m - 1, d).getDay()]]
  return (conf?.activo && conf.entrada && conf.salida) ? conf : null
}

const slotsDelDia = computed(() => {
  const conf = confDia()
  advertenciaFecha.value = ''
  rangoHorario.value     = ''

  if (!form.value.fecha) return []

  // Verificar si es día laborable
  if (miHorario.value) {
    const h = typeof miHorario.value === 'string' ? JSON.parse(miHorario.value) : miHorario.value
    const norm = {}
    for (const [k, v] of Object.entries(h)) {
      const clave = MAPA_ADMIN[k] ?? k
      norm[clave] = { activo: v.activo ?? false }
    }
    const [y, m, d] = form.value.fecha.split('-').map(Number)
    const diaConf = norm[DIAS_KEY[new Date(y, m - 1, d).getDay()]]
    const NOMBRES = { lun:'lunes', mar:'martes', mie:'miércoles', jue:'jueves', vie:'viernes', sab:'sábado', dom:'domingo' }
    const diaKey  = DIAS_KEY[new Date(y, m - 1, d).getDay()]
    if (diaConf !== undefined && !diaConf.activo) {
      advertenciaFecha.value = `No atiendes los ${NOMBRES[diaKey] ?? diaKey}.`
      return []
    }
  }

  if (!conf) return []
  rangoHorario.value = `${conf.entrada} – ${conf.salida}`

  const [hIn, mIn]   = conf.entrada.split(':').map(Number)
  const [hOut, mOut] = conf.salida.split(':').map(Number)
  let   cur  = hIn * 60 + mIn
  const end  = hOut * 60 + mOut

  const editId = citaEditando.value?.cita_id
  const citasDia = misCitas.value.filter(c =>
    c.estado === 'programada' &&
    c.fecha_hora?.startsWith(form.value.fecha) &&
    c.cita_id !== editId
  )

  const slots = []
  while (cur + 60 <= end) {
    const hh   = String(Math.floor(cur / 60)).padStart(2, '0')
    const mm   = String(cur % 60).padStart(2, '0')
    const hora = `${hh}:${mm}`
    const ocupado = citasDia.some(c => {
      const [, t]    = (c.fecha_hora ?? '').split(' ')
      const [ch, cm] = (t ?? '00:00').split(':').map(Number)
      const cStart   = ch * 60 + cm
      return cur < cStart + 60 && cur + 60 > cStart
    })
    const bloqueado = slotBloqueadoPorEvento(cur)
    slots.push({ hora, ocupado, bloqueado })
    cur += 30
  }
  return slots
})

function onFechaChange() {
  form.value.hora = ''
}

// ── Modal ─────────────────────────────────────────────────────────────────────
function abrirModalNueva() {
  reagendando.value  = false
  citaEditando.value = null
  form.value         = { paciente_id: '', fecha: '', hora: '', motivo: '' }
  errorMsg.value     = ''
  showModal.value    = true
}

function abrirModalReagendar(cita) {
  reagendando.value  = true
  citaEditando.value = cita
  const [fecha]      = (cita.fecha_hora ?? '').split(' ')
  form.value         = { paciente_id: cita.paciente_id, fecha, hora: '', motivo: cita.motivo ?? '' }
  errorMsg.value     = ''
  showModal.value    = true
}

function cerrarModal() {
  showModal.value    = false
  citaEditando.value = null
}

async function guardarCita() {
  if (!form.value.hora) return
  guardando.value = true
  errorMsg.value  = ''

  const fechaHora = `${form.value.fecha} ${form.value.hora}:00`
  const token     = localStorage.getItem('token')
  const headers   = { Authorization: `Bearer ${token}`, Accept: 'application/json', 'Content-Type': 'application/json' }

  try {
    if (reagendando.value) {
      await axios.put(`/api/fisio/citas/${citaEditando.value.cita_id}`,
        { fecha_hora: fechaHora, motivo: form.value.motivo },
        { headers }
      )
      mostrarToast('ok', 'Cita reagendada. El administrador fue notificado.')
    } else {
      await axios.post('/api/fisio/citas',
        { paciente_id: form.value.paciente_id, fecha_hora: fechaHora, motivo: form.value.motivo },
        { headers }
      )
      mostrarToast('ok', 'Cita agendada. El administrador fue notificado.')
    }
    await cargar()
    cerrarModal()
  } catch (e) {
    errorMsg.value = e?.response?.data?.message || 'Error al guardar. Verifica los datos e intenta de nuevo.'
  } finally {
    guardando.value = false
  }
}

// ── Carga ─────────────────────────────────────────────────────────────────────
async function cargar() {
  loading.value = true
  try {
    const [cr, pr, hr] = await Promise.allSettled([
      fisioService.misCitas(),
      fisioService.misPacientes(),
      agendaService.getHorario(),
    ])
    if (cr.status === 'fulfilled') misCitas.value       = cr.value.data
    if (pr.status === 'fulfilled') todosPacientes.value = pr.value.data
    if (hr.status === 'fulfilled') {
      const data = hr.value.data
      miHorario.value = (data && !Array.isArray(data) && Object.keys(data).length > 0) ? data : null
    }
  } finally {
    loading.value = false
  }
}

onMounted(cargar)
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

.pag-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; }
.page-title { color: #ffffff; font-size: 1.4rem; font-weight: 700; }
.page-sub   { color: #6b7280; font-size: 0.82rem; margin-top: 0.2rem; }
.page-sub strong { color: #4ade80; }

.btn-add {
  background: #074434; color: #fff; border: none; border-radius: 8px;
  padding: 0.55rem 1rem; font-size: 0.85rem; font-weight: 600; cursor: pointer;
  transition: background 0.15s; white-space: nowrap;
}
.btn-add:hover { background: #0a5c46; }

.filtros-row { display: flex; gap: 0.75rem; margin-bottom: 1rem; flex-wrap: wrap; }
.filtro-select {
  background: #111111; border: 1px solid #1c1c1c; color: #9ca3af;
  padding: 0.5rem 0.75rem; border-radius: 7px; font-size: 0.82rem; cursor: pointer;
}
.filtro-select:focus { outline: none; border-color: #074434; }

.stats-row  { display: flex; gap: 0.75rem; margin-bottom: 1.25rem; }
.mini-stat  { background: #111111; border: 1px solid #1c1c1c; border-radius: 8px; padding: 0.75rem 1.25rem; display: flex; flex-direction: column; gap: 0.2rem; min-width: 90px; }
.ms-val     { color: #fff; font-size: 1.6rem; font-weight: 700; line-height: 1; }
.ms-val.green { color: #4ade80; }
.ms-val.blue  { color: #38bdf8; }
.ms-val.red   { color: #f87171; }
.ms-label   { color: #6b7280; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.4px; }

.empty-state { color: #4b5563; text-align: center; padding: 3rem; font-size: 0.9rem; }

.citas-list { display: flex; flex-direction: column; gap: 0.75rem; }
.cita-card  { background: #111111; border: 1px solid #1c1c1c; border-radius: 10px; padding: 1rem 1.25rem; display: flex; align-items: center; gap: 1rem; transition: border-color 0.15s; }
.cita-card:hover { border-color: #2a2a2a; }

.c-avatar { width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 14px; font-weight: 700; flex-shrink: 0; }
.c-info   { flex: 1; min-width: 0; }
.c-name-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.3rem; }
.c-nombre { color: #fff; font-size: 0.9rem; font-weight: 700; }
.c-fecha  { color: #9ca3af; font-size: 0.78rem; margin-bottom: 0.35rem; }
.c-fecha strong { color: #38bdf8; }
.c-diag   { display: flex; align-items: center; gap: 0.4rem; }
.diag-label { color: #4b5563; font-size: 0.72rem; }
.diag-badge { background: #1a1a1a; border: 1px solid #2a2a2a; color: #9ca3af; font-size: 0.72rem; padding: 0.15rem 0.5rem; border-radius: 4px; }

.c-right { flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem; }

.estado-badge { display: inline-block; padding: 0.25rem 0.7rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: capitalize; }
.estado-badge.programada { background: rgba(59,130,246,0.15); color: #93c5fd; }
.estado-badge.atendida   { background: rgba(74,222,128,0.15); color: #4ade80; }
.estado-badge.cancelada  { background: rgba(239,68,68,0.15);  color: #f87171; }

.btn-reagendar {
  display: inline-flex; align-items: center; gap: 0.35rem;
  background: rgba(255,255,255,0.04); border: 1px solid #2a2a2a;
  color: #9ca3af; font-size: 0.75rem; font-weight: 600;
  padding: 0.28rem 0.65rem; border-radius: 6px; cursor: pointer;
  font-family: inherit; transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.btn-reagendar:hover { background: rgba(74,222,128,0.08); color: #4ade80; border-color: rgba(74,222,128,0.3); }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal { background: #111111; border: 1px solid #1c1c1c; border-radius: 12px; padding: 1.5rem; width: 100%; max-width: 460px; box-shadow: 0 20px 60px rgba(0,0,0,0.5); max-height: 90vh; overflow-y: auto; }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.modal-header h3 { color: #fff; font-size: 1rem; font-weight: 700; }
.modal-close { background: none; border: none; color: #6b7280; cursor: pointer; padding: 0.25rem; border-radius: 5px; display: flex; transition: color 0.15s; }
.modal-close:hover { color: #d1d5db; }
.modal-form { display: flex; flex-direction: column; gap: 1rem; }

.form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.form-group label { color: #9ca3af; font-size: 0.8rem; font-weight: 600; }
.form-group input, .form-group select, .form-group textarea {
  background: #0d0d0d; border: 1px solid #1c1c1c; border-radius: 7px;
  padding: 0.6rem 0.75rem; color: #d1d5db; font-size: 0.875rem;
  outline: none; width: 100%; transition: border-color 0.15s; font-family: inherit;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #074434; }
.form-group textarea { resize: none; }

.info-readonly { display: flex; justify-content: space-between; align-items: center; background: #0d0d0d; border: 1px solid #1c1c1c; border-radius: 7px; padding: 0.6rem 0.75rem; }
.ir-label { color: #6b7280; font-size: 0.78rem; }
.ir-val   { color: #e5e7eb; font-size: 0.85rem; font-weight: 600; }

.field-warn { font-size: 0.72rem; color: #fb923c; margin-top: 2px; display: block; }
.field-hint { font-size: 0.72rem; color: #4b5563; display: block; margin-top: 4px; }
.label-hint { font-size: 0.68rem; color: #4ade80; font-weight: 400; margin-left: 6px; }

/* Slot picker */
.slots-grid { display: flex; flex-wrap: wrap; gap: 0.45rem; margin-top: 0.25rem; }
.slot-btn {
  display: inline-flex; align-items: center; gap: 0.35rem;
  padding: 0.38rem 0.75rem; border-radius: 6px; border: 1px solid #2a2a2a;
  background: #0d0d0d; color: #9ca3af; font-size: 0.8rem; font-weight: 500;
  cursor: pointer; font-family: inherit; transition: background 0.15s, border-color 0.15s, color 0.15s;
}
.slot-disponible:hover { background: rgba(74,222,128,0.08); border-color: rgba(74,222,128,0.4); color: #4ade80; }
.slot-selected  { background: rgba(74,222,128,0.15) !important; border-color: #4ade80 !important; color: #4ade80 !important; font-weight: 700; }
.slot-ocupado   { opacity: 0.4; cursor: not-allowed; text-decoration: line-through; }
.slot-bloqueado { opacity: 0.55; cursor: not-allowed; background: rgba(251,113,133,0.08) !important; border-color: rgba(251,113,133,0.3) !important; color: #fb7185 !important; }
.slot-tag { font-size: 0.62rem; color: #6b7280; background: rgba(255,255,255,0.05); border-radius: 3px; padding: 1px 4px; }
.tag-bloqueado { background: rgba(251,113,133,0.15); color: #fb7185; }
.slots-empty { color: #6b7280; font-size: 0.82rem; padding: 0.5rem 0; }
.slots-evento-aviso { background: rgba(251,113,133,0.08); border: 1px solid rgba(251,113,133,0.25); color: #fb7185; border-radius: 8px; padding: 0.65rem 0.9rem; font-size: 0.82rem; font-weight: 500; }

.error-msg { color: #f87171; font-size: 0.82rem; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); border-radius: 6px; padding: 0.5rem 0.75rem; }

.modal-actions { display: flex; gap: 0.6rem; }
.btn-guardar  { flex: 1; background: #074434; color: #fff; border: none; border-radius: 7px; padding: 0.65rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: background 0.15s; }
.btn-guardar:hover:not(:disabled) { background: #0a5c46; }
.btn-guardar:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-cancelar { flex: 1; background: #1c1c1c; color: #9ca3af; border: 1px solid #2a2a2a; border-radius: 7px; padding: 0.65rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; }
.btn-cancelar:hover { background: #2a2a2a; color: #d1d5db; }

.notif-hint { display: flex; align-items: center; gap: 0.4rem; color: #4b5563; font-size: 0.72rem; margin-top: 0.5rem; }

/* Toast */
.fisio-toast { position: fixed; bottom: 1.75rem; right: 1.75rem; z-index: 9999; display: flex; align-items: center; gap: 0.6rem; padding: 0.75rem 1.1rem; border-radius: 10px; font-size: 0.82rem; font-weight: 500; max-width: 360px; box-shadow: 0 8px 32px rgba(0,0,0,0.5); }
.fisio-toast.ok  { background: #052e16; border: 1px solid rgba(74,222,128,0.3); color: #4ade80; }
.fisio-toast.err { background: #2d1111; border: 1px solid rgba(239,68,68,0.3);  color: #f87171; }
.toast-slide-enter-active { animation: toastIn 0.3s ease; }
.toast-slide-leave-active { animation: toastIn 0.25s ease reverse; }
@keyframes toastIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
</style>
