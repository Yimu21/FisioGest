<template>
  <FisioLayout>

    <!-- Header -->
    <div class="dash-header">
      <div>
        <h2 class="page-title">Dashboard</h2>
        <p class="page-sub">Bienvenido, <strong>{{ userName }}</strong> — semana del {{ semanaLabel }}</p>
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

    <!-- Tabla de mis citas -->
    <div class="table-card">
      <div class="table-header">
        <h3 class="table-title">Mis Citas — Historial y Próximas</h3>
        <span class="badge-count">{{ misCitas.length }} citas</span>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Paciente</th>
              <th>Fecha y Hora</th>
              <th>Motivo</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="4" class="td-empty">Cargando...</td>
            </tr>
            <tr v-else-if="misCitas.length === 0">
              <td colspan="4" class="td-empty">No tienes citas asignadas aún.</td>
            </tr>
            <tr v-for="c in misCitas" :key="c.cita_id" v-else>
              <td class="td-nombre">{{ nombrePaciente(c.paciente_id) }}</td>
              <td class="td-fecha">{{ formatFecha(c.fecha_hora) }}</td>
              <td>{{ c.motivo || '—' }}</td>
              <td><span class="estado-badge" :class="c.estado">{{ c.estado }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
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

const loading   = ref(true)
const showModal = ref(false)
const guardando = ref(false)

const misCitas          = ref([])
const misPacientesLista = ref([])

const currentUser = computed(() => getUser())
const userName    = computed(() => currentUser.value?.nombre ?? 'Fisioterapeuta')
const fisioId     = computed(() => currentUser.value?.fisioterapeuta_id ?? null)

const sesionForm = ref({ paciente_id: '', fecha: '', hora: '', tipo: 'terapia', notas: '' })

const misPacientes     = computed(() => misPacientesLista.value.length)
const misCitasHoy      = computed(() => {
  const today = new Date().toISOString().slice(0, 10)
  return misCitas.value.filter(c => (c.fecha_hora ?? '').startsWith(today)).length
})
const citasCompletadas = computed(() => misCitas.value.filter(c => c.estado === 'atendida').length)
const citasPendientes  = computed(() => misCitas.value.filter(c => c.estado === 'programada').length)

const semanaLabel = computed(() => {
  const hoy = new Date()
  return hoy.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' })
})

function nombrePaciente(id) {
  const p = misPacientesLista.value.find(p => p.paciente_id === id)
  return p ? `${p.nombre} ${p.apellido}` : `Paciente #${id}`
}

function formatFecha(fh) {
  if (!fh) return '—'
  return new Date(fh).toLocaleString('es-ES', { dateStyle: 'medium', timeStyle: 'short' })
}

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
    showModal.value = false
    sesionForm.value = { paciente_id: '', fecha: '', hora: '', tipo: 'terapia', notas: '' }
  } catch {} finally {
    guardando.value = false
  }
}

async function cargar() {
  loading.value = true
  try {
    // Refrescar datos del usuario desde la API para asegurar que fisioterapeuta_id está actualizado
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

.dash-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.25rem;
}

.page-title {
  color: #ffffff;
  font-size: 1.4rem;
  font-weight: 700;
  margin-bottom: 0.2rem;
}

.page-sub {
  color: #6b7280;
  font-size: 0.82rem;
}
.page-sub strong { color: #4ade80; }

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
  white-space: nowrap;
}
.btn-add:hover { background: #0a5c46; }

/* Stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.9rem;
  margin-bottom: 1.25rem;
}

.stat-card {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 10px;
  padding: 1.1rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-card.stat-warn {
  background: #1a1408;
  border-color: #2e2008;
}

.stat-info { display: flex; flex-direction: column; gap: 0.5rem; }
.stat-label { color: #6b7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.stat-value { color: #ffffff; font-size: 2.4rem; font-weight: 700; line-height: 1; }

.stat-icon {
  width: 44px; height: 44px; border-radius: 9px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.stat-icon.green  { background: rgba(74,222,128,0.12); color: #4ade80; }
.stat-icon.blue   { background: rgba(56,189,248,0.12); color: #38bdf8; }
.stat-icon.yellow { background: rgba(251,191,36,0.12); color: #fbbf24; }

/* Table */
.table-card {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 10px;
  padding: 1.1rem 1.25rem;
}

.table-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.9rem;
}

.table-title { color: #ffffff; font-size: 0.95rem; font-weight: 600; }

.badge-count {
  background: rgba(74,222,128,0.12);
  color: #4ade80;
  font-size: 0.72rem;
  font-weight: 600;
  padding: 0.15rem 0.6rem;
  border-radius: 20px;
}

.table-wrapper { overflow-x: auto; }

table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
thead tr { border-bottom: 1px solid #1c1c1c; }
th { text-align: left; color: #6b7280; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 0.6rem 0.75rem; }
td { color: #d1d5db; padding: 0.75rem 0.75rem; border-bottom: 1px solid #161616; }
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: rgba(255,255,255,0.02); }
.td-nombre { font-weight: 600; color: #ffffff; }
.td-fecha  { color: #38bdf8; }
.td-empty  { text-align: center; color: #4b5563; padding: 2rem; }

.estado-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
.estado-badge.programada { background: rgba(59,130,246,0.15); color: #93c5fd; }
.estado-badge.atendida   { background: rgba(74,222,128,0.15); color: #4ade80; }
.estado-badge.cancelada  { background: rgba(239,68,68,0.15);  color: #f87171; }

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
