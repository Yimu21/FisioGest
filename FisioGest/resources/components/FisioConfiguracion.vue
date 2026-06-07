<template>
  <FisioLayout>

    <div class="cfg-header">
      <h2 class="page-title">Configuración</h2>
      <p class="page-sub">Administra tu perfil y acceso al sistema</p>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button class="tab-btn" :class="{ active: tab === 'perfil' }"   @click="tab = 'perfil'">👤 Mi Perfil</button>
      <button class="tab-btn" :class="{ active: tab === 'cuenta' }"   @click="tab = 'cuenta'">🔒 Seguridad</button>
      <button class="tab-btn" :class="{ active: tab === 'exportar' }" @click="tab = 'exportar'">📤 Exportar datos</button>
    </div>

    <!-- ══════════════════════════════════════════
         TAB 1 — Mi Perfil
    ══════════════════════════════════════════ -->
    <div v-if="tab === 'perfil'" class="cfg-card">
      <div class="perfil-top">
        <div class="avatar-grande">{{ initials }}</div>
        <div>
          <h3 class="sec-title">{{ perfil.nombre }} {{ perfil.apellido }}</h3>
          <p class="sec-sub" style="margin:0">{{ perfil.especialidad || 'Sin especialidad definida' }}</p>
        </div>
      </div>

      <div class="divider"></div>

      <form class="cfg-form" @submit.prevent="guardarPerfil">
        <div class="form-row">
          <div class="form-group">
            <label>Nombre</label>
            <input v-model="perfil.nombre" type="text" placeholder="Tu nombre" required />
          </div>
          <div class="form-group">
            <label>Apellido</label>
            <input v-model="perfil.apellido" type="text" placeholder="Tu apellido" required />
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Especialidad</label>
            <select v-model="perfil.especialidad">
              <option value="">Sin especificar</option>
              <option>Traumatología</option>
              <option>Deportiva</option>
              <option>Neurológica</option>
              <option>Rehabilitación</option>
              <option>Pediátrica</option>
              <option>Geriatría</option>
              <option>Respiratoria</option>
              <option>Otro</option>
            </select>
          </div>
          <div class="form-group">
            <label>Teléfono</label>
            <input v-model="perfil.telefono" type="text" placeholder="+591 XXXXXXXX" />
          </div>
        </div>

        <p v-if="errorPerfil" class="error-msg">{{ errorPerfil }}</p>

        <div class="form-actions">
          <button type="submit" class="btn-save" :disabled="guardando">
            {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
          </button>
        </div>
      </form>
    </div>

    <!-- ══════════════════════════════════════════
         TAB 2 — Seguridad / Contraseña
    ══════════════════════════════════════════ -->
    <div v-if="tab === 'cuenta'" class="cfg-card">
      <h3 class="sec-title">Cambiar Contraseña</h3>
      <p class="sec-sub">Por seguridad, ingresa tu contraseña actual para confirmar el cambio.</p>

      <form class="cfg-form" @submit.prevent="cambiarContrasena">
        <div class="form-group" style="max-width:400px">
          <label>Contraseña actual</label>
          <div class="pass-wrap">
            <input v-model="cuenta.actual" :type="showPassActual ? 'text' : 'password'" placeholder="••••••••" autocomplete="current-password" required />
            <button type="button" class="pass-eye" @click="showPassActual = !showPassActual" tabindex="-1"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><template v-if="showPassActual"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></template><template v-else><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></template></svg></button>
          </div>
        </div>
        <div class="form-row" style="max-width:820px">
          <div class="form-group">
            <label>Nueva contraseña</label>
            <div class="pass-wrap">
              <input v-model="cuenta.nueva" :type="showPassNueva ? 'text' : 'password'" placeholder="••••••••" autocomplete="new-password" required />
              <button type="button" class="pass-eye" @click="showPassNueva = !showPassNueva" tabindex="-1"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><template v-if="showPassNueva"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></template><template v-else><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></template></svg></button>
            </div>
          </div>
          <div class="form-group">
            <label>Confirmar nueva contraseña</label>
            <div class="pass-wrap">
              <input v-model="cuenta.confirmar" :type="showPassConfirm ? 'text' : 'password'" placeholder="••••••••" autocomplete="new-password" required />
              <button type="button" class="pass-eye" @click="showPassConfirm = !showPassConfirm" tabindex="-1"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><template v-if="showPassConfirm"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></template><template v-else><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></template></svg></button>
            </div>
          </div>
        </div>

        <p v-if="errorCuenta" class="error-msg">{{ errorCuenta }}</p>

        <div class="form-actions">
          <button type="submit" class="btn-save" :disabled="guardando">
            {{ guardando ? 'Actualizando...' : 'Cambiar contraseña' }}
          </button>
        </div>
      </form>
    </div>

    <!-- ══════════════════════════════════════════
         TAB 3 — Exportar datos
    ══════════════════════════════════════════ -->
    <div v-if="tab === 'exportar'" class="cfg-card">
      <h3 class="sec-title">Exportar Mis Datos</h3>
      <p class="sec-sub">Descarga la información de tus pacientes y citas en PDF, Excel o CSV.</p>

      <div class="export-grid">

        <!-- Pacientes -->
        <div class="export-card">
          <div class="export-icon" style="background:rgba(56,189,248,0.1);color:#38bdf8">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
            </svg>
          </div>
          <div class="export-info">
            <h4>Mis Pacientes</h4>
            <p>Nombre, género, fecha de nacimiento y teléfono de tus {{ misPacientes.length }} pacientes asignados.</p>
          </div>
          <div class="export-btns">
            <button class="btn-export btn-pdf"   :disabled="exportando" @click="exportarPacientes('pdf')">📄 PDF</button>
            <button class="btn-export btn-excel" :disabled="exportando" @click="exportarPacientes('xlsx')">📊 Excel</button>
            <button class="btn-export btn-csv"   :disabled="exportando" @click="exportarPacientes('csv')">📋 CSV</button>
          </div>
        </div>

        <!-- Citas -->
        <div class="export-card export-card-citas">
          <div class="export-icon-row">
            <div class="export-icon" style="background:rgba(74,222,128,0.1);color:#4ade80">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
            </div>
            <div class="export-info">
              <h4>Mis Citas</h4>
              <p>Fecha, paciente, estado y motivo. Puedes filtrar por paciente.</p>
            </div>
          </div>

          <!-- Filtro por paciente -->
          <div class="filtro-row">
            <label class="filtro-label">Filtrar por paciente:</label>
            <select v-model="filtroPacienteCitas" class="filtro-select">
              <option value="">Todos los pacientes</option>
              <option v-for="p in misPacientes" :key="p.paciente_id" :value="p.paciente_id">
                {{ p.nombre }} {{ p.apellido }}
              </option>
            </select>
            <span class="filtro-count">{{ citasFiltradas.length }} cita{{ citasFiltradas.length !== 1 ? 's' : '' }}</span>
          </div>

          <div class="export-btns" style="padding-left:0">
            <button class="btn-export btn-pdf"   :disabled="exportando" @click="exportarCitas('pdf')">📄 PDF</button>
            <button class="btn-export btn-excel" :disabled="exportando" @click="exportarCitas('xlsx')">📊 Excel</button>
            <button class="btn-export btn-csv"   :disabled="exportando" @click="exportarCitas('csv')">📋 CSV</button>
          </div>
        </div>

      </div>
    </div>

    <!-- Toast -->
    <Teleport to="body">
      <Transition name="toast-slide">
        <div v-if="toast.visible" class="cfg-toast" :class="toast.tipo">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline v-if="toast.tipo === 'ok'" points="20 6 9 17 4 12"/>
            <circle v-else cx="12" cy="12" r="10"/>
          </svg>
          {{ toast.msg }}
        </div>
      </Transition>
    </Teleport>

  </FisioLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import FisioLayout from '@/components/FisioLayout.vue'
import { getUser, saveUser } from '@/services/api'
import axios from 'axios'

const tab         = ref('perfil')
const showPassActual  = ref(false)
const showPassNueva   = ref(false)
const showPassConfirm = ref(false)
const guardando   = ref(false)
const exportando  = ref(false)
const errorPerfil = ref('')
const errorCuenta = ref('')

// ── Datos para exportación ────────────────────────────────────────────────────
const misPacientes       = ref([])
const misCitas           = ref([])
const filtroPacienteCitas = ref('')

const citasFiltradas = computed(() => {
  if (!filtroPacienteCitas.value) return misCitas.value
  return misCitas.value.filter(c => Number(c.paciente_id) === Number(filtroPacienteCitas.value))
})
const toast = ref({ visible: false, tipo: 'ok', msg: '' })
let toastTimer = null

const perfil = ref({ nombre: '', apellido: '', especialidad: '', telefono: '' })
const cuenta = ref({ actual: '', nueva: '', confirmar: '' })

const initials = computed(() => {
  const n = `${perfil.value.nombre} ${perfil.value.apellido}`.trim() || 'F'
  return n.split(' ').map(w => w[0] ?? '').join('').toUpperCase().slice(0, 2)
})

function authHeaders() {
  return { headers: { Authorization: `Bearer ${localStorage.getItem('token')}`, Accept: 'application/json' } }
}

function mostrarToast(tipo, msg) {
  clearTimeout(toastTimer)
  toast.value = { visible: true, tipo, msg }
  toastTimer = setTimeout(() => { toast.value.visible = false }, 3500)
}

async function guardarPerfil() {
  errorPerfil.value = ''
  guardando.value   = true
  try {
    const res = await axios.put('/api/fisio/mi-perfil', {
      nombre:       perfil.value.nombre,
      apellido:     perfil.value.apellido,
      especialidad: perfil.value.especialidad,
      telefono:     perfil.value.telefono,
    }, authHeaders())

    if (!res.data.success) { errorPerfil.value = res.data.message; return }

    // Actualizar nombre en localStorage
    const user = getUser()
    if (user) saveUser({ ...user, nombre: `${perfil.value.nombre} ${perfil.value.apellido}` }, localStorage.getItem('token'))

    mostrarToast('ok', 'Perfil actualizado correctamente.')
  } catch (e) {
    errorPerfil.value = e?.response?.data?.message ?? 'Error al guardar el perfil.'
  } finally {
    guardando.value = false
  }
}

async function cambiarContrasena() {
  errorCuenta.value = ''
  if (cuenta.value.nueva !== cuenta.value.confirmar) {
    errorCuenta.value = 'Las contraseñas nuevas no coinciden.'
    return
  }
  guardando.value = true
  try {
    const res = await axios.put('/api/fisio/mi-cuenta', {
      contrasena_actual: cuenta.value.actual,
      contrasena_nueva:  cuenta.value.nueva,
    }, authHeaders())

    if (!res.data.success) { errorCuenta.value = res.data.message; return }

    cuenta.value = { actual: '', nueva: '', confirmar: '' }
    mostrarToast('ok', 'Contraseña actualizada correctamente.')
  } catch (e) {
    errorCuenta.value = e?.response?.data?.message ?? 'Error al cambiar la contraseña.'
  } finally {
    guardando.value = false
  }
}

async function cargar() {
  try {
    const [perfilRes, pacRes, citRes] = await Promise.allSettled([
      axios.get('/api/fisio/mi-perfil',      authHeaders()),
      axios.get('/api/fisio/mis-pacientes',  authHeaders()),
      axios.get('/api/fisio/mis-citas',      authHeaders()),
    ])
    if (perfilRes.status === 'fulfilled' && perfilRes.value.data) {
      const d = perfilRes.value.data
      perfil.value = { nombre: d.nombre ?? '', apellido: d.apellido ?? '', especialidad: d.especialidad ?? '', telefono: d.telefono ?? '' }
    }
    if (pacRes.status === 'fulfilled') misPacientes.value = pacRes.value.data
    if (citRes.status === 'fulfilled') misCitas.value     = citRes.value.data
  } catch {}
}

// ── Helpers de nombre ─────────────────────────────────────────────────────────
function nombrePaciente(id) {
  const p = misPacientes.value.find(x => Number(x.paciente_id) === Number(id))
  return p ? `${p.nombre} ${p.apellido}` : `Paciente #${id}`
}

// ── Generadores de archivo ────────────────────────────────────────────────────
function descargarBlob(blob, nombre) {
  const a = document.createElement('a')
  a.href = URL.createObjectURL(blob)
  a.download = nombre
  a.click()
  URL.revokeObjectURL(a.href)
}

function generarCSV(cols, filas, nombre) {
  const header = cols.join(',')
  const rows   = filas.map(r => r.map(c => `"${String(c ?? '').replace(/"/g, '""')}"`).join(',')).join('\n')
  descargarBlob(new Blob(['﻿' + header + '\n' + rows], { type: 'text/csv;charset=utf-8' }), nombre + '.csv')
}

async function generarExcel(cols, filas, nombre) {
  const XLSX = await import('xlsx')
  const wb   = XLSX.utils.book_new()
  const ws   = XLSX.utils.aoa_to_sheet([cols, ...filas])
  ws['!cols'] = cols.map(() => ({ wch: 22 }))
  XLSX.utils.book_append_sheet(wb, ws, nombre)
  XLSX.writeFile(wb, nombre + '_' + hoy() + '.xlsx')
}

async function generarPDF(cols, filas, titulo, nombre) {
  const { default: jsPDF } = await import('jspdf')
  await import('jspdf-autotable')
  const doc = new jsPDF({ orientation: filas[0]?.length > 4 ? 'landscape' : 'portrait' })
  doc.setFontSize(15); doc.setTextColor(40)
  doc.text(`${perfil.value.nombre} ${perfil.value.apellido} — ${titulo}`, 14, 16)
  doc.setFontSize(9); doc.setTextColor(100)
  doc.text(`Generado: ${new Date().toLocaleDateString('es-ES')}`, 14, 23)
  doc.autoTable({
    head: [cols], body: filas, startY: 28,
    styles: { fontSize: 8, cellPadding: 3 },
    headStyles: { fillColor: [7, 68, 52], textColor: 255, fontStyle: 'bold' },
    alternateRowStyles: { fillColor: [245, 245, 245] },
  })
  doc.save(nombre + '_' + hoy() + '.pdf')
}

function hoy() { return new Date().toISOString().slice(0, 10) }

// ── Exportar pacientes ────────────────────────────────────────────────────────
async function exportarPacientes(formato) {
  exportando.value = true
  try {
    const cols  = ['Nombre', 'Apellido', 'Género', 'Fecha Nacimiento', 'Teléfono']
    const filas = misPacientes.value.map(p => [p.nombre, p.apellido, p.genero ?? '—', p.fecha_nacimiento ?? '—', p.telefono ?? '—'])
    if (formato === 'csv')  generarCSV(cols, filas, `mis_pacientes_${hoy()}`)
    if (formato === 'xlsx') await generarExcel(cols, filas, 'Mis Pacientes')
    if (formato === 'pdf')  await generarPDF(cols, filas, 'Mis Pacientes', 'mis_pacientes')
    mostrarToast('ok', 'Pacientes exportados correctamente.')
  } catch { mostrarToast('err', 'Error al exportar.') }
  finally { exportando.value = false }
}

// ── Exportar citas ────────────────────────────────────────────────────────────
async function exportarCitas(formato) {
  exportando.value = true
  try {
    const cols  = ['Fecha y Hora', 'Paciente', 'Estado', 'Motivo']
    const filas = citasFiltradas.value.map(c => [
      c.fecha_hora ?? '—',
      nombrePaciente(c.paciente_id),
      c.estado ?? '—',
      c.motivo ?? '—',
    ])
    const sufijo = filtroPacienteCitas.value
      ? `_${nombrePaciente(filtroPacienteCitas.value).replace(/ /g, '_')}`
      : ''
    if (formato === 'csv')  generarCSV(cols, filas, `mis_citas${sufijo}_${hoy()}`)
    if (formato === 'xlsx') await generarExcel(cols, filas, `Mis Citas${sufijo}`)
    if (formato === 'pdf')  await generarPDF(cols, filas, `Mis Citas${sufijo}`, `mis_citas${sufijo}`)
    mostrarToast('ok', 'Citas exportadas correctamente.')
  } catch { mostrarToast('err', 'Error al exportar.') }
  finally { exportando.value = false }
}

onMounted(cargar)
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

.cfg-header { margin-bottom: 1.5rem; }
.page-title { color: #ffffff; font-size: 1.4rem; font-weight: 700; }
.page-sub   { color: #6b7280; font-size: 0.82rem; margin-top: 0.2rem; }

/* Tabs */
.tabs { display: flex; gap: 0.35rem; margin-bottom: 1.25rem; }
.tab-btn {
  background: #111111; border: 1px solid #1c1c1c; color: #6b7280;
  padding: 0.5rem 1.1rem; border-radius: 8px; font-size: 0.82rem; font-weight: 600;
  cursor: pointer; transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.tab-btn:hover  { background: #1a1a1a; color: #d1d5db; }
.tab-btn.active { background: rgba(7,68,52,0.4); border-color: rgba(74,222,128,0.3); color: #ffffff; }

/* Card */
.cfg-card {
  background: #111111; border: 1px solid #1c1c1c; border-radius: 10px;
  padding: 1.5rem 1.75rem;
}

/* Perfil top */
.perfil-top { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem; }
.avatar-grande {
  width: 56px; height: 56px; border-radius: 50%; flex-shrink: 0;
  background: rgba(7,68,52,0.5); color: #4ade80;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; font-weight: 700;
}
.sec-title { color: #ffffff; font-size: 1rem; font-weight: 700; margin-bottom: 0.15rem; }
.sec-sub   { color: #6b7280; font-size: 0.8rem; margin-bottom: 1.25rem; }

.divider { border: none; border-top: 1px solid #1c1c1c; margin: 0 0 1.25rem; }

/* Form */
.cfg-form { display: flex; flex-direction: column; gap: 1rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.form-group label { color: #9ca3af; font-size: 0.8rem; font-weight: 600; }
.form-group input,
.form-group select {
  background: #0d0d0d; border: 1px solid #1c1c1c; border-radius: 7px;
  padding: 0.6rem 0.75rem; color: #d1d5db; font-size: 0.875rem; outline: none;
  transition: border-color 0.15s; font-family: inherit;
}
.form-group input:focus,
.form-group select:focus { border-color: #074434; box-shadow: 0 0 0 3px rgba(7,68,52,0.2); }
.form-group input::placeholder { color: #4b5563; }
.form-actions { padding-top: 0.5rem; }

.btn-save {
  background: #074434; color: #ffffff; border: none; border-radius: 8px;
  padding: 0.6rem 1.4rem; font-size: 0.875rem; font-weight: 600;
  cursor: pointer; transition: background 0.15s;
}
.btn-save:hover:not(:disabled) { background: #0a5c46; }
.btn-save:disabled { opacity: 0.5; cursor: not-allowed; }

.error-msg {
  color: #f87171; font-size: 0.82rem;
  background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);
  border-radius: 6px; padding: 0.5rem 0.75rem;
}

/* Export */
.export-grid { display: flex; flex-direction: column; gap: 0.9rem; }

.export-card {
  background: #0d0d0d; border: 1px solid #1c1c1c; border-radius: 10px;
  padding: 1rem 1.25rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
}
.export-card-citas { flex-direction: column; align-items: flex-start; gap: 0.85rem; }

.export-icon-row { display: flex; align-items: center; gap: 1rem; flex: 1; }
.export-icon {
  width: 46px; height: 46px; border-radius: 10px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.export-info { flex: 1; }
.export-info h4 { color: #ffffff; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.2rem; }
.export-info p  { color: #6b7280; font-size: 0.78rem; }

.export-btns { display: flex; gap: 0.45rem; flex-wrap: wrap; }
.btn-export {
  border: 1px solid #2a2a2a; border-radius: 7px;
  padding: 0.42rem 0.9rem; font-size: 0.78rem; font-weight: 600;
  cursor: pointer; transition: background 0.15s, border-color 0.15s;
}
.btn-export:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-pdf   { background: rgba(239,68,68,0.08);  color: #f87171; border-color: rgba(239,68,68,0.25); }
.btn-pdf:not(:disabled):hover   { background: rgba(239,68,68,0.18); }
.btn-excel { background: rgba(74,222,128,0.08); color: #4ade80; border-color: rgba(74,222,128,0.25); }
.btn-excel:not(:disabled):hover { background: rgba(74,222,128,0.18); }
.btn-csv   { background: rgba(107,114,128,0.1); color: #9ca3af; border-color: rgba(107,114,128,0.2); }
.btn-csv:not(:disabled):hover   { background: rgba(107,114,128,0.2); color: #d1d5db; }

/* Filtro paciente */
.filtro-row {
  display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;
  background: #111111; border: 1px solid #1c1c1c; border-radius: 8px; padding: 0.6rem 0.9rem;
  width: 100%;
}
.filtro-label { color: #9ca3af; font-size: 0.8rem; font-weight: 600; white-space: nowrap; }
.filtro-select {
  background: #0d0d0d; border: 1px solid #1c1c1c; color: #d1d5db;
  padding: 0.38rem 0.7rem; border-radius: 7px; font-size: 0.82rem; outline: none;
  flex: 1; min-width: 180px; cursor: pointer; transition: border-color 0.15s;
}
.filtro-select:focus { border-color: #074434; }
.filtro-count { color: #4b5563; font-size: 0.75rem; white-space: nowrap; }

/* Toast */
.cfg-toast {
  position: fixed; bottom: 1.75rem; right: 1.75rem; z-index: 9999;
  display: flex; align-items: center; gap: 0.6rem;
  padding: 0.75rem 1.1rem; border-radius: 10px;
  font-size: 0.82rem; font-weight: 500;
  box-shadow: 0 8px 32px rgba(0,0,0,0.5);
}
.cfg-toast.ok  { background: #052e16; border: 1px solid rgba(74,222,128,0.3); color: #4ade80; }
.cfg-toast.err { background: #2d1111; border: 1px solid rgba(239,68,68,0.3);  color: #f87171; }
.toast-slide-enter-active { animation: toastIn 0.3s ease; }
.toast-slide-leave-active { animation: toastIn 0.25s ease reverse; }
@keyframes toastIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }

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
