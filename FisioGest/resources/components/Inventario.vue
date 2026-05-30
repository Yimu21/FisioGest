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
              <th>Cantidad Total</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in items" :key="item.id">
              <td>{{ item.nombre }}</td>
              <td><span class="tag-tipo">{{ item.tipo }}</span></td>
              <td class="td-center">{{ item.cantidad }}</td>
              <td>
                <span class="estado-badge" :class="item.estado">
                  <span class="estado-dot"></span>
                  {{ etiquetaEstado(item.estado) }}
                </span>
              </td>
              <td class="td-actions">
                <button class="btn-edit" @click="openModal(item)">Edit</button>
                <button class="btn-assign">Assign</button>
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
              <input v-model="form.modelo" type="text" placeholder="Modelo/Marca" required />
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
                <label>Estado</label>
                <div class="estado-select">
                  <span class="estado-dot available"></span>
                  <select v-model="form.estado">
                    <option value="disponible">Disponible</option>
                    <option value="en_uso">En Uso</option>
                    <option value="mantenimiento">Mantenimiento</option>
                    <option value="baja">Stock Bajo</option>
                  </select>
                </div>
              </div>
            </div>

            <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>

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
import { inventarioService } from '@/services/api'

const showModal   = ref(false)
const editingItem = ref(null)
const saving      = ref(false)
const errorMsg    = ref('')

const form = ref({ nombre: '', modelo: '', tipo: 'Prótesis', cantidad: 1, estado: 'disponible' })

const items = ref([])

const stats = computed(() => ({
  total:     items.value.length,
  enUso:     items.value.filter(i => i.estado === 'en_uso').length,
  stockBajo: items.value.filter(i => i.estado === 'baja').length,
  alertas:   items.value.filter(i => i.cantidad <= 3).length,
}))

async function cargarItems() {
  try {
    const res = await inventarioService.getAll()
    items.value = res.data
  } catch {
    items.value = []
  }
}

function openModal(item) {
  editingItem.value = item
  errorMsg.value = ''
  if (item) {
    form.value = { nombre: item.nombre, modelo: item.modelo ?? '', tipo: item.tipo, cantidad: item.cantidad, estado: item.estado }
  } else {
    form.value = { nombre: '', modelo: '', tipo: 'Prótesis', cantidad: 1, estado: 'disponible' }
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingItem.value = null
}

const etiquetas = { disponible: 'Disponible', en_uso: 'En Uso', mantenimiento: 'Mantenimiento', baja: 'Stock Bajo' }
function etiquetaEstado(val) { return etiquetas[val] ?? val }

async function saveItem() {
  saving.value   = true
  errorMsg.value = ''
  try {
    await inventarioService.create(form.value)
    await cargarItems()
    closeModal()
  } catch {
    errorMsg.value = 'Error al guardar. Verifica los datos.'
  } finally {
    saving.value = false
  }
}

onMounted(cargarItems)
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

/* Header */
.inv-header {
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
.inv-stats {
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
  background: #074434;
  border-color: #0a5c46;
}

.sbox-label {
  color: #6b7280;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.stat-box.critical .sbox-label { color: rgba(255,255,255,0.65); }

.sbox-value {
  color: #ffffff;
  font-size: 2.2rem;
  font-weight: 700;
  line-height: 1;
}

.sbox-row {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.dot-green {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #4ade80;
  flex-shrink: 0;
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

thead tr {
  border-bottom: 1px solid #1c1c1c;
}

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

.td-center { text-align: center; }

.tag-tipo {
  background: rgba(107,114,128,0.15);
  color: #9ca3af;
  padding: 0.2rem 0.55rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
}

/* Estado badge */
.estado-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  font-weight: 500;
}

.estado-badge .estado-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.estado-badge.disponible { color: #4ade80; }
.estado-badge.disponible .estado-dot { background: #4ade80; }

.estado-badge.en_uso { color: #38bdf8; }
.estado-badge.en_uso .estado-dot { background: #38bdf8; }

.estado-badge.mantenimiento { color: #f59e0b; }
.estado-badge.mantenimiento .estado-dot { background: #f59e0b; }

.estado-badge.baja { color: #f87171; }
.estado-badge.baja .estado-dot { background: #f87171; }

/* Action buttons */
.td-actions { display: flex; gap: 0.4rem; align-items: center; }

.btn-edit {
  background: #1c1c1c;
  color: #d1d5db;
  border: 1px solid #2a2a2a;
  border-radius: 5px;
  padding: 0.3rem 0.7rem;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-edit:hover { background: #2a2a2a; }

.btn-assign {
  background: #074434;
  color: #ffffff;
  border: none;
  border-radius: 5px;
  padding: 0.3rem 0.7rem;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-assign:hover { background: #0a5c46; }

/* ── Modal ── */
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
  max-width: 440px;
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
}

.form-group input:focus,
.form-group select:focus {
  border-color: #074434;
  box-shadow: 0 0 0 3px rgba(7,68,52,0.2);
}

.form-group input::placeholder { color: #4b5563; }

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.estado-select {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #0d0d0d;
  border: 1px solid #1c1c1c;
  border-radius: 7px;
  padding: 0.6rem 0.75rem;
}

.estado-select .estado-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #4ade80;
  flex-shrink: 0;
}

.estado-select select {
  background: none;
  border: none;
  padding: 0;
  color: #d1d5db;
  font-size: 0.875rem;
  outline: none;
  width: 100%;
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
.btn-guardar:hover { background: #0a5c46; }

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
