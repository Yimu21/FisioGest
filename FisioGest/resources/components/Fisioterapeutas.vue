<template>
  <AppLayout>

    <h2 class="page-title">Fisioterapeutas</h2>

    <!-- ── Stat cards ── -->
    <div class="stats-row">
      <div class="stat-card">
        <span class="stat-label">Total Especialistas</span>
        <span class="stat-value">{{ fisioterapeutas.length }}</span>
      </div>
      <div class="stat-card active-card">
        <div class="active-top">
          <span class="stat-label">Activos Hoy</span>
          <span class="active-dot"></span>
        </div>
        <span class="stat-value">{{ activos }}</span>
        <div class="active-bar"></div>
      </div>
    </div>

    <!-- ── Table ── -->
    <div class="table-card">
      <div class="table-card-header">
        <h3 class="section-title">Fisioterapeutas Especialistas</h3>
        <button class="btn-nuevo" @click="openModal(null)">+ Nuevo Registro</button>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Foto</th>
              <th>Nombre Completo</th>
              <th>Especialidad</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="f in fisioterapeutas" :key="f.id">
              <td>
                <div class="avatar">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                  </svg>
                </div>
              </td>
              <td class="td-nombre">{{ f.nombre }}</td>
              <td class="td-esp">{{ f.especialidad }}</td>
              <td>
                <label class="toggle-label">
                  <input type="checkbox" v-model="f.activo" />
                  <span class="toggle-track">
                    <span class="toggle-thumb"></span>
                  </span>
                  <span class="toggle-text" :class="f.activo ? 'on' : 'off'">
                    {{ f.activo ? 'Activo' : 'Inactiva' }}
                  </span>
                </label>
              </td>
              <td class="td-actions">
                <button class="btn-editar" @click="openModal(f)">Editar</button>
                <button class="btn-horario" @click="openHorario(f)">Ver Horario</button>
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
            <h3>{{ editando ? 'Editar Fisioterapeuta' : 'Nuevo Registro' }}</h3>
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
                <label>Fecha de Nacimiento</label>
                <input v-model="form.fechaNacimiento" type="date" required />
              </div>
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label>Especialidad</label>
                <select v-model="form.especialidad">
                  <option>Diagnóstico</option>
                  <option>Rehabilitación</option>
                  <option>Neurología</option>
                  <option>Deportiva</option>
                  <option>Pediatría</option>
                  <option>Traumatología</option>
                </select>
              </div>
              <div class="form-group">
                <label>Estado</label>
                <select v-model="form.activo">
                  <option :value="true">Activo</option>
                  <option :value="false">Inactiva</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label>Correo electrónico</label>
              <input v-model="form.correo" type="email" placeholder="correo@ejemplo.com" />
            </div>

            <div class="modal-actions">
              <button type="submit" class="btn-guardar">Guardar</button>
              <button type="button" class="btn-cancelar" @click="closeModal">Cancelar</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── Modal Horario ── -->
    <Teleport to="body">
      <div class="modal-overlay" v-if="showHorario" @click.self="showHorario = false">
        <div class="modal">
          <div class="modal-header">
            <h3>Horario — {{ horarioDe?.nombre }}</h3>
            <button class="modal-close" @click="showHorario = false">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <div class="horario-grid">
            <div v-for="dia in dias" :key="dia" class="horario-row">
              <span class="horario-dia">{{ dia }}</span>
              <span class="horario-hora">09:00 – 17:00</span>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/components/AppLayout.vue'

const showModal   = ref(false)
const showHorario = ref(false)
const editando    = ref(null)
const horarioDe   = ref(null)

const dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes']

const form = ref({ nombre: '', fechaNacimiento: '', especialidad: 'Diagnóstico', activo: true, correo: '' })

const fisioterapeutas = ref([
  { id: 1, nombre: 'Manrivel Gorado', fechaNacimiento: '1994-03-09', especialidad: 'Traumatología', activo: true  },
  { id: 2, nombre: 'Barvis Raten',    fechaNacimiento: '1996-11-07', especialidad: 'Deportiva',     activo: false },
  { id: 3, nombre: 'Bardena Drides',  fechaNacimiento: '1991-05-22', especialidad: 'Deportiva',     activo: true  },
  { id: 4, nombre: 'Marina Gomez',    fechaNacimiento: '1996-07-05', especialidad: 'Traumatología', activo: true  },
  { id: 5, nombre: 'Retmen Nones',    fechaNacimiento: '1997-12-29', especialidad: 'Deportiva',     activo: false },
])

const activos = computed(() => fisioterapeutas.value.filter(f => f.activo).length)

function openModal(item) {
  editando.value = item
  form.value = item
    ? { nombre: item.nombre, fechaNacimiento: item.fechaNacimiento, especialidad: item.especialidad, activo: item.activo, correo: item.correo ?? '' }
    : { nombre: '', fechaNacimiento: '', especialidad: 'Diagnóstico', activo: true, correo: '' }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editando.value  = null
}

function guardar() {
  if (editando.value) {
    const idx = fisioterapeutas.value.findIndex(f => f.id === editando.value.id)
    if (idx !== -1) fisioterapeutas.value[idx] = { ...editando.value, ...form.value }
  } else {
    const nextId = Math.max(...fisioterapeutas.value.map(f => f.id)) + 1
    fisioterapeutas.value.push({ id: nextId, ...form.value })
  }
  closeModal()
}

function openHorario(f) {
  horarioDe.value  = f
  showHorario.value = true
}
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
  grid-template-columns: 1fr 1fr;
  gap: 0.9rem;
  margin-bottom: 1.25rem;
  max-width: 520px;
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

/* Active card */
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
  bottom: 0;
  left: 0;
  right: 0;
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
}

.section-title {
  color: #ffffff;
  font-size: 0.95rem;
  font-weight: 600;
}

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

/* Avatar */
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
}

.td-nombre {
  font-weight: 600;
  color: #f3f4f6;
}

.td-esp {
  color: #A9AFB2;
}

/* ── Toggle switch ── */
.toggle-label {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  user-select: none;
}

.toggle-label input[type="checkbox"] {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-track {
  position: relative;
  width: 38px;
  height: 21px;
  background: #2a2a2a;
  border-radius: 11px;
  transition: background 0.2s;
  flex-shrink: 0;
}

.toggle-label input:checked ~ .toggle-track {
  background: #074434;
}

.toggle-thumb {
  position: absolute;
  top: 2.5px;
  left: 2.5px;
  width: 16px;
  height: 16px;
  background: #6b7280;
  border-radius: 50%;
  transition: transform 0.2s, background 0.2s;
}

.toggle-label input:checked ~ .toggle-track .toggle-thumb {
  transform: translateX(17px);
  background: #ffffff;
}

.toggle-text {
  font-size: 0.8rem;
  font-weight: 500;
}
.toggle-text.on  { color: #4ade80; }
.toggle-text.off { color: #6b7280; }

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
  white-space: nowrap;
  transition: background 0.15s;
}
.btn-editar:hover { background: #2a2a2a; }

.btn-horario {
  background: #074434;
  color: #ffffff;
  border: none;
  border-radius: 5px;
  padding: 0.3rem 0.75rem;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.15s;
}
.btn-horario:hover { background: #0a5c46; }

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
.form-group select {
  background: #0d0d0d;
  border: 1px solid #1c1c1c;
  border-radius: 7px;
  padding: 0.6rem 0.75rem;
  color: #f3f4f6;
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

/* Horario modal */
.horario-grid {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.horario-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.6rem 0.75rem;
  background: #0d0d0d;
  border: 1px solid #1c1c1c;
  border-radius: 7px;
}

.horario-dia {
  color: #A9AFB2;
  font-size: 0.82rem;
  font-weight: 600;
}

.horario-hora {
  color: #f3f4f6;
  font-size: 0.82rem;
  font-weight: 500;
}
</style>
