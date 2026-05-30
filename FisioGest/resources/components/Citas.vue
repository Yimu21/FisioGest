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

    <!-- Tabla -->
    <div class="table-card">
      <h3 class="table-title">Historial y Próximos Turnos</h3>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Paciente</th>
              <th>Fisioterapeuta</th>
              <th>Fecha y Hora</th>
              <th>Motivo</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="5" class="td-empty">Cargando citas...</td>
            </tr>
            <tr v-else-if="citas.length === 0">
              <td colspan="5" class="td-empty">No hay citas agendadas. Haz clic en "Agendar Nueva Cita" para empezar.</td>
            </tr>
            <tr v-for="cita in citas" :key="cita.cita_id" v-else>
              <td class="td-nombre">{{ nombrePaciente(cita.paciente_id) }}</td>
              <td>{{ nombreFisio(cita.fisioterapeuta_id) }}</td>
              <td class="td-fecha">{{ formatFecha(cita.fecha_hora) }}</td>
              <td>{{ cita.motivo || '—' }}</td>
              <td>
                <span class="estado-badge" :class="cita.estado">{{ cita.estado }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <div class="modal-overlay" v-if="showModal" @click.self="closeModal">
        <div class="modal">
          <div class="modal-header">
            <h3>Agendar Nueva Cita</h3>
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
                {{ saving ? 'Guardando...' : 'Agendar Cita' }}
              </button>
              <button type="button" class="btn-cancelar" @click="closeModal">Cancelar</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import { citaService, pacienteService } from '@/services/api'

const citas      = ref([])
const pacientes  = ref([])
const loading    = ref(true)
const showModal  = ref(false)
const saving     = ref(false)
const errorMsg   = ref('')

const fisioterapeutas = ref([
  { fisioterapeuta_id: 1, nombre: 'Manrivel', apellido: 'Gorado',  especialidad: 'Traumatología' },
  { fisioterapeuta_id: 2, nombre: 'Barvis',   apellido: 'Raten',   especialidad: 'Deportiva'     },
  { fisioterapeuta_id: 3, nombre: 'Bardena',  apellido: 'Drides',  especialidad: 'Deportiva'     },
  { fisioterapeuta_id: 4, nombre: 'Marina',   apellido: 'Gomez',   especialidad: 'Traumatología' },
  { fisioterapeuta_id: 5, nombre: 'Retmen',   apellido: 'Nones',   especialidad: 'Deportiva'     },
])

const form = ref({ paciente_id: '', fisioterapeuta_id: '', fecha: '', hora: '', motivo: '' })

function openModal() {
  form.value = { paciente_id: '', fisioterapeuta_id: '', fecha: '', hora: '', motivo: '' }
  errorMsg.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

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
  const d = new Date(fechaHora)
  return d.toLocaleString('es-ES', { dateStyle: 'medium', timeStyle: 'short' })
}

async function cargarDatos() {
  loading.value = true
  try {
    const [citasRes, pacientesRes] = await Promise.allSettled([
      citaService.getAll(),
      pacienteService.getAll(),
    ])
    if (citasRes.status === 'fulfilled')     citas.value     = citasRes.value.data
    if (pacientesRes.status === 'fulfilled') pacientes.value = pacientesRes.value.data
  } finally {
    loading.value = false
  }
}

async function saveCita() {
  saving.value   = true
  errorMsg.value = ''
  try {
    await citaService.create({
      paciente_id:       form.value.paciente_id,
      fisioterapeuta_id: form.value.fisioterapeuta_id,
      fecha_hora:        `${form.value.fecha} ${form.value.hora}:00`,
      motivo:            form.value.motivo,
    })
    await cargarDatos()
    closeModal()
  } catch (e) {
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

/* Table */
.table-card {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 10px;
  padding: 1.1rem 1.25rem;
}

.table-title {
  color: #ffffff;
  font-size: 0.95rem;
  font-weight: 600;
  margin-bottom: 0.9rem;
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

.estado-badge.programada  { background: rgba(59,130,246,0.15); color: #93c5fd; }
.estado-badge.atendida    { background: rgba(74,222,128,0.15); color: #4ade80; }
.estado-badge.cancelada   { background: rgba(239,68,68,0.15);  color: #f87171; }
.estado-badge.reprogramada { background: rgba(245,158,11,0.15); color: #fbbf24; }

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
</style>
