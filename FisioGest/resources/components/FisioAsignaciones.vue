<template>
  <FisioLayout>

    <div class="pag-header">
      <h2 class="page-title">Asignaciones de Equipo</h2>
      <p class="page-sub">Gestiona el inventario y las asignaciones de equipos a tus pacientes.</p>
    </div>

    <!-- Toast de feedback -->
    <transition name="toast-fade">
      <div v-if="toast.visible" class="toast" :class="toast.tipo">{{ toast.mensaje }}</div>
    </transition>

    <div v-if="loading" class="empty-state">Cargando equipos...</div>

    <template v-else>

      <!-- ═══════════════════════════════════════════════════════════
           TABLA 1 — INVENTARIO DE EQUIPOS
      ════════════════════════════════════════════════════════════════ -->
      <div class="section-header">
        <div>
          <h3 class="section-title">Inventario de Equipos</h3>
          <p class="section-sub">Resumen de equipos disponibles en el sistema.</p>
        </div>
        <select v-model="filtroEstado" class="filtro-select">
          <option value="">Todos los estados</option>
          <option value="disponible">Disponible</option>
          <option value="baja">Stock Bajo</option>
          <option value="no_disponible">No Disponible</option>
          <option value="mantenimiento">Mantenimiento</option>
        </select>
      </div>

      <div class="tabla-wrap">
        <table class="tabla">
          <thead>
            <tr>
              <th>Equipo</th>
              <th>Tipo</th>
              <th>Estado</th>
              <th class="col-centro">Disponibles</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="itemsFiltrados.length === 0">
              <td colspan="4" class="empty-row">No hay equipos registrados.</td>
            </tr>
            <tr v-for="item in itemsFiltrados" :key="item.id_inventario" class="tr-inventario">
              <td class="td-bold">{{ item.nombre }}</td>
              <td class="td-gris">{{ item.tipo ?? '—' }}</td>
              <td>
                <span class="estado-pill" :class="estadoClass(estadoCalculado(item))">
                  <span class="dot"></span>{{ etiquetaEstado(estadoCalculado(item)) }}
                </span>
              </td>
              <td class="col-centro td-bold" :class="disponiblesClass(disponiblesDeItem(item), item.cantidad)">
                {{ disponiblesDeItem(item) }}
                <span class="td-total">/ {{ item.cantidad ?? '—' }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ═══════════════════════════════════════════════════════════
           TABLA 2 — ASIGNACIONES ACTIVAS
      ════════════════════════════════════════════════════════════════ -->
      <div class="section-header" style="margin-top: 2.25rem;">
        <div>
          <h3 class="section-title">Asignaciones Activas</h3>
          <p class="section-sub">Equipos asignados a tus pacientes. Usa la fila inferior para crear nuevas asignaciones.</p>
        </div>
        <select v-model="filtroPaciente" class="filtro-select">
          <option value="">Todos los pacientes</option>
          <option v-for="p in misPacientes" :key="p.paciente_id" :value="p.paciente_id">
            {{ p.nombre }} {{ p.apellido }}
          </option>
        </select>
      </div>

      <div class="tabla-wrap">
        <table class="tabla">
          <thead>
            <tr>
              <th>Equipo</th>
              <th>Paciente</th>
              <th>Fecha de asignación</th>
              <th class="col-centro">Acción</th>
            </tr>
          </thead>
          <tbody>

            <!-- Filas de asignaciones existentes -->
            <tr v-if="asignacionesFiltradas.length === 0">
              <td colspan="4" class="empty-row">
                {{ filtroPaciente ? 'No hay asignaciones para este paciente.' : 'Sin asignaciones activas.' }}
              </td>
            </tr>
            <tr
              v-for="a in asignacionesFiltradas"
              :key="a.id_asignaciones"
              class="tr-asignacion"
            >
              <td class="td-bold">{{ a.equipo }}</td>
              <td>
                <div class="paciente-cell">
                  <span class="avatar">{{ a.iniciales }}</span>
                  {{ a.paciente }}
                </div>
              </td>
              <td class="td-gris">{{ formatFecha(a.fecha_asignacion) }}</td>
              <td class="col-centro">
                <button class="btn-liberar" :disabled="guardando" @click="liberar(a)">
                  Liberar
                </button>
              </td>
            </tr>

            <!-- Fila para crear nueva asignación -->
            <tr class="tr-nueva">
              <td>
                <select v-model="nuevaInventario_id" class="select-inline">
                  <option value="">Seleccionar equipo...</option>
                  <option
                    v-for="item in items"
                    :key="item.id_inventario"
                    :value="item.id_inventario"
                    :disabled="disponiblesDeItem(item) === 0"
                    :class="{ 'opt-sin-stock': disponiblesDeItem(item) === 0 }"
                  >
                    {{ item.nombre }}{{ disponiblesDeItem(item) === 0 ? ' (Sin stock)' : '' }}
                  </option>
                </select>
              </td>
              <td>
                <select v-model="nuevaPaciente_id" class="select-inline">
                  <option value="">Seleccionar paciente...</option>
                  <option
                    v-for="p in misPacientes"
                    :key="p.paciente_id"
                    :value="p.paciente_id"
                  >
                    {{ p.nombre }} {{ p.apellido }}
                  </option>
                </select>
                <span v-if="sinStock" class="aviso-sin-stock">⚠ Este equipo no tiene unidades disponibles.</span>
              </td>
              <td class="td-gris td-hoy">Hoy · {{ formatFecha(hoy) }}</td>
              <td class="col-centro">
                <button
                  class="btn-asignar"
                  :disabled="guardando || !nuevaInventario_id || !nuevaPaciente_id || sinStock"
                  @click="asignar"
                >
                  + Asignar
                </button>
              </td>
            </tr>

          </tbody>
        </table>
      </div>

    </template>

  </FisioLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import FisioLayout from '@/components/FisioLayout.vue'
import { inventarioService, fisioService, asignacionesService } from '@/services/api'

// ── Estado ───────────────────────────────────────────────────────────────────
const loading   = ref(true)
const guardando = ref(false)

const items               = ref([])
const misPacientes        = ref([])
const asignaciones        = ref([])   // solo las del fisio actual (para la tabla)
const todasAsignaciones   = ref([])   // todas activas del sistema (para calcular stock)

const filtroEstado   = ref('')
const filtroPaciente = ref('')

const nuevaInventario_id = ref('')
const nuevaPaciente_id   = ref('')

const toast   = ref({ visible: false, mensaje: '', tipo: 'success' })
const hoy     = new Date().toISOString().split('T')[0]
let toastTimer = null

// ── Computed ──────────────────────────────────────────────────────────────────

// Verifica si el equipo seleccionado tiene stock = 0
const sinStock = computed(() => {
  if (!nuevaInventario_id.value) return false
  const item = items.value.find(i => Number(i.id_inventario) === Number(nuevaInventario_id.value))
  return item ? disponiblesDeItem(item) === 0 : false
})

const itemsFiltrados = computed(() => {
  if (!filtroEstado.value) return items.value
  return items.value.filter(i => estadoCalculado(i) === filtroEstado.value)
})

const asignacionesEnriquecidas = computed(() =>
  asignaciones.value.map(a => {
    const item = items.value.find(i => Number(i.id_inventario) === Number(a.inventario_id))
    const pac  = misPacientes.value.find(p => Number(p.paciente_id) === Number(a.paciente_id))
    return {
      ...a,
      equipo:    item?.nombre ?? `Equipo #${a.inventario_id}`,
      paciente:  pac  ? `${pac.nombre} ${pac.apellido}` : `Paciente #${a.paciente_id}`,
      iniciales: pac  ? `${pac.nombre[0] ?? ''}${pac.apellido?.[0] ?? ''}`.toUpperCase() : '?',
    }
  })
)

const asignacionesFiltradas = computed(() => {
  if (!filtroPaciente.value) return asignacionesEnriquecidas.value
  return asignacionesEnriquecidas.value.filter(
    a => Number(a.paciente_id) === Number(filtroPaciente.value)
  )
})

// ── Helpers ───────────────────────────────────────────────────────────────────

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

// Estado calculado desde stock real (igual que en Inventario admin)
function estadoCalculado(item) {
  if (item.estado === 'mantenimiento') return 'mantenimiento'
  const disp  = disponiblesDeItem(item)
  const total = item.cantidad ?? 0
  if (disp === 0)              return 'no_disponible'
  if (disp < 5)                return 'baja'
  return 'disponible'
}

const etiquetasEstado = { disponible: 'Disponible', no_disponible: 'No Disponible', mantenimiento: 'Mantenimiento', baja: 'Stock Bajo' }
function etiquetaEstado(val) { return etiquetasEstado[val] ?? val }

function estadoClass(estado) {
  if (estado === 'disponible')    return 'green'
  if (estado === 'baja')          return 'yellow'
  if (estado === 'no_disponible') return 'red'
  if (estado === 'mantenimiento') return 'gray'
  return 'gray'
}

function formatFecha(fecha) {
  if (!fecha) return '—'
  return new Date(fecha + 'T00:00:00').toLocaleDateString('es-ES', {
    day: '2-digit', month: 'short', year: 'numeric'
  })
}

function mostrarToast(mensaje, tipo = 'success') {
  clearTimeout(toastTimer)
  toast.value = { visible: true, mensaje, tipo }
  toastTimer = setTimeout(() => { toast.value.visible = false }, 3000)
}

// ── Acciones ──────────────────────────────────────────────────────────────────

async function asignar() {
  if (!nuevaInventario_id.value || !nuevaPaciente_id.value) return
  guardando.value = true
  try {
    await asignacionesService.create({
      inventario_id: nuevaInventario_id.value,
      paciente_id:   nuevaPaciente_id.value,
    })
    nuevaInventario_id.value = ''
    nuevaPaciente_id.value   = ''
    await refrescarTodo()
    mostrarToast('Equipo asignado correctamente.', 'success')
  } catch {
    mostrarToast('No se pudo asignar el equipo. Intenta de nuevo.', 'error')
  } finally {
    guardando.value = false
  }
}

async function liberar(asignacion) {
  guardando.value = true
  try {
    await asignacionesService.liberar(asignacion.id_asignaciones)
    await refrescarTodo()
    mostrarToast('Equipo liberado correctamente.', 'success')
  } catch {
    mostrarToast('No se pudo liberar el equipo. Intenta de nuevo.', 'error')
  } finally {
    guardando.value = false
  }
}

// ── Carga de datos ────────────────────────────────────────────────────────────

// Refresca inventario + asignaciones sin spinner (para usar después de asignar/liberar)
async function refrescarTodo() {
  const [ir, ar, todasR] = await Promise.allSettled([
    inventarioService.getAll(),
    fisioService.misAsignaciones(),
    asignacionesService.getAll(),
  ])
  if (ir.status    === 'fulfilled') items.value             = ir.value.data
  if (ar.status    === 'fulfilled') asignaciones.value      = ar.value.data
  if (todasR.status === 'fulfilled') todasAsignaciones.value = todasR.value.data
}

async function cargarAsignaciones() {
  const [misR, todasR] = await Promise.allSettled([
    fisioService.misAsignaciones(),
    asignacionesService.getAll(),
  ])
  asignaciones.value      = misR.status   === 'fulfilled' ? misR.value.data   : []
  todasAsignaciones.value = todasR.status === 'fulfilled' ? todasR.value.data : []
}

async function cargar() {
  loading.value = true
  try {
    const [ir, pr] = await Promise.allSettled([
      inventarioService.getAll(),
      fisioService.misPacientes(),
    ])
    if (ir.status === 'fulfilled') items.value        = ir.value.data
    if (pr.status === 'fulfilled') misPacientes.value = pr.value.data
    await cargarAsignaciones()
  } finally {
    loading.value = false
  }
}

onMounted(cargar)
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

/* ── Cabecera de página ───────────────────────────────────────────────────── */
.pag-header   { margin-bottom: 1.75rem; }
.page-title   { color: #ffffff; font-size: 1.4rem; font-weight: 700; }
.page-sub     { color: #6b7280; font-size: 0.82rem; margin-top: 0.2rem; }

/* ── Toast ───────────────────────────────────────────────────────────────── */
.toast {
  position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 200;
  padding: 0.75rem 1.25rem; border-radius: 8px;
  font-size: 0.83rem; font-weight: 600;
  box-shadow: 0 8px 24px rgba(0,0,0,0.4);
}
.toast.success { background: #064e2e; color: #4ade80; border: 1px solid #4ade8033; }
.toast.error   { background: #4c1313; color: #f87171; border: 1px solid #f8717133; }
.toast-fade-enter-active, .toast-fade-leave-active { transition: opacity .25s, transform .25s; }
.toast-fade-enter-from, .toast-fade-leave-to       { opacity: 0; transform: translateY(8px); }

/* ── Cabecera de sección ─────────────────────────────────────────────────── */
.section-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.85rem;
  flex-wrap: wrap;
}
.section-title { color: #e5e7eb; font-size: 0.95rem; font-weight: 700; margin-bottom: 0.15rem; }
.section-sub   { color: #4b5563; font-size: 0.75rem; }

.filtro-select {
  background: #111111; border: 1px solid #1c1c1c; color: #9ca3af;
  padding: 0.45rem 0.75rem; border-radius: 7px;
  font-size: 0.8rem; cursor: pointer; flex-shrink: 0;
}
.filtro-select:focus { outline: none; border-color: #074434; }

/* ── Tabla ───────────────────────────────────────────────────────────────── */
.tabla-wrap {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 10px;
  overflow: hidden;
}

.tabla {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.83rem;
}

.tabla thead tr {
  background: #0e0e0e;
  border-bottom: 1px solid #1c1c1c;
}

.tabla th {
  padding: 0.7rem 1.1rem;
  text-align: left;
  color: #4b5563;
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.tabla td {
  padding: 0.82rem 1.1rem;
  color: #d1d5db;
  border-top: 1px solid #161616;
  vertical-align: middle;
}

.tr-inventario:hover td,
.tr-asignacion:hover td { background: #141414; }

.td-bold  { color: #ffffff; font-weight: 600; }
.td-gris  { color: #6b7280; }
.td-hoy   { font-size: 0.78rem; }
.col-centro { text-align: center; }

/* ── Empty rows ──────────────────────────────────────────────────────────── */
.empty-state { color: #4b5563; text-align: center; padding: 3rem; font-size: 0.9rem; }
.empty-row   { color: #374151; text-align: center; padding: 1.5rem; font-style: italic; }

/* ── Estado pills ────────────────────────────────────────────────────────── */
.estado-pill {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 0.3rem 0.7rem; border-radius: 20px;
  font-size: 0.72rem; font-weight: 600;
}
.estado-pill.green  { background: rgba(74,222,128,0.12); color: #4ade80; }
.estado-pill.yellow { background: rgba(251,191,36,0.12); color: #fbbf24; }
.estado-pill.blue   { background: rgba(56,189,248,0.12); color: #38bdf8; }
.estado-pill.red    { background: rgba(248,113,113,0.12); color: #f87171; }
.estado-pill.gray   { background: rgba(107,114,128,0.12); color: #9ca3af; }
.dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; display: inline-block; }

/* ── Avatar del paciente ─────────────────────────────────────────────────── */
.paciente-cell { display: flex; align-items: center; gap: 0.6rem; }
.avatar {
  width: 28px; height: 28px;
  background: #064e2e; color: #4ade80;
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  font-size: 0.65rem; font-weight: 700; flex-shrink: 0;
}

/* ── Fila de nueva asignación ────────────────────────────────────────────── */
.tr-nueva td {
  background: #0c0c0c;
  border-top: 1px dashed #1e1e1e;
}

.select-inline {
  width: 100%;
  background: #161616;
  border: 1px solid #262626;
  color: #9ca3af;
  padding: 0.45rem 0.65rem;
  border-radius: 6px;
  font-size: 0.8rem;
  cursor: pointer;
  outline: none;
  transition: border-color .15s;
}
.select-inline:focus  { border-color: #074434; color: #d1d5db; }

/* ── Botones ─────────────────────────────────────────────────────────────── */
.btn-asignar {
  background: rgba(74, 222, 128, 0.12);
  border: 1px solid rgba(74, 222, 128, 0.3);
  color: #4ade80;
  padding: 0.4rem 0.95rem;
  border-radius: 6px;
  font-size: 0.78rem; font-weight: 600;
  cursor: pointer; white-space: nowrap;
  transition: background .15s, border-color .15s;
}
.btn-asignar:hover:not(:disabled)    { background: rgba(74,222,128,0.2); border-color: rgba(74,222,128,0.5); }
.btn-asignar:disabled                { opacity: 0.35; cursor: not-allowed; }

/* Opciones sin stock en el select */
.opt-sin-stock { color: #6b7280; }

/* Aviso de sin stock */
.aviso-sin-stock {
  display: block; margin-top: 0.4rem;
  color: #f87171; font-size: 0.75rem; font-weight: 500;
}

.btn-liberar {
  background: rgba(251, 191, 36, 0.1);
  border: 1px solid rgba(251, 191, 36, 0.3);
  color: #fbbf24;
  padding: 0.4rem 0.95rem;
  border-radius: 6px;
  font-size: 0.78rem; font-weight: 600;
  cursor: pointer; white-space: nowrap;
  transition: background .15s, border-color .15s;
}
.btn-liberar:hover:not(:disabled) { background: rgba(251,191,36,0.18); border-color: rgba(251,191,36,0.5); }
.btn-liberar:disabled             { opacity: 0.4; cursor: not-allowed; }

/* ── Columna disponibles ─────────────────────────────────────────────────── */
.td-total    { color: #374151; font-weight: 400; font-size: 0.75rem; margin-left: 2px; }
.disp-ok     { color: #4ade80; }
.disp-bajo   { color: #fbbf24; }
.disp-agotado{ color: #f87171; }
</style>
