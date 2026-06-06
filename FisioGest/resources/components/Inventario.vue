<template>
  <AppLayout>

    <!-- Header -->
    <div class="inv-header">
      <h2 class="page-title">Inventario</h2>
      <button class="btn-add" @click="openModal(null)">+ Añadir Nuevo Equipo</button>
    </div>

    <!-- Stats -->
    <div class="inv-stats">
      <div class="stat-box">
        <span class="sbox-label">Total Items</span>
        <span class="sbox-value">{{ stats.total }}</span>
      </div>
      <div class="stat-box">
        <span class="sbox-label">En Uso</span>
        <div class="sbox-row">
          <span class="sbox-value">{{ stats.enUso }}</span>
          <span class="dot-green"></span>
        </div>
      </div>
      <div class="stat-box">
        <span class="sbox-label">Stock Bajo</span>
        <span class="sbox-value">{{ stats.stockBajo }}</span>
      </div>
      <div class="stat-box critical">
        <span class="sbox-label">Alertas Críticas</span>
        <span class="sbox-value">{{ stats.alertas }}</span>
      </div>
    </div>

    <!-- Table -->
    <div class="table-card">
      <h3 class="table-title">Items</h3>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Equipo</th>
              <th>Tipo</th>
              <th class="td-center">Cantidad Total</th>
              <th class="td-center">Disponibles</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="items.length === 0">
              <td colspan="6" class="td-empty">No hay equipos registrados.</td>
            </tr>
            <tr v-for="item in items" :key="item.id_inventario">
              <td class="td-nombre">{{ item.nombre }}</td>
              <td><span class="tag-tipo">{{ item.tipo }}</span></td>
              <td class="td-center">{{ item.cantidad }}</td>
              <td class="td-center">
                <span class="disp-val" :class="disponiblesClass(disponiblesDeItem(item), item.cantidad)">
                  {{ disponiblesDeItem(item) }}
                </span>
                <span class="disp-total">/ {{ item.cantidad }}</span>
              </td>
              <td>
                <span class="estado-badge" :class="estadoCalculado(item)">
                  <span class="estado-dot"></span>
                  {{ etiquetaEstado(estadoCalculado(item)) }}
                </span>
              </td>
              <td class="td-actions">
                <button class="btn-edit" @click="openModal(item)">Editar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         TABLA DE ASIGNACIONES ACTIVAS (vista administrador)
    ════════════════════════════════════════════════════════════════ -->
    <div class="table-card asig-card">

      <div class="asig-header">
        <div>
          <h3 class="table-title">Asignaciones Activas</h3>
          <p class="asig-sub">Equipos actualmente asignados a pacientes en el sistema.</p>
        </div>
        <div class="filtros-row">
          <select v-model="filtroAsignPaciente" class="filtro-select">
            <option value="">Todos los pacientes</option>
            <option v-for="p in pacientes" :key="p.paciente_id" :value="p.paciente_id">
              {{ p.nombre }} {{ p.apellido }}
            </option>
          </select>
          <select v-model="filtroAsignEspecialista" class="filtro-select">
            <option value="">Todos los especialistas</option>
            <option v-for="f in fisioterapeutas" :key="f.fisioterapeuta_id" :value="f.fisioterapeuta_id">
              {{ f.nombre }} {{ f.apellido }}
            </option>
          </select>
        </div>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Equipo</th>
              <th>Paciente</th>
              <th>Especialista</th>
              <th>Fecha de asignación</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="asignacionesFiltradas.length === 0">
              <td colspan="5" class="td-empty">
                {{ filtroAsignPaciente || filtroAsignEspecialista
                   ? 'No hay asignaciones para el filtro seleccionado.'
                   : 'No hay asignaciones activas en el sistema.' }}
              </td>
            </tr>
            <tr v-for="a in asignacionesFiltradas" :key="a.id_asignaciones">
              <td class="td-nombre">{{ a.equipo }}</td>
              <td>
                <div class="persona-cell">
                  <span class="avatar av-pac">{{ a.iniciales_pac }}</span>
                  {{ a.paciente }}
                </div>
              </td>
              <td>
                <div class="persona-cell">
                  <span class="avatar av-fisio">{{ a.iniciales_fisio }}</span>
                  {{ a.especialista }}
                </div>
              </td>
              <td class="td-fecha">{{ formatFecha(a.fecha_asignacion) }}</td>
              <td>
                <span class="estado-badge disponible">
                  <span class="estado-dot"></span>
                  Activo
                </span>
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
            <h3>{{ editingItem ? 'Editar Equipo' : 'Añadir Nuevo Equipo' }}</h3>
            <button class="modal-close" @click="closeModal">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <form class="modal-form" @submit.prevent="saveItem">
            <div class="form-group">
              <label>Nombre del Equipo</label>
              <input v-model="form.nombre" type="text" placeholder="Nombre del Equipo" required />
            </div>

            <div class="form-group">
              <label>Modelo/Marca</label>
              <input v-model="form.modelo" type="text" placeholder="Modelo/Marca" />
            </div>

            <div class="form-group">
              <label>Categoría</label>
              <select v-model="form.tipo">
                <option>Prótesis</option>
                <option>Equipo de Rehabilitación</option>
                <option>Material Clínico</option>
                <option>Electroterapia</option>
                <option>Otro</option>
              </select>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Cantidad</label>
                <input v-model.number="form.cantidad" type="number" min="0" placeholder="0" required />
              </div>
              <div class="form-group">
                <label>Mantenimiento</label>
                <label class="checkbox-row">
                  <input type="checkbox" v-model="form.mantenimiento" />
                  <span>Marcar en mantenimiento</span>
                </label>
              </div>
            </div>

            <p class="estado-preview">
              Estado resultante:
              <span class="estado-badge" :class="estadoPreview">
                <span class="estado-dot"></span>
                {{ etiquetaEstado(estadoPreview) }}
              </span>
            </p>

            <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>

            <!-- Zona de peligro: solo visible al editar -->
            <div v-if="editingItem" class="danger-zone">
              <div class="danger-header">
                <span class="danger-icon">⚠</span>
                <span class="danger-label">Zona de peligro</span>
              </div>
              <button
                type="button"
                class="btn-eliminar"
                :disabled="saving"
                @click="deleteItem"
              >
                Eliminar equipo
              </button>
            </div>

            <div class="modal-actions">
              <button type="submit" class="btn-guardar" :disabled="saving">
                {{ saving ? 'Guardando...' : 'Guardar' }}
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
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import { inventarioService, asignacionesService, pacienteService, fisioterapeutaService } from '@/services/api'

// ── Estado ────────────────────────────────────────────────────────────────────
const showModal   = ref(false)
const editingItem = ref(null)
const saving      = ref(false)
const errorMsg    = ref('')

const form = ref({ nombre: '', modelo: '', tipo: 'Prótesis', cantidad: 1, mantenimiento: false })

const items             = ref([])
const todasAsignaciones = ref([])
const pacientes         = ref([])
const fisioterapeutas   = ref([])

const filtroAsignPaciente     = ref('')
const filtroAsignEspecialista = ref('')

// ── Stats (basados en stock calculado, no en valor de BD) ─────────────────────
const stats = computed(() => ({
  total:     items.value.length,
  enUso:     items.value.filter(i => estadoCalculado(i) === 'no_disponible').length,
  stockBajo: items.value.filter(i => estadoCalculado(i) === 'baja').length,
  alertas:   items.value.filter(i => disponiblesDeItem(i) === 0 && (i.cantidad ?? 0) > 0).length,
}))

// ── Disponibles ───────────────────────────────────────────────────────────────
function disponiblesDeItem(item) {
  const asignados = todasAsignaciones.value.filter(
    a => Number(a.inventario_id) === Number(item.id_inventario)
  ).length
  return Math.max(0, (item.cantidad ?? 0) - asignados)
}

function disponiblesClass(disponibles, total) {
  if (disponibles === 0) return 'disp-agotado'
  if (disponibles < 5)   return 'disp-bajo'
  return 'disp-ok'
}

// ── Umbral dinámico (configurable desde Configuración) ────────────────────────
function getUmbral() { return Number(localStorage.getItem('inventario_umbral') ?? 5) }

// ── Estado calculado desde stock real (ignora valor en BD salvo mantenimiento) ─
function estadoCalculado(item) {
  if (item.estado === 'mantenimiento') return 'mantenimiento'
  const disp  = disponiblesDeItem(item)
  const total = item.cantidad ?? 0
  if (disp === 0)                 return 'no_disponible'
  if (disp < getUmbral())         return 'baja'
  return 'disponible'
}

// ── Asignaciones enriquecidas (para tabla admin) ──────────────────────────────
const asignacionesEnriquecidas = computed(() =>
  todasAsignaciones.value.map(a => {
    const item  = items.value.find(i => Number(i.id_inventario) === Number(a.inventario_id))
    const pac   = pacientes.value.find(p => Number(p.paciente_id) === Number(a.paciente_id))
    const fisio = pac
      ? fisioterapeutas.value.find(f => Number(f.fisioterapeuta_id) === Number(pac.fisioterapeuta_id))
      : null
    return {
      ...a,
      equipo:           item?.nombre  ?? `Equipo #${a.inventario_id}`,
      paciente:         pac  ? `${pac.nombre} ${pac.apellido}`   : `Paciente #${a.paciente_id}`,
      especialista:     fisio ? `${fisio.nombre} ${fisio.apellido}` : '—',
      fisioterapeuta_id: pac?.fisioterapeuta_id ?? null,
      iniciales_pac:    pac  ? `${pac.nombre[0] ?? ''}${pac.apellido?.[0] ?? ''}`.toUpperCase() : '?',
      iniciales_fisio:  fisio ? `${fisio.nombre[0] ?? ''}${fisio.apellido?.[0] ?? ''}`.toUpperCase() : '—',
    }
  })
)

const asignacionesFiltradas = computed(() => {
  let r = asignacionesEnriquecidas.value
  if (filtroAsignPaciente.value)
    r = r.filter(a => Number(a.paciente_id) === Number(filtroAsignPaciente.value))
  if (filtroAsignEspecialista.value)
    r = r.filter(a => Number(a.fisioterapeuta_id) === Number(filtroAsignEspecialista.value))
  return r
})

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatFecha(fecha) {
  if (!fecha) return '—'
  return new Date(fecha + 'T00:00:00').toLocaleDateString('es-ES', {
    day: '2-digit', month: 'short', year: 'numeric'
  })
}

// ── Carga ─────────────────────────────────────────────────────────────────────
async function cargarItems() {
  const [ir, ar, pr, fr] = await Promise.allSettled([
    inventarioService.getAll(),
    asignacionesService.getAll(),
    pacienteService.getAll(),
    fisioterapeutaService.getAll(),
  ])
  if (ir.status === 'fulfilled') items.value             = ir.value.data
  if (ar.status === 'fulfilled') todasAsignaciones.value = ar.value.data
  if (pr.status === 'fulfilled') pacientes.value         = pr.value.data
  if (fr.status === 'fulfilled') fisioterapeutas.value   = fr.value.data
}

function closeModal() {
  showModal.value   = false
  editingItem.value = null
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const etiquetas = { disponible: 'Disponible', no_disponible: 'No Disponible', mantenimiento: 'Mantenimiento', baja: 'Stock Bajo' }
function etiquetaEstado(val) { return etiquetas[val] ?? val }

// Estado que se mostrará en el modal según la cantidad ingresada
const estadoPreview = computed(() => {
  if (form.value.mantenimiento) return 'mantenimiento'
  const cantidad  = form.value.cantidad ?? 0
  const asignados = editingItem.value
    ? todasAsignaciones.value.filter(a => Number(a.inventario_id) === Number(editingItem.value.id_inventario)).length
    : 0
  const disp = Math.max(0, cantidad - asignados)
  if (disp === 0 && cantidad > 0) return 'en_uso'
  if (disp < getUmbral())         return 'baja'
  return 'disponible'
})

// ── Modal ─────────────────────────────────────────────────────────────────────
function openModal(item) {
  editingItem.value = item
  errorMsg.value    = ''
  form.value = item
    ? { nombre: item.nombre, modelo: item.modelo ?? '', tipo: item.tipo, cantidad: item.cantidad, mantenimiento: item.estado === 'mantenimiento' }
    : { nombre: '', modelo: '', tipo: 'Prótesis', cantidad: 1, mantenimiento: false }
  showModal.value = true
}

// ── CRUD ──────────────────────────────────────────────────────────────────────
async function saveItem() {
  saving.value   = true
  errorMsg.value = ''
  try {
    const payload = {
      nombre:   form.value.nombre,
      modelo:   form.value.modelo,
      tipo:     form.value.tipo,
      cantidad: form.value.cantidad,
      estado:   estadoPreview.value,   // siempre se calcula automáticamente
    }
    if (editingItem.value) {
      await inventarioService.update(editingItem.value.id_inventario, payload)
    } else {
      await inventarioService.create(payload)
    }
    await cargarItems()
    closeModal()
  } catch {
    errorMsg.value = 'Error al guardar. Verifica los datos.'
  } finally {
    saving.value = false
  }
}

async function deleteItem() {
  if (!window.confirm(`¿Eliminar "${editingItem.value.nombre}"? Esta acción no se puede deshacer.`)) return
  saving.value = true
  try {
    await inventarioService.delete(editingItem.value.id_inventario)
    await cargarItems()
    closeModal()
  } catch {
    errorMsg.value = 'Error al eliminar el equipo.'
    saving.value   = false
  }
}

onMounted(cargarItems)
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

/* Header */
.inv-header {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;
}
.page-title { color: #ffffff; font-size: 1.4rem; font-weight: 700; }

.btn-add {
  background: #074434; color: #ffffff; border: none; border-radius: 8px;
  padding: 0.55rem 1rem; font-size: 0.85rem; font-weight: 600; cursor: pointer;
  transition: background 0.15s;
}
.btn-add:hover { background: #0a5c46; }

/* Stats */
.inv-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.9rem; margin-bottom: 1.25rem; }

.stat-box {
  background: #111111; border: 1px solid #1c1c1c; border-radius: 10px;
  padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: 0.5rem;
}
.stat-box.critical { background: #074434; border-color: #0a5c46; }

.sbox-label { color: #6b7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.stat-box.critical .sbox-label { color: rgba(255,255,255,0.65); }
.sbox-value { color: #ffffff; font-size: 2.2rem; font-weight: 700; line-height: 1; }
.sbox-row { display: flex; align-items: center; gap: 0.6rem; }
.dot-green { width: 10px; height: 10px; border-radius: 50%; background: #4ade80; flex-shrink: 0; }

/* Table */
.table-card {
  background: #111111; border: 1px solid #1c1c1c; border-radius: 10px; padding: 1.1rem 1.25rem;
}
.table-title { color: #ffffff; font-size: 0.95rem; font-weight: 600; margin-bottom: 0.9rem; }
.table-wrapper { overflow-x: auto; }

table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
thead tr { border-bottom: 1px solid #1c1c1c; }

th {
  text-align: left; color: #6b7280; font-size: 0.75rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.5px; padding: 0.6rem 0.75rem;
}
td { color: #d1d5db; padding: 0.75rem 0.75rem; border-bottom: 1px solid #161616; }
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: rgba(255,255,255,0.02); }

.td-center { text-align: center; }
.td-nombre { color: #ffffff; font-weight: 600; }
.td-empty  { text-align: center; color: #374151; padding: 2rem; font-style: italic; }

/* Disponibles */
.disp-val   { font-weight: 700; margin-right: 2px; }
.disp-total { color: #374151; font-size: 0.75rem; }
.disp-ok      { color: #4ade80; }
.disp-bajo    { color: #fbbf24; }
.disp-agotado { color: #f87171; }

/* Tag tipo */
.tag-tipo {
  background: rgba(107,114,128,0.15); color: #9ca3af;
  padding: 0.2rem 0.55rem; border-radius: 4px; font-size: 0.75rem; font-weight: 500;
}

/* Estado badge */
.estado-badge { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; font-weight: 500; }
.estado-badge .estado-dot { width: 8px; height: 8px; border-radius: 50%; }
.estado-badge.disponible   { color: #4ade80; }
.estado-badge.disponible .estado-dot { background: #4ade80; }
.estado-badge.no_disponible       { color: #f87171; }
.estado-badge.no_disponible .estado-dot { background: #f87171; }
.estado-badge.mantenimiento { color: #f59e0b; }
.estado-badge.mantenimiento .estado-dot { background: #f59e0b; }
.estado-badge.baja         { color: #f87171; }
.estado-badge.baja .estado-dot { background: #f87171; }

/* Action buttons */
.td-actions { display: flex; gap: 0.4rem; align-items: center; }

.btn-edit {
  background: #1c1c1c; color: #d1d5db; border: 1px solid #2a2a2a;
  border-radius: 5px; padding: 0.3rem 0.75rem; font-size: 0.78rem; font-weight: 600;
  cursor: pointer; transition: background 0.15s;
}
.btn-edit:hover { background: #2a2a2a; }

/* Modal */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.7);
  display: flex; align-items: center; justify-content: center; z-index: 100;
}

.modal {
  background: #111111; border: 1px solid #1c1c1c; border-radius: 12px;
  padding: 1.5rem; width: 100%; max-width: 440px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}

.modal-header {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;
}
.modal-header h3 { color: #ffffff; font-size: 1rem; font-weight: 700; }

.modal-close {
  background: none; border: none; color: #6b7280; cursor: pointer;
  padding: 0.25rem; border-radius: 5px; display: flex; transition: color 0.15s;
}
.modal-close:hover { color: #d1d5db; }

.modal-form { display: flex; flex-direction: column; gap: 1rem; }

.form-group { display: flex; flex-direction: column; gap: 0.35rem; }

.form-group label { color: #9ca3af; font-size: 0.8rem; font-weight: 600; }

.form-group input,
.form-group select {
  background: #0d0d0d; border: 1px solid #1c1c1c; border-radius: 7px;
  padding: 0.6rem 0.75rem; color: #d1d5db; font-size: 0.875rem;
  outline: none; width: 100%; transition: border-color 0.15s;
}
.form-group input:focus,
.form-group select:focus { border-color: #074434; box-shadow: 0 0 0 3px rgba(7,68,52,0.2); }
.form-group input::placeholder { color: #4b5563; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }

.estado-select {
  display: flex; align-items: center; gap: 0.5rem;
  background: #0d0d0d; border: 1px solid #1c1c1c; border-radius: 7px; padding: 0.6rem 0.75rem;
}
.estado-select .estado-dot { width: 8px; height: 8px; border-radius: 50%; background: #4ade80; flex-shrink: 0; }
.estado-select select { background: none; border: none; padding: 0; color: #d1d5db; font-size: 0.875rem; outline: none; width: 100%; }

.error-msg { color: #f87171; font-size: 0.8rem; margin: 0; }

/* Zona de peligro */
.danger-zone {
  border: 1px solid rgba(248, 113, 113, 0.2);
  border-radius: 8px;
  padding: 0.85rem 1rem;
  background: rgba(248, 113, 113, 0.04);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.danger-header { display: flex; align-items: center; gap: 0.4rem; }
.danger-icon   { color: #f87171; font-size: 0.85rem; }
.danger-label  { color: #f87171; font-size: 0.78rem; font-weight: 600; }

.btn-eliminar {
  background: rgba(248, 113, 113, 0.1);
  border: 1px solid rgba(248, 113, 113, 0.35);
  color: #f87171;
  padding: 0.38rem 0.85rem;
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.15s;
}
.btn-eliminar:hover:not(:disabled) { background: rgba(248,113,113,0.2); }
.btn-eliminar:disabled             { opacity: 0.4; cursor: not-allowed; }

/* Modal actions */
.modal-actions { display: flex; gap: 0.6rem; margin-top: 0.25rem; }

.btn-guardar {
  flex: 1; background: #074434; color: #ffffff; border: none; border-radius: 7px;
  padding: 0.65rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: background 0.15s;
}
.btn-guardar:hover:not(:disabled) { background: #0a5c46; }
.btn-guardar:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-cancelar {
  flex: 1; background: #1c1c1c; color: #9ca3af; border: 1px solid #2a2a2a; border-radius: 7px;
  padding: 0.65rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: background 0.15s, color 0.15s;
}
.btn-cancelar:hover { background: #2a2a2a; color: #d1d5db; }

/* ── Tabla de asignaciones ───────────────────────────────────────────────── */
.asig-card { margin-top: 1.5rem; }

.asig-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.9rem;
  flex-wrap: wrap;
}

.asig-sub { color: #4b5563; font-size: 0.75rem; margin-top: 0.15rem; }

.filtros-row { display: flex; gap: 0.6rem; flex-wrap: wrap; }

.filtro-select {
  background: #0d0d0d; border: 1px solid #1c1c1c; color: #9ca3af;
  padding: 0.42rem 0.75rem; border-radius: 7px; font-size: 0.8rem; cursor: pointer; outline: none;
}
.filtro-select:focus { border-color: #074434; }

.td-fecha { color: #6b7280; font-size: 0.82rem; }

/* Avatares */
.persona-cell { display: flex; align-items: center; gap: 0.55rem; }

.avatar {
  width: 28px; height: 28px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.64rem; font-weight: 700; flex-shrink: 0;
}
.av-pac   { background: #064e2e; color: #4ade80; }
.av-fisio { background: #0c2a4a; color: #38bdf8; }

/* Checkbox mantenimiento */
.checkbox-row {
  display: flex; align-items: center; gap: 0.5rem; cursor: pointer;
  background: #0d0d0d; border: 1px solid #1c1c1c; border-radius: 7px;
  padding: 0.6rem 0.75rem; color: #d1d5db; font-size: 0.875rem;
}
.checkbox-row input[type="checkbox"] { width: 15px; height: 15px; accent-color: #4ade80; cursor: pointer; }

/* Preview estado */
.estado-preview {
  font-size: 0.8rem; color: #6b7280; display: flex; align-items: center; gap: 0.5rem;
}
</style>
