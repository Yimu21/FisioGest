<template>
  <AppLayout>

    <!-- Header -->
    <div class="pac-header">
      <h2 class="page-title">Registro de Pacientes</h2>
      <button class="btn-add" @click="openModal">+ Nuevo Paciente</button>
    </div>

    <!-- Stats -->
    <div class="pac-stats">
      <div class="stat-box">
        <span class="sbox-label">Total Pacientes</span>
        <span class="sbox-value">{{ pacientes.length }}</span>
      </div>
      <div class="stat-box">
        <span class="sbox-label">Masculino</span>
        <span class="sbox-value">{{ pacientes.filter(p => p.genero === 'masculino').length }}</span>
      </div>
      <div class="stat-box">
        <span class="sbox-label">Femenino</span>
        <span class="sbox-value">{{ pacientes.filter(p => p.genero === 'femenino').length }}</span>
      </div>
      <div class="stat-box">
        <span class="sbox-label">Con Especialista</span>
        <span class="sbox-value">{{ pacientes.filter(p => p.fisioterapeuta_id).length }}</span>
      </div>
    </div>

    <!-- Tabla -->
    <div class="table-card">
      <h3 class="table-title">Expedientes Clínicos</h3>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Paciente</th>
              <th>Género</th>
              <th>Fecha Nacimiento</th>
              <th>Teléfono</th>
              <th>Especialista Asignado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="5" class="td-empty">Cargando pacientes...</td>
            </tr>
            <tr v-else-if="pacientes.length === 0">
              <td colspan="5" class="td-empty">No hay pacientes registrados. Haz clic en "Nuevo Paciente" para empezar.</td>
            </tr>
            <tr v-for="p in pacientes" :key="p.paciente_id" v-else>
              <td class="td-nombre">{{ p.nombre }} {{ p.apellido }}</td>
              <td>
                <span class="genero-badge" :class="p.genero">{{ capitalize(p.genero) }}</span>
              </td>
              <td class="td-fecha">{{ formatFecha(p.fecha_nacimiento) }}</td>
              <td>{{ p.telefono || '—' }}</td>
              <td class="td-fisio">{{ nombreFisio(p.fisioterapeuta_id) }}</td>
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
            <h3>Nuevo Paciente</h3>
            <button class="modal-close" @click="closeModal">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <form class="modal-form" @submit.prevent="savePaciente">
            <div class="form-row">
              <div class="form-group">
                <label>Nombre *</label>
                <input v-model="form.nombre" type="text" placeholder="Nombre" required />
              </div>
              <div class="form-group">
                <label>Apellido *</label>
                <input v-model="form.apellido" type="text" placeholder="Apellido" required />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Fecha de Nacimiento *</label>
                <input v-model="form.fecha_nacimiento" type="date" required />
              </div>
              <div class="form-group">
                <label>Género *</label>
                <select v-model="form.genero" required>
                  <option value="masculino">Masculino</option>
                  <option value="femenino">Femenino</option>
                  <option value="otro">Otro</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label>Teléfono</label>
              <input v-model="form.telefono" type="text" placeholder="Ej. 7123-4567" />
            </div>

            <div class="form-group">
              <label>Especialista Inicial</label>
              <select v-model="form.fisioterapeuta_id">
                <option value="">-- Sin especialista inicial --</option>
                <option v-for="f in fisioterapeutas" :key="f.fisioterapeuta_id" :value="f.fisioterapeuta_id">
                  {{ f.nombre }} {{ f.apellido }} — {{ f.especialidad }}
                </option>
              </select>
            </div>

            <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>

            <div class="modal-actions">
              <button type="submit" class="btn-guardar" :disabled="saving">
                {{ saving ? 'Guardando...' : 'Guardar Paciente' }}
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
import { pacienteService } from '@/services/api'

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

const form = ref({
  nombre: '', apellido: '', fecha_nacimiento: '',
  genero: 'masculino', telefono: '', fisioterapeuta_id: ''
})

function openModal() {
  form.value = { nombre: '', apellido: '', fecha_nacimiento: '', genero: 'masculino', telefono: '', fisioterapeuta_id: '' }
  errorMsg.value = ''
  showModal.value = true
}

function closeModal() { showModal.value = false }

function capitalize(str) { return str ? str.charAt(0).toUpperCase() + str.slice(1) : '' }

function formatFecha(fecha) {
  if (!fecha) return '—'
  const [y, m, d] = fecha.split('-')
  return `${d}/${m}/${y}`
}

function nombreFisio(id) {
  if (!id) return 'Sin asignar'
  const f = fisioterapeutas.value.find(f => f.fisioterapeuta_id === Number(id))
  return f ? `${f.nombre} ${f.apellido}` : 'Sin asignar'
}

async function cargarPacientes() {
  loading.value = true
  try {
    const res = await pacienteService.getAll()
    pacientes.value = res.data
  } catch {
    pacientes.value = []
  } finally {
    loading.value = false
  }
}

async function savePaciente() {
  saving.value   = true
  errorMsg.value = ''
  try {
    await pacienteService.create(form.value)
    await cargarPacientes()
    closeModal()
  } catch {
    errorMsg.value = 'Error al guardar el paciente. Verifica los datos.'
  } finally {
    saving.value = false
  }
}

onMounted(cargarPacientes)
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

.pac-header {
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

.pac-stats {
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

table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }

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
.td-fecha  { color: #9ca3af; }
.td-fisio  { color: #4ade80; }
.td-empty  { text-align: center; color: #4b5563; padding: 2rem; }

.genero-badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}
.genero-badge.masculino  { background: rgba(59,130,246,0.15); color: #93c5fd; }
.genero-badge.femenino   { background: rgba(236,72,153,0.15); color: #f9a8d4; }
.genero-badge.otro       { background: rgba(107,114,128,0.15); color: #9ca3af; }

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
  max-width: 480px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.modal-header h3 { color: #ffffff; font-size: 1rem; font-weight: 700; }

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

.modal-form { display: flex; flex-direction: column; gap: 1rem; }

.form-group { display: flex; flex-direction: column; gap: 0.35rem; }

.form-group label { color: #9ca3af; font-size: 0.8rem; font-weight: 600; }

.form-group input,
.form-group select {
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
.form-group select:focus {
  border-color: #074434;
  box-shadow: 0 0 0 3px rgba(7,68,52,0.2);
}

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }

.error-msg {
  color: #f87171;
  font-size: 0.82rem;
  background: rgba(239,68,68,0.1);
  border: 1px solid rgba(239,68,68,0.2);
  border-radius: 6px;
  padding: 0.5rem 0.75rem;
}

.modal-actions { display: flex; gap: 0.6rem; margin-top: 0.25rem; }

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
