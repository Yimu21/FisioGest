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
              <th>Teléfono</th>
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
              <td class="td-tel">{{ f.telefono || '—' }}</td>
              <td>
                <label class="toggle-label">
                  <input type="checkbox" v-model="f.activo" @change="toggleActivo(f)" />
                  <span class="toggle-track">
                    <span class="toggle-thumb"></span>
                  </span>
                  <span class="toggle-text" :class="f.activo ? 'on' : 'off'">
                    {{ f.activo ? 'Activo' : 'Inactivo' }}
                  </span>
                </label>
              </td>
              <td class="td-actions">
                <button class="btn-editar" @click="openModal(f)">Editar</button>
                <button class="btn-horario" @click="openEditHorario(f)">Horario</button>
                <button class="btn-eliminar" @click="eliminar(f)">Eliminar</button>
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
                <label>Teléfono</label>
                <input v-model="form.telefono" type="tel" placeholder="Ej: 7890-1234" />
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
                  <option :value="false">Inactivo</option>
                </select>
              </div>
            </div>

            <div class="modal-actions">
              <button type="submit" class="btn-guardar">Guardar</button>
              <button type="button" class="btn-cancelar" @click="closeModal">Cancelar</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── Modal Editar Horario ── -->
    <Teleport to="body">
      <div class="modal-overlay" v-if="showEditHorario" @click.self="closeEditHorario">
        <div class="modal modal-horario">
          <div class="modal-header">
            <h3>Horario — {{ horarioFisio?.nombre }}</h3>
            <button class="modal-close" @click="closeEditHorario">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <div class="horario-edit-grid">
            <div v-for="dia in diasSemana" :key="dia" class="horario-edit-row">
              <label class="day-toggle">
                <input type="checkbox" v-model="horarioForm[dia].activo" />
                <span class="day-track"><span class="day-thumb"></span></span>
                <span class="day-name" :class="{ 'day-off': !horarioForm[dia].activo }">{{ dia }}</span>
              </label>
              <div class="horario-times" :class="{ 'times-disabled': !horarioForm[dia].activo }">
                <input
                  class="time-input"
                  type="time"
                  v-model="horarioForm[dia].inicio"
                  :disabled="!horarioForm[dia].activo"
                />
                <span class="times-sep">–</span>
                <input
                  class="time-input"
                  type="time"
                  v-model="horarioForm[dia].fin"
                  :disabled="!horarioForm[dia].activo"
                />
              </div>
            </div>
          </div>

          <div class="modal-actions" style="margin-top:1.25rem;">
            <button class="btn-guardar" @click="guardarHorario">Guardar Horario</button>
            <button class="btn-cancelar" @click="closeEditHorario">Cancelar</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── Toast ── -->
    <Teleport to="body">
      <div class="toast" :class="toast.type" v-if="toast.visible">
        {{ toast.msg }}
      </div>
    </Teleport>

  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'

const showModal       = ref(false)
const showEditHorario = ref(false)
const editando        = ref(null)
const horarioFisio    = ref(null)
const horarioForm     = ref({})
const fisioterapeutas = ref([])
const toast           = ref({ visible: false, msg: '', type: 'success' })

const diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']

function defaultHorario() {
  return {
    Lunes:     { activo: true,  inicio: '09:00', fin: '17:00' },
    Martes:    { activo: true,  inicio: '09:00', fin: '17:00' },
    Miércoles: { activo: true,  inicio: '09:00', fin: '17:00' },
    Jueves:    { activo: true,  inicio: '09:00', fin: '17:00' },
    Viernes:   { activo: true,  inicio: '09:00', fin: '17:00' },
    Sábado:    { activo: false, inicio: '09:00', fin: '13:00' },
  }
}

const form = ref({ nombre: '', telefono: '', especialidad: 'Diagnóstico', activo: true })

const activos = computed(() => fisioterapeutas.value.filter(f => f.activo).length)

function showToast(msg, type = 'success') {
  toast.value = { visible: true, msg, type }
  setTimeout(() => { toast.value.visible = false }, 3000)
}

onMounted(async () => {
  try {
    const res  = await fetch('/api/fisioterapeutas')
    const data = await res.json()
    fisioterapeutas.value = data.map(f => ({
      id:           f.fisioterapeuta_id,
      nombre:       `${f.nombre} ${f.apellido}`.trim(),
      especialidad: f.especialidad ?? 'Diagnóstico',
      telefono:     f.telefono ?? '',
      activo:       !!f.activo,
      horario:      f.horario ?? defaultHorario(),
    }))
  } catch (e) {
    console.error('Error al cargar fisioterapeutas:', e)
    showToast('Error al cargar fisioterapeutas', 'error')
  }
})

function openModal(item) {
  editando.value = item
  form.value = item
    ? { nombre: item.nombre, telefono: item.telefono ?? '', especialidad: item.especialidad, activo: item.activo }
    : { nombre: '', telefono: '', especialidad: 'Diagnóstico', activo: true }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editando.value  = null
}

async function guardar() {
  try {
    const payload = {
      nombre:       form.value.nombre,
      especialidad: form.value.especialidad,
      telefono:     form.value.telefono,
      activo:       form.value.activo,
    }

    if (editando.value) {
      await fetch(`/api/fisioterapeutas/${editando.value.id}`, {
        method:  'PUT',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body:    JSON.stringify(payload),
      })
      const idx = fisioterapeutas.value.findIndex(f => f.id === editando.value.id)
      if (idx !== -1) fisioterapeutas.value[idx] = { ...fisioterapeutas.value[idx], ...form.value }
      showToast('Fisioterapeuta actualizado correctamente')
    } else {
      const res  = await fetch('/api/fisioterapeutas', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body:    JSON.stringify(payload),
      })
      const data = await res.json()
      fisioterapeutas.value.push({ id: data.id, ...form.value, horario: defaultHorario() })
      showToast('Fisioterapeuta registrado correctamente')
    }
  } catch (e) {
    console.error('Error al guardar fisioterapeuta:', e)
    showToast('Error al guardar', 'error')
  }
  closeModal()
}

async function toggleActivo(f) {
  try {
    await fetch(`/api/fisioterapeutas/${f.id}/activo`, {
      method:  'PATCH',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body:    JSON.stringify({ activo: f.activo }),
    })
    showToast(`Estado actualizado: ${f.activo ? 'Activo' : 'Inactivo'}`)
  } catch (e) {
    f.activo = !f.activo
    console.error('Error al actualizar estado:', e)
    showToast('Error al actualizar estado', 'error')
  }
}

async function eliminar(f) {
  if (!confirm(`¿Eliminar a ${f.nombre}? Esta acción no se puede deshacer.`)) return
  try {
    await fetch(`/api/fisioterapeutas/${f.id}`, {
      method:  'DELETE',
      headers: { 'Accept': 'application/json' },
    })
    fisioterapeutas.value = fisioterapeutas.value.filter(x => x.id !== f.id)
    showToast('Fisioterapeuta eliminado')
  } catch (e) {
    console.error('Error al eliminar:', e)
    showToast('Error al eliminar', 'error')
  }
}

function openEditHorario(f) {
  horarioFisio.value    = f
  horarioForm.value     = JSON.parse(JSON.stringify(f.horario ?? defaultHorario()))
  showEditHorario.value = true
}

function closeEditHorario() {
  showEditHorario.value = false
  horarioFisio.value    = null
}

async function guardarHorario() {
  try {
    await fetch(`/api/fisioterapeutas/${horarioFisio.value.id}/horario`, {
      method:  'PUT',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body:    JSON.stringify({ horario: horarioForm.value }),
    })
    const idx = fisioterapeutas.value.findIndex(f => f.id === horarioFisio.value.id)
    if (idx !== -1) fisioterapeutas.value[idx].horario = { ...horarioForm.value }
    closeEditHorario()
    showToast('Horario guardado')
  } catch (e) {
    console.error('Error al guardar horario:', e)
    showToast('Error al guardar horario', 'error')
  }
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

/* Modal horario editable */
.modal-horario { max-width: 500px; }

.horario-edit-grid {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
}

.horario-edit-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.55rem 0.8rem;
  background: #0d0d0d;
  border: 1px solid #1c1c1c;
  border-radius: 7px;
  transition: border-color 0.15s;
}
.horario-edit-row:has(.day-toggle input:checked) {
  border-color: #074434;
}

/* Toggle del día */
.day-toggle {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  user-select: none;
  min-width: 130px;
}
.day-toggle input[type="checkbox"] {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}
.day-track {
  position: relative;
  width: 34px;
  height: 19px;
  background: #2a2a2a;
  border-radius: 10px;
  transition: background 0.2s;
  flex-shrink: 0;
}
.day-toggle input:checked ~ .day-track { background: #074434; }
.day-thumb {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 15px;
  height: 15px;
  background: #6b7280;
  border-radius: 50%;
  transition: transform 0.2s, background 0.2s;
}
.day-toggle input:checked ~ .day-track .day-thumb {
  transform: translateX(15px);
  background: #ffffff;
}
.day-name {
  color: #f3f4f6;
  font-size: 0.82rem;
  font-weight: 600;
  transition: color 0.15s;
}
.day-name.day-off { color: #4b5563; }

/* Inputs de hora */
.horario-times {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: opacity 0.15s;
}
.horario-times.times-disabled { opacity: 0.3; pointer-events: none; }

.time-input {
  background: #1a1a1a;
  border: 1px solid #2a2a2a;
  border-radius: 6px;
  padding: 0.35rem 0.5rem;
  color: #f3f4f6;
  font-size: 0.82rem;
  font-family: inherit;
  outline: none;
  width: 88px;
  text-align: center;
  transition: border-color 0.15s;
}
.time-input:focus { border-color: #074434; box-shadow: 0 0 0 2px rgba(7,68,52,0.2); }
.time-input:disabled { cursor: not-allowed; }
.time-input::-webkit-calendar-picker-indicator { filter: invert(0.5); cursor: pointer; }

.times-sep {
  color: #6b7280;
  font-size: 0.85rem;
  font-weight: 600;
}

/* ── Teléfono en tabla ── */
.td-tel {
  color: #A9AFB2;
  font-size: 0.83rem;
}

/* ── Botón eliminar ── */
.btn-eliminar {
  background: rgba(220,38,38,0.12);
  color: #f87171;
  border: 1px solid rgba(220,38,38,0.25);
  border-radius: 5px;
  padding: 0.3rem 0.75rem;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.15s, border-color 0.15s;
}
.btn-eliminar:hover {
  background: rgba(220,38,38,0.22);
  border-color: rgba(220,38,38,0.5);
}

/* ── Toast ── */
.toast {
  position: fixed;
  bottom: 1.5rem;
  right: 1.5rem;
  padding: 0.7rem 1.2rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  z-index: 9999;
  animation: slideIn 0.2s ease;
}
.toast.success {
  background: #074434;
  color: #4ade80;
  border: 1px solid #0a5c46;
}
.toast.error {
  background: rgba(220,38,38,0.15);
  color: #f87171;
  border: 1px solid rgba(220,38,38,0.35);
}
@keyframes slideIn {
  from { transform: translateY(12px); opacity: 0; }
  to   { transform: translateY(0);    opacity: 1; }
}
</style>
