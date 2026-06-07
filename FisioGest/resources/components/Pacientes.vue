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
              <th>Acceso al Portal</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" class="td-empty">Cargando pacientes...</td>
            </tr>
            <tr v-else-if="pacientes.length === 0">
              <td colspan="7" class="td-empty">No hay pacientes registrados. Haz clic en "Nuevo Paciente" para empezar.</td>
            </tr>
            <tr v-for="p in pacientes" :key="p.paciente_id" v-else>
              <td class="td-nombre">{{ p.nombre }} {{ p.apellido }}</td>
              <td>
                <span class="genero-badge" :class="p.genero">{{ capitalize(p.genero) }}</span>
              </td>
              <td class="td-fecha">{{ formatFecha(p.fecha_nacimiento) }}</td>
              <td>{{ p.telefono || '—' }}</td>
              <td class="td-fisio">{{ nombreFisio(p.fisioterapeuta_id) }}</td>

              <!-- Toggle acceso al portal — 3 estados -->
              <td class="td-acceso">
                <!-- Sin cuenta vinculada → gris, abre modal -->
                <div v-if="!p.usuario_id" class="acceso-inactivo">
                  <button class="toggle-btn off" @click="openEditModal(p, true)" title="Habilitar acceso al portal">
                    <span class="toggle-knob"></span>
                  </button>
                  <span class="acceso-label">Sin cuenta</span>
                </div>
                <!-- Tiene cuenta y portal activo → verde -->
                <div v-else-if="p.portal_activo" class="acceso-activo">
                  <button
                    class="toggle-btn on"
                    :disabled="toggling === p.paciente_id"
                    @click="confirmarRevocacion(p)"
                    title="Clic para revocar acceso"
                  >
                    <span class="toggle-knob"></span>
                  </button>
                  <span class="acceso-email">{{ p.correo }}</span>
                </div>
                <!-- Tiene cuenta pero portal inactivo → amarillo, re-habilita sin password -->
                <div v-else class="acceso-suspendido">
                  <button
                    class="toggle-btn suspended"
                    :disabled="toggling === p.paciente_id"
                    @click="habilitarAcceso(p)"
                    title="Clic para re-habilitar acceso"
                  >
                    <span class="toggle-knob"></span>
                  </button>
                  <span class="acceso-email suspended">{{ p.correo }}</span>
                </div>
              </td>

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

    <!-- ── Modal: Crear / Editar paciente ── -->
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

            <!-- Datos personales -->
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

            <!-- Sección credenciales de acceso al portal -->
            <div class="creds-section">
              <div class="creds-header">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <span>Acceso al Portal del Paciente</span>
                <span v-if="editingPaciente?.usuario_id && editingPaciente?.portal_activo" class="creds-badge active">Activo</span>
                <span v-else-if="editingPaciente?.usuario_id && !editingPaciente?.portal_activo" class="creds-badge suspended">Suspendido</span>
                <span v-else class="creds-badge inactive">Sin cuenta</span>
              </div>

              <!-- Tiene cuenta inactiva: solo mostrar email y botón re-habilitar -->
              <template v-if="editingPaciente?.usuario_id && !editingPaciente?.portal_activo">
                <div class="creds-hint">La cuenta existe pero el acceso está suspendido. Puedes re-habilitarlo sin cambiar la contraseña.</div>
                <div class="form-group">
                  <label>Correo registrado</label>
                  <input :value="form.correo" type="email" disabled style="opacity:0.5" />
                </div>
                <div class="revoke-row">
                  <button type="button" class="btn-habilitar" @click="habilitarDesdeModal">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Re-habilitar acceso al portal
                  </button>
                </div>
              </template>

              <!-- Tiene cuenta activa: mostrar correo y nueva contraseña opcional -->
              <template v-else-if="editingPaciente?.usuario_id && editingPaciente?.portal_activo">
                <div class="creds-hint">Deja los campos vacíos para conservar los datos actuales.</div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Correo electrónico</label>
                    <input v-model="form.correo" type="email" placeholder="correo@ejemplo.com" />
                  </div>
                  <div class="form-group">
                    <label>Nueva contraseña</label>
                    <div class="pass-wrap">
                      <input v-model="form.contrasena" :type="showPassPac ? 'text' : 'password'" placeholder="Dejar vacío para no cambiar" />
                      <button type="button" class="pass-eye" @click="showPassPac = !showPassPac" tabindex="-1">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <template v-if="showPassPac"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></template>
                          <template v-else><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></template>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="revoke-row">
                  <button type="button" class="btn-revocar" @click="confirmarRevocacion(editingPaciente)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="10"/>
                      <line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    Revocar acceso al portal
                  </button>
                </div>
              </template>

              <!-- Sin cuenta: pedir correo + contraseña para crear -->
              <template v-else>
                <div class="creds-hint">Completa estos campos para habilitar el acceso al portal.</div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Correo electrónico *</label>
                    <input v-model="form.correo" type="email" placeholder="correo@ejemplo.com" :required="!editingPaciente" />
                  </div>
                  <div class="form-group">
                    <label>Contraseña *</label>
                    <div class="pass-wrap">
                      <input v-model="form.contrasena" :type="showPassPac ? 'text' : 'password'" placeholder="Mínimo 8 caracteres" :required="!editingPaciente" />
                      <button type="button" class="pass-eye" @click="showPassPac = !showPassPac" tabindex="-1">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <template v-if="showPassPac"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></template>
                          <template v-else><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></template>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              </template>
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

    <!-- ── Modal: Confirmar eliminación ── -->
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

    <!-- ── Modal: Confirmar revocación de acceso ── -->
    <Teleport to="body">
      <div class="modal-overlay" v-if="showRevokeModal" @click.self="closeRevokeModal">
        <div class="modal modal-sm">
          <div class="modal-header">
            <h3>Revocar Acceso al Portal</h3>
            <button class="modal-close" @click="closeRevokeModal">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <div class="delete-warning">
            <div class="warning-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
            </div>
            <p class="warning-text">
              Se eliminará el acceso al portal de
              <strong>{{ pacienteAEliminar?.nombre }} {{ pacienteAEliminar?.apellido }}</strong>.
              La cuenta de usuario quedará desactivada.
            </p>
          </div>
          <div class="modal-actions" style="margin-top:1.25rem;">
            <button class="btn-eliminar" @click="confirmRevocacion" :disabled="toggling === pacienteAEliminar?.paciente_id">
              {{ toggling === pacienteAEliminar?.paciente_id ? 'Revocando...' : 'Sí, revocar acceso' }}
            </button>
            <button class="btn-cancelar" @click="closeRevokeModal">Cancelar</button>
          </div>
        </div>
      </div>
    </Teleport>

  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import { pacienteService, fisioterapeutaService } from '@/services/api'

const pacientes        = ref([])
const loading          = ref(true)
const showModal        = ref(false)
const saving           = ref(false)
const errorMsg         = ref('')
const editingPaciente  = ref(null)
const toggling         = ref(null)

const showDeleteModal   = ref(false)
const showRevokeModal   = ref(false)
const showPassPac       = ref(false)
const pacienteAEliminar = ref(null)
const deleting          = ref(false)

const fisioterapeutas = ref([])

const form = ref({
  nombre: '', apellido: '', fecha_nacimiento: '',
  genero: 'masculino', telefono: '', fisioterapeuta_id: '',
  correo: '', contrasena: '',
})

function openModal() {
  editingPaciente.value = null
  form.value = {
    nombre: '', apellido: '', fecha_nacimiento: '',
    genero: 'masculino', telefono: '', fisioterapeuta_id: '',
    correo: '', contrasena: '',
  }
  errorMsg.value  = ''
  showModal.value = true
}

function openEditModal(p, focusCredenciales = false) {
  editingPaciente.value = p
  form.value = {
    nombre:            p.nombre,
    apellido:          p.apellido,
    fecha_nacimiento:  p.fecha_nacimiento ?? '',
    genero:            p.genero ?? 'masculino',
    telefono:          p.telefono ?? '',
    fisioterapeuta_id: p.fisioterapeuta_id ?? '',
    correo:            p.correo ?? '',
    contrasena:        '',
  }
  errorMsg.value  = ''
  showModal.value = true
  if (focusCredenciales) {
    // Scroll a la sección de credenciales tras el render
    setTimeout(() => {
      document.querySelector('.creds-section')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
    }, 100)
  }
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

function confirmarRevocacion(p) {
  showModal.value         = false   // cierra el modal de edición si estaba abierto
  pacienteAEliminar.value = p
  showRevokeModal.value   = true
}

function closeRevokeModal() {
  showRevokeModal.value   = false
  pacienteAEliminar.value = null
}

async function confirmRevocacion() {
  const p = pacienteAEliminar.value
  if (!p) return
  toggling.value = p.paciente_id
  try {
    await pacienteService.revocarAcceso(p.paciente_id)
    await cargarPacientes()
    closeRevokeModal()
  } catch (err) {
    alert(err?.response?.data?.message ?? 'Error al revocar acceso.')
  } finally {
    toggling.value = null
  }
}

async function habilitarAcceso(p) {
  toggling.value = p.paciente_id
  try {
    await pacienteService.habilitarAcceso(p.paciente_id)
    await cargarPacientes()
  } catch (err) {
    alert(err?.response?.data?.message ?? 'Error al habilitar acceso.')
  } finally {
    toggling.value = null
  }
}

async function habilitarDesdeModal() {
  if (!editingPaciente.value) return
  toggling.value = editingPaciente.value.paciente_id
  try {
    await pacienteService.habilitarAcceso(editingPaciente.value.paciente_id)
    await cargarPacientes()
    closeModal()
  } catch (err) {
    errorMsg.value = err?.response?.data?.message ?? 'Error al habilitar acceso.'
  } finally {
    toggling.value = null
  }
}

async function confirmDelete() {
  deleting.value = true
  try {
    await pacienteService.delete(pacienteAEliminar.value.paciente_id)
    await cargarPacientes()
    closeDeleteModal()
  } catch {
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
    const [pacRes, fisioRes] = await Promise.allSettled([
      pacienteService.getAll(),
      fisioterapeutaService.getAll(),
    ])
    if (pacRes.status === 'fulfilled')  pacientes.value      = pacRes.value.data
    if (fisioRes.status === 'fulfilled') fisioterapeutas.value = fisioRes.value.data
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
      // Guardar datos del paciente (el endpoint también maneja email/password si hay usuario vinculado)
      await pacienteService.update(editingPaciente.value.paciente_id, form.value)

      // Si no tiene cuenta y se proveyeron credenciales → crear cuenta
      if (!editingPaciente.value.usuario_id && form.value.correo && form.value.contrasena) {
        await pacienteService.crearCredenciales(editingPaciente.value.paciente_id, {
          correo:    form.value.correo,
          contrasena: form.value.contrasena,
        })
      }
      // Cuenta suspendida: no hacer nada extra (el usuario usa habilitarDesdeModal)
    } else {
      // Nuevo paciente: el endpoint ya maneja credenciales si se enviaron
      await pacienteService.create(form.value)
      await cargarPacientes()
      closeModal()
      return
    }

    await cargarPacientes()
    closeModal()
  } catch (err) {
    errorMsg.value = err?.response?.data?.message ?? 'Error al guardar el paciente. Verifica los datos.'
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

/* ── Toggle de acceso al portal ── */
.td-acceso { white-space: nowrap; }

.acceso-activo,
.acceso-inactivo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.toggle-btn {
  position: relative;
  width: 36px;
  height: 20px;
  border-radius: 99px;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
  flex-shrink: 0;
  padding: 0;
}

.toggle-btn.on        { background: #074434; }
.toggle-btn.off       { background: #2a2a2a; }
.toggle-btn.suspended { background: rgba(251,191,36,0.2); }
.toggle-btn:disabled  { opacity: 0.5; cursor: wait; }

.toggle-knob {
  position: absolute;
  top: 2px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #fff;
  transition: left 0.2s;
}
.toggle-btn.on        .toggle-knob { left: 18px; background: #4ade80; }
.toggle-btn.suspended .toggle-knob { left: 10px; background: #fbbf24; }
.toggle-btn.off       .toggle-knob { left: 2px; }

.acceso-email {
  font-size: 0.75rem;
  color: #4ade80;
  max-width: 150px;
  overflow: hidden;
  text-overflow: ellipsis;
}
.acceso-email.suspended { color: #fbbf24; }

.acceso-label {
  font-size: 0.75rem;
  color: #4b5563;
}

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
  max-width: 520px;
  max-height: 90vh;
  overflow-y: auto;
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

/* Sección credenciales */
.creds-section {
  border: 1px solid #1c1c1c;
  border-radius: 9px;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
  background: #0d0d0d;
}

.creds-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.82rem;
  font-weight: 600;
  color: #9ca3af;
}

.creds-badge {
  font-size: 0.68rem;
  font-weight: 700;
  padding: 0.15rem 0.55rem;
  border-radius: 99px;
  margin-left: auto;
}
.creds-badge.active    { background: rgba(74,222,128,0.12); color: #4ade80; }
.creds-badge.inactive  { background: rgba(107,114,128,0.15); color: #6b7280; }
.creds-badge.suspended { background: rgba(251,191,36,0.12); color: #fbbf24; }

.creds-hint {
  font-size: 0.75rem;
  color: #4b5563;
}

.revoke-row { display: flex; }

.btn-revocar {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  background: none;
  border: 1px solid rgba(239,68,68,0.25);
  border-radius: 7px;
  color: #f87171;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.4rem 0.85rem;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.15s;
}
.btn-revocar:hover { background: rgba(239,68,68,0.08); }

.btn-habilitar {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  background: none;
  border: 1px solid rgba(74,222,128,0.25);
  border-radius: 7px;
  color: #4ade80;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.4rem 0.85rem;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.15s;
}
.btn-habilitar:hover { background: rgba(74,222,128,0.08); }

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

.td-acciones { width: 1%; white-space: nowrap; padding-right: 0.5rem; }

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
.btn-accion:hover { background: rgba(255,255,255,0.08); color: #e5e7eb; border-color: #3a3a3a; }
.btn-accion.btn-danger { color: #f87171; border-color: rgba(239,68,68,0.2); }
.btn-accion.btn-danger:hover { background: rgba(239,68,68,0.1); color: #fca5a5; border-color: rgba(239,68,68,0.35); }

.modal-sm { max-width: 380px; }

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

.pass-wrap { position: relative; display: flex; }
.pass-wrap input { flex: 1; padding-right: 2.5rem !important; }
.pass-eye {
  position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer;
  color: #6b7280; padding: 0.2rem; display: flex; align-items: center;
  transition: color 0.15s;
}
.pass-eye:hover { color: #d1d5db; }
</style>
