<template>
  <FisioLayout>

    <div class="pag-header">
      <div>
        <h2 class="page-title">Citas Registradas <span class="page-title-sub">(Historial)</span></h2>
        <p class="page-sub">Registro histórico de las citas de <strong>{{ userName }}</strong>.</p>
      </div>
    </div>

    <!-- Filtros -->
    <div class="filtros-row">
      <select v-model="filtroPaciente" class="filtro-select">
        <option value="">Todos los pacientes</option>
        <option v-for="p in todosPacientes" :key="p.paciente_id" :value="p.paciente_id">
          {{ p.nombre }} {{ p.apellido }}
        </option>
      </select>
      <select v-model="filtroEstado" class="filtro-select">
        <option value="">Todos los estados</option>
        <option value="programada">Programada</option>
        <option value="atendida">Atendida</option>
        <option value="cancelada">Cancelada</option>
      </select>
    </div>

    <!-- Stats rápidas -->
    <div class="stats-row">
      <div class="mini-stat">
        <span class="ms-val">{{ misCitas.length }}</span>
        <span class="ms-label">Total</span>
      </div>
      <div class="mini-stat">
        <span class="ms-val green">{{ misCitas.filter(c=>c.estado==='atendida').length }}</span>
        <span class="ms-label">Completadas</span>
      </div>
      <div class="mini-stat">
        <span class="ms-val blue">{{ misCitas.filter(c=>c.estado==='programada').length }}</span>
        <span class="ms-label">Programadas</span>
      </div>
      <div class="mini-stat">
        <span class="ms-val red">{{ misCitas.filter(c=>c.estado==='cancelada').length }}</span>
        <span class="ms-label">Canceladas</span>
      </div>
    </div>

    <!-- Lista de citas -->
    <div v-if="loading" class="empty-state">Cargando citas...</div>

    <div v-else-if="citasFiltradas.length === 0" class="empty-state">
      No hay citas que coincidan con los filtros seleccionados.
    </div>

    <div v-else class="citas-list">
      <div v-for="c in citasFiltradas" :key="c.cita_id" class="cita-card">

        <!-- Avatar -->
        <div class="c-avatar" :style="{ background: avatarColor(nombrePaciente(c.paciente_id)) }">
          {{ inicialesPaciente(c.paciente_id) }}
        </div>

        <!-- Info izquierda -->
        <div class="c-info">
          <div class="c-name-row">
            <span class="c-nombre">{{ nombrePaciente(c.paciente_id) }}</span>
            <span class="c-pid">ID: {{ c.paciente_id }}</span>
          </div>
          <p class="c-fecha">📅 Fecha: <strong>{{ formatFecha(c.fecha_hora) }}</strong></p>
          <div class="c-diag" v-if="c.motivo">
            <span class="diag-label">Motivo / Diagnóstico:</span>
            <span class="diag-badge">{{ c.motivo }}</span>
          </div>
        </div>

        <!-- Info derecha -->
        <div class="c-right">
          <span class="estado-badge" :class="c.estado">{{ estadoLabel(c.estado) }}</span>
          <div class="c-dur">
            <span class="dur-label">Terapeuta</span>
            <span class="dur-val green">{{ userName }}</span>
          </div>
        </div>
      </div>
    </div>

  </FisioLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import FisioLayout from '@/components/FisioLayout.vue'
import { fisioService, getUser } from '@/services/api'

const loading        = ref(true)
const misCitas       = ref([])
const todosPacientes = ref([])
const filtroPaciente = ref('')
const filtroEstado   = ref('')

const currentUser = computed(() => getUser())
const userName    = computed(() => currentUser.value?.nombre ?? 'Fisioterapeuta')

const citasFiltradas = computed(() => {
  let r = misCitas.value
  if (filtroPaciente.value) r = r.filter(c => c.paciente_id == filtroPaciente.value)
  if (filtroEstado.value)   r = r.filter(c => c.estado === filtroEstado.value)
  return r.slice().sort((a, b) => new Date(b.fecha_hora) - new Date(a.fecha_hora))
})

function nombrePaciente(id) {
  const p = todosPacientes.value.find(p => p.paciente_id === id)
  return p ? `${p.nombre} ${p.apellido}` : `Paciente #${id}`
}

function inicialesPaciente(id) {
  const n = nombrePaciente(id)
  return n.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
}

const colores = ['#1e3a8a','#4c1d95','#78350f','#14532d','#7f1d1d','#0c4a6e']
function avatarColor(nombre = '') {
  return colores[nombre.charCodeAt(0) % colores.length]
}

function formatFecha(fh) {
  if (!fh) return '—'
  try { return new Date(fh).toLocaleString('es-ES', { dateStyle: 'medium', timeStyle: 'short' }) } catch { return fh }
}

function estadoLabel(estado) {
  const map = { programada: 'Programada', atendida: 'Completada', cancelada: 'Cancelada' }
  return map[estado] ?? estado
}

async function cargar() {
  loading.value = true
  try {
    const [cr, pr] = await Promise.allSettled([
      fisioService.misCitas(),
      fisioService.misPacientes(),
    ])
    if (cr.status === 'fulfilled') misCitas.value       = cr.value.data
    if (pr.status === 'fulfilled') todosPacientes.value = pr.value.data
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
.page-title-sub { font-size: 0.95rem; font-weight: 400; color: #6b7280; }
.page-sub { color: #6b7280; font-size: 0.82rem; margin-top: 0.2rem; }
.page-sub strong { color: #4ade80; }

.filtros-row {
  display: flex; gap: 0.75rem; margin-bottom: 1rem; flex-wrap: wrap;
}
.filtro-select {
  background: #111111; border: 1px solid #1c1c1c; color: #9ca3af;
  padding: 0.5rem 0.75rem; border-radius: 7px; font-size: 0.82rem; cursor: pointer;
}
.filtro-select:focus { outline: none; border-color: #074434; }

/* Stats row */
.stats-row {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}
.mini-stat {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 8px;
  padding: 0.75rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  min-width: 90px;
}
.ms-val { color: #ffffff; font-size: 1.6rem; font-weight: 700; line-height: 1; }
.ms-val.green { color: #4ade80; }
.ms-val.blue  { color: #38bdf8; }
.ms-val.red   { color: #f87171; }
.ms-label { color: #6b7280; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.4px; }

.empty-state { color: #4b5563; text-align: center; padding: 3rem; font-size: 0.9rem; }

/* Citas list */
.citas-list { display: flex; flex-direction: column; gap: 0.75rem; }

.cita-card {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 10px;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: border-color 0.15s;
}
.cita-card:hover { border-color: #2a2a2a; }

.c-avatar {
  width: 46px; height: 46px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 14px; font-weight: 700; flex-shrink: 0;
}

.c-info { flex: 1; min-width: 0; }
.c-name-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.3rem; }
.c-nombre { color: #ffffff; font-size: 0.9rem; font-weight: 700; }
.c-pid { color: #4b5563; font-size: 0.72rem; font-family: monospace; }
.c-fecha { color: #9ca3af; font-size: 0.78rem; margin-bottom: 0.35rem; }
.c-fecha strong { color: #38bdf8; }

.c-diag { display: flex; align-items: center; gap: 0.4rem; }
.diag-label { color: #4b5563; font-size: 0.72rem; }
.diag-badge { background: #1a1a1a; border: 1px solid #2a2a2a; color: #9ca3af; font-size: 0.72rem; padding: 0.15rem 0.5rem; border-radius: 4px; }

.c-right {
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.6rem;
}

.c-dur { display: flex; flex-direction: column; align-items: flex-end; gap: 1px; }
.dur-label { color: #4b5563; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.3px; }
.dur-val { color: #d1d5db; font-size: 0.8rem; font-weight: 600; }
.dur-val.green { color: #4ade80; }

/* Estado badges */
.estado-badge { display: inline-block; padding: 0.25rem 0.7rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: capitalize; }
.estado-badge.programada { background: rgba(59,130,246,0.15); color: #93c5fd; }
.estado-badge.atendida   { background: rgba(74,222,128,0.15); color: #4ade80; }
.estado-badge.cancelada  { background: rgba(239,68,68,0.15);  color: #f87171; }
</style>
