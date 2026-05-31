<template>
  <FisioLayout>

    <div class="pag-header">
      <div>
        <h2 class="page-title">Asignaciones de Equipo</h2>
        <p class="page-sub">Equipos clínicos asignados a tus pacientes activos.</p>
      </div>
    </div>

    <!-- Filtros -->
    <div class="filtros-row">
      <select v-model="filtroEstado" class="filtro-select">
        <option value="">Todos los estados</option>
        <option value="disponible">Disponible</option>
        <option value="baja">Stock Bajo</option>
        <option value="asignado">Asignado</option>
      </select>
      <select v-model="filtroPaciente" class="filtro-select">
        <option value="">Todos los pacientes</option>
        <option v-for="p in misPacientes" :key="p.paciente_id" :value="p.paciente_id">
          {{ p.nombre }} {{ p.apellido }}
        </option>
      </select>
    </div>

    <!-- Inventario real de BD -->
    <div v-if="loading" class="empty-state">Cargando equipos...</div>

    <div v-else-if="itemsFiltrados.length === 0 && asignacionesDemo.length === 0" class="empty-state">
      No hay equipos registrados.
    </div>

    <div v-else class="items-list">

      <!-- Items del inventario real -->
      <div v-for="item in itemsFiltrados" :key="item.inventario_id ?? item.id" class="item-card">
        <div class="item-icon">🩺</div>
        <div class="item-info">
          <p class="item-name">{{ item.nombre }}</p>
          <p class="item-meta">Tipo: {{ item.tipo ?? '—' }} · Cantidad: {{ item.cantidad ?? '—' }}</p>
        </div>
        <div class="item-mid">
          <span class="meta-label">Paciente asignado</span>
          <span class="meta-val">{{ pacientePorItem(item) }}</span>
        </div>
        <div class="item-mid">
          <span class="meta-label">Terapeuta</span>
          <span class="meta-val green">{{ userName }}</span>
        </div>
        <div class="item-estado">
          <span class="estado-pill" :class="estadoClass(item.estado)">
            <span class="dot"></span>
            {{ item.estado ?? 'N/A' }}
          </span>
        </div>
      </div>

      <!-- Asignaciones demo si no hay datos reales -->
      <template v-if="itemsFiltrados.length === 0">
        <div v-for="a in asignacionesDemo" :key="a.id" class="item-card">
          <div class="item-icon">{{ a.icon }}</div>
          <div class="item-info">
            <p class="item-name">{{ a.equipo }}</p>
            <p class="item-meta">Paciente: {{ a.paciente }} · {{ a.pid }}</p>
          </div>
          <div class="item-mid">
            <span class="meta-label">Duración</span>
            <span class="meta-val">{{ a.duracion }}</span>
          </div>
          <div class="item-mid">
            <span class="meta-label">Tiempo Restante</span>
            <span class="meta-val blue">{{ a.tiempo }}</span>
          </div>
          <div class="item-estado">
            <span class="estado-pill" :class="a.estadoClass">
              <span class="dot"></span>
              {{ a.estado }}
            </span>
          </div>
        </div>
      </template>

    </div>

  </FisioLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import FisioLayout from '@/components/FisioLayout.vue'
import { inventarioService, fisioService, getUser } from '@/services/api'

const loading        = ref(true)
const items          = ref([])
const misPacientes   = ref([])
const filtroEstado   = ref('')
const filtroPaciente = ref('')

const currentUser = computed(() => getUser())
const userName    = computed(() => currentUser.value?.nombre ?? 'Fisioterapeuta')

const itemsFiltrados = computed(() => {
  let r = items.value
  if (filtroEstado.value) r = r.filter(i => (i.estado ?? '') === filtroEstado.value)
  return r
})

function pacientePorItem(item) {
  if (item.paciente_id) {
    const p = misPacientes.value.find(p => p.paciente_id === item.paciente_id)
    return p ? `${p.nombre} ${p.apellido}` : `ID: ${item.paciente_id}`
  }
  return 'Sin asignar'
}

function estadoClass(estado) {
  if (estado === 'disponible') return 'green'
  if (estado === 'baja' || estado === 'Stock Bajo') return 'yellow'
  return 'blue'
}

const asignacionesDemo = [
  { id: 1, icon: '🦿', equipo: 'Prótesis Dinámica de Miembro Inferior', paciente: 'Elena Rodriguez', pid: 'ID: ER001', duracion: '15 Oct 2023 – 15 Oct 2025', tiempo: '31 días', estado: 'En Uso', estadoClass: 'green' },
  { id: 2, icon: '🖐',  equipo: 'Órtesis Dinámica de Mano Muñeca',      paciente: 'Carlos Pérez',    pid: 'ID: CP025', duracion: '15 Nov – 25 Nov 2023',          tiempo: '7 días',  estado: 'Terminado', estadoClass: 'yellow' },
  { id: 3, icon: '🦵', equipo: 'Órtesis Modular KAFO de Fibra de C.',   paciente: 'Carlos Pérez',    pid: 'ID: CP025', duracion: '10 Nov – 20 Nov 2023',          tiempo: '7 días',  estado: 'Mantenimiento', estadoClass: 'blue' },
]

async function cargar() {
  loading.value = true
  try {
    const [ir, pr] = await Promise.allSettled([
      inventarioService.getAll(),
      fisioService.misPacientes(),
    ])
    if (ir.status === 'fulfilled') items.value        = ir.value.data
    if (pr.status === 'fulfilled') misPacientes.value = pr.value.data
  } finally {
    loading.value = false
  }
}

onMounted(cargar)
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

.pag-header { margin-bottom: 1.25rem; }
.page-title { color: #ffffff; font-size: 1.4rem; font-weight: 700; }
.page-sub   { color: #6b7280; font-size: 0.82rem; margin-top: 0.2rem; }

.filtros-row {
  display: flex; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap;
}
.filtro-select {
  background: #111111; border: 1px solid #1c1c1c; color: #9ca3af;
  padding: 0.5rem 0.75rem; border-radius: 7px; font-size: 0.82rem; cursor: pointer;
}
.filtro-select:focus { outline: none; border-color: #074434; }

.empty-state { color: #4b5563; text-align: center; padding: 3rem; font-size: 0.9rem; }

.items-list { display: flex; flex-direction: column; gap: 0.75rem; }

.item-card {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 10px;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.item-icon {
  width: 52px; height: 52px;
  background: #1a1a1a;
  border: 1px solid #2a2a2a;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 24px; flex-shrink: 0;
}

.item-info { flex: 1; min-width: 0; }
.item-name { color: #ffffff; font-size: 0.88rem; font-weight: 600; margin-bottom: 0.2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.item-meta { color: #6b7280; font-size: 0.75rem; }

.item-mid { min-width: 130px; flex-shrink: 0; }
.meta-label { display: block; color: #4b5563; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
.meta-val { color: #d1d5db; font-size: 0.82rem; font-weight: 500; }
.meta-val.green { color: #4ade80; }
.meta-val.blue  { color: #38bdf8; }

.item-estado { flex-shrink: 0; }

.estado-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}
.estado-pill.green  { background: rgba(74,222,128,0.12); color: #4ade80; }
.estado-pill.yellow { background: rgba(251,191,36,0.12); color: #fbbf24; }
.estado-pill.blue   { background: rgba(56,189,248,0.12); color: #38bdf8; }

.dot {
  width: 6px; height: 6px; border-radius: 50%; background: currentColor; display: inline-block;
}
</style>
