<template>
  <AppLayout>

    <h2 class="page-title">Dashboard</h2>

    <!-- ── Stats grid ── -->
    <div class="stats-grid">

      <!-- Citas de hoy -->
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

      <!-- Pacientes nuevos -->
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

      <!-- Alerta de inventario -->
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
        <h3 class="chart-title">Chartas y gratias</h3>
        <div class="chart-legend">
          <span class="legend-item">
            <span class="dot green"></span> Citas
          </span>
          <span class="legend-item">
            <span class="dot gray"></span> Pacientes
          </span>
        </div>
      </div>
      <div class="chart-wrapper">
        <Line :data="chartData" :options="chartOptions" />
      </div>
    </div>

  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS, CategoryScale, LinearScale,
  PointElement, LineElement, Tooltip, Legend, Filler
} from 'chart.js'
import AppLayout from '@/components/AppLayout.vue'
import { citaService, pacienteService, inventarioService } from '@/services/api'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Legend, Filler)

const stats = ref({ citasHoy: 0, pacientesNuevos: 0, alertas: 0 })
const alertasInventario = ref([])

const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']

const chartData = ref({
  labels: meses,
  datasets: [
    {
      label: 'Citas',
      data: [20, 35, 45, 30, 55, 60, 75, 90, 70, 65, 80, 95],
      borderColor: '#4ade80',
      backgroundColor: 'rgba(74,222,128,0.12)',
      tension: 0.4,
      fill: true,
      pointBackgroundColor: '#4ade80',
      pointRadius: 3,
      pointHoverRadius: 5,
    },
    {
      label: 'Pacientes',
      data: [10, 20, 25, 40, 35, 50, 45, 60, 55, 70, 65, 75],
      borderColor: '#6b7280',
      backgroundColor: 'rgba(107,114,128,0.08)',
      tension: 0.4,
      fill: true,
      pointBackgroundColor: '#6b7280',
      pointRadius: 3,
      pointHoverRadius: 5,
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
      backgroundColor: '#1a1a1a',
      titleColor: '#ffffff',
      bodyColor: '#9ca3af',
      borderColor: '#2a2a2a',
      borderWidth: 1,
      padding: 10,
    }
  },
  scales: {
    x: {
      grid: { color: '#1a1a1a' },
      ticks: { color: '#6b7280', font: { size: 11 } },
      border: { color: '#1a1a1a' },
    },
    y: {
      grid: { color: '#1a1a1a' },
      ticks: { color: '#6b7280', font: { size: 11 } },
      border: { color: '#1a1a1a' },
      beginAtZero: true,
    }
  }
}

onMounted(async () => {
  try {
    const [citasRes, pacientesRes, inventarioRes] = await Promise.allSettled([
      citaService.getAll(),
      pacienteService.getAll(),
      inventarioService.getAll(),
    ])

    if (citasRes.status === 'fulfilled') {
      const today = new Date().toISOString().slice(0, 10)
      const citas = citasRes.value.data
      stats.value.citasHoy = citas.filter(c => c.fecha?.startsWith(today)).length || citas.length
    }

    if (pacientesRes.status === 'fulfilled') {
      stats.value.pacientesNuevos = pacientesRes.value.data.length
    }

    if (inventarioRes.status === 'fulfilled') {
      const items = inventarioRes.value.data
      const bajos = items.filter(i => i.cantidad !== undefined && i.cantidad <= 5)
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

.page-title {
  color: #ffffff;
  font-size: 1.4rem;
  font-weight: 700;
  margin-bottom: 1.25rem;
}

/* ── Stats grid ── */
.stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1.5fr;
  gap: 0.9rem;
  margin-bottom: 1rem;
}

.stat-card {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 10px;
  padding: 1.1rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.stat-label {
  color: #6b7280;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  font-weight: 600;
}

.stat-value {
  color: #ffffff;
  font-size: 2.4rem;
  font-weight: 700;
  line-height: 1;
}

.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.stat-icon.green { background: rgba(74,222,128,0.12); color: #4ade80; }
.stat-icon.gray  { background: rgba(107,114,128,0.12); color: #9ca3af; }

/* Alert card */
.alert-card {
  flex-direction: column;
  align-items: stretch;
  gap: 0.6rem;
}

.alert-top {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}


.alert-counts {
  display: flex;
  gap: 1.25rem;
  color: #6b7280;
  font-size: 0.8rem;
}
.alert-counts strong { color: #ffffff; }

.alert-list {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.alert-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #0d0d0d;
  border: 1px solid #1c1c1c;
  border-radius: 7px;
  padding: 0.45rem 0.7rem;
}

.alert-row-info {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.alert-row-title {
  color: #6b7280;
  font-size: 0.68rem;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.alert-row-sub {
  color: #d1d5db;
  font-size: 0.78rem;
  font-weight: 500;
}

.alert-row-count {
  color: #ffffff;
  font-weight: 700;
  font-size: 1rem;
}

.alert-empty {
  color: #4b5563;
  font-size: 0.8rem;
  text-align: center;
  padding: 0.6rem;
}

/* ── Chart ── */
.chart-card {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 10px;
  padding: 1.1rem 1.25rem;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.chart-title {
  color: #ffffff;
  font-size: 0.95rem;
  font-weight: 600;
}

.chart-legend {
  display: flex;
  gap: 1rem;
  font-size: 0.78rem;
  color: #6b7280;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}
.dot.green { background: #4ade80; }
.dot.gray  { background: #6b7280; }

.chart-wrapper {
  height: 260px;
}
</style>
