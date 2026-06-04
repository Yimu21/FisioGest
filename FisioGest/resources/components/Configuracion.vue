<template>
  <AppLayout>

    <div class="cfg-header">
      <h2 class="page-title">Configuración</h2>
      <p class="page-sub">Ajustes generales del sistema FisioGest</p>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button
        v-for="tab in tabs" :key="tab.key"
        class="tab-btn" :class="{ active: tabActiva === tab.key }"
        @click="tabActiva = tab.key"
      >
        <span v-html="tab.icon"></span>{{ tab.label }}
      </button>
    </div>

    <!-- ══════════════════════════════════════════
         TAB 1 — Datos de la clínica
    ══════════════════════════════════════════ -->
    <div v-if="tabActiva === 'clinica'" class="cfg-card">
      <h3 class="sec-title">Datos de la Clínica</h3>
      <p class="sec-sub">Información general que aparecerá en reportes y documentos.</p>

      <form class="cfg-form" @submit.prevent="guardarClinica">
        <div class="form-row">
          <div class="form-group">
            <label>Nombre de la clínica</label>
            <input v-model="clinica.nombre" type="text" placeholder="FisioGest Clínica" />
          </div>
          <div class="form-group">
            <label>Teléfono</label>
            <input v-model="clinica.telefono" type="text" placeholder="+591 XXXXXXXX" />
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Correo electrónico</label>
            <input v-model="clinica.email" type="email" placeholder="contacto@clinica.com" />
          </div>
          <div class="form-group">
            <label>Dirección</label>
            <input v-model="clinica.direccion" type="text" placeholder="Av. Principal #123" />
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-save" :disabled="guardando">
            {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
          </button>
        </div>
      </form>
    </div>

    <!-- ══════════════════════════════════════════
         TAB 2 — Configuración de citas
    ══════════════════════════════════════════ -->
    <div v-if="tabActiva === 'citas'" class="cfg-card">
      <h3 class="sec-title">Configuración de Citas</h3>
      <p class="sec-sub">Parámetros que controlan la agenda y disponibilidad de horarios.</p>

      <form class="cfg-form" @submit.prevent="guardarCitas">
        <div class="form-row">
          <div class="form-group">
            <label>Duración por defecto (minutos)</label>
            <input v-model.number="citas.duracion" type="number" min="15" max="180" step="15" />
            <span class="form-hint">Tiempo reservado por cada cita en la agenda</span>
          </div>
          <div class="form-group">
            <label>Máximo de citas por día</label>
            <input v-model.number="citas.maxPorDia" type="number" min="1" max="50" />
            <span class="form-hint">Límite diario total para toda la clínica</span>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Horario de inicio</label>
            <input v-model="citas.horarioInicio" type="time" />
          </div>
          <div class="form-group">
            <label>Horario de cierre</label>
            <input v-model="citas.horarioFin" type="time" />
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-save" :disabled="guardando">
            {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
          </button>
        </div>
      </form>
    </div>

    <!-- ══════════════════════════════════════════
         TAB 3 — Inventario
    ══════════════════════════════════════════ -->
    <div v-if="tabActiva === 'inventario'" class="cfg-card">
      <h3 class="sec-title">Configuración de Inventario</h3>
      <p class="sec-sub">Ajustes que afectan el comportamiento del control de stock.</p>

      <form class="cfg-form" @submit.prevent="guardarInventario">
        <div class="form-group" style="max-width: 320px">
          <label>Umbral de alerta de stock bajo</label>
          <input v-model.number="inventario.umbral" type="number" min="1" max="100" />
          <span class="form-hint">
            Cuando los disponibles de un equipo caigan por debajo de este número,
            el estado cambiará a <strong style="color:#fbbf24">Stock Bajo</strong>.
          </span>
        </div>

        <div class="umbral-preview">
          <div class="preview-item">
            <span class="estado-badge disponible"><span class="dot"></span>Disponible</span>
            <span class="preview-desc">≥ {{ inventario.umbral }} unidades disponibles</span>
          </div>
          <div class="preview-item">
            <span class="estado-badge baja"><span class="dot"></span>Stock Bajo</span>
            <span class="preview-desc">Entre 1 y {{ inventario.umbral - 1 }} disponibles</span>
          </div>
          <div class="preview-item">
            <span class="estado-badge no_disponible"><span class="dot"></span>No Disponible</span>
            <span class="preview-desc">0 unidades disponibles</span>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-save" :disabled="guardando">
            {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
          </button>
        </div>
      </form>
    </div>

    <!-- ══════════════════════════════════════════
         TAB 4 — Mi cuenta
    ══════════════════════════════════════════ -->
    <div v-if="tabActiva === 'cuenta'" class="cfg-card">
      <h3 class="sec-title">Mi Cuenta</h3>
      <p class="sec-sub">Actualiza el nombre de usuario o cambia tu contraseña.</p>

      <form class="cfg-form" @submit.prevent="guardarCuenta">
        <div class="form-group">
          <label>Nombre</label>
          <input v-model="cuenta.nombre" type="text" placeholder="Administrador" />
        </div>

        <div class="divider"></div>
        <p class="sec-sub" style="margin-bottom:0.75rem">Cambiar contraseña <span class="hint-opcional">(opcional)</span></p>

        <div class="form-group">
          <label>Contraseña actual</label>
          <input v-model="cuenta.contrasenaActual" type="password" placeholder="••••••••" autocomplete="current-password" />
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Nueva contraseña</label>
            <input v-model="cuenta.contrasenaNueva" type="password" placeholder="••••••••" autocomplete="new-password" />
          </div>
          <div class="form-group">
            <label>Confirmar contraseña</label>
            <input v-model="cuenta.contrasenaConfirm" type="password" placeholder="••••••••" autocomplete="new-password" />
          </div>
        </div>

        <p v-if="errorCuenta" class="error-msg">{{ errorCuenta }}</p>

        <div class="form-actions">
          <button type="submit" class="btn-save" :disabled="guardando">
            {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
          </button>
        </div>
      </form>
    </div>

    <!-- ══════════════════════════════════════════
         TAB 5 — Exportar datos
    ══════════════════════════════════════════ -->
    <div v-if="tabActiva === 'exportar'" class="cfg-card">
      <h3 class="sec-title">Exportar Datos</h3>
      <p class="sec-sub">Descarga los registros del sistema en PDF, Excel o CSV.</p>

      <div class="export-grid">
        <div class="export-card">
          <div class="export-icon" style="background:rgba(56,189,248,0.1);color:#38bdf8">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
          <div class="export-info">
            <h4>Pacientes</h4>
            <p>Nombre, género, fecha de nacimiento, teléfono y especialista asignado.</p>
          </div>
          <div class="export-btns">
            <button class="btn-export btn-pdf"   :disabled="exportando" @click="exportarFormato('pacientes','pdf')">📄 PDF</button>
            <button class="btn-export btn-excel" :disabled="exportando" @click="exportarFormato('pacientes','xlsx')">📊 Excel</button>
            <button class="btn-export btn-csv"   :disabled="exportando" @click="exportar('pacientes')">📋 CSV</button>
          </div>
        </div>

        <!-- Citas con filtros -->
        <div class="export-card export-card-tall">
          <div class="export-card-header">
            <div class="export-icon" style="background:rgba(74,222,128,0.1);color:#4ade80">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
            </div>
            <div class="export-info">
              <h4>Citas</h4>
              <p>Fecha, paciente, fisioterapeuta, estado y motivo. Aplica filtros antes de exportar.</p>
            </div>
            <span class="filtro-count-badge">{{ citasFiltradas.length }} resultado{{ citasFiltradas.length !== 1 ? 's' : '' }}</span>
          </div>

          <!-- Filtros -->
          <div class="filtros-grid">
            <div class="filtro-group">
              <label>Desde</label>
              <input v-model="filtroCitas.desde" type="date" class="filtro-input" />
            </div>
            <div class="filtro-group">
              <label>Hasta</label>
              <input v-model="filtroCitas.hasta" type="date" class="filtro-input" />
            </div>
            <div class="filtro-group">
              <label>Especialista</label>
              <select v-model="filtroCitas.fisioId" class="filtro-input">
                <option value="">Todos</option>
                <option v-for="f in listadoFisios" :key="f.fisioterapeuta_id" :value="f.fisioterapeuta_id">
                  {{ f.nombre }} {{ f.apellido }}
                </option>
              </select>
            </div>
            <div class="filtro-group">
              <label>Paciente</label>
              <select v-model="filtroCitas.pacienteId" class="filtro-input">
                <option value="">Todos</option>
                <option v-for="p in listadoPacientes" :key="p.paciente_id" :value="p.paciente_id">
                  {{ p.nombre }} {{ p.apellido }}
                </option>
              </select>
            </div>
            <div class="filtro-group filtro-reset">
              <button class="btn-reset" @click="resetFiltrosCitas">↺ Limpiar</button>
            </div>
          </div>

          <div class="export-btns">
            <button class="btn-export btn-pdf"   :disabled="exportando || citasFiltradas.length === 0" @click="exportarFormato('citas','pdf')">📄 PDF</button>
            <button class="btn-export btn-excel" :disabled="exportando || citasFiltradas.length === 0" @click="exportarFormato('citas','xlsx')">📊 Excel</button>
            <button class="btn-export btn-csv"   :disabled="exportando || citasFiltradas.length === 0" @click="exportarCitasCSV()">📋 CSV</button>
          </div>
        </div>

        <!-- card inventario placeholder - reemplaza el original abajo -->
        <div class="export-card">
          <div class="export-icon" style="background:rgba(251,191,36,0.1);color:#fbbf24">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
              <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
            </svg>
          </div>
          <div class="export-info">
            <h4>Inventario</h4>
            <p>Equipos, cantidad total, asignados, disponibles y estado actual.</p>
          </div>
          <div class="export-btns">
            <button class="btn-export btn-pdf"   :disabled="exportando" @click="exportarFormato('inventario','pdf')">📄 PDF</button>
            <button class="btn-export btn-excel" :disabled="exportando" @click="exportarFormato('inventario','xlsx')">📊 Excel</button>
            <button class="btn-export btn-csv"   :disabled="exportando" @click="exportar('inventario')">📋 CSV</button>
          </div>
        </div>

        <!-- Especialistas -->
        <div class="export-card">
          <div class="export-icon" style="background:rgba(167,139,250,0.1);color:#a78bfa">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              <line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>
            </svg>
          </div>
          <div class="export-info">
            <h4>Especialistas</h4>
            <p>Nombre, especialidad, teléfono, estado y correo de todos los fisioterapeutas.</p>
          </div>
          <div class="export-btns">
            <button class="btn-export btn-pdf"   :disabled="exportando" @click="exportarFormato('especialistas','pdf')">📄 PDF</button>
            <button class="btn-export btn-excel" :disabled="exportando" @click="exportarFormato('especialistas','xlsx')">📊 Excel</button>
            <button class="btn-export btn-csv"   :disabled="exportando" @click="exportar('especialistas')">📋 CSV</button>
          </div>
        </div>

      </div>
    </div>


    <!-- Toast global -->
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

  </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import axios from 'axios'
import { getUser, saveUser } from '@/services/api'

const tabs = [
  { key: 'clinica',    label: 'Datos de la clínica',  icon: '🏥 ' },
  { key: 'citas',      label: 'Citas',                 icon: '📅 ' },
  { key: 'inventario', label: 'Inventario',             icon: '📦 ' },
  { key: 'cuenta',     label: 'Mi cuenta',             icon: '👤 ' },
  { key: 'exportar',   label: 'Exportar datos',        icon: '📤 ' },
]
const tabActiva = ref('clinica')
const guardando = ref(false)
const errorCuenta = ref('')

// Precargar datos de exportación al entrar al tab
watch(tabActiva, (val) => { if (val === 'exportar') cargarDatosExportacion() })

const toast = ref({ visible: false, tipo: 'ok', msg: '' })
let toastTimer = null

function mostrarToast(tipo, msg) {
  clearTimeout(toastTimer)
  toast.value = { visible: true, tipo, msg }
  toastTimer = setTimeout(() => { toast.value.visible = false }, 3500)
}

// ── Datos de la clínica ───────────────────────────────────────────────────────
const clinica = ref({ nombre: '', telefono: '', email: '', direccion: '' })

async function guardarClinica() {
  guardando.value = true
  try {
    await axios.put('/api/admin/configuracion', {
      clinica_nombre:    clinica.value.nombre,
      clinica_telefono:  clinica.value.telefono,
      clinica_email:     clinica.value.email,
      clinica_direccion: clinica.value.direccion,
    }, authHeaders())
    mostrarToast('ok', 'Datos de la clínica guardados.')
  } catch { mostrarToast('err', 'Error al guardar.') }
  finally { guardando.value = false }
}

// ── Configuración de citas ────────────────────────────────────────────────────
const citas = ref({ duracion: 60, maxPorDia: 10, horarioInicio: '08:00', horarioFin: '20:00' })

async function guardarCitas() {
  guardando.value = true
  try {
    await axios.put('/api/admin/configuracion', {
      cita_duracion_minutos: String(citas.value.duracion),
      cita_max_por_dia:      String(citas.value.maxPorDia),
      cita_horario_inicio:   citas.value.horarioInicio,
      cita_horario_fin:      citas.value.horarioFin,
    }, authHeaders())
    mostrarToast('ok', 'Configuración de citas guardada.')
  } catch { mostrarToast('err', 'Error al guardar.') }
  finally { guardando.value = false }
}

// ── Inventario ────────────────────────────────────────────────────────────────
const inventario = ref({ umbral: 5 })

async function guardarInventario() {
  guardando.value = true
  try {
    await axios.put('/api/admin/configuracion', {
      inventario_umbral: String(inventario.value.umbral),
    }, authHeaders())
    localStorage.setItem('inventario_umbral', String(inventario.value.umbral))
    mostrarToast('ok', 'Umbral de inventario actualizado.')
  } catch { mostrarToast('err', 'Error al guardar.') }
  finally { guardando.value = false }
}

// ── Mi cuenta ─────────────────────────────────────────────────────────────────
const cuenta = ref({ nombre: '', contrasenaActual: '', contrasenaNueva: '', contrasenaConfirm: '' })

async function guardarCuenta() {
  errorCuenta.value = ''
  if (cuenta.value.contrasenaNueva && cuenta.value.contrasenaNueva !== cuenta.value.contrasenaConfirm) {
    errorCuenta.value = 'Las contraseñas nuevas no coinciden.'
    return
  }
  guardando.value = true
  try {
    const payload = { nombre: cuenta.value.nombre }
    if (cuenta.value.contrasenaActual) {
      payload.contrasena_actual = cuenta.value.contrasenaActual
      payload.contrasena_nueva  = cuenta.value.contrasenaNueva
    }
    const res = await axios.put('/api/admin/cuenta', payload, authHeaders())
    if (!res.data.success) {
      errorCuenta.value = res.data.message
      return
    }
    cuenta.value.contrasenaActual = ''
    cuenta.value.contrasenaNueva  = ''
    cuenta.value.contrasenaConfirm = ''
    // Actualizar nombre en localStorage
    const user = getUser()
    if (user && cuenta.value.nombre) {
      saveUser({ ...user, nombre: cuenta.value.nombre }, localStorage.getItem('token'))
    }
    mostrarToast('ok', 'Cuenta actualizada correctamente.')
  } catch (e) {
    errorCuenta.value = e?.response?.data?.message ?? 'Error al actualizar la cuenta.'
  } finally {
    guardando.value = false
  }
}

// ── Exportar ──────────────────────────────────────────────────────────────────
const exportando = ref(false)

// Datos precargados para filtros de citas
const listadoFisios    = ref([])
const listadoPacientes = ref([])
const todasLasCitas    = ref([])

// Filtros de citas
const filtroCitas = ref({ desde: '', hasta: '', fisioId: '', pacienteId: '' })

function resetFiltrosCitas() {
  filtroCitas.value = { desde: '', hasta: '', fisioId: '', pacienteId: '' }
}

// Citas filtradas reactivamente
const citasFiltradas = computed(() => {
  return todasLasCitas.value.filter(c => {
    const fecha = (c.fecha_hora ?? '').slice(0, 10)
    if (filtroCitas.value.desde    && fecha < filtroCitas.value.desde)                                    return false
    if (filtroCitas.value.hasta    && fecha > filtroCitas.value.hasta)                                    return false
    if (filtroCitas.value.fisioId  && Number(c.fisioterapeuta_id) !== Number(filtroCitas.value.fisioId))  return false
    if (filtroCitas.value.pacienteId && Number(c.paciente_id) !== Number(filtroCitas.value.pacienteId))   return false
    return true
  })
})

// Precargar datos para filtros al abrir tab exportar
async function cargarDatosExportacion() {
  if (listadoFisios.value.length && todasLasCitas.value.length) return // ya cargado
  const h = authHeaders()
  const [fisRes, pacRes, citRes] = await Promise.allSettled([
    axios.get('/api/fisioterapeutas', h),
    axios.get('/api/pacientes', h),
    axios.get('/api/citas', h),
  ])
  if (fisRes.status === 'fulfilled') listadoFisios.value    = fisRes.value.data
  if (pacRes.status === 'fulfilled') listadoPacientes.value = pacRes.value.data
  if (citRes.status === 'fulfilled') todasLasCitas.value    = citRes.value.data
}

// Construye texto legible de filtros activos para citas
function getFiltrosTexto() {
  const partes = []
  if (filtroCitas.value.desde)      partes.push(`Desde: ${formatFechaCorta(filtroCitas.value.desde)}`)
  if (filtroCitas.value.hasta)      partes.push(`Hasta: ${formatFechaCorta(filtroCitas.value.hasta)}`)
  if (filtroCitas.value.fisioId) {
    const f = listadoFisios.value.find(x => Number(x.fisioterapeuta_id) === Number(filtroCitas.value.fisioId))
    if (f) partes.push(`Especialista: ${f.nombre} ${f.apellido}`)
  }
  if (filtroCitas.value.pacienteId) {
    const p = listadoPacientes.value.find(x => Number(x.paciente_id) === Number(filtroCitas.value.pacienteId))
    if (p) partes.push(`Paciente: ${p.nombre} ${p.apellido}`)
  }
  return partes.length ? partes.join('   |   ') : 'Sin filtros aplicados (todos los registros)'
}

function formatFechaCorta(iso) {
  if (!iso) return ''
  const [y, m, d] = iso.split('-')
  return `${d}/${m}/${y}`
}

// Exportar CSV de citas con filtros aplicados
function exportarCitasCSV() {
  const cols  = ['Fecha y Hora', 'Paciente', 'Fisioterapeuta', 'Estado', 'Motivo']
  const filas = citasFiltradas.value.map(c => {
    const p = listadoPacientes.value.find(x => Number(x.paciente_id) === Number(c.paciente_id))
    const f = listadoFisios.value.find(x => Number(x.fisioterapeuta_id) === Number(c.fisioterapeuta_id))
    return [
      c.fecha_hora ?? '—',
      p ? `${p.nombre} ${p.apellido}` : `Paciente #${c.paciente_id}`,
      f ? `${f.nombre} ${f.apellido}` : '—',
      c.estado ?? '—',
      c.motivo ?? '—',
    ]
  })
  // Encabezado de metadatos
  const clinicaNombre = clinica.value.nombre || 'FisioGest'
  const meta  = `"${clinicaNombre} — Reporte de Citas"\n"Generado: ${new Date().toLocaleDateString('es-ES')}"\n"Filtros: ${getFiltrosTexto()}"\n"Total registros: ${filas.length}"\n`
  const header = cols.join(',')
  const rows   = filas.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(',')).join('\n')
  const blob   = new Blob(['﻿' + meta + header + '\n' + rows], { type: 'text/csv;charset=utf-8' })
  const a = document.createElement('a')
  a.href = URL.createObjectURL(blob)
  a.download = `citas_${new Date().toISOString().slice(0, 10)}.csv`
  a.click()
  URL.revokeObjectURL(a.href)
}

// CSV — descarga desde el backend (para tipos sin filtros)
function exportar(tipo) {
  const token = localStorage.getItem('token')
  fetch(`/api/admin/exportar/${tipo}`, { headers: { Authorization: `Bearer ${token}`, Accept: 'text/csv' } })
    .then(r => r.blob())
    .then(blob => {
      const a = document.createElement('a')
      a.href = URL.createObjectURL(blob)
      a.download = `${tipo}_${new Date().toISOString().slice(0, 10)}.csv`
      a.click()
      URL.revokeObjectURL(a.href)
    })
    .catch(() => mostrarToast('err', 'Error al exportar.'))
}

// Metadatos de columnas y etiquetas por tipo
const EXPORT_META = {
  pacientes:     { titulo: 'Reporte de Pacientes',     cols: ['Nombre', 'Apellido', 'Género', 'Fecha Nacimiento', 'Teléfono', 'Especialista'] },
  citas:         { titulo: 'Reporte de Citas',          cols: ['Fecha y Hora', 'Paciente', 'Fisioterapeuta', 'Estado', 'Motivo'] },
  inventario:    { titulo: 'Reporte de Inventario',     cols: ['Equipo', 'Tipo', 'Cantidad Total', 'Asignados', 'Disponibles', 'Estado'] },
  especialistas: { titulo: 'Reporte de Especialistas',  cols: ['Nombre', 'Apellido', 'Especialidad', 'Teléfono', 'Estado', 'Correo'] },
}

// Obtener filas como array de arrays desde el backend JSON
async function obtenerFilas(tipo) {
  const h = authHeaders()
  const [pacRes, citRes, invRes, fisRes, asigRes] = await Promise.allSettled([
    tipo === 'pacientes'  || tipo === 'citas'                    ? axios.get('/api/pacientes',       h) : Promise.resolve(null),
    tipo === 'citas'                                             ? axios.get('/api/citas',            h) : Promise.resolve(null),
    tipo === 'inventario'                                        ? axios.get('/api/inventario',       h) : Promise.resolve(null),
    tipo === 'pacientes'  || tipo === 'citas' || tipo === 'especialistas' ? axios.get('/api/fisioterapeutas', h) : Promise.resolve(null),
    tipo === 'inventario'                                        ? axios.get('/api/asignaciones',    h) : Promise.resolve(null),
  ])

  const pac  = pacRes.status  === 'fulfilled' && pacRes.value  ? pacRes.value.data  : []
  const cit  = citRes.status  === 'fulfilled' && citRes.value  ? citRes.value.data  : []
  const inv  = invRes.status  === 'fulfilled' && invRes.value  ? invRes.value.data  : []
  const fis  = fisRes.status  === 'fulfilled' && fisRes.value  ? fisRes.value.data  : []
  const asig = asigRes.status === 'fulfilled' && asigRes.value ? asigRes.value.data : []

  if (tipo === 'pacientes') {
    return pac.map(p => {
      const f = fis.find(x => Number(x.fisioterapeuta_id) === Number(p.fisioterapeuta_id))
      return [p.nombre, p.apellido, p.genero ?? '—', p.fecha_nacimiento ?? '—', p.telefono ?? '—', f ? `${f.nombre} ${f.apellido}` : '—']
    })
  }
  if (tipo === 'citas') {
    // Usar las citas ya filtradas (reactivas) en lugar de todas las citas
    return citasFiltradas.value.map(c => {
      const p = listadoPacientes.value.find(x => Number(x.paciente_id) === Number(c.paciente_id)) ?? pac.find(x => Number(x.paciente_id) === Number(c.paciente_id))
      const f = listadoFisios.value.find(x => Number(x.fisioterapeuta_id) === Number(c.fisioterapeuta_id)) ?? fis.find(x => Number(x.fisioterapeuta_id) === Number(c.fisioterapeuta_id))
      return [c.fecha_hora ?? '—', p ? `${p.nombre} ${p.apellido}` : '—', f ? `${f.nombre} ${f.apellido}` : '—', c.estado ?? '—', c.motivo ?? '—']
    })
  }
  if (tipo === 'inventario') {
    const umbral = Number(localStorage.getItem('inventario_umbral') ?? 5)
    const etiquetas = { disponible: 'Disponible', no_disponible: 'No Disponible', baja: 'Stock Bajo', mantenimiento: 'Mantenimiento' }
    return inv.map(item => {
      const asignados   = asig.filter(a => Number(a.inventario_id) === Number(item.id_inventario)).length
      const disponibles = Math.max(0, (item.cantidad ?? 0) - asignados)
      // Calcular estado real (igual que en Inventario.vue, no usar el valor de BD)
      let estadoReal
      if (item.estado === 'mantenimiento')           estadoReal = 'mantenimiento'
      else if (disponibles === 0)                       estadoReal = 'no_disponible'
      else if (disponibles < umbral)                   estadoReal = 'baja'
      else                                             estadoReal = 'disponible'
      return [item.nombre, item.tipo ?? '—', item.cantidad ?? 0, asignados, disponibles, etiquetas[estadoReal] ?? estadoReal]
    })
  }
  if (tipo === 'especialistas') {
    return fis.map(f => [
      f.nombre ?? '—',
      f.apellido ?? '—',
      f.especialidad ?? '—',
      f.telefono ?? '—',
      f.activo ? 'Activo' : 'Inactivo',
      f.correo_usuario ?? '—',
    ])
  }
  return []
}

// PDF — genera con jsPDF + autoTable
async function generarPDF(tipo, filas) {
  const { default: jsPDF } = await import('jspdf')
  await import('jspdf-autotable')
  const meta = EXPORT_META[tipo]
  const doc  = new jsPDF({ orientation: 'landscape' })
  const clinicaNombre = clinica.value.nombre || 'FisioGest'

  // Encabezado principal
  doc.setFontSize(16); doc.setTextColor(40)
  doc.text(clinicaNombre, 14, 16)
  doc.setFontSize(12); doc.setTextColor(60)
  doc.text(meta.titulo, 14, 25)

  // Línea de metadatos
  let y = 32
  doc.setFontSize(8); doc.setTextColor(120)
  doc.text(`Generado: ${new Date().toLocaleDateString('es-ES', { day:'2-digit', month:'long', year:'numeric' })}   |   Total registros: ${filas.length}`, 14, y)
  y += 6

  // Filtros activos (solo para citas)
  if (tipo === 'citas') {
    const filtrosTexto = getFiltrosTexto()
    doc.setTextColor(7, 68, 52)
    doc.text(`Filtros aplicados: ${filtrosTexto}`, 14, y)
    y += 6
  }

  // Línea separadora
  doc.setDrawColor(200); doc.line(14, y, 283, y); y += 4

  doc.autoTable({
    head:       [meta.cols],
    body:       filas,
    startY:     y,
    styles:     { fontSize: 8, cellPadding: 3 },
    headStyles: { fillColor: [7, 68, 52], textColor: 255, fontStyle: 'bold' },
    alternateRowStyles: { fillColor: [245, 245, 245] },
    didDrawPage: (data) => {
      // Pie de página
      const pageCount = doc.internal.getNumberOfPages()
      doc.setFontSize(7); doc.setTextColor(150)
      doc.text(`${clinicaNombre}  —  Página ${data.pageNumber} de ${pageCount}`, 14, doc.internal.pageSize.height - 8)
    },
  })

  doc.save(`${tipo}_${new Date().toISOString().slice(0, 10)}.pdf`)
}

// Excel — genera con SheetJS
async function generarExcel(tipo, filas) {
  const XLSX = await import('xlsx')
  const meta = EXPORT_META[tipo]
  const wb   = XLSX.utils.book_new()
  const clinicaNombre = clinica.value.nombre || 'FisioGest'
  const fechaGen = new Date().toLocaleDateString('es-ES', { day:'2-digit', month:'long', year:'numeric' })

  // Filas de metadatos antes de los datos
  const metaFilas = [
    [clinicaNombre, '', '', '', '', ''],
    [meta.titulo,   '', '', '', '', ''],
    [`Generado: ${fechaGen}`, '', `Total registros: ${filas.length}`, '', '', ''],
  ]
  if (tipo === 'citas') {
    metaFilas.push([`Filtros: ${getFiltrosTexto()}`, '', '', '', '', ''])
  }
  metaFilas.push([]) // fila vacía separadora

  const ws = XLSX.utils.aoa_to_sheet([...metaFilas, meta.cols, ...filas])
  ws['!cols'] = meta.cols.map(() => ({ wch: 22 }))

  XLSX.utils.book_append_sheet(wb, ws, tipo.charAt(0).toUpperCase() + tipo.slice(1))
  XLSX.writeFile(wb, `${tipo}_${new Date().toISOString().slice(0, 10)}.xlsx`)
}

// Función principal de exportación con formato
async function exportarFormato(tipo, formato) {
  exportando.value = true
  try {
    const filas = await obtenerFilas(tipo)
    if (formato === 'pdf')  await generarPDF(tipo, filas)
    if (formato === 'xlsx') await generarExcel(tipo, filas)
    mostrarToast('ok', `${tipo.charAt(0).toUpperCase() + tipo.slice(1)} exportado correctamente.`)
  } catch (e) {
    console.error(e)
    mostrarToast('err', 'Error al generar el archivo.')
  } finally {
    exportando.value = false
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function authHeaders() {
  return { headers: { Authorization: `Bearer ${localStorage.getItem('token')}`, Accept: 'application/json' } }
}

// ── Carga inicial ─────────────────────────────────────────────────────────────
async function cargar() {
  try {
    const res = await axios.get('/api/admin/configuracion', authHeaders())
    const c   = res.data
    clinica.value    = { nombre: c.clinica_nombre ?? '', telefono: c.clinica_telefono ?? '', email: c.clinica_email ?? '', direccion: c.clinica_direccion ?? '' }
    citas.value      = { duracion: Number(c.cita_duracion_minutos ?? 60), maxPorDia: Number(c.cita_max_por_dia ?? 10), horarioInicio: c.cita_horario_inicio ?? '08:00', horarioFin: c.cita_horario_fin ?? '20:00' }
    inventario.value = { umbral: Number(c.inventario_umbral ?? 5) }
    // Guardar umbral en localStorage para uso en Inventario.vue
    localStorage.setItem('inventario_umbral', String(inventario.value.umbral))
    // Nombre del admin
    const user = getUser()
    if (user) cuenta.value.nombre = user.nombre ?? ''
  } catch {}
}

onMounted(cargar)
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

.cfg-header { margin-bottom: 1.5rem; }
.page-title { color: #ffffff; font-size: 1.4rem; font-weight: 700; }
.page-sub   { color: #6b7280; font-size: 0.82rem; margin-top: 0.2rem; }

/* Tabs */
.tabs { display: flex; gap: 0.35rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
.tab-btn {
  display: flex; align-items: center; gap: 0.4rem;
  background: #111111; border: 1px solid #1c1c1c; color: #6b7280;
  padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.82rem; font-weight: 600;
  cursor: pointer; transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.tab-btn:hover  { background: #1a1a1a; color: #d1d5db; }
.tab-btn.active { background: rgba(7,68,52,0.4); border-color: rgba(74,222,128,0.3); color: #ffffff; }

/* Card */
.cfg-card {
  background: #111111; border: 1px solid #1c1c1c; border-radius: 10px;
  padding: 1.5rem 1.75rem;
}
.sec-title { color: #ffffff; font-size: 1rem; font-weight: 700; margin-bottom: 0.25rem; }
.sec-sub   { color: #6b7280; font-size: 0.8rem; margin-bottom: 1.5rem; }

/* Form */
.cfg-form { display: flex; flex-direction: column; gap: 1rem; }
.form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.form-group label { color: #9ca3af; font-size: 0.8rem; font-weight: 600; }
.form-group input {
  background: #0d0d0d; border: 1px solid #1c1c1c; border-radius: 7px;
  padding: 0.6rem 0.75rem; color: #d1d5db; font-size: 0.875rem; outline: none;
  transition: border-color 0.15s;
}
.form-group input:focus { border-color: #074434; box-shadow: 0 0 0 3px rgba(7,68,52,0.2); }
.form-group input::placeholder { color: #4b5563; }
.form-hint { color: #4b5563; font-size: 0.75rem; line-height: 1.4; }
.form-actions { display: flex; padding-top: 0.5rem; }

.btn-save {
  background: #074434; color: #ffffff; border: none; border-radius: 8px;
  padding: 0.6rem 1.4rem; font-size: 0.875rem; font-weight: 600;
  cursor: pointer; transition: background 0.15s;
}
.btn-save:hover:not(:disabled) { background: #0a5c46; }
.btn-save:disabled { opacity: 0.5; cursor: not-allowed; }

/* Divider */
.divider { border: none; border-top: 1px solid #1c1c1c; margin: 0.5rem 0; }
.hint-opcional { color: #4b5563; font-size: 0.75rem; font-weight: 400; }

/* Error */
.error-msg { color: #f87171; font-size: 0.82rem; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); border-radius: 6px; padding: 0.5rem 0.75rem; }

/* Umbral preview */
.umbral-preview {
  display: flex; gap: 1rem; flex-wrap: wrap;
  background: #0d0d0d; border: 1px solid #1c1c1c; border-radius: 8px; padding: 1rem;
}
.preview-item { display: flex; align-items: center; gap: 0.6rem; font-size: 0.8rem; }
.preview-desc { color: #6b7280; }
.estado-badge { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; font-weight: 600; white-space: nowrap; }
.dot          { width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
.disponible    { color: #4ade80; }
.baja          { color: #fbbf24; }
.no_disponible { color: #f87171; }

/* Export */
.export-grid { display: flex; flex-direction: column; gap: 0.75rem; }
.export-card {
  display: flex; align-items: center; gap: 1rem;
  background: #0d0d0d; border: 1px solid #1c1c1c; border-radius: 10px; padding: 1rem 1.25rem;
}
.export-icon {
  width: 48px; height: 48px; border-radius: 10px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.export-info { flex: 1; }
.export-info h4 { color: #ffffff; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.2rem; }
.export-info p  { color: #6b7280; font-size: 0.78rem; }
.export-btns { display: flex; gap: 0.45rem; flex-shrink: 0; flex-wrap: wrap; }
.btn-export {
  display: flex; align-items: center; gap: 0.4rem; flex-shrink: 0;
  border: 1px solid #2a2a2a; border-radius: 7px;
  padding: 0.42rem 0.85rem; font-size: 0.78rem; font-weight: 600;
  cursor: pointer; transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.btn-export:disabled { opacity: 0.45; cursor: not-allowed; }

.btn-pdf   { background: rgba(239,68,68,0.08);   color: #f87171; border-color: rgba(239,68,68,0.25); }
.btn-pdf:not(:disabled):hover   { background: rgba(239,68,68,0.18); border-color: rgba(239,68,68,0.5); }

.btn-excel { background: rgba(74,222,128,0.08);  color: #4ade80; border-color: rgba(74,222,128,0.25); }
.btn-excel:not(:disabled):hover { background: rgba(74,222,128,0.18); border-color: rgba(74,222,128,0.5); }

.btn-csv   { background: rgba(107,114,128,0.1);  color: #9ca3af; border-color: rgba(107,114,128,0.25); }
.btn-csv:not(:disabled):hover   { background: rgba(107,114,128,0.2); border-color: rgba(107,114,128,0.5); color: #d1d5db; }

/* Tarjeta de citas con filtros */
.export-card-tall {
  flex-direction: column; align-items: flex-start; gap: 1rem;
}
.export-card-header {
  display: flex; align-items: center; gap: 1rem; width: 100%;
}
.filtro-count-badge {
  margin-left: auto; background: rgba(74,222,128,0.1);
  color: #4ade80; border: 1px solid rgba(74,222,128,0.25);
  padding: 0.25rem 0.7rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;
  white-space: nowrap; flex-shrink: 0;
}
.filtros-grid {
  display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto;
  gap: 0.75rem; width: 100%; align-items: end;
  background: #111111; border: 1px solid #1c1c1c; border-radius: 8px; padding: 0.9rem 1rem;
}
.filtro-group { display: flex; flex-direction: column; gap: 0.3rem; }
.filtro-group label { color: #9ca3af; font-size: 0.75rem; font-weight: 600; }
.filtro-input {
  background: #0d0d0d; border: 1px solid #1c1c1c; border-radius: 7px;
  padding: 0.45rem 0.65rem; color: #d1d5db; font-size: 0.82rem; outline: none;
  transition: border-color 0.15s; font-family: inherit; width: 100%;
}
.filtro-input:focus { border-color: #074434; }
.filtro-reset { justify-content: flex-end; }
.btn-reset {
  background: #1c1c1c; border: 1px solid #2a2a2a; color: #9ca3af;
  padding: 0.45rem 0.9rem; border-radius: 7px; font-size: 0.78rem; font-weight: 600;
  cursor: pointer; transition: background 0.15s, color 0.15s; white-space: nowrap;
}
.btn-reset:hover { background: #2a2a2a; color: #d1d5db; }

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
</style>
