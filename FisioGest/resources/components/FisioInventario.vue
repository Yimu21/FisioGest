<template>
  <FisioLayout>

    <div class="inv-header">
      <div>
        <h2 class="page-title">Inventario</h2>
        <p class="page-sub">Equipos disponibles en la clínica</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="inv-stats">
      <div class="stat-box">
        <span class="sbox-label">Total Items</span>
        <span class="sbox-value">{{ stats.total }}</span>
      </div>
      <div class="stat-box">
        <span class="sbox-label">Disponibles</span>
        <div class="sbox-row">
          <span class="sbox-value">{{ stats.disponibles }}</span>
          <span class="dot-green"></span>
        </div>
      </div>
      <div class="stat-box">
        <span class="sbox-label">Stock Bajo</span>
        <span class="sbox-value">{{ stats.stockBajo }}</span>
      </div>
      <div class="stat-box critical">
        <span class="sbox-label">Agotados</span>
        <span class="sbox-value">{{ stats.agotados }}</span>
      </div>
    </div>

    <!-- Tabla -->
    <div class="table-card">
      <div class="table-top">
        <h3 class="table-title">Equipos</h3>
        <input v-model="busqueda" class="busqueda-input" placeholder="Buscar equipo..." />
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Equipo</th>
              <th>Tipo</th>
              <th class="td-center">Cantidad Total</th>
              <th class="td-center">Disponibles</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="5" class="td-empty">Cargando...</td>
            </tr>
            <tr v-else-if="itemsFiltrados.length === 0">
              <td colspan="5" class="td-empty">No hay equipos registrados.</td>
            </tr>
            <tr v-for="item in itemsFiltrados" :key="item.id_inventario">
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
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </FisioLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import FisioLayout from '@/components/FisioLayout.vue'
import { inventarioService, asignacionesService } from '@/services/api'

const loading          = ref(true)
const items            = ref([])
const asignaciones     = ref([])
const busqueda         = ref('')

// ── Carga ─────────────────────────────────────────────────────────────────────
async function cargar() {
  loading.value = true
  try {
    const [ir, ar] = await Promise.allSettled([
      inventarioService.getAll(),
      asignacionesService.getAll(),
    ])
    if (ir.status === 'fulfilled') items.value        = ir.value.data
    if (ar.status === 'fulfilled') asignaciones.value = ar.value.data
  } finally {
    loading.value = false
  }
}

// ── Stock disponible ──────────────────────────────────────────────────────────
function disponiblesDeItem(item) {
  const asignados = asignaciones.value.filter(
    a => Number(a.inventario_id) === Number(item.id_inventario)
  ).length
  return Math.max(0, (item.cantidad ?? 0) - asignados)
}

function disponiblesClass(disponibles, total) {
  if (disponibles === 0)                             return 'disp-agotado'
  if (disponibles <= Math.ceil((total ?? 0) * 0.3)) return 'disp-bajo'
  return 'disp-ok'
}

function estadoCalculado(item) {
  if (item.estado === 'mantenimiento') return 'mantenimiento'
  const disp  = disponiblesDeItem(item)
  const total = item.cantidad ?? 0
  if (disp === 0 && total > 0)             return 'en_uso'
  if (disp <= Math.ceil(total * 0.3))      return 'baja'
  return 'disponible'
}

const etiquetas = { disponible: 'Disponible', en_uso: 'En Uso', mantenimiento: 'Mantenimiento', baja: 'Stock Bajo' }
function etiquetaEstado(val) { return etiquetas[val] ?? val }

// ── Stats ─────────────────────────────────────────────────────────────────────
const stats = computed(() => ({
  total:       items.value.length,
  disponibles: items.value.filter(i => estadoCalculado(i) === 'disponible').length,
  stockBajo:   items.value.filter(i => estadoCalculado(i) === 'baja').length,
  agotados:    items.value.filter(i => estadoCalculado(i) === 'en_uso').length,
}))

// ── Búsqueda ──────────────────────────────────────────────────────────────────
const itemsFiltrados = computed(() => {
  if (!busqueda.value.trim()) return items.value
  const q = busqueda.value.toLowerCase()
  return items.value.filter(i =>
    i.nombre.toLowerCase().includes(q) || i.tipo.toLowerCase().includes(q)
  )
})

onMounted(cargar)
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

.inv-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; }
.page-title { color: #ffffff; font-size: 1.4rem; font-weight: 700; }
.page-sub   { color: #6b7280; font-size: 0.82rem; margin-top: 0.15rem; }

.inv-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.9rem; margin-bottom: 1.25rem; }

.stat-box {
  background: #111111; border: 1px solid #1c1c1c; border-radius: 10px;
  padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: 0.5rem;
}
.stat-box.critical { background: #1a0e0e; border-color: #2e1414; }

.sbox-label { color: #6b7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.sbox-value { color: #ffffff; font-size: 2.2rem; font-weight: 700; line-height: 1; }
.sbox-row   { display: flex; align-items: center; gap: 0.6rem; }
.dot-green  { width: 10px; height: 10px; border-radius: 50%; background: #4ade80; flex-shrink: 0; }

.table-card {
  background: #111111; border: 1px solid #1c1c1c; border-radius: 10px; padding: 1.1rem 1.25rem;
}
.table-top    { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.9rem; }
.table-title  { color: #ffffff; font-size: 0.95rem; font-weight: 600; }
.busqueda-input {
  background: #0d0d0d; border: 1px solid #1c1c1c; border-radius: 7px;
  padding: 0.4rem 0.75rem; color: #d1d5db; font-size: 0.82rem; outline: none;
  width: 220px; transition: border-color 0.15s;
}
.busqueda-input:focus { border-color: #074434; }
.busqueda-input::placeholder { color: #4b5563; }

.table-wrapper { overflow-x: auto; }

table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
thead tr { border-bottom: 1px solid #1c1c1c; }

th {
  text-align: left; color: #6b7280; font-size: 0.75rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.5px; padding: 0.6rem 0.75rem;
}
td { color: #d1d5db; padding: 0.75rem; border-bottom: 1px solid #161616; }
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: rgba(255,255,255,0.02); }

.td-center { text-align: center; }
.td-nombre { color: #ffffff; font-weight: 600; }
.td-empty  { text-align: center; color: #374151; padding: 2rem; font-style: italic; }

.tag-tipo {
  background: rgba(107,114,128,0.15); color: #9ca3af;
  padding: 0.2rem 0.55rem; border-radius: 4px; font-size: 0.75rem; font-weight: 500;
}

.disp-val   { font-weight: 700; margin-right: 2px; }
.disp-total { color: #374151; font-size: 0.75rem; }
.disp-ok      { color: #4ade80; }
.disp-bajo    { color: #fbbf24; }
.disp-agotado { color: #f87171; }

.estado-badge { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; font-weight: 500; }
.estado-badge .estado-dot { width: 8px; height: 8px; border-radius: 50%; }
.estado-badge.disponible    { color: #4ade80; }
.estado-badge.disponible .estado-dot    { background: #4ade80; }
.estado-badge.en_uso        { color: #38bdf8; }
.estado-badge.en_uso .estado-dot        { background: #38bdf8; }
.estado-badge.mantenimiento { color: #f59e0b; }
.estado-badge.mantenimiento .estado-dot { background: #f59e0b; }
.estado-badge.baja          { color: #f87171; }
.estado-badge.baja .estado-dot          { background: #f87171; }
</style>
