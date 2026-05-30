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
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="6" class="td-empty">Cargando pacientes...</td>
            </tr>
            <tr v-else-if="pacientes.length === 0">
              <td colspan="6" class="td-empty">No hay pacientes registrados. Haz clic en "Nuevo Paciente" para empezar.</td>
            </tr>
            <tr v-for="p in pacientes" :key="p.paciente_id" v-else>
              <td class="td-nombre">{{ p.nombre }} {{ p.apellido }}</td>
              <td>
                <span class="genero-badge" :class="p.genero">{{ capitalize(p.genero) }}</span>
              </td>
              <td class="td-fecha">{{ formatFecha(p.fecha_nacimiento) }}</td>
              <td>{{ p.telefono || '—' }}</td>
              <td class="td-fisio">{{ nombreFisio(p.fisioterapeuta_id) }}</td>
              <td class="td-acciones">
                <button class="btn-accion" @click="openEditModal(p)" title="Editar paciente">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                  Editar
                </button>
                <button class="btn-accion btn-danger" @click="openDeleteModal(p)" title="Eliminar paciente">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/>
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                  </svg>
                  Eliminar
                </button>
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
            <h3>{{ editingPaciente ? 'Editar Paciente' : 'Nuevo Paciente' }}</h3>
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
                {{ saving ? 'Guardando...' : (editingPaciente ? 'Guardar Cambios' : 'Guardar Paciente') }}
              </button>
              <button type="button" class="btn-cancelar" @click="closeModal">Cancelar</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Modal: Confirmar eliminación -->
    <Teleport to="body">
      <div class="modal-overlay" v-if="showDeleteModal" @click.self="closeDeleteModal">
        <div class="modal modal-sm">
          <div class="modal-header">
            <h3>Eliminar Paciente</h3>
            <button class="modal-close" @click="closeDeleteModal">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <div class="delete-warning">
            <div class="warning-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
            </div>
            <p class="warning-text">
              ¿Estás seguro de eliminar a
              <strong>{{ pacienteAEliminar?.nombre }} {{ pacienteAEliminar?.apellido }}</strong>?
              Esta acción no se puede deshacer.
            </p>
          </div>

          <div class="modal-actions" style="margin-top:1.25rem;">
            <button class="btn-eliminar" @click="confirmDelete" :disabled="deleting">
              {{ deleting ? 'Eliminando...' : 'Sí, eliminar' }}
            </button>
            <button class="btn-cancelar" @click="closeDeleteModal">Cancelar</button>
          </div>
        </div>
      </div>
    </Teleport>

  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import { pacienteService } from '@/services/api'

const pacientes        = ref([])
const loading          = ref(true)
const showModal        = ref(false)
const saving           = ref(false)
const errorMsg         = ref('')
const editingPaciente  = ref(null)

const showDeleteModal   = ref(false)
const pacienteAEliminar = ref(null)
const deleting          = ref(false)

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
  editingPaciente.value = null
  form.value = { nombre: '', apellido: '', fecha_nacimiento: '', genero: 'masculino', telefono: '', fisioterapeuta_id: '' }
  errorMsg.value  = ''
  showModal.value = true
}

function openEditModal(p) {
  editingPaciente.value = p
  form.value = {
    nombre:            p.nombre,
    apellido:          p.apellido,
    fecha_nacimiento:  p.fecha_nacimiento ?? '',
    genero:            p.genero ?? 'masculino',
    telefono:          p.telefono ?? '',
    fisioterapeuta_id: p.fisioterapeuta_id ?? '',
  }
  errorMsg.value  = ''
  showModal.value = true
}

function closeModal() {
  showModal.value       = false
  editingPaciente.value = null
}

function openDeleteModal(p) {
  pacienteAEliminar.value = p
  showDeleteModal.value   = true
}

function closeDeleteModal() {
  showDeleteModal.value   = false
  pacienteAEliminar.value = null
}

async function confirmDelete() {
  deleting.value = true
  try {
    await pacienteService.delete(pacienteAEliminar.value.paciente_id)
    await cargarPacientes()
    closeDeleteModal()
  } catch {
    // si falla simplemente cerramos
    closeDeleteModal()
  } finally {
    deleting.value = false
  }
}

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
    if (editingPaciente.value) {
      await pacienteService.update(editingPaciente.value.paciente_id, form.value)
    } else {
      await pacienteService.create(form.value)
    }
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
  margin-right: 0.4rem;
}
.btn-accion:last-child { margin-right: 0; }
.btn-accion:hover {
  background: rgba(255,255,255,0.08);
  color: #e5e7eb;
  border-color: #3a3a3a;
}
.btn-accion.btn-danger { color: #f87171; border-color: rgba(239,68,68,0.2); }
.btn-accion.btn-danger:hover {
  background: rgba(239,68,68,0.1);
  color: #fca5a5;
  border-color: rgba(239,68,68,0.35);
}

/* Modal compacto */
.modal-sm { max-width: 380px; }

/* Modal de eliminación */
.delete-warning {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
  background: rgba(239,68,68,0.06);
  border: 1px solid rgba(239,68,68,0.2);
  border-radius: 8px;
  padding: 1rem;
}

.warning-icon { flex-shrink: 0; margin-top: 0.1rem; }

.warning-text {
  color: #d1d5db;
  font-size: 0.875rem;
  line-height: 1.5;
  margin: 0;
}

.warning-text strong { color: #ffffff; }

.btn-eliminar {
  flex: 1;
  background: #7f1d1d;
  color: #fca5a5;
  border: 1px solid rgba(239,68,68,0.3);
  border-radius: 7px;
  padding: 0.65rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
  font-family: inherit;
}
.btn-eliminar:hover:not(:disabled) { background: #991b1b; }
.btn-eliminar:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
