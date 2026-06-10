<template>
  <AppLayout>

    <h2 class="page-title">Dashboard</h2>

    <!-- ── Stats grid ── -->
    <div class="stats-grid">

      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Citas de Hoy</span>
          <span class="stat-value">{{ stats.citasHoy }}</span>
        </div>
        <div class="stat-icon green">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Pacientes Nuevos (Mes)</span>
          <span class="stat-value">{{ stats.pacientesNuevos }}</span>
        </div>
        <div class="stat-icon gray">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </div>
      </div>

      <div class="stat-card alert-card">
        <div class="alert-top">
          <span class="stat-label">Alerta de Inventario</span>
        </div>
        <div class="alert-counts">
          <span><strong>{{ stats.alertas }}</strong> Alertas</span>
          <span><strong>{{ stats.pacientesNuevos }}</strong> Pacientes</span>
        </div>
        <div class="alert-list">
          <div v-for="item in alertasInventario" :key="item.id" class="alert-row">
            <div class="alert-row-info">
              <span class="alert-row-title">Alerta de Inventario</span>
              <span class="alert-row-sub">{{ item.nombre }}</span>
            </div>
            <span class="alert-row-count">{{ item.cantidad }}</span>
          </div>
          <div v-if="alertasInventario.length === 0" class="alert-empty">
            Sin alertas de inventario
          </div>
        </div>
      </div>

    </div>

    <!-- ── Chart ── -->
    <div class="chart-card">
      <div class="chart-header">
        <h3 class="chart-title">Estadísticas</h3>
        <div class="chart-legend">
          <span class="legend-item"><span class="dot green"></span> Citas</span>
          <span class="legend-item"><span class="dot gray"></span> Pacientes</span>
        </div>
      </div>
      <div class="chart-wrapper">
        <Line :data="chartData" :options="chartOptions" />
      </div>
    </div>

    <!-- ── Calendario Semanal ── -->
    <div class="cal-card">
      <div class="cal-header">
        <div>
          <h3 class="cal-title">Agenda Semanal — Todas las Citas</h3>
          <p class="cal-sub">{{ weekLabel }}</p>
        </div>
        <div class="cal-nav">
          <button class="nav-btn" @click="weekOffset--">&#8249; Anterior</button>
          <button class="nav-btn today-btn" :class="{ active: weekOffset === 0 }" @click="weekOffset = 0">Hoy</button>
          <button class="nav-btn" @click="weekOffset++">Siguiente &#8250;</button>
        </div>
      </div>

      <div class="cal-scroll">
        <table class="cal-table">
          <thead>
            <tr>
              <th class="time-col"></th>
              <th
                v-for="d in weekDays"
                :key="d.date"
                class="day-col"
                :class="{ 'today-col': d.isToday }"
              >
                {{ d.label }}<span v-if="d.isToday" class="today-dot">&#9679;</span>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="slot in slots" :key="slot">
              <td class="time-cell">{{ slot }}</td>
              <td
                v-for="d in weekDays"
                :key="d.date"
                class="slot-cell"
                :class="{ 'today-slot': d.isToday }"
              >
                <div
                  v-for="cita in citasEnSlot(d.date, slot)"
                  :key="cita.cita_id"
                  class="appt"
                  :class="colorMap[cita.estado] || 'appt-blue'"
                >
                  <span class="appt-name">{{ nombrePaciente(cita.paciente_id) }}</span>
                  <span class="appt-info">{{ (cita.fecha_hora || '').slice(11,16) }} &middot; {{ estadoLabel[cita.estado] || cita.estado }}</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="citasEnSemana === 0" class="cal-empty">
        <span class="cal-empty-icon">📅</span>
        <p>No hay citas para esta semana</p>
      </div>

      <div class="cal-legend-bar">
        <span class="cal-li"><span class="legend-dot appt-blue"></span> Programada</span>
        <span class="cal-li"><span class="legend-dot appt-green"></span> Completada</span>
        <span class="cal-li"><span class="legend-dot appt-yellow"></span> Reprogramada</span>
        <span class="cal-li"><span class="legend-dot appt-red"></span> Cancelada</span>
      </div>
    </div>

  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS, CategoryScale, LinearScale,
  PointElement, LineElement, Tooltip, Legend, Filler
} from 'chart.js'
import AppLayout from '@/components/AppLayout.vue'
import { citaService, pacienteService, inventarioService } from '@/services/api'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Legend, Filler)

const stats             = ref({ citasHoy: 0, pacientesNuevos: 0, alertas: 0 })
const alertasInventario = ref([])
const allCitas          = ref([])
const allPacientes      = ref([])
const weekOffset        = ref(0)

// ── Chart ────────────────────────────────────────────────────────────────────
const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']

const chartData = ref({
  labels: meses,
  datasets: [
    {
      label: 'Citas',
      data: [20, 35, 45, 30, 55, 60, 75, 90, 70, 65, 80, 95],
      borderColor: '#4ade80',
      backgroundColor: 'rgba(74,222,128,0.12)',
      tension: 0.4, fill: true,
      pointBackgroundColor: '#4ade80', pointRadius: 3, pointHoverRadius: 5,
    },
    {
      label: 'Pacientes',
      data: [10, 20, 25, 40, 35, 50, 45, 60, 55, 70, 65, 75],
      borderColor: '#6b7280',
      backgroundColor: 'rgba(107,114,128,0.08)',
      tension: 0.4, fill: true,
      pointBackgroundColor: '#6b7280', pointRadius: 3, pointHoverRadius: 5,
    }
  ]
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: { mode: 'index', intersect: false },
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#1a1a1a', titleColor: '#ffffff',
      bodyColor: '#9ca3af', borderColor: '#2a2a2a', borderWidth: 1, padding: 10,
    }
  },
  scales: {
    x: { grid: { color: '#1a1a1a' }, ticks: { color: '#6b7280', font: { size: 11 } }, border: { color: '#1a1a1a' } },
    y: { grid: { color: '#1a1a1a' }, ticks: { color: '#6b7280', font: { size: 11 } }, border: { color: '#1a1a1a' }, beginAtZero: true }
  }
}

// ── Calendario ────────────────────────────────────────────────────────────────
const DAY_NAMES = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']
const MESES_CAL = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic']

const slots = [
  '08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30',
  '12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30',
  '16:00','16:30','17:00','17:30','18:00','18:30','19:00','19:30','20:00',
]

const colorMap = {
  programada: 'appt-blue', atendida: 'appt-green',
  cancelada:  'appt-red',  reprogramada: 'appt-yellow',
}
const estadoLabel = {
  programada: 'Programada', atendida: 'Completada',
  cancelada:  'Cancelada',  reprogramada: 'Reprogramada',
}

function getMonday(offset) {
  const d   = new Date()
  const day = d.getDay()
  const mon = new Date(d)
  mon.setDate(d.getDate() + (day === 0 ? -6 : 1 - day) + offset * 7)
  mon.setHours(0, 0, 0, 0)
  return mon
}

const weekDays = computed(() => {
  const mon   = getMonday(weekOffset.value)
  const today = new Date().toISOString().slice(0, 10)
  return DAY_NAMES.map((name, i) => {
    const d   = new Date(mon)
    d.setDate(mon.getDate() + i)
    const iso = d.toISOString().slice(0, 10)
    return { label: `${name} ${d.getDate()}`, date: iso, isToday: iso === today }
  })
})

const weekLabel = computed(() => {
  const days  = weekDays.value
  const first = days[0].date
  const last  = days[6].date
  const fmt   = iso => {
    const [, m, d] = iso.split('-')
    return `${parseInt(d)} ${MESES_CAL[parseInt(m) - 1]}`
  }
  return `Semana del ${fmt(first)} al ${fmt(last)} de ${last.slice(0, 4)}`
})

const agendaSemanal = computed(() => {
  const agenda = {}
  for (const d of weekDays.value) agenda[d.date] = {}

  for (const cita of allCitas.value) {
    const fh = cita.fecha_hora ?? cita.fecha
    if (!fh) continue
    const date = fh.slice(0, 10)
    if (!(date in agenda)) continue
    const h    = parseInt(fh.slice(11, 13))
    const m    = parseInt(fh.slice(14, 16))
    const slot = String(h).padStart(2, '0') + ':' + (m < 30 ? '00' : '30')
    if (!agenda[date][slot]) agenda[date][slot] = []
    agenda[date][slot].push(cita)
  }
  return agenda
})

const citasEnSemana = computed(() =>
  weekDays.value.reduce((sum, d) =>
    sum + Object.values(agendaSemanal.value[d.date] || {}).reduce((s, arr) => s + arr.length, 0), 0)
)

function citasEnSlot(date, slot) {
  return agendaSemanal.value[date]?.[slot] ?? []
}

function nombrePaciente(id) {
  const p = allPacientes.value.find(p => p.paciente_id === id)
  return p ? `${p.nombre} ${p.apellido}` : `Paciente #${id}`
}

// ── Carga de datos ────────────────────────────────────────────────────────────
onMounted(async () => {
  try {
    const [citasRes, pacientesRes, inventarioRes] = await Promise.allSettled([
      citaService.getAll(),
      pacienteService.getAll(),
      inventarioService.getAll(),
    ])

    if (citasRes.status === 'fulfilled') {
      const citas    = citasRes.value.data
      allCitas.value = citas
      const today    = new Date().toISOString().slice(0, 10)
      stats.value.citasHoy = citas.filter(c =>
        (c.fecha_hora ?? c.fecha ?? '').startsWith(today)
      ).length

      const citasPorMes = Array(12).fill(0)
      for (const c of citas) {
        const fh = c.fecha_hora ?? c.fecha
        if (!fh) continue
        const m = parseInt(fh.slice(5, 7)) - 1
        if (m >= 0 && m < 12) citasPorMes[m]++
      }
      chartData.value = {
        ...chartData.value,
        datasets: [
          { ...chartData.value.datasets[0], data: citasPorMes },
          { ...chartData.value.datasets[1] },
        ]
      }
    }

    if (pacientesRes.status === 'fulfilled') {
      const pacientes = pacientesRes.value.data
      allPacientes.value          = pacientes
      stats.value.pacientesNuevos = pacientes.length

      const pacientesPorMes = Array(12).fill(0)
      for (const p of pacientes) {
        const ca = p.created_at
        if (!ca) continue
        const m = parseInt(ca.slice(5, 7)) - 1
        if (m >= 0 && m < 12) pacientesPorMes[m]++
      }
      chartData.value = {
        ...chartData.value,
        datasets: [
          { ...chartData.value.datasets[0] },
          { ...chartData.value.datasets[1], data: pacientesPorMes },
        ]
      }
    }

    if (inventarioRes.status === 'fulfilled') {
      const items  = inventarioRes.value.data
      const bajos  = items.filter(i => i.cantidad !== undefined && i.cantidad <= 5)
      alertasInventario.value = bajos.slice(0, 4).map(i => ({
        id: i.inventario_id || i.id,
        nombre: i.nombre || i.descripcion || 'Item',
        cantidad: i.cantidad,
      }))
      stats.value.alertas = bajos.length
    }
  } catch (_) {}
})
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

.page-title { color: #ffffff; font-size: 1.4rem; font-weight: 700; margin-bottom: 1.25rem; }

/* Stats grid */
.stats-grid { display: grid; grid-template-columns: 1fr 1fr 1.5fr; gap: 0.9rem; margin-bottom: 1rem; }

.stat-card { background: #111111; border: 1px solid #1c1c1c; border-radius: 10px; padding: 1.1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; }
.stat-info { display: flex; flex-direction: column; gap: 0.5rem; }
.stat-label { color: #6b7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.6px; font-weight: 600; }
.stat-value { color: #ffffff; font-size: 2.4rem; font-weight: 700; line-height: 1; }
.stat-icon { width: 44px; height: 44px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-icon.green { background: rgba(74,222,128,0.12); color: #4ade80; }
.stat-icon.gray  { background: rgba(107,114,128,0.12); color: #9ca3af; }

.alert-card { flex-direction: column; align-items: stretch; gap: 0.6rem; }
.alert-top  { display: flex; flex-direction: column; gap: 0.35rem; }
.alert-counts { display: flex; gap: 1.25rem; color: #6b7280; font-size: 0.8rem; }
.alert-counts strong { color: #ffffff; }
.alert-list { display: flex; flex-direction: column; gap: 0.35rem; }
.alert-row { display: flex; justify-content: space-between; align-items: center; background: #0d0d0d; border: 1px solid #1c1c1c; border-radius: 7px; padding: 0.45rem 0.7rem; }
.alert-row-info { display: flex; flex-direction: column; gap: 1px; }
.alert-row-title { color: #6b7280; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.4px; }
.alert-row-sub   { color: #d1d5db; font-size: 0.78rem; font-weight: 500; }
.alert-row-count { color: #ffffff; font-weight: 700; font-size: 1rem; }
.alert-empty { color: #4b5563; font-size: 0.8rem; text-align: center; padding: 0.6rem; }

/* Chart */
.chart-card { background: #111111; border: 1px solid #1c1c1c; border-radius: 10px; padding: 1.1rem 1.25rem; margin-bottom: 1rem; }
.chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
.chart-title  { color: #ffffff; font-size: 0.95rem; font-weight: 600; }
.chart-legend { display: flex; gap: 1rem; font-size: 0.78rem; color: #6b7280; }
.legend-item  { display: flex; align-items: center; gap: 0.35rem; }
.dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.dot.green { background: #4ade80; }
.dot.gray  { background: #6b7280; }
.chart-wrapper { height: 260px; }

/* ── Calendario ──────────────────────────────────────────────────────────────── */
.cal-card { background: #111111; border: 1px solid #1c1c1c; border-radius: 10px; overflow: hidden; margin-top: 1rem; }

.cal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid #1c1c1c; }
.cal-title  { color: #ffffff; font-size: 0.95rem; font-weight: 600; margin: 0 0 0.15rem; }
.cal-sub    { color: #6b7280; font-size: 0.78rem; margin: 0; }

.cal-nav { display: flex; gap: 0.5rem; }
.nav-btn { background: #1c1c1c; border: 1px solid #2a2a2a; color: #9ca3af; padding: 0.35rem 0.8rem; border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer; transition: background 0.15s, color 0.15s; }
.nav-btn:hover { background: #2a2a2a; color: #d1d5db; }
.nav-btn.today-btn.active { background: rgba(74,222,128,0.12); color: #4ade80; border-color: rgba(74,222,128,0.3); }

.cal-scroll { overflow-x: auto; }

.cal-table { width: 100%; border-collapse: collapse; min-width: 700px; font-size: 0.82rem; }
.cal-table thead tr { border-bottom: 1px solid #1c1c1c; }
.cal-table thead th { padding: 0.6rem 0.4rem; color: #9ca3af; font-size: 0.75rem; font-weight: 600; text-align: center; border-right: 1px solid #161616; }
.cal-table thead th:last-child { border-right: none; }
.time-col { width: 52px; }
.day-col  { min-width: 110px; }
.today-col { color: #4ade80 !important; background: rgba(74,222,128,0.04); }
.today-dot { margin-left: 3px; font-size: 7px; vertical-align: middle; }

.cal-table tbody tr { border-bottom: 1px solid #0f0f0f; }
.cal-table tbody tr:hover td { background: rgba(255,255,255,0.015); }

.time-cell { padding: 3px 6px; color: #4b5563; font-size: 0.7rem; text-align: center; border-right: 1px solid #1c1c1c; white-space: nowrap; vertical-align: top; }
.slot-cell { padding: 2px 3px; border-right: 1px solid #0f0f0f; vertical-align: top; min-height: 26px; }
.slot-cell:last-child { border-right: none; }
.today-slot { background: rgba(74,222,128,0.02); }

.appt { display: flex; flex-direction: column; padding: 3px 6px; border-radius: 4px; margin-bottom: 2px; border-left: 3px solid; cursor: pointer; transition: opacity 0.15s; }
.appt:hover { opacity: 0.8; }
.appt-blue   { background: #1e3a8a; border-color: #3b82f6; }
.appt-green  { background: #14532d; border-color: #22c55e; }
.appt-red    { background: #7f1d1d; border-color: #dc2626; }
.appt-yellow { background: #78350f; border-color: #f59e0b; }

.appt-name { font-size: 0.7rem; font-weight: 600; color: #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px; }
.appt-info { font-size: 0.62rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.cal-empty { text-align: center; padding: 2.5rem 1rem; color: #4b5563; }
.cal-empty-icon { font-size: 2rem; display: block; margin-bottom: 0.5rem; }
.cal-empty p { margin: 0; font-size: 0.875rem; }

.cal-legend-bar { display: flex; gap: 1.2rem; flex-wrap: wrap; padding: 0.75rem 1.25rem; border-top: 1px solid #1c1c1c; }
.cal-li { display: flex; align-items: center; gap: 0.4rem; font-size: 0.75rem; color: #6b7280; }
.legend-dot { display: inline-block; width: 12px; height: 12px; border-radius: 2px; border-left: 3px solid; flex-shrink: 0; }
</style>
