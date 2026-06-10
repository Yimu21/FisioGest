<template>
  <FisioLayout>

    <!-- Toast de notificación -->
    <Teleport to="body">
      <Transition name="toast">
        <div v-if="toast.visible" class="toast-notif" :class="toast.type">
          <div class="toast-icon">
            <svg v-if="toast.type === 'ok'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
              <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </div>
          <div class="toast-body">
            <p class="toast-title">{{ toast.title }}</p>
            <p class="toast-msg">{{ toast.msg }}</p>
          </div>
          <button class="toast-close" @click="toast.visible = false">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
      </Transition>
    </Teleport>

    <!-- Header -->
    <div class="dash-header">
      <div>
        <h2 class="page-title">Mi Agenda</h2>
        <p class="page-sub">Administra tu horario laboral y eventos personales</p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tabs-bar">
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'horario' }"
        @click="activeTab = 'horario'"
      >
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <polyline points="12 6 12 12 16 14"/>
        </svg>
        Horario Laboral
      </button>
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'calendario' }"
        @click="activeTab = 'calendario'"
      >
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="4" width="18" height="18" rx="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        Mi Calendario
      </button>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         TAB: HORARIO LABORAL
    ═══════════════════════════════════════════════════════════ -->
    <div v-if="activeTab === 'horario'" class="tab-content">

      <div class="section-card">
        <div class="section-card-header">
          <h3 class="section-title">Días y horarios de trabajo</h3>
          <p class="section-desc">Activa los días en que atenderás y define tu hora de entrada y salida.</p>
        </div>

        <div class="days-grid">
          <div
            v-for="dia in dias"
            :key="dia.key"
            class="day-card"
            :class="{ 'day-active': horario[dia.key]?.activo }"
          >
            <div class="day-header">
              <span class="day-name">{{ dia.label }}</span>
              <label class="toggle">
                <input type="checkbox" v-model="horario[dia.key].activo" />
                <span class="toggle-track"></span>
              </label>
            </div>

            <div v-if="horario[dia.key]?.activo" class="day-times">
              <div class="time-field">
                <span class="time-label">Entrada</span>
                <input
                  type="time"
                  class="time-input"
                  v-model="horario[dia.key].entrada"
                />
              </div>
              <div class="time-sep">—</div>
              <div class="time-field">
                <span class="time-label">Salida</span>
                <input
                  type="time"
                  class="time-input"
                  v-model="horario[dia.key].salida"
                />
              </div>
            </div>
            <div v-else class="day-off-label">No laborable</div>
          </div>
        </div>

        <div class="save-row">
          <button
            class="btn-save"
            :class="{ 'btn-saving': savingHorario, 'btn-saved': horarioGuardado }"
            @click="guardarHorario"
            :disabled="savingHorario || horarioGuardado"
          >
            <!-- Estado: guardando — spinner -->
            <svg v-if="savingHorario" class="spinner" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>

            <!-- Estado: guardado ✓ -->
            <svg v-else-if="horarioGuardado" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="20 6 9 17 4 12"/>
            </svg>

            <!-- Estado: normal -->
            <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
              <polyline points="17 21 17 13 7 13 7 21"/>
              <polyline points="7 3 7 8 15 8"/>
            </svg>

            <span>
              {{ savingHorario ? 'Guardando…' : horarioGuardado ? '¡Guardado!' : 'Guardar horario' }}
            </span>
          </button>
        </div>
      </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════════
         TAB: MI CALENDARIO
    ═══════════════════════════════════════════════════════════ -->
    <div v-if="activeTab === 'calendario'" class="tab-content">

      <div class="cal-layout">

        <!-- Columna izquierda: calendario -->
        <div class="cal-col">
          <div class="section-card">
            <!-- Nav del mes -->
            <div class="cal-nav">
              <button class="cal-nav-btn" @click="mesAnterior">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="15 18 9 12 15 6"/>
                </svg>
              </button>
              <span class="cal-month-label">{{ mesLabel }}</span>
              <button class="cal-nav-btn" @click="mesSiguiente">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="9 18 15 12 9 6"/>
                </svg>
              </button>
            </div>

            <!-- Cabecera de días -->
            <div class="cal-grid">
              <div v-for="d in ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom']" :key="d" class="cal-weekday">{{ d }}</div>

              <!-- Celdas vacías del inicio -->
              <div v-for="n in offsetInicio" :key="'e-'+n" class="cal-cell cal-empty"></div>

              <!-- Días del mes -->
              <div
                v-for="day in diasDelMes"
                :key="day"
                class="cal-cell"
                :class="{
                  'cal-today': esHoy(day),
                  'cal-selected': diaSeleccionado === day,
                  'cal-has-events': eventosDelDia(day).length > 0
                }"
                @click="seleccionarDia(day)"
              >
                <span class="cal-day-num">{{ day }}</span>
                <div class="cal-dots">
                  <span
                    v-for="(ev, i) in eventosDelDia(day).slice(0, 3)"
                    :key="i"
                    class="cal-dot"
                    :style="{ background: colorTipo(ev.tipo) }"
                  ></span>
                </div>
              </div>
            </div>

            <!-- Leyenda -->
            <div class="cal-legend">
              <span v-for="t in tipos" :key="t.key" class="legend-item">
                <span class="legend-dot" :style="{ background: t.color }"></span>
                {{ t.label }}
              </span>
            </div>
          </div>
        </div>

        <!-- Columna derecha: eventos del día seleccionado -->
        <div class="events-col">
          <div class="section-card events-panel">
            <div class="events-header">
              <div>
                <h3 class="section-title">
                  {{ diaSeleccionado ? diaSeleccionadoLabel : 'Selecciona un día' }}
                </h3>
                <p class="section-desc" v-if="diaSeleccionado">
                  {{ eventosDelDia(diaSeleccionado).length }} evento(s)
                </p>
              </div>
              <button v-if="diaSeleccionado" class="btn-add-ev" @click="abrirModalNuevo">
                + Nuevo
              </button>
            </div>

            <div v-if="!diaSeleccionado" class="events-empty-hint">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="1.5">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
              <p>Haz clic en un día del calendario para ver o agregar eventos.</p>
            </div>

            <div v-else-if="eventosDelDia(diaSeleccionado).length === 0" class="events-empty-hint">
              <p>No hay eventos para este día.</p>
              <button class="btn-add-ev-alt" @click="abrirModalNuevo">Agregar evento</button>
            </div>

            <div v-else class="events-list">
              <div
                v-for="ev in eventosDelDia(diaSeleccionado)"
                :key="ev.evento_id"
                class="event-card"
                :style="{ borderLeftColor: colorTipo(ev.tipo) }"
              >
                <div class="event-card-top">
                  <div>
                    <span class="event-title">{{ ev.titulo }}</span>
                    <span class="event-badge" :style="{ background: colorTipo(ev.tipo) + '22', color: colorTipo(ev.tipo) }">
                      {{ labelTipo(ev.tipo) }}
                    </span>
                  </div>
                  <div class="event-actions">
                    <button class="icon-action" title="Editar" @click="abrirModalEditar(ev)">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                      </svg>
                    </button>
                    <button class="icon-action danger" title="Eliminar" @click="eliminarEvento(ev.evento_id)">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6l-1 14H6L5 6"/>
                        <path d="M10 11v6"/><path d="M14 11v6"/>
                        <path d="M9 6V4h6v2"/>
                      </svg>
                    </button>
                  </div>
                </div>
                <p v-if="ev.hora_inicio" class="event-time">
                  {{ ev.hora_inicio }}{{ ev.hora_fin ? ' – ' + ev.hora_fin : '' }}
                </p>
                <p v-if="ev.descripcion" class="event-desc">{{ ev.descripcion }}</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         MODAL EVENTO
    ═══════════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-backdrop" @click.self="showModal = false">
        <div class="modal-box">
          <div class="modal-header">
            <h3>{{ modalEditando ? 'Editar evento' : 'Nuevo evento' }}</h3>
            <button class="modal-close" @click="showModal = false">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <div class="modal-body">
            <div class="form-group">
              <label class="form-label">Título *</label>
              <input type="text" class="form-input" v-model="form.titulo" placeholder="Ej. Reunión de equipo" />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Tipo</label>
                <select class="form-input" v-model="form.tipo">
                  <option v-for="t in tipos" :key="t.key" :value="t.key">{{ t.label }}</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Fecha</label>
                <input type="date" class="form-input" v-model="form.fecha" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Hora inicio</label>
                <input type="time" class="form-input" v-model="form.hora_inicio" />
              </div>
              <div class="form-group">
                <label class="form-label">Hora fin</label>
                <input type="time" class="form-input" v-model="form.hora_fin" />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Descripción</label>
              <textarea class="form-input" rows="3" v-model="form.descripcion" placeholder="Detalles opcionales…"></textarea>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn-cancel" @click="showModal = false">Cancelar</button>
            <button class="btn-save" @click="guardarEvento" :disabled="savingEvento">
              {{ savingEvento ? 'Guardando…' : (modalEditando ? 'Guardar cambios' : 'Crear evento') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

  </FisioLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import FisioLayout from './FisioLayout.vue'
import { agendaService } from '@/services/api'

// ── Tabs ─────────────────────────────────────────────────────────────────────
const activeTab = ref('horario')

// ═══════════════════════════════════════════════════════════════════════════
// HORARIO LABORAL
// ═══════════════════════════════════════════════════════════════════════════
const dias = [
  { key: 'lun', label: 'Lunes' },
  { key: 'mar', label: 'Martes' },
  { key: 'mie', label: 'Miércoles' },
  { key: 'jue', label: 'Jueves' },
  { key: 'vie', label: 'Viernes' },
  { key: 'sab', label: 'Sábado' },
  { key: 'dom', label: 'Domingo' },
]

function horarioDefecto() {
  const h = {}
  dias.forEach(d => {
    h[d.key] = { activo: ['lun','mar','mie','jue','vie'].includes(d.key), entrada: '08:00', salida: '17:00' }
  })
  return h
}

const horario = ref(horarioDefecto())
const savingHorario  = ref(false)
const horarioGuardado = ref(false)

// ── Toast ─────────────────────────────────────────────────────────────────
const toast = ref({ visible: false, type: 'ok', title: '', msg: '' })
let toastTimer = null

function mostrarToast(type, title, msg, duracion = 4000) {
  clearTimeout(toastTimer)
  toast.value = { visible: true, type, title, msg }
  toastTimer = setTimeout(() => { toast.value.visible = false }, duracion)
}

async function cargarHorario() {
  try {
    const res = await agendaService.getHorario()

    // Sin horario configurado (null) o vacío ([]) → usar valores por defecto
    const data = res.data
    const estaVacio = !data
      || (Array.isArray(data) && data.length === 0)
      || (typeof data === 'object' && !Array.isArray(data) && Object.keys(data).length === 0)
    if (estaVacio) return

    // Detectar si viene en formato admin (claves "Lunes","Martes"... con inicio/fin)
    // y normalizar al formato fisio (claves "lun","mar"... con entrada/salida)
    const mapaAdmin = {
      'Lunes':'lun', 'Martes':'mar', 'Miércoles':'mie',
      'Jueves':'jue', 'Viernes':'vie', 'Sábado':'sab', 'Domingo':'dom'
    }
    const esFormatoAdmin = Object.keys(data).some(k => k in mapaAdmin)
    if (esFormatoAdmin) {
      const normalizado = horarioDefecto()
      for (const [diaLargo, clave] of Object.entries(mapaAdmin)) {
        if (data[diaLargo]) {
          normalizado[clave] = {
            activo:  data[diaLargo].activo ?? false,
            entrada: data[diaLargo].inicio  ?? data[diaLargo].entrada ?? '08:00',
            salida:  data[diaLargo].fin     ?? data[diaLargo].salida  ?? '17:00',
          }
        }
      }
      horario.value = normalizado
    } else {
      // Asegurar que todos los días existan (merge con default por si faltan claves)
      horario.value = { ...horarioDefecto(), ...data }
    }
  } catch (e) {
    console.error('Error al cargar horario:', e)
  }
}

async function guardarHorario() {
  if (savingHorario.value || horarioGuardado.value) return
  savingHorario.value = true
  try {
    await agendaService.saveHorario(horario.value)
    horarioGuardado.value = true
    mostrarToast('ok', '¡Horario guardado!', 'Tu horario de trabajo fue actualizado correctamente.')
    setTimeout(() => { horarioGuardado.value = false }, 2500)
  } catch {
    mostrarToast('err', 'Error al guardar', 'No se pudo guardar el horario. Intenta de nuevo.')
  } finally {
    savingHorario.value = false
  }
}

// ═══════════════════════════════════════════════════════════════════════════
// CALENDARIO
// ═══════════════════════════════════════════════════════════════════════════
const tipos = [
  { key: 'personal',     label: 'Personal',      color: '#4ade80' },
  { key: 'capacitacion', label: 'Capacitación',  color: '#60a5fa' },
  { key: 'vacaciones',   label: 'Vacaciones',    color: '#fb923c' },
  { key: 'bloqueo',      label: 'Bloqueo',       color: '#f87171' },
]

function colorTipo(tipo) {
  return tipos.find(t => t.key === tipo)?.color ?? '#6b7280'
}
function labelTipo(tipo) {
  return tipos.find(t => t.key === tipo)?.label ?? tipo
}

const hoy = new Date()
const viewYear  = ref(hoy.getFullYear())
const viewMonth = ref(hoy.getMonth()) // 0-indexed

const mesLabel = computed(() => {
  const d = new Date(viewYear.value, viewMonth.value, 1)
  return d.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' })
    .replace(/^\w/, c => c.toUpperCase())
})

const diasDelMes = computed(() => {
  const total = new Date(viewYear.value, viewMonth.value + 1, 0).getDate()
  return Array.from({ length: total }, (_, i) => i + 1)
})

// Offset: lunes = 0
const offsetInicio = computed(() => {
  const d = new Date(viewYear.value, viewMonth.value, 1).getDay()
  return d === 0 ? 6 : d - 1
})

function mesAnterior() {
  if (viewMonth.value === 0) { viewMonth.value = 11; viewYear.value-- }
  else viewMonth.value--
}
function mesSiguiente() {
  if (viewMonth.value === 11) { viewMonth.value = 0; viewYear.value++ }
  else viewMonth.value++
}

function esHoy(day) {
  return day === hoy.getDate() && viewMonth.value === hoy.getMonth() && viewYear.value === hoy.getFullYear()
}

const diaSeleccionado = ref(null)

function seleccionarDia(day) {
  diaSeleccionado.value = day
}

const diaSeleccionadoLabel = computed(() => {
  if (!diaSeleccionado.value) return ''
  const d = new Date(viewYear.value, viewMonth.value, diaSeleccionado.value)
  return d.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
    .replace(/^\w/, c => c.toUpperCase())
})

// ── Eventos ───────────────────────────────────────────────────────────────
const eventos = ref([])

async function cargarEventos() {
  try {
    const res = await agendaService.getEventos()
    eventos.value = res.data
  } catch {}
}

function fechaDelDia(day) {
  const m = String(viewMonth.value + 1).padStart(2, '0')
  const d = String(day).padStart(2, '0')
  return `${viewYear.value}-${m}-${d}`
}

function eventosDelDia(day) {
  const fecha = fechaDelDia(day)
  return eventos.value.filter(e => e.fecha === fecha)
}

// ── Modal ─────────────────────────────────────────────────────────────────
const showModal     = ref(false)
const modalEditando = ref(null)  // null = nuevo, id = editar
const savingEvento  = ref(false)

const formVacio = () => ({
  titulo: '', descripcion: '', fecha: '', hora_inicio: '', hora_fin: '', tipo: 'personal'
})
const form = ref(formVacio())

function abrirModalNuevo() {
  modalEditando.value = null
  form.value = { ...formVacio(), fecha: fechaDelDia(diaSeleccionado.value) }
  showModal.value = true
}

function abrirModalEditar(ev) {
  modalEditando.value = ev.evento_id
  form.value = {
    titulo:      ev.titulo,
    descripcion: ev.descripcion ?? '',
    fecha:       ev.fecha,
    hora_inicio: ev.hora_inicio ?? '',
    hora_fin:    ev.hora_fin ?? '',
    tipo:        ev.tipo,
  }
  showModal.value = true
}

async function guardarEvento() {
  if (!form.value.titulo.trim()) return
  savingEvento.value = true
  try {
    if (modalEditando.value) {
      await agendaService.updateEvento(modalEditando.value, form.value)
    } else {
      await agendaService.createEvento(form.value)
    }
    await cargarEventos()
    showModal.value = false
  } catch {}
  finally { savingEvento.value = false }
}

async function eliminarEvento(id) {
  if (!confirm('¿Eliminar este evento?')) return
  try {
    await agendaService.deleteEvento(id)
    await cargarEventos()
  } catch {}
}

// ── Init ──────────────────────────────────────────────────────────────────
onMounted(() => {
  cargarHorario()
  cargarEventos()
})
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── Header ── */
.dash-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}
.page-title { font-size: 1.5rem; font-weight: 700; color: #f4f4f5; }
.page-sub   { font-size: 0.82rem; color: #6b7280; margin-top: 2px; }

/* ── Tabs ── */
.tabs-bar {
  display: flex;
  gap: 4px;
  margin-bottom: 1.5rem;
  background: #111;
  border: 1px solid #1c1c1c;
  border-radius: 10px;
  padding: 4px;
  width: fit-content;
}
.tab-btn {
  display: flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.5rem 1.1rem;
  border: none;
  border-radius: 7px;
  background: transparent;
  color: #6b7280;
  font-size: 0.83rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}
.tab-btn:hover { color: #d1d5db; background: rgba(255,255,255,0.04); }
.tab-btn.active { background: rgba(74,222,128,0.12); color: #4ade80; }

.tab-content { animation: fadeIn 0.18s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }

/* ── Cards ── */
.section-card {
  background: #111;
  border: 1px solid #1c1c1c;
  border-radius: 12px;
  padding: 1.5rem;
}
.section-card-header { margin-bottom: 1.25rem; }
.section-title { font-size: 1rem; font-weight: 600; color: #e4e4e7; margin-bottom: 3px; }
.section-desc  { font-size: 0.78rem; color: #6b7280; }

/* ═══════════════════════════════════════════════════
   HORARIO LABORAL
═══════════════════════════════════════════════════ */
.days-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.day-card {
  background: #161616;
  border: 1px solid #222;
  border-radius: 10px;
  padding: 1rem;
  transition: border-color 0.2s;
}
.day-card.day-active {
  border-color: rgba(74,222,128,0.25);
  background: rgba(74,222,128,0.04);
}

.day-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}
.day-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: #e4e4e7;
}

/* Toggle */
.toggle { position: relative; display: inline-block; width: 38px; height: 22px; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-track {
  position: absolute;
  inset: 0;
  background: #333;
  border-radius: 11px;
  cursor: pointer;
  transition: background 0.2s;
}
.toggle-track::before {
  content: '';
  position: absolute;
  width: 16px; height: 16px;
  left: 3px; top: 3px;
  background: #fff;
  border-radius: 50%;
  transition: transform 0.2s;
}
.toggle input:checked + .toggle-track { background: #4ade80; }
.toggle input:checked + .toggle-track::before { transform: translateX(16px); }

.day-times {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.time-field { display: flex; flex-direction: column; gap: 2px; flex: 1; }
.time-label { font-size: 0.68rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.04em; }
.time-input {
  background: #1a1a1a;
  border: 1px solid #2a2a2a;
  border-radius: 6px;
  color: #e4e4e7;
  padding: 0.35rem 0.5rem;
  font-size: 0.82rem;
  width: 100%;
  outline: none;
  color-scheme: dark;
}
.time-input:focus { border-color: rgba(74,222,128,0.4); }
.time-sep { color: #444; font-size: 0.9rem; margin-top: 14px; }

.day-off-label { font-size: 0.76rem; color: #4b5563; font-style: italic; }

.save-row {
  display: flex;
  align-items: center;
  justify-content: flex-end;
}

.btn-save {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #4ade80;
  color: #052e16;
  border: none;
  border-radius: 8px;
  padding: 0.6rem 1.35rem;
  font-size: 0.85rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
  box-shadow: 0 2px 12px rgba(74,222,128,0.25);
}
.btn-save:hover:not(:disabled) {
  background: #22c55e;
  transform: translateY(-1px);
  box-shadow: 0 4px 18px rgba(74,222,128,0.35);
}
.btn-save:active:not(:disabled) { transform: translateY(0); }

.btn-save.btn-saving {
  background: #374151;
  color: #9ca3af;
  cursor: not-allowed;
  box-shadow: none;
}
.btn-save.btn-saved {
  background: #166534;
  color: #bbf7d0;
  cursor: default;
  box-shadow: 0 2px 12px rgba(74,222,128,0.15);
}

/* Spinner animation */
@keyframes spin { to { transform: rotate(360deg); } }
.spinner { animation: spin 0.8s linear infinite; }

/* ── Toast ── */
.toast-notif {
  position: fixed;
  bottom: 1.75rem;
  right: 1.75rem;
  z-index: 9999;
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem 1.1rem;
  border-radius: 12px;
  min-width: 300px;
  max-width: 400px;
  box-shadow: 0 12px 40px rgba(0,0,0,0.5);
  border: 1px solid transparent;
}
.toast-notif.ok {
  background: #052e16;
  border-color: rgba(74,222,128,0.35);
}
.toast-notif.err {
  background: #2d1111;
  border-color: rgba(248,113,113,0.35);
}

.toast-icon {
  flex-shrink: 0;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 1px;
}
.toast-notif.ok .toast-icon  { background: rgba(74,222,128,0.15); color: #4ade80; }
.toast-notif.err .toast-icon { background: rgba(248,113,113,0.15); color: #f87171; }

.toast-body { flex: 1; min-width: 0; }
.toast-title {
  font-size: 0.85rem;
  font-weight: 700;
  margin-bottom: 2px;
}
.toast-notif.ok  .toast-title { color: #4ade80; }
.toast-notif.err .toast-title { color: #f87171; }

.toast-msg {
  font-size: 0.78rem;
  color: #9ca3af;
  line-height: 1.4;
}

.toast-close {
  flex-shrink: 0;
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 2px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  transition: color 0.15s;
  margin-top: 1px;
}
.toast-close:hover { color: #d1d5db; }

/* Transición del toast */
.toast-enter-active { animation: toastIn 0.3s ease; }
.toast-leave-active { animation: toastOut 0.25s ease forwards; }
@keyframes toastIn {
  from { opacity: 0; transform: translateY(16px) scale(0.96); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes toastOut {
  from { opacity: 1; transform: translateY(0) scale(1); }
  to   { opacity: 0; transform: translateY(8px) scale(0.96); }
}

/* ═══════════════════════════════════════════════════
   CALENDARIO
═══════════════════════════════════════════════════ */
.cal-layout {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 1rem;
  align-items: start;
}

/* Navegación del mes */
.cal-nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}
.cal-month-label {
  font-size: 0.95rem;
  font-weight: 600;
  color: #e4e4e7;
  text-transform: capitalize;
}
.cal-nav-btn {
  background: none;
  border: 1px solid #222;
  border-radius: 6px;
  color: #6b7280;
  cursor: pointer;
  padding: 0.3rem 0.4rem;
  display: flex;
  align-items: center;
  transition: background 0.15s, color 0.15s;
}
.cal-nav-btn:hover { background: rgba(255,255,255,0.06); color: #d1d5db; }

/* Grid del calendario */
.cal-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 3px;
  margin-bottom: 1rem;
}
.cal-weekday {
  text-align: center;
  font-size: 0.68rem;
  font-weight: 600;
  color: #6b7280;
  padding: 0.25rem 0;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.cal-cell {
  aspect-ratio: 1;
  border-radius: 7px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  gap: 3px;
  border: 1px solid transparent;
  transition: background 0.12s, border-color 0.12s;
}
.cal-cell:hover { background: rgba(255,255,255,0.04); }
.cal-empty { cursor: default; }
.cal-empty:hover { background: none; }

.cal-today .cal-day-num {
  background: #4ade80;
  color: #052e16;
  border-radius: 50%;
  width: 22px;
  height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}
.cal-selected {
  border-color: rgba(74,222,128,0.4);
  background: rgba(74,222,128,0.07);
}
.cal-day-num {
  font-size: 0.78rem;
  color: #d1d5db;
  font-weight: 500;
  line-height: 1;
}
.cal-dots {
  display: flex;
  gap: 2px;
}
.cal-dot {
  width: 4px;
  height: 4px;
  border-radius: 50%;
}

/* Leyenda */
.cal-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  border-top: 1px solid #1c1c1c;
  padding-top: 0.75rem;
}
.legend-item {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.72rem;
  color: #6b7280;
}
.legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

/* Panel de eventos */
.events-panel { height: 100%; }
.events-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1rem;
}
.btn-add-ev {
  background: rgba(74,222,128,0.12);
  color: #4ade80;
  border: 1px solid rgba(74,222,128,0.2);
  border-radius: 7px;
  padding: 0.4rem 0.8rem;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
  white-space: nowrap;
}
.btn-add-ev:hover { background: rgba(74,222,128,0.2); }

.events-empty-hint {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 2rem 0;
  color: #4b5563;
  font-size: 0.82rem;
  text-align: center;
}
.btn-add-ev-alt {
  background: rgba(74,222,128,0.1);
  color: #4ade80;
  border: 1px solid rgba(74,222,128,0.2);
  border-radius: 7px;
  padding: 0.45rem 1rem;
  font-size: 0.8rem;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-add-ev-alt:hover { background: rgba(74,222,128,0.2); }

.events-list { display: flex; flex-direction: column; gap: 0.6rem; }

.event-card {
  background: #161616;
  border: 1px solid #222;
  border-left: 3px solid #4ade80;
  border-radius: 8px;
  padding: 0.75rem;
}
.event-card-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.5rem;
  margin-bottom: 4px;
}
.event-title {
  font-size: 0.85rem;
  font-weight: 600;
  color: #e4e4e7;
  display: block;
  margin-bottom: 3px;
}
.event-badge {
  font-size: 0.65rem;
  font-weight: 600;
  padding: 2px 7px;
  border-radius: 99px;
  display: inline-block;
}
.event-actions { display: flex; gap: 4px; flex-shrink: 0; }
.icon-action {
  background: none;
  border: 1px solid #222;
  border-radius: 5px;
  color: #6b7280;
  cursor: pointer;
  padding: 3px 5px;
  display: flex;
  align-items: center;
  transition: background 0.12s, color 0.12s;
}
.icon-action:hover { background: rgba(255,255,255,0.05); color: #d1d5db; }
.icon-action.danger:hover { background: rgba(248,113,113,0.1); color: #f87171; border-color: rgba(248,113,113,0.3); }

.event-time { font-size: 0.73rem; color: #6b7280; margin-bottom: 3px; }
.event-desc { font-size: 0.76rem; color: #9ca3af; line-height: 1.4; }

/* ═══════════════════════════════════════════════════
   MODAL
═══════════════════════════════════════════════════ */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}
.modal-box {
  background: #141414;
  border: 1px solid #222;
  border-radius: 14px;
  width: 100%;
  max-width: 480px;
  overflow: hidden;
  box-shadow: 0 25px 60px rgba(0,0,0,0.6);
  animation: slideUp 0.2s ease;
}
@keyframes slideUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.1rem 1.25rem;
  border-bottom: 1px solid #1c1c1c;
}
.modal-header h3 { font-size: 0.95rem; font-weight: 600; color: #e4e4e7; }
.modal-close {
  background: none; border: none; color: #6b7280; cursor: pointer;
  display: flex; align-items: center; padding: 3px; border-radius: 4px;
}
.modal-close:hover { color: #d1d5db; }

.modal-body { padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem; }
.modal-footer {
  display: flex; justify-content: flex-end; gap: 0.5rem;
  padding: 1rem 1.25rem;
  border-top: 1px solid #1c1c1c;
}

.form-group { display: flex; flex-direction: column; gap: 5px; }
.form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.form-label { font-size: 0.76rem; color: #9ca3af; font-weight: 500; }
.form-input {
  background: #1a1a1a;
  border: 1px solid #2a2a2a;
  border-radius: 7px;
  color: #e4e4e7;
  padding: 0.5rem 0.7rem;
  font-size: 0.83rem;
  outline: none;
  width: 100%;
  color-scheme: dark;
  font-family: inherit;
  resize: vertical;
}
.form-input:focus { border-color: rgba(74,222,128,0.4); }

.btn-cancel {
  background: none;
  border: 1px solid #2a2a2a;
  border-radius: 8px;
  color: #6b7280;
  padding: 0.5rem 1rem;
  font-size: 0.82rem;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}
.btn-cancel:hover { background: rgba(255,255,255,0.05); color: #d1d5db; }

@media (max-width: 900px) {
  .cal-layout { grid-template-columns: 1fr; }
  .days-grid  { grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); }
}
</style>
