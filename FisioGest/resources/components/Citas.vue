<template>
  <AppLayout>

    <!-- Header -->
    <div class="citas-header">
      <h2 class="page-title">Agenda de Citas</h2>
      <button class="btn-add" @click="openModal">+ Agendar Nueva Cita</button>
    </div>

    <!-- Stats -->
    <div class="citas-stats">
      <div class="stat-box">
        <span class="sbox-label">Total Citas</span>
        <span class="sbox-value">{{ citas.length }}</span>
      </div>
      <div class="stat-box">
        <span class="sbox-label">Programadas</span>
        <span class="sbox-value">{{ citas.filter(c => c.estado === 'programada').length }}</span>
      </div>
      <div class="stat-box">
        <span class="sbox-label">Atendidas</span>
        <span class="sbox-value">{{ citas.filter(c => c.estado === 'atendida').length }}</span>
      </div>
      <div class="stat-box critical">
        <span class="sbox-label">Canceladas</span>
        <span class="sbox-value">{{ citas.filter(c => c.estado === 'cancelada').length }}</span>
      </div>
    </div>

    <!-- Layout: calendario + tabla -->
    <div class="agenda-layout">

      <!-- Calendario -->
      <div class="calendar-widget">
        <div class="cal-nav">
          <button class="cal-nav-btn" @click="mesAnterior">&#8249;</button>
          <span class="cal-mes-label">{{ mesLabel }}</span>
          <button class="cal-nav-btn" @click="mesSiguiente">&#8250;</button>
        </div>

        <div class="cal-grid">
          <div class="cal-dow" v-for="d in diasSemana" :key="d">{{ d }}</div>
          <div
            v-for="dia in diasDelMes"
            :key="dia.key"
            class="cal-day"
            :class="{
              'cal-otro-mes':     !dia.esEsteMes,
              'cal-hoy':           dia.esHoy,
              'cal-con-citas':     dia.tieneCitas,
              'cal-seleccionado':  dia.estaSeleccionado,
            }"
            @click="seleccionarDia(dia)"
          >
            <span class="cal-day-num">{{ dia.numero }}</span>
            <span v-if="dia.tieneCitas && dia.esEsteMes" class="cal-dot"></span>
          </div>
        </div>

        <div class="cal-leyenda">
          <span class="leyenda-item"><span class="leyenda-dot"></span> Con citas</span>
          <span class="leyenda-item"><span class="leyenda-hoy"></span> Hoy</span>
        </div>

        <button v-if="fechaSeleccionada" class="btn-limpiar-filtro" @click="limpiarFiltro">
          &#10005; Ver todas las citas
        </button>
      </div>

      <!-- Tabla -->
      <div class="table-card">
        <div class="table-title-row">
          <h3 class="table-title">
            {{ fechaSeleccionada ? 'Citas del ' + formatFechaCorta(fechaSeleccionada) : 'Historial y Próximos Turnos' }}
          </h3>
          <span v-if="fechaSeleccionada" class="table-count">
            {{ citasFiltradas.length }} cita{{ citasFiltradas.length !== 1 ? 's' : '' }}
          </span>
        </div>

        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Paciente</th>
                <th>Fisioterapeuta</th>
                <th>Fecha y Hora</th>
                <th>Motivo</th>
                <th>Estado</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="6" class="td-empty">Cargando citas...</td>
              </tr>
              <tr v-else-if="citasFiltradas.length === 0">
                <td colspan="6" class="td-empty">
                  {{ fechaSeleccionada
                    ? 'No hay citas para esta fecha.'
                    : 'No hay citas agendadas. Haz clic en "Agendar Nueva Cita" para empezar.' }}
                </td>
              </tr>
              <tr v-for="cita in citasFiltradas" :key="cita.cita_id" v-else>
                <td class="td-nombre">{{ nombrePaciente(cita.paciente_id) }}</td>
                <td>{{ nombreFisio(cita.fisioterapeuta_id) }}</td>
                <td class="td-fecha">{{ formatFecha(cita.fecha_hora) }}</td>
                <td>{{ cita.motivo || '—' }}</td>
                <td>
                  <div class="estado-cell">
                    <span class="estado-badge" :class="cita.estado">{{ cita.estado }}</span>
                    <button class="btn-edit-estado" @click="openEstadoModal(cita)" title="Cambiar estado">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                      </svg>
                    </button>
                  </div>
                </td>
                <td class="td-acciones">
                  <button class="btn-accion" @click="openEditModal(cita)" title="Editar cita">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Editar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal: Nueva / Editar Cita -->
    <Teleport to="body">
      <div class="modal-overlay" v-if="showModal" @click.self="closeModal">
        <div class="modal">
          <div class="modal-header">
            <h3>{{ editingCita ? 'Editar Cita' : 'Agendar Nueva Cita' }}</h3>
            <button class="modal-close" @click="closeModal">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <form class="modal-form" @submit.prevent="saveCita">
            <div class="form-group">
              <label>Paciente *</label>
              <select v-model="form.paciente_id" required>
                <option value="">-- Seleccione un paciente --</option>
                <option v-for="p in pacientes" :key="p.paciente_id" :value="p.paciente_id">
                  {{ p.nombre }} {{ p.apellido }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Fisioterapeuta *</label>
              <select v-model="form.fisioterapeuta_id" required>
                <option value="">-- Seleccione un especialista --</option>
                <option v-for="f in fisioterapeutas" :key="f.fisioterapeuta_id" :value="f.fisioterapeuta_id">
                  {{ f.nombre }} {{ f.apellido }} — {{ f.especialidad }}
                </option>
              </select>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Fecha *</label>
                <input v-model="form.fecha" type="date" required />
              </div>
              <div class="form-group">
                <label>Hora *</label>
                <input v-model="form.hora" type="time" required />
              </div>
            </div>

            <div class="form-group">
              <label>Motivo</label>
              <textarea v-model="form.motivo" rows="3" placeholder="Ej. Terapia de rehabilitación..."></textarea>
            </div>

            <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>

            <div class="modal-actions">
              <button type="submit" class="btn-guardar" :disabled="saving">
                {{ saving ? 'Guardando...' : (editingCita ? 'Guardar Cambios' : 'Agendar Cita') }}
              </button>
              <button type="button" class="btn-cancelar" @click="closeModal">Cancelar</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Modal: Cambiar Estado -->
    <Teleport to="body">
      <div class="modal-overlay" v-if="showEstadoModal" @click.self="closeEstadoModal">
        <div class="modal modal-sm">
          <div class="modal-header">
            <h3>Cambiar Estado de Cita</h3>
            <button class="modal-close" @click="closeEstadoModal">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <div class="estado-modal-info">
            <div class="info-row">
              <span class="info-label">Paciente</span>
              <span class="info-val">{{ nombrePaciente(citaSeleccionada?.paciente_id) }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Fecha y Hora</span>
              <span class="info-val td-fecha">{{ formatFecha(citaSeleccionada?.fecha_hora) }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Estado actual</span>
              <span class="estado-badge" :class="citaSeleccionada?.estado">{{ citaSeleccionada?.estado }}</span>
            </div>
          </div>

          <div class="form-group" style="margin-top:1rem;">
            <label>Nuevo Estado *</label>
            <select v-model="nuevoEstado">
              <option value="programada">Programada</option>
              <option value="atendida">Atendida</option>
              <option value="cancelada">Cancelada (libera el horario)</option>
            </select>
          </div>

          <p v-if="errorEstado" class="error-msg" style="margin-top:0.75rem;">{{ errorEstado }}</p>

          <div class="modal-actions" style="margin-top:1.25rem;">
            <button class="btn-guardar" @click="saveEstado" :disabled="updatingEstado">
              {{ updatingEstado ? 'Guardando...' : 'Guardar Cambio' }}
            </button>
            <button class="btn-cancelar" @click="closeEstadoModal">Cancelar</button>
          </div>
        </div>
      </div>
    </Teleport>

  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import { citaService, pacienteService, fisioterapeutaService } from '@/services/api'

const citas      = ref([])
const pacientes  = ref([])
const loading    = ref(true)
const showModal    = ref(false)
const saving       = ref(false)
const errorMsg     = ref('')
const editingCita  = ref(null)

const showEstadoModal  = ref(false)
const citaSeleccionada = ref(null)
const nuevoEstado      = ref('')
const updatingEstado   = ref(false)
const errorEstado      = ref('')

const fisioterapeutas = ref([])

const form = ref({ paciente_id: '', fisioterapeuta_id: '', fecha: '', hora: '', motivo: '' })

// ── Calendario ────────────────────────────────────────────────────────────────

const diasSemana      = ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa', 'Do']
const mesActual       = ref(new Date())
const fechaSeleccionada = ref(null)

const mesLabel = computed(() =>
  mesActual.value.toLocaleString('es-ES', { month: 'long', year: 'numeric' })
)

const diasConCitas = computed(() => {
  const set = new Set()
  citas.value.forEach(c => { if (c.fecha_hora) set.add(c.fecha_hora.slice(0, 10)) })
  return set
})

const diasDelMes = computed(() => {
  const year  = mesActual.value.getFullYear()
  const month = mesActual.value.getMonth()
  const hoy   = new Date()
  const hoyStr = toDateStr(hoy)

  // Primer día del mes: ajustar a semana con lunes=0
  const primerDia   = new Date(year, month, 1)
  const ultimoDia   = new Date(year, month + 1, 0)
  const inicioSemana = (primerDia.getDay() + 6) % 7

  const dias = []

  // Relleno mes anterior
  for (let i = inicioSemana - 1; i >= 0; i--) {
    const d = new Date(year, month, -i)
    dias.push({ numero: d.getDate(), esEsteMes: false, key: `p-${i}` })
  }

  // Días del mes actual
  for (let d = 1; d <= ultimoDia.getDate(); d++) {
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`
    dias.push({
      numero:          d,
      esEsteMes:       true,
      esHoy:           dateStr === hoyStr,
      tieneCitas:      diasConCitas.value.has(dateStr),
      estaSeleccionado: fechaSeleccionada.value === dateStr,
      dateStr,
      key:             dateStr,
    })
  }

  // Relleno mes siguiente hasta completar 42 celdas
  const restante = 42 - dias.length
  for (let d = 1; d <= restante; d++) {
    dias.push({ numero: d, esEsteMes: false, key: `n-${d}` })
  }

  return dias
})

function toDateStr(date) {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

function mesAnterior() {
  const d = new Date(mesActual.value)
  d.setDate(1)
  d.setMonth(d.getMonth() - 1)
  mesActual.value = d
}

function mesSiguiente() {
  const d = new Date(mesActual.value)
  d.setDate(1)
  d.setMonth(d.getMonth() + 1)
  mesActual.value = d
}

function seleccionarDia(dia) {
  if (!dia.esEsteMes || !dia.tieneCitas) return
  fechaSeleccionada.value = fechaSeleccionada.value === dia.dateStr ? null : dia.dateStr
}

function limpiarFiltro() {
  fechaSeleccionada.value = null
}

// ── Lista filtrada y ordenada ─────────────────────────────────────────────────

const citasFiltradas = computed(() => {
  const ordenadas = citas.value.slice().sort((a, b) => new Date(b.fecha_hora) - new Date(a.fecha_hora))
  if (!fechaSeleccionada.value) return ordenadas
  return ordenadas.filter(c => c.fecha_hora && c.fecha_hora.slice(0, 10) === fechaSeleccionada.value)
})

// ── Modales ───────────────────────────────────────────────────────────────────

function openModal() {
  editingCita.value = null
  form.value = { paciente_id: '', fisioterapeuta_id: '', fecha: '', hora: '', motivo: '' }
  errorMsg.value = ''
  showModal.value = true
}

function openEditModal(cita) {
  editingCita.value = cita
  const [fecha, horaCompleta] = (cita.fecha_hora ?? '').split(' ')
  const hora = horaCompleta ? horaCompleta.slice(0, 5) : ''
  form.value = {
    paciente_id:       cita.paciente_id,
    fisioterapeuta_id: cita.fisioterapeuta_id,
    fecha,
    hora,
    motivo: cita.motivo ?? '',
  }
  errorMsg.value  = ''
  showModal.value = true
}

function closeModal() {
  showModal.value   = false
  editingCita.value = null
}

function openEstadoModal(cita) {
  citaSeleccionada.value = cita
  nuevoEstado.value      = cita.estado
  errorEstado.value      = ''
  showEstadoModal.value  = true
}

function closeEstadoModal() {
  showEstadoModal.value  = false
  citaSeleccionada.value = null
}

async function saveEstado() {
  updatingEstado.value = true
  errorEstado.value    = ''
  try {
    await citaService.updateEstado(citaSeleccionada.value.cita_id, nuevoEstado.value)
    await cargarDatos()
    closeEstadoModal()
  } catch {
    errorEstado.value = 'Error al actualizar el estado. Intenta nuevamente.'
  } finally {
    updatingEstado.value = false
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function nombrePaciente(id) {
  const p = pacientes.value.find(p => p.paciente_id === id)
  return p ? `${p.nombre} ${p.apellido}` : `Paciente #${id}`
}

function nombreFisio(id) {
  const f = fisioterapeutas.value.find(f => f.fisioterapeuta_id === id)
  return f ? `${f.nombre} ${f.apellido}` : `Fisio #${id}`
}

function formatFecha(fechaHora) {
  if (!fechaHora) return '—'
  return new Date(fechaHora).toLocaleString('es-ES', { dateStyle: 'medium', timeStyle: 'short' })
}

function formatFechaCorta(dateStr) {
  if (!dateStr) return ''
  const [y, m, d] = dateStr.split('-')
  return new Date(Number(y), Number(m) - 1, Number(d))
    .toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' })
}

// ── Datos ─────────────────────────────────────────────────────────────────────

async function cargarDatos() {
  loading.value = true
  try {
    const [citasRes, pacientesRes, fisiosRes] = await Promise.allSettled([
      citaService.getAll(),
      pacienteService.getAll(),
      fisioterapeutaService.getAll(),
    ])
    if (citasRes.status === 'fulfilled')     citas.value           = citasRes.value.data
    if (pacientesRes.status === 'fulfilled') pacientes.value       = pacientesRes.value.data
    if (fisiosRes.status === 'fulfilled')    fisioterapeutas.value = fisiosRes.value.data
  } finally {
    loading.value = false
  }
}

async function saveCita() {
  saving.value   = true
  errorMsg.value = ''
  const payload = {
    paciente_id:       form.value.paciente_id,
    fisioterapeuta_id: form.value.fisioterapeuta_id,
    fecha_hora:        `${form.value.fecha} ${form.value.hora}:00`,
    motivo:            form.value.motivo,
  }
  try {
    if (editingCita.value) {
      await citaService.update(editingCita.value.cita_id, payload)
    } else {
      await citaService.create(payload)
    }
    await cargarDatos()
    closeModal()
  } catch {
    errorMsg.value = 'Error al guardar la cita. Verifica los datos.'
  } finally {
    saving.value = false
  }
}

onMounted(cargarDatos)
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

.citas-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.page-title {
  color: #ffffff;
  font-size: 1.4rem;
  font-weight: 700;
}

.btn-add {
  background: #074434;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  padding: 0.55rem 1rem;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-add:hover { background: #0a5c46; }

/* Stats */
.citas-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.9rem;
  margin-bottom: 1.25rem;
}

.stat-box {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 10px;
  padding: 1rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.stat-box.critical {
  background: #2d1111;
  border-color: #4a1c1c;
}

.sbox-label {
  color: #6b7280;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.sbox-value {
  color: #ffffff;
  font-size: 2.2rem;
  font-weight: 700;
  line-height: 1;
}

/* ── Layout principal ───────────────────────────────────────────────────────── */
.agenda-layout {
  display: flex;
  gap: 1.1rem;
  align-items: flex-start;
}

/* ── Calendario widget ──────────────────────────────────────────────────────── */
.calendar-widget {
  flex-shrink: 0;
  width: 258px;
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 10px;
  padding: 1rem;
  position: sticky;
  top: 1rem;
}

.cal-nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.85rem;
}

.cal-nav-btn {
  background: none;
  border: none;
  color: #9ca3af;
  font-size: 1.4rem;
  line-height: 1;
  cursor: pointer;
  padding: 0.1rem 0.4rem;
  border-radius: 5px;
  transition: color 0.15s, background 0.15s;
}
.cal-nav-btn:hover { color: #ffffff; background: rgba(255,255,255,0.07); }

.cal-mes-label {
  color: #ffffff;
  font-size: 0.82rem;
  font-weight: 700;
  text-transform: capitalize;
  text-align: center;
  flex: 1;
}

.cal-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 2px;
}

.cal-dow {
  color: #4b5563;
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  text-align: center;
  padding: 0.3rem 0;
}

.cal-day {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 30px;
  border-radius: 6px;
  cursor: default;
  transition: background 0.12s;
}

.cal-day-num {
  font-size: 0.75rem;
  color: #6b7280;
  line-height: 1;
}

.cal-otro-mes .cal-day-num { color: #2a2a2a; }

.cal-hoy {
  background: rgba(7, 68, 52, 0.35);
}
.cal-hoy .cal-day-num { color: #4ade80; font-weight: 700; }

.cal-con-citas { cursor: pointer; }
.cal-con-citas .cal-day-num { color: #e5e7eb; font-weight: 600; }
.cal-con-citas:hover { background: rgba(255,255,255,0.07); }

.cal-seleccionado {
  background: #074434 !important;
  border: 1px solid #0a5c46;
}
.cal-seleccionado .cal-day-num { color: #ffffff !important; font-weight: 700; }

.cal-dot {
  display: block;
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: #38bdf8;
  margin-top: 2px;
}
.cal-seleccionado .cal-dot { background: #93c5fd; }

.cal-leyenda {
  display: flex;
  gap: 0.9rem;
  margin-top: 0.7rem;
  padding-top: 0.6rem;
  border-top: 1px solid #1c1c1c;
}

.leyenda-item {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.68rem;
  color: #4b5563;
}

.leyenda-dot {
  display: inline-block;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #38bdf8;
}

.leyenda-hoy {
  display: inline-block;
  width: 12px;
  height: 12px;
  border-radius: 3px;
  background: rgba(7, 68, 52, 0.5);
  border: 1px solid #4ade80;
}

.btn-limpiar-filtro {
  display: block;
  width: 100%;
  margin-top: 0.75rem;
  background: rgba(239,68,68,0.08);
  border: 1px solid rgba(239,68,68,0.2);
  border-radius: 6px;
  color: #f87171;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.4rem;
  cursor: pointer;
  transition: background 0.15s;
  font-family: inherit;
}
.btn-limpiar-filtro:hover { background: rgba(239,68,68,0.15); }

/* ── Tabla ──────────────────────────────────────────────────────────────────── */
.table-card {
  flex: 1;
  min-width: 0;
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 10px;
  padding: 1.1rem 1.25rem;
}

.table-title-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.9rem;
}

.table-title {
  color: #ffffff;
  font-size: 0.95rem;
  font-weight: 600;
}

.table-count {
  background: rgba(56,189,248,0.1);
  color: #38bdf8;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 0.15rem 0.55rem;
  border-radius: 20px;
}

.table-wrapper { overflow-x: auto; }

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

thead tr { border-bottom: 1px solid #1c1c1c; }

th {
  text-align: left;
  color: #6b7280;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 0.6rem 0.75rem;
}

td {
  color: #d1d5db;
  padding: 0.75rem 0.75rem;
  border-bottom: 1px solid #161616;
}

tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: rgba(255,255,255,0.02); }

.td-nombre { font-weight: 600; color: #ffffff; }
.td-fecha  { color: #38bdf8; }
.td-empty  { text-align: center; color: #4b5563; padding: 2rem; }

/* Estado badge */
.estado-badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.estado-badge.programada   { background: rgba(59,130,246,0.15);  color: #93c5fd; }
.estado-badge.atendida     { background: rgba(74,222,128,0.15);  color: #4ade80; }
.estado-badge.cancelada    { background: rgba(239,68,68,0.15);   color: #f87171; }
.estado-badge.reprogramada { background: rgba(245,158,11,0.15);  color: #fbbf24; }

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.modal {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 12px;
  padding: 1.5rem;
  width: 100%;
  max-width: 460px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.modal-header h3 {
  color: #ffffff;
  font-size: 1rem;
  font-weight: 700;
}

.modal-close {
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 5px;
  display: flex;
  transition: color 0.15s;
}
.modal-close:hover { color: #d1d5db; }

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.form-group label {
  color: #9ca3af;
  font-size: 0.8rem;
  font-weight: 600;
}

.form-group input,
.form-group select,
.form-group textarea {
  background: #0d0d0d;
  border: 1px solid #1c1c1c;
  border-radius: 7px;
  padding: 0.6rem 0.75rem;
  color: #d1d5db;
  font-size: 0.875rem;
  outline: none;
  width: 100%;
  transition: border-color 0.15s;
  font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #074434;
  box-shadow: 0 0 0 3px rgba(7,68,52,0.2);
}

.form-group textarea { resize: none; }

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.error-msg {
  color: #f87171;
  font-size: 0.82rem;
  background: rgba(239,68,68,0.1);
  border: 1px solid rgba(239,68,68,0.2);
  border-radius: 6px;
  padding: 0.5rem 0.75rem;
}

.modal-actions {
  display: flex;
  gap: 0.6rem;
  margin-top: 0.25rem;
}

.btn-guardar {
  flex: 1;
  background: #074434;
  color: #ffffff;
  border: none;
  border-radius: 7px;
  padding: 0.65rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-guardar:hover:not(:disabled) { background: #0a5c46; }
.btn-guardar:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-cancelar {
  flex: 1;
  background: #1c1c1c;
  color: #9ca3af;
  border: 1px solid #2a2a2a;
  border-radius: 7px;
  padding: 0.65rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}
.btn-cancelar:hover { background: #2a2a2a; color: #d1d5db; }

/* Columna de acciones */
.td-acciones {
  width: 1%;
  white-space: nowrap;
  padding-right: 0.5rem;
}

.btn-accion {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  background: rgba(255,255,255,0.04);
  border: 1px solid #2a2a2a;
  border-radius: 6px;
  color: #9ca3af;
  font-size: 0.78rem;
  font-weight: 600;
  padding: 0.3rem 0.65rem;
  cursor: pointer;
  transition: background 0.15s, color 0.15s, border-color 0.15s;
  font-family: inherit;
}
.btn-accion:hover {
  background: rgba(255,255,255,0.08);
  color: #e5e7eb;
  border-color: #3a3a3a;
}

/* Estado cell con botón de editar */
.estado-cell {
  display: flex;
  align-items: center;
  gap: 0.45rem;
}

.btn-edit-estado {
  background: none;
  border: none;
  color: #4b5563;
  cursor: pointer;
  padding: 0.2rem;
  border-radius: 4px;
  display: flex;
  align-items: center;
  transition: color 0.15s, background 0.15s;
  flex-shrink: 0;
}
.btn-edit-estado:hover {
  color: #9ca3af;
  background: rgba(255,255,255,0.06);
}

/* Modal compacto para cambiar estado */
.modal-sm { max-width: 380px; }

.estado-modal-info {
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
  background: #0d0d0d;
  border: 1px solid #1c1c1c;
  border-radius: 8px;
  padding: 0.85rem 1rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.82rem;
}

.info-label {
  color: #6b7280;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.72rem;
  letter-spacing: 0.4px;
}

.info-val {
  color: #d1d5db;
  font-weight: 500;
}
</style>
