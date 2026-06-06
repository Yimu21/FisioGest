<template>
  <AppLayout>

    <h2 class="page-title">Contactos</h2>

    <!-- ── Stat cards ── -->
    <div class="stats-row">
      <div class="stat-card">
        <span class="stat-label">Total Contactos</span>
        <span class="stat-value">{{ stats.total }}</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Fisioterapeutas</span>
        <span class="stat-value">{{ stats.fisioterapeutas }}</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Referidos</span>
        <span class="stat-value">{{ stats.referidos }}</span>
      </div>
      <div class="stat-card active-card">
        <div class="active-top">
          <span class="stat-label">Proveedores</span>
          <span class="active-dot"></span>
        </div>
        <span class="stat-value">{{ stats.proveedores }}</span>
        <div class="active-bar"></div>
      </div>
    </div>

    <!-- ── Table ── -->
    <div class="table-card">
      <div class="table-card-header">
        <h3 class="section-title">Directorio de Contactos</h3>
        <div class="header-right">
          <!-- Filtros de tipo -->
          <div class="filter-tabs">
            <button
              v-for="t in tiposOpciones" :key="t.value"
              :class="['tab-btn', tipoFiltro === t.value && 'tab-active']"
              @click="tipoFiltro = t.value; cargar()"
            >{{ t.label }}</button>
          </div>
          <!-- Búsqueda -->
          <input
            v-model="search"
            @input="cargar"
            placeholder="Buscar contacto..."
            class="search-input"
          />
          <button class="btn-nuevo" @click="openModal(null)">+ Añadir Nuevo Contacto</button>
        </div>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Tipo</th>
              <th>Teléfono</th>
              <th>Email</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="6" class="td-loading">Cargando...</td>
            </tr>
            <tr v-else-if="contactos.length === 0">
              <td colspan="6" class="td-loading">No hay contactos registrados.</td>
            </tr>
            <tr v-else v-for="c in contactos" :key="c.id">
              <td>
                <div class="name-cell">
                  <div class="avatar">{{ initials(c.nombre) }}</div>
                  <span class="td-nombre">{{ c.nombre }}</span>
                </div>
              </td>
              <td>
                <span :class="['badge', badgeClass[c.tipo]]">{{ c.tipo }}</span>
              </td>
              <td class="td-muted">{{ c.telefono || '—' }}</td>
              <td class="td-muted">{{ c.email || '—' }}</td>
              <td>
                <div class="status-cell">
                    <span :class="['status-dot', dotClass[c.estado]]"></span>
                     <span :class="['status-text', dotClass[c.estado]]">
                        {{ c.estado.charAt(0).toUpperCase() + c.estado.slice(1) }}
                    </span>
                </div>
              </td>
              <td class="td-actions">
                <button class="btn-editar" @click="openModal(c)">Editar</button>
                <button class="btn-eliminar" @click="eliminar(c.id)">Eliminar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── Modal Nuevo / Editar ── -->
    <Teleport to="body">
      <div class="modal-overlay" v-if="showModal" @click.self="closeModal">
        <div class="modal">
          <div class="modal-header">
            <h3>{{ editando ? 'Editar Contacto' : 'Nuevo Contacto' }}</h3>
            <button class="modal-close" @click="closeModal">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <form class="modal-form" @submit.prevent="guardar">
            <div class="form-row-2">
              <div class="form-group">
                <label>Nombre Completo</label>
                <input v-model="form.nombre" type="text" placeholder="Nombre completo" required />
              </div>
              <div class="form-group">
                <label>Tipo</label>
                <select v-model="form.tipo" required>
                  <option value="fisioterapeuta">Fisioterapeuta</option>
                  <option value="paciente">Paciente</option>
                  <option value="referido">Referido</option>
                  <option value="proveedor">Proveedor</option>
                </select>
              </div>
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label>Teléfono</label>
                <input v-model="form.telefono" type="text" placeholder="+503 0000-0000" />
              </div>
              <div class="form-group">
                <label>Estado</label>
                <select v-model="form.estado">
                  <option value="activo">Activo</option>
                  <option value="inactivo">Inactivo</option>
                  <option value="pendiente">Pendiente</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label>Correo electrónico</label>
              <input v-model="form.email" type="email" placeholder="correo@ejemplo.com" />
            </div>

            <div class="form-group">
              <label>Notas</label>
              <textarea v-model="form.notas" rows="3" placeholder="Observaciones..."></textarea>
            </div>

            <div class="modal-actions">
              <button type="submit" class="btn-guardar">
                {{ editando ? 'Actualizar' : 'Guardar' }}
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

const showModal  = ref(false)
const editando   = ref(null)
const loading    = ref(false)
const contactos  = ref([])
const search     = ref('')
const tipoFiltro = ref('')

const stats = ref({ total: 0, fisioterapeutas: 0, referidos: 0, proveedores: 0 })

const form = ref({ nombre: '', tipo: 'paciente', telefono: '', email: '', estado: 'activo', notas: '' })

const tiposOpciones = [
  { value: '', label: 'Todos' },
  { value: 'fisioterapeuta', label: 'Fisioterapeuta' },
  { value: 'paciente',       label: 'Paciente' },
  { value: 'referido',       label: 'Referido' },
  { value: 'proveedor',      label: 'Proveedor' },
]

const badgeClass = {
  fisioterapeuta: 'badge-fis',
  paciente:       'badge-pac',
  referido:       'badge-ref',
  proveedor:      'badge-prov',
}

const dotClass = {
  activo:   'dot-active',
  inactivo: 'dot-inactive',
  pendiente:'dot-pending',
}

function initials(nombre) {
  return nombre.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase()
}

async function cargar() {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (search.value)     params.append('search', search.value)
    if (tipoFiltro.value) params.append('tipo', tipoFiltro.value)

    const res  = await fetch(`/api/contactos?${params}`)
    const data = await res.json()
    contactos.value = data.data ?? data
  } catch (e) {
    console.error('Error al cargar contactos:', e)
  } finally {
    loading.value = false
  }
}

async function cargarStats() {
  try {
    const res  = await fetch('/api/contactos/stats')
    const data = await res.json()
    stats.value = data
  } catch (e) {
    console.error('Error al cargar stats:', e)
  }
}

function openModal(item) {
  editando.value = item
  form.value = item
    ? { nombre: item.nombre, tipo: item.tipo, telefono: item.telefono ?? '', email: item.email ?? '', estado: item.estado, notas: item.notas ?? '' }
    : { nombre: '', tipo: 'paciente', telefono: '', email: '', estado: 'activo', notas: '' }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editando.value  = null
}

async function guardar() {
  try {
    if (editando.value) {
      await fetch(`/api/contactos/${editando.value.id}`, {
        method:  'PUT',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body:    JSON.stringify(form.value),
      })
    } else {
      await fetch('/api/contactos', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body:    JSON.stringify(form.value),
      })
    }
    closeModal()
    cargar()
    cargarStats()
  } catch (e) {
    console.error('Error al guardar contacto:', e)
  }
}

async function eliminar(id) {
  if (!confirm('¿Eliminar este contacto?')) return
  try {
    await fetch(`/api/contactos/${id}`, {
      method:  'DELETE',
      headers: { 'Accept': 'application/json' },
    })
    cargar()
    cargarStats()
  } catch (e) {
    console.error('Error al eliminar contacto:', e)
  }
}

onMounted(() => {
  cargar()
  cargarStats()
})
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

.page-title {
  color: #ffffff;
  font-size: 1.4rem;
  font-weight: 700;
  margin-bottom: 1.25rem;
}

/* ── Stat cards ── */
.stats-row {
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
  flex-direction: column;
  gap: 0.5rem;
}

.stat-label {
  color: #A9AFB2;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.stat-value {
  color: #ffffff;
  font-size: 2.4rem;
  font-weight: 700;
  line-height: 1;
}

.active-card {
  border-color: #074434;
  position: relative;
  overflow: hidden;
}

.active-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.active-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #4ade80;
  box-shadow: 0 0 8px rgba(74,222,128,0.5);
}

.active-bar {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 3px;
  background: #074434;
}

/* ── Table card ── */
.table-card {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 10px;
  overflow: hidden;
}

.table-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem 0.75rem;
  border-bottom: 1px solid #1c1c1c;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-wrap: wrap;
}

.section-title {
  color: #ffffff;
  font-size: 0.95rem;
  font-weight: 600;
}

/* Filter tabs */
.filter-tabs {
  display: flex;
  gap: 0.3rem;
}

.tab-btn {
  background: transparent;
  border: 1px solid #1c1c1c;
  color: #6b7280;
  border-radius: 6px;
  padding: 0.3rem 0.65rem;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}
.tab-btn:hover { border-color: #2a2a2a; color: #d1d5db; }
.tab-active {
  background: rgba(7,68,52,0.3);
  border-color: #074434;
  color: #4ade80;
}

/* Search */
.search-input {
  background: #0d0d0d;
  border: 1px solid #1c1c1c;
  border-radius: 7px;
  padding: 0.4rem 0.75rem;
  color: #f3f4f6;
  font-size: 0.8rem;
  outline: none;
  width: 180px;
  transition: border-color 0.15s;
}
.search-input::placeholder { color: #4b5563; }
.search-input:focus { border-color: #074434; }

.btn-nuevo {
  background: #074434;
  color: #ffffff;
  border: none;
  border-radius: 7px;
  padding: 0.45rem 0.9rem;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
  white-space: nowrap;
}
.btn-nuevo:hover { background: #0a5c46; }

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
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 0.7rem 1rem;
}

td {
  color: #d1d5db;
  padding: 0.8rem 1rem;
  border-bottom: 1px solid #161616;
  vertical-align: middle;
}

tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: rgba(255,255,255,0.02); }

.td-loading {
  text-align: center;
  color: #4b5563;
  padding: 2rem;
}

/* Name cell */
.name-cell {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(7,68,52,0.3);
  border: 1px solid #074434;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4ade80;
  font-size: 0.72rem;
  font-weight: 700;
  flex-shrink: 0;
}

.td-nombre {
  font-weight: 600;
  color: #f3f4f6;
}

.td-muted { color: #A9AFB2; }

/* Badges de tipo */
.badge {
  padding: 0.2rem 0.6rem;
  border-radius: 5px;
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: capitalize;
}
.badge-fis  { background: rgba(74,222,128,0.12); color: #4ade80; }
.badge-pac  { background: rgba(96,165,250,0.12); color: #60a5fa; }
.badge-ref  { background: rgba(192,132,252,0.12); color: #c084fc; }
.badge-prov { background: rgba(251,146,60,0.12); color: #fb923c; }

/* Estado */
.status-cell {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  flex-shrink: 0;
}
.dot-active   { background: #4ade80; box-shadow: 0 0 6px rgba(74,222,128,0.4); }
.dot-inactive { background: #4b5563; }
.dot-pending  { background: #f59e0b; box-shadow: 0 0 6px rgba(245,158,11,0.4); }

.status-text { font-size: 0.82rem; font-weight: 500; }
.text-activo   { color: #4ade80; }
.text-inactivo { color: #6b7280; }
.text-pendiente{ color: #f59e0b; }

/* Action buttons */
.td-actions { display: flex; gap: 0.4rem; align-items: center; }

.btn-editar {
  background: #1c1c1c;
  color: #d1d5db;
  border: 1px solid #2a2a2a;
  border-radius: 5px;
  padding: 0.3rem 0.75rem;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-editar:hover { background: #2a2a2a; }

.btn-eliminar {
  background: rgba(153,27,27,0.2);
  color: #fca5a5;
  border: 1px solid rgba(153,27,27,0.4);
  border-radius: 5px;
  padding: 0.3rem 0.75rem;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-eliminar:hover { background: rgba(153,27,27,0.35); }

/* ── Modal ── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.72);
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

.modal-header h3 {
  color: #ffffff;
  font-size: 1rem;
  font-weight: 700;
}

.modal-close {
  background: none;
  border: none;
  color: #A9AFB2;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 5px;
  display: flex;
  transition: color 0.15s;
}
.modal-close:hover { color: #ffffff; }

.modal-form { display: flex; flex-direction: column; gap: 1rem; }

.form-row-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.form-group { display: flex; flex-direction: column; gap: 0.35rem; }

.form-group label {
  color: #A9AFB2;
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
  color: #f3f4f6;
  font-size: 0.875rem;
  font-family: inherit;
  outline: none;
  width: 100%;
  transition: border-color 0.15s;
  resize: vertical;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #074434;
  box-shadow: 0 0 0 3px rgba(7,68,52,0.2);
}

.form-group input::placeholder,
.form-group textarea::placeholder { color: #4b5563; }

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
.btn-guardar:hover { background: #0a5c46; }

.btn-cancelar {
  flex: 1;
  background: #1c1c1c;
  color: #A9AFB2;
  border: 1px solid #2a2a2a;
  border-radius: 7px;
  padding: 0.65rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}
.btn-cancelar:hover { background: #2a2a2a; color: #ffffff; }
</style>