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
          <span v-if="hayFiltrosActivos" class="table-count">
            {{ citasFiltradas.length }} cita{{ citasFiltradas.length !== 1 ? 's' : '' }}
          </span>
        </div>

        <!-- Filtros adicionales -->
        <div class="filtros-bar">
          <div class="filtro-group">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <select v-model="filtroFisio" class="filtro-select">
              <option value="">Todos los especialistas</option>
              <option v-for="f in fisioterapeutas" :key="f.fisioterapeuta_id" :value="f.fisioterapeuta_id">
                {{ f.nombre }} {{ f.apellido }}
              </option>
            </select>
          </div>

          <div class="filtro-group">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <select v-model="filtroPaciente" class="filtro-select">
              <option value="">Todos los pacientes</option>
              <option v-for="p in pacientes" :key="p.paciente_id" :value="p.paciente_id">
                {{ p.nombre }} {{ p.apellido }}
              </option>
            </select>
          </div>

          <div class="filtro-group">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
            <select v-model="filtroEstado" class="filtro-select">
              <option value="">Todos los estados</option>
              <option value="programada">Programada</option>
              <option value="atendida">Atendida</option>
              <option value="cancelada">Cancelada</option>
            </select>
          </div>

          <button v-if="hayFiltrosActivos" class="btn-limpiar-filtros" @click="limpiarTodosFiltros">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
            Limpiar filtros
          </button>
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
                  <button class="btn-accion btn-accion-delete" @click="openDeleteModal(cita)" title="Eliminar cita">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="3 6 5 6 21 6"/>
                      <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                      <path d="M10 11v6M14 11v6"/>
                      <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                    </svg>
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
              <select v-model="form.paciente_id" required @change="onPacienteChange">
                <option value="">-- Seleccione un paciente --</option>
                <option v-for="p in pacientes" :key="p.paciente_id" :value="p.paciente_id">
                  {{ p.nombre }} {{ p.apellido }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Fisioterapeuta *</label>

              <!-- Paciente tiene especialista asignado → solo lectura -->
              <template v-if="fisioAsignadoPaciente">
                <div class="fisio-asignado-box">
                  <div class="fa-info">
                    <span class="fa-nombre">{{ nombreFisio(fisioAsignadoPaciente) }}</span>
                    <span class="fa-badge">Especialista asignado</span>
                  </div>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                  </svg>
                </div>
              </template>

              <!-- Sin asignación → select libre -->
              <template v-else>
                <select v-model="form.fisioterapeuta_id" required @change="() => { form.hora = ''; validarFecha() }">
                  <option value="">-- Seleccione un especialista --</option>
                  <option v-for="f in fisioterapeutas" :key="f.fisioterapeuta_id" :value="f.fisioterapeuta_id">
                    {{ f.nombre }} {{ f.apellido }} — {{ f.especialidad }}
                  </option>
                </select>
                <span v-if="!form.paciente_id" class="field-hint">Selecciona primero el paciente para auto-asignar su especialista.</span>
                <span v-else class="field-hint warn-hint">Este paciente no tiene especialista asignado. Asígnalo en la sección Pacientes.</span>
              </template>
            </div>

            <!-- Fecha -->
            <div class="form-group">
              <label>Fecha *</label>
              <input v-model="form.fecha" type="date" required @change="onFechaChange" />
              <span v-if="advertenciaFecha" class="field-warn">{{ advertenciaFecha }}</span>
            </div>

            <!-- Selector de hora -->
            <div class="form-group">
              <label>
                Hora *
                <span v-if="rangoHorario" class="label-hint">{{ rangoHorario }}</span>
              </label>

              <!-- Slot picker: fisio + fecha seleccionados y hay horario configurado -->
              <template v-if="slotsDelDia.length > 0">
                <div v-if="slotsDelDia.every(s => s.ocupado)" class="slots-empty">
                  No hay horarios disponibles para este día.
                </div>
                <div v-else class="slots-grid">
                  <button
                    v-for="s in slotsDelDia"
                    :key="s.hora"
                    type="button"
                    class="slot-btn"
                    :class="{
                      'slot-selected':  form.hora === s.hora,
                      'slot-ocupado':   s.ocupado,
                      'slot-disponible': !s.ocupado,
                    }"
                    :disabled="s.ocupado"
                    @click="seleccionarSlot(s.hora)"
                  >
                    {{ formatSlotLabel(s.hora) }}
                    <span v-if="s.ocupado" class="slot-tag">Ocupado</span>
                  </button>
                </div>
                <span v-if="!form.hora && form.fecha" class="field-warn">Selecciona una hora disponible.</span>
              </template>

              <!-- Fallback: sin horario configurado para este día -->
              <template v-else>
                <div class="time-spinner">
                  <div class="ts-col">
                    <button type="button" class="ts-btn" @click="adjustHour(1)">▲</button>
                    <span class="ts-val">{{ spinnerHH }}</span>
                    <button type="button" class="ts-btn" @click="adjustHour(-1)">▼</button>
                  </div>
                  <span class="ts-sep">:</span>
                  <div class="ts-col">
                    <button type="button" class="ts-btn" @click="adjustMin(1)">▲</button>
                    <span class="ts-val">{{ spinnerMM }}</span>
                    <button type="button" class="ts-btn" @click="adjustMin(-1)">▼</button>
                  </div>
                  <div class="ts-col ts-ampm">
                    <button type="button" class="ts-btn" @click="toggleAmPm">▲</button>
                    <span class="ts-val">{{ spinnerAmPm }}</span>
                    <button type="button" class="ts-btn" @click="toggleAmPm">▼</button>
                  </div>
                </div>
                <span v-if="advertenciaHora" class="field-warn">{{ advertenciaHora }}</span>
                <span v-if="!form.fisioterapeuta_id || !form.fecha" class="field-hint">
                  Selecciona fisioterapeuta y fecha para ver los horarios disponibles.
                </span>
              </template>
            </div>

            <div class="form-group">
              <label>Motivo</label>
              <textarea v-model="form.motivo" rows="3" placeholder="Ej. Terapia de rehabilitación..."></textarea>
            </div>

            <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>

            <div class="modal-actions">
              <button type="submit" class="btn-guardar" :disabled="saving || bloqueoFecha || bloqueoHora || (slotsDelDia.length > 0 && !form.hora)">
                {{ saving ? 'Guardando...' : (editingCita ? 'Guardar Cambios' : 'Agendar Cita') }}
              </button>
              <button type="button" class="btn-cancelar" @click="closeModal">Cancelar</button>
            </div>

          </form>
        </div>
      </div>
    </Teleport>

    <!-- Modal: Confirmar Eliminación -->
    <Teleport to="body">
      <div class="modal-overlay" v-if="showDeleteModal" @click.self="showDeleteModal = false">
        <div class="modal modal-sm">
          <div class="modal-header">
            <h3>Eliminar Cita</h3>
            <button class="modal-close" @click="showDeleteModal = false">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <div class="estado-modal-info" v-if="citaAEliminar">
            <div class="info-row">
              <span class="info-label">Paciente</span>
              <span class="info-val">{{ nombrePaciente(citaAEliminar.paciente_id) }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Fisioterapeuta</span>
              <span class="info-val">{{ nombreFisio(citaAEliminar.fisioterapeuta_id) }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Fecha y hora</span>
              <span class="info-val td-fecha">{{ formatFecha(citaAEliminar.fecha_hora) }}</span>
            </div>
          </div>

          <p class="delete-warning">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            Esta acción eliminará la cita permanentemente y no se puede deshacer.
          </p>

          <div class="modal-actions" style="margin-top:1rem;">
            <button class="btn-confirmar-eliminar" :disabled="deleting" @click="eliminarCita">
              {{ deleting ? 'Eliminando...' : 'Sí, eliminar cita' }}
            </button>
            <button class="btn-cancelar" @click="showDeleteModal = false">Cancelar</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Toast de aviso de horario (no bloquea, solo informa) -->
    <Teleport to="body">
      <Transition name="aviso-toast">
        <div v-if="avisoToast" class="aviso-toast-wrap" @click="avisoToast = ''">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
          <div>
            <p class="avt-title">Cita guardada con aviso</p>
            <p class="avt-msg">{{ avisoToast }}</p>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Toast de éxito -->
    <Teleport to="body">
      <Transition name="aviso-toast">
        <div v-if="successToast" class="success-toast-wrap" @click="successToast = ''">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
          <div>
            <p class="avt-title">¡Listo!</p>
            <p class="avt-msg" style="color:#86efac">{{ successToast }}</p>
          </div>
        </div>
      </Transition>
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
            <select v-model="nuevoEstado" required>
              <option value="" disabled>-- Selecciona un nuevo estado --</option>
              <option v-if="citaSeleccionada?.estado !== 'programada'" value="programada">Programada</option>
              <option v-if="citaSeleccionada?.estado !== 'atendida'"   value="atendida">Atendida</option>
              <option v-if="citaSeleccionada?.estado !== 'cancelada'"  value="cancelada">Cancelada (libera el horario)</option>
            </select>
          </div>

          <p v-if="errorEstado" class="error-msg" style="margin-top:0.75rem;">{{ errorEstado }}</p>

          <div class="modal-actions" style="margin-top:1.25rem;">
            <button class="btn-guardar" @click="saveEstado" :disabled="updatingEstado || !nuevoEstado">
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
import { ref, computed, onMounted, watch } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import { citaService, pacienteService, fisioterapeutaService } from '@/services/api'

const citas      = ref([])
const pacientes  = ref([])
const loading    = ref(true)
const showModal    = ref(false)
const saving       = ref(false)
const errorMsg     = ref('')
const editingCita  = ref(null)
const avisoToast   = ref('')
let   avisoTimer   = null
const successToast = ref('')
let   successTimer = null

const showEstadoModal  = ref(false)
const citaSeleccionada = ref(null)
const nuevoEstado      = ref('')
const updatingEstado   = ref(false)
const errorEstado      = ref('')

const fisioterapeutas = ref([])

const form = ref({ paciente_id: '', fisioterapeuta_id: '', fecha: '', hora: '', motivo: '' })
const showDeleteModal = ref(false)
const citaAEliminar   = ref(null)
const deleting        = ref(false)

// ── Validación de horario del fisioterapeuta (frontend) ───────────────────
const advertenciaFecha  = ref('')
const advertenciaHora   = ref('')
const bloqueoFecha      = ref(false)  // true = día no laborable, bloquea submit
const bloqueoHora       = ref(false)  // true = hora fuera de rango, bloquea submit
const horaMin           = ref('')
const horaMax           = ref('')
const rangoHorario      = ref('')

const DIAS_KEY   = ['dom', 'lun', 'mar', 'mie', 'jue', 'vie', 'sab']
const MAPA_ADMIN = { 'Lunes':'lun','Martes':'mar','Miércoles':'mie','Jueves':'jue','Viernes':'vie','Sábado':'sab','Domingo':'dom' }

// ── Slot picker ───────────────────────────────────────────────────────────────
// Devuelve la config normalizada {activo,entrada,salida} para el día seleccionado
function confDiaNormalizada() {
  if (!form.value.fisioterapeuta_id || !form.value.fecha) return null
  const f = fisioterapeutas.value.find(f => f.fisioterapeuta_id == form.value.fisioterapeuta_id)
  if (!f || !f.horario) return null
  const h = typeof f.horario === 'string' ? JSON.parse(f.horario) : f.horario
  const norm = {}
  for (const [k, v] of Object.entries(h)) {
    const clave = MAPA_ADMIN[k] ?? k
    norm[clave] = { activo: v.activo ?? false, entrada: v.entrada ?? v.inicio ?? null, salida: v.salida ?? v.fin ?? null }
  }
  const [y, m, d] = form.value.fecha.split('-').map(Number)
  const diaClave  = DIAS_KEY[new Date(y, m - 1, d).getDay()]
  const conf      = norm[diaClave]
  return (conf?.activo && conf.entrada && conf.salida) ? conf : null
}

// Slots de 30 min dentro del horario, con flag de ocupado
const slotsDelDia = computed(() => {
  const conf = confDiaNormalizada()
  if (!conf) return []

  const [hIn, mIn]   = conf.entrada.split(':').map(Number)
  const [hOut, mOut] = conf.salida.split(':').map(Number)
  let   cur  = hIn * 60 + mIn
  const end  = hOut * 60 + mOut

  // Citas programadas del mismo fisio en este día (excluye la que se edita)
  const editId = editingCita.value?.cita_id
  const citasDia = citas.value.filter(c =>
    c.fisioterapeuta_id == form.value.fisioterapeuta_id &&
    c.estado === 'programada' &&
    c.fecha_hora?.startsWith(form.value.fecha) &&
    c.cita_id !== editId
  )

  const slots = []
  while (cur + 60 <= end) {           // cada slot requiere 60 min
    const hh   = String(Math.floor(cur / 60)).padStart(2, '0')
    const mm   = String(cur % 60).padStart(2, '0')
    const hora = `${hh}:${mm}`

    // Un slot en T está ocupado si [T, T+60] solapa con alguna cita [cStart, cStart+60]
    const ocupado = citasDia.some(c => {
      const [, t]  = (c.fecha_hora ?? '').split(' ')
      const [ch, cm] = (t ?? '00:00').split(':').map(Number)
      const cStart = ch * 60 + cm
      return cur < cStart + 60 && cur + 60 > cStart
    })

    slots.push({ hora, ocupado })
    cur += 30
  }
  return slots
})

function seleccionarSlot(hora) {
  form.value.hora = hora
  advertenciaHora.value = ''
  bloqueoHora.value     = false
}

function onFechaChange() {
  form.value.hora = ''   // resetear hora al cambiar fecha
  validarFecha()
}

function formatSlotLabel(hora) {
  const [h, m] = hora.split(':').map(Number)
  const ampm   = h >= 12 ? 'p.m.' : 'a.m.'
  const hh     = h % 12 || 12
  return `${hh}:${String(m).padStart(2,'0')} ${ampm}`
}

function horarioFisioActual() {
  if (!form.value.fisioterapeuta_id) return null
  const f = fisioterapeutas.value.find(f => f.fisioterapeuta_id == form.value.fisioterapeuta_id)
  if (!f || !f.horario) return null
  return typeof f.horario === 'string' ? JSON.parse(f.horario) : f.horario
}

function validarFecha() {
  advertenciaFecha.value = ''
  bloqueoFecha.value     = false
  horaMin.value          = ''
  horaMax.value          = ''
  rangoHorario.value     = ''
  advertenciaHora.value  = ''
  bloqueoHora.value      = false
  if (!form.value.fecha) return

  const horario = horarioFisioActual()
  if (!horario) return

  const [y, m, d] = form.value.fecha.split('-').map(Number)
  const diaSemana = new Date(y, m - 1, d).getDay()
  const clave = DIAS_KEY[diaSemana]
  const conf  = horario[clave]

  if (conf !== undefined && conf !== null && !conf.activo) {
    const NOMBRES = { lun:'lunes', mar:'martes', mie:'miércoles', jue:'jueves', vie:'viernes', sab:'sábado', dom:'domingo' }
    advertenciaFecha.value = `El fisioterapeuta no atiende los ${NOMBRES[clave] ?? clave}. No es posible agendar en este día.`
    bloqueoFecha.value = true
    return
  }

  if (conf?.entrada) horaMin.value = conf.entrada
  if (conf?.salida)  horaMax.value = conf.salida
  if (conf?.entrada && conf?.salida) rangoHorario.value = `${conf.entrada} – ${conf.salida}`

  validarHora()
}

function validarHora() {
  advertenciaHora.value = ''
  bloqueoHora.value     = false
  if (!form.value.hora || !horaMin.value) return
  if (form.value.hora < horaMin.value) {
    advertenciaHora.value = `Fuera del horario: el fisioterapeuta atiende desde las ${horaMin.value}.`
    bloqueoHora.value = true
  } else if (horaMax.value && form.value.hora >= horaMax.value) {
    advertenciaHora.value = `Fuera del horario: el fisioterapeuta atiende hasta las ${horaMax.value}.`
    bloqueoHora.value = true
  }
}

// ── Time Spinner ──────────────────────────────────────────────────────────────
const spinnerH   = ref(8)   // 1-12
const spinnerMin = ref(0)   // 0-59 in steps of 5
const spinnerPm  = ref(false)

const spinnerHH   = computed(() => String(spinnerH.value).padStart(2, '0'))
const spinnerMM   = computed(() => String(spinnerMin.value).padStart(2, '0'))
const spinnerAmPm = computed(() => spinnerPm.value ? 'p.m.' : 'a.m.')

function syncSpinnerToForm() {
  let h = spinnerH.value % 12
  if (spinnerPm.value) h += 12
  form.value.hora = `${String(h).padStart(2, '0')}:${spinnerMM.value}`
  validarHora()
}

function adjustHour(delta) {
  spinnerH.value = ((spinnerH.value - 1 + delta + 12) % 12) + 1
  syncSpinnerToForm()
}

function adjustMin(delta) {
  spinnerMin.value = (spinnerMin.value + delta * 5 + 60) % 60
  syncSpinnerToForm()
}

function toggleAmPm() {
  spinnerPm.value = !spinnerPm.value
  syncSpinnerToForm()
}

// Inicializar spinner con hora actual cuando se abre el modal
watch(() => form.value.hora, (val) => {
  if (!val) return
  const [hh, mm] = val.split(':').map(Number)
  spinnerPm.value = hh >= 12
  spinnerH.value  = hh % 12 || 12
  spinnerMin.value = Math.round(mm / 5) * 5 % 60
}, { immediate: false })

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
  // No limpia filtroFisio/filtroPaciente/filtroEstado — esos persisten
}

// ── Auto-asignación de fisioterapeuta por paciente ────────────────────────────

// Retorna el fisioterapeuta_id asignado al paciente seleccionado (o null)
const fisioAsignadoPaciente = computed(() => {
  if (!form.value.paciente_id) return null
  const p = pacientes.value.find(p => p.paciente_id == form.value.paciente_id)
  return p?.fisioterapeuta_id || null
})

function onPacienteChange() {
  const fisioId = fisioAsignadoPaciente.value
  form.value.fisioterapeuta_id = fisioId ?? ''
  form.value.hora = ''
  resetAdvertencias()
  if (fisioId) validarFecha()
}

// ── Filtros adicionales ───────────────────────────────────────────────────────

const filtroFisio    = ref('')
const filtroPaciente = ref('')
const filtroEstado   = ref('')

const hayFiltrosActivos = computed(() =>
  !!fechaSeleccionada.value || !!filtroFisio.value || !!filtroPaciente.value || !!filtroEstado.value
)

function limpiarTodosFiltros() {
  fechaSeleccionada.value = null
  filtroFisio.value       = ''
  filtroPaciente.value    = ''
  filtroEstado.value      = ''
}

// ── Lista filtrada y ordenada ─────────────────────────────────────────────────

const citasFiltradas = computed(() => {
  let result = citas.value.slice().sort((a, b) => new Date(b.fecha_hora) - new Date(a.fecha_hora))
  if (fechaSeleccionada.value)
    result = result.filter(c => c.fecha_hora && c.fecha_hora.slice(0, 10) === fechaSeleccionada.value)
  if (filtroFisio.value)
    result = result.filter(c => c.fisioterapeuta_id == filtroFisio.value)
  if (filtroPaciente.value)
    result = result.filter(c => c.paciente_id == filtroPaciente.value)
  if (filtroEstado.value)
    result = result.filter(c => c.estado === filtroEstado.value)
  return result
})

// ── Modales ───────────────────────────────────────────────────────────────────

// ── Banner de horario del fisio seleccionado ─────────────────────────────
const horarioBanner = computed(() => {
  if (!form.value.fisioterapeuta_id) return null
  const f = fisioterapeutas.value.find(f => f.fisioterapeuta_id == form.value.fisioterapeuta_id)
  if (!f || !f.horario) return null
  const h = typeof f.horario === 'string' ? JSON.parse(f.horario) : f.horario
  const NOMBRES = { lun:'Lun', mar:'Mar', mie:'Mié', jue:'Jue', vie:'Vie', sab:'Sáb', dom:'Dom' }
  const activos = ['lun','mar','mie','jue','vie','sab','dom']
    .filter(k => h[k]?.activo)
    .map(k => `${NOMBRES[k]} ${h[k].entrada}–${h[k].salida}`)
  return activos.length ? activos : null
})

function resetAdvertencias() {
  advertenciaFecha.value = ''
  advertenciaHora.value  = ''
  bloqueoFecha.value     = false
  bloqueoHora.value      = false
  horaMin.value = ''
  horaMax.value = ''
  rangoHorario.value = ''
}

async function recargarFisioterapeutas() {
  try {
    const res = await fisioterapeutaService.getAll()
    fisioterapeutas.value = res.data.filter(f => f.activo)
  } catch {}
}

async function openModal() {
  editingCita.value = null
  spinnerH.value   = 8
  spinnerMin.value = 0
  spinnerPm.value  = false
  form.value = { paciente_id: '', fisioterapeuta_id: '', fecha: '', hora: '08:00', motivo: '' }
  errorMsg.value = ''
  resetAdvertencias()
  await recargarFisioterapeutas()
  showModal.value = true
}

async function openEditModal(cita) {
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
  errorMsg.value = ''
  resetAdvertencias()
  await recargarFisioterapeutas()
  showModal.value = true
  validarFecha()
}

function closeModal() {
  showModal.value   = false
  editingCita.value = null
}

function openDeleteModal(cita) {
  citaAEliminar.value  = cita
  showDeleteModal.value = true
}

async function eliminarCita() {
  deleting.value = true
  try {
    await citaService.delete(citaAEliminar.value.cita_id)
    await cargarDatos()
    showDeleteModal.value = false
    citaAEliminar.value   = null
    clearTimeout(successTimer)
    successToast.value = 'Cita eliminada correctamente.'
    successTimer = setTimeout(() => { successToast.value = '' }, 4000)
  } catch {
    // mantener modal abierto si falla
  } finally {
    deleting.value = false
  }
}

function openEstadoModal(cita) {
  citaSeleccionada.value = cita
  nuevoEstado.value      = ''   // sin preselección — el estado actual no aparece como opción
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
    if (fisiosRes.status === 'fulfilled')    fisioterapeutas.value = fisiosRes.value.data.filter(f => f.activo)
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
    let res
    if (editingCita.value) {
      res = await citaService.update(editingCita.value.cita_id, payload)
    } else {
      res = await citaService.create(payload)
    }
    await cargarDatos()
    closeModal()
    // Toast de éxito
    clearTimeout(successTimer)
    successToast.value = editingCita.value ? 'Cita actualizada con éxito.' : 'Cita agendada con éxito.'
    successTimer = setTimeout(() => { successToast.value = '' }, 4000)
    // Si el backend devuelve un aviso de horario, mostrarlo como toast
    if (res?.data?.aviso) {
      clearTimeout(avisoTimer)
      avisoToast.value = res.data.aviso
      avisoTimer = setTimeout(() => { avisoToast.value = '' }, 6000)
    }
  } catch (err) {
    const msg = err?.response?.data?.message || ''
    errorMsg.value = msg || 'Error al guardar la cita. Verifica los datos e inténtalo de nuevo.'
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

/* ── Filtros bar ── */
.filtros-bar {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-wrap: wrap;
  margin-bottom: 0.9rem;
  padding-bottom: 0.9rem;
  border-bottom: 1px solid #1c1c1c;
}

.filtro-group {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  background: #0d0d0d;
  border: 1px solid #1c1c1c;
  border-radius: 7px;
  padding: 0.35rem 0.65rem;
  color: #6b7280;
  transition: border-color 0.15s;
}
.filtro-group:focus-within {
  border-color: #074434;
}

.filtro-select {
  background: transparent;
  border: none;
  outline: none;
  color: #d1d5db;
  font-size: 0.8rem;
  font-family: inherit;
  cursor: pointer;
  min-width: 160px;
}
.filtro-select option { background: #111111; }

.btn-limpiar-filtros {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: rgba(239,68,68,0.07);
  border: 1px solid rgba(239,68,68,0.2);
  border-radius: 6px;
  color: #f87171;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.35rem 0.7rem;
  cursor: pointer;
  transition: background 0.15s;
  font-family: inherit;
  margin-left: auto;
}
.btn-limpiar-filtros:hover { background: rgba(239,68,68,0.14); }

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

.field-warn {
  font-size: 0.72rem;
  color: #fb923c;
  margin-top: 3px;
  display: block;
}

.label-hint {
  font-size: 0.68rem;
  color: #4ade80;
  font-weight: 400;
  margin-left: 6px;
}

/* ── Slot picker ── */
.slots-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.45rem;
  margin-top: 0.25rem;
}

.slot-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.38rem 0.75rem;
  border-radius: 6px;
  border: 1px solid #2a2a2a;
  background: #0d0d0d;
  color: #9ca3af;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.15s, border-color 0.15s, color 0.15s;
  white-space: nowrap;
}

.slot-disponible:hover {
  background: rgba(74,222,128,0.08);
  border-color: rgba(74,222,128,0.4);
  color: #4ade80;
}

.slot-selected {
  background: rgba(74,222,128,0.15) !important;
  border-color: #4ade80 !important;
  color: #4ade80 !important;
  font-weight: 700;
}

.slot-ocupado {
  opacity: 0.4;
  cursor: not-allowed;
  text-decoration: line-through;
}

.slot-tag {
  font-size: 0.62rem;
  font-weight: 600;
  color: #6b7280;
  background: rgba(255,255,255,0.05);
  border-radius: 3px;
  padding: 1px 4px;
}

.slots-empty {
  color: #6b7280;
  font-size: 0.82rem;
  padding: 0.5rem 0;
}

.field-hint {
  font-size: 0.72rem;
  color: #4b5563;
  margin-top: 4px;
  display: block;
}

.warn-hint { color: #f59e0b; }

/* Box especialista asignado */
.fisio-asignado-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(74,222,128,0.06);
  border: 1px solid rgba(74,222,128,0.25);
  border-radius: 7px;
  padding: 0.6rem 0.75rem;
  gap: 0.5rem;
}
.fa-info { display: flex; align-items: center; gap: 0.65rem; }
.fa-nombre { color: #e5e7eb; font-size: 0.875rem; font-weight: 600; }
.fa-badge {
  background: rgba(74,222,128,0.12);
  color: #4ade80;
  font-size: 0.68rem;
  font-weight: 700;
  padding: 0.15rem 0.5rem;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}
.fisio-asignado-box svg { color: #374151; flex-shrink: 0; }

/* ── Time Spinner ── */
.time-spinner {
  display: flex;
  align-items: center;
  gap: 4px;
  background: #1a1a2e;
  border: 1px solid #2d2d44;
  border-radius: 6px;
  padding: 4px 10px;
  width: fit-content;
  height: 38px;
}
.ts-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1px;
}
.ts-btn {
  background: none;
  border: none;
  color: #4ade80;
  font-size: 0.55rem;
  cursor: pointer;
  padding: 1px 4px;
  border-radius: 3px;
  line-height: 1;
  transition: background 0.15s;
}
.ts-btn:hover {
  background: rgba(74,222,128,0.12);
}
.ts-val {
  font-size: 0.9rem;
  font-weight: 600;
  color: #f1f5f9;
  min-width: 2ch;
  text-align: center;
}
.ts-sep {
  font-size: 0.9rem;
  font-weight: 600;
  color: #4ade80;
  margin: 0 1px;
  align-self: center;
}
.ts-ampm .ts-val {
  font-size: 0.7rem;
  color: #94a3b8;
  min-width: 3ch;
}

/* ── Banner de horario ── */
.horario-banner {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  background: rgba(74,222,128,0.05);
  border: 1px solid rgba(74,222,128,0.2);
  border-radius: 8px;
  padding: 0.65rem 0.85rem;
  color: #4ade80;
  font-size: 0.76rem;
}
.horario-banner svg { flex-shrink: 0; margin-top: 1px; }
.hb-title { font-weight: 700; margin-right: 4px; }
.hb-slot {
  display: inline-block;
  background: rgba(74,222,128,0.1);
  border-radius: 4px;
  padding: 1px 6px;
  margin: 2px 2px 0 0;
  color: #6ee7b7;
  font-size: 0.7rem;
}

/* ── Toast de aviso (horario fuera de rango pero guardado) ── */
.aviso-toast-wrap {
  position: fixed;
  bottom: 1.75rem;
  right: 1.75rem;
  z-index: 9999;
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  background: #2d1f00;
  border: 1px solid rgba(251,146,60,0.4);
  border-radius: 12px;
  padding: 1rem 1.1rem;
  min-width: 300px;
  max-width: 400px;
  box-shadow: 0 12px 40px rgba(0,0,0,0.5);
  cursor: pointer;
  color: #fb923c;
}
.aviso-toast-wrap svg { flex-shrink: 0; margin-top: 2px; }
.avt-title { font-size: 0.82rem; font-weight: 700; margin-bottom: 2px; }
.avt-msg   { font-size: 0.75rem; color: #d97706; line-height: 1.4; }

.success-toast-wrap {
  position: fixed;
  bottom: 1.75rem;
  right: 1.75rem;
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  background: #052e16;
  border: 1px solid rgba(74,222,128,0.3);
  border-radius: 10px;
  padding: 0.8rem 1rem;
  z-index: 9999;
  box-shadow: 0 8px 32px rgba(0,0,0,0.5);
  cursor: pointer;
  max-width: 320px;
  color: #4ade80;
}
.success-toast-wrap svg { flex-shrink: 0; margin-top: 2px; }

.aviso-toast-enter-active { animation: toastSlide 0.3s ease; }
.aviso-toast-leave-active { animation: toastSlide 0.25s ease reverse; }
@keyframes toastSlide {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: none; }
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

.btn-accion-delete {
  color: #6b7280;
  padding: 0.3rem 0.5rem;
}
.btn-accion-delete:hover {
  background: rgba(239,68,68,0.1) !important;
  color: #f87171 !important;
  border-color: rgba(239,68,68,0.3) !important;
}

.delete-warning {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  color: #f87171;
  font-size: 0.8rem;
  background: rgba(239,68,68,0.08);
  border: 1px solid rgba(239,68,68,0.2);
  border-radius: 7px;
  padding: 0.65rem 0.85rem;
  margin-top: 1rem;
  line-height: 1.4;
}
.delete-warning svg { flex-shrink: 0; margin-top: 1px; }

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

/* ── Zona eliminar cita ── */
.delete-zone {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #1c1c1c;
}

.btn-eliminar-cita {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: none;
  border: none;
  color: #6b7280;
  font-size: 0.78rem;
  font-weight: 500;
  cursor: pointer;
  padding: 0.3rem 0;
  font-family: inherit;
  transition: color 0.15s;
}
.btn-eliminar-cita:hover { color: #f87171; }

.delete-confirm-msg {
  color: #f87171;
  font-size: 0.8rem;
  margin-bottom: 0.6rem;
  line-height: 1.4;
}

.delete-confirm-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-confirmar-eliminar {
  background: rgba(239,68,68,0.15);
  border: 1px solid rgba(239,68,68,0.35);
  color: #f87171;
  border-radius: 6px;
  padding: 0.4rem 0.9rem;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.15s;
}
.btn-confirmar-eliminar:hover:not(:disabled) { background: rgba(239,68,68,0.25); }
.btn-confirmar-eliminar:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-cancelar-eliminar {
  background: #1c1c1c;
  border: 1px solid #2a2a2a;
  color: #9ca3af;
  border-radius: 6px;
  padding: 0.4rem 0.9rem;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.15s;
}
.btn-cancelar-eliminar:hover { background: #2a2a2a; color: #d1d5db; }
</style>
