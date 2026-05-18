<template>
  <AppLayout>
    <h2 class="page-title">Dashboard</h2>

    <!-- Stat cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Citas de Hoy</span>
          <span class="stat-value">{{ stats.citasHoy }}</span>
        </div>
        <div class="stat-icon green">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <span class="stat-label">Pacientes Nuevos (Mes)</span>
          <span class="stat-value">{{ stats.pacientesNuevos }}</span>
        </div>
        <div class="stat-icon gray">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </div>
      </div>

      <div class="stat-card alert-card">
        <div class="alert-header">
          <span class="stat-label">Alerta de Inventario</span>
          <span class="alert-badge" style="color:#074434">{{ paletteName }}</span>
        </div>
        <div class="alert-counts">
          <span><strong>{{ stats.alertas }}</strong> Alertas</span>
          <span><strong>{{ stats.pacientesNuevos }}</strong> Pacientes</span>
        </div>
        <div class="alert-list">
          <div v-for="item in alertasInventario" :key="item.id" class="alert-row">
            <div>
              <div class="alert-title">Alerta de Inventario</div>
              <div class="alert-sub">{{ item.nombre }}</div>
            </div>
            <span class="alert-count">{{ item.cantidad }}</span>
          </div>
          <div v-if="alertasInventario.length === 0" class="alert-empty">Sin alertas de inventario</div>
        </div>
      </div>
    </div>

    <!-- Chart -->
    <div class="chart-card">
      <h3 class="chart-title">Chartas y grafias</h3>
      <div class="chart-legend">
        <span class="legend-dot green"></span><span>Citas</span>
        <span class="legend-dot gray" style="margin-left:1rem"></span><span>Pacientes</span>
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

const paletteName = '#074434'

const stats = ref({ citasHoy: 0, pacientesNuevos: 0, alertas: 0 })
const alertasInventario = ref([])

const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']

const chartData = ref({
  labels: meses,
  datasets: [
    {
      label: 'Citas',
      data: [20, 35, 45, 30, 55, 60, 75, 90, 70, 65, 80, 95],
      borderColor: '#074434',
      backgroundColor: 'rgba(7,68,52,0.15)',
      tension: 0.4,
      fill: true,
      pointBackgroundColor: '#074434',
      pointRadius: 4,
    },
    {
      label: 'Pacientes',
      data: [10, 20, 25, 40, 35, 50, 45, 60, 55, 70, 65, 75],
      borderColor: '#A9AFB2',
      backgroundColor: 'rgba(169,175,178,0.1)',
      tension: 0.4,
      fill: true,
      pointBackgroundColor: '#A9AFB2',
      pointRadius: 4,
    }
  ]
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#111111',
      titleColor: '#FFFFFF',
      bodyColor: '#A9AFB2',
      borderColor: '#074434',
      borderWidth: 1,
    }
  },
  scales: {
    x: {
      grid: { color: '#1a1a1a' },
      ticks: { color: '#A9AFB2', font: { size: 11 } }
    },
    y: {
      grid: { color: '#1a1a1a' },
      ticks: { color: '#A9AFB2', font: { size: 11 } },
      beginAtZero: true
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
.page-title {
  color: #FFFFFF;
  font-size: 1.6rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
}

/* Stats grid */
.stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1.4fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: #111111;
  border: 1px solid #1f1f1f;
  border-radius: 10px;
  padding: 1.25rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-info {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.stat-label {
  color: #A9AFB2;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  color: #FFFFFF;
  font-size: 2.2rem;
  font-weight: 700;
  line-height: 1;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon.green { background: rgba(7,68,52,0.3); color: #074434; }
.stat-icon.gray  { background: rgba(169,175,178,0.15); color: #A9AFB2; }

/* Alert card */
.alert-card {
  flex-direction: column;
  align-items: flex-start;
  gap: 0.6rem;
}

.alert-header {
  display: flex;
  justify-content: space-between;
  width: 100%;
  align-items: center;
}

.alert-badge {
  font-size: 0.7rem;
  font-weight: 700;
  background: rgba(7,68,52,0.2);
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
}

.alert-counts {
  display: flex;
  gap: 1.5rem;
  color: #A9AFB2;
  font-size: 0.82rem;
}

.alert-counts strong { color: #FFFFFF; }

.alert-list {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.alert-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #0a0a0a;
  border: 1px solid #1f1f1f;
  border-radius: 6px;
  padding: 0.5rem 0.75rem;
}

.alert-title { color: #A9AFB2; font-size: 0.75rem; text-transform: uppercase; }
.alert-sub   { color: #FFFFFF; font-size: 0.82rem; }
.alert-count { color: #FFFFFF; font-weight: 700; font-size: 1rem; }
.alert-empty { color: #A9AFB2; font-size: 0.82rem; text-align: center; padding: 0.5rem; }

/* Chart */
.chart-card {
  background: #111111;
  border: 1px solid #1f1f1f;
  border-radius: 10px;
  padding: 1.25rem 1.5rem;
}

.chart-title {
  color: #FFFFFF;
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.chart-legend {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-bottom: 1rem;
  font-size: 0.82rem;
  color: #A9AFB2;
}

.legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
}

.legend-dot.green { background: #074434; }
.legend-dot.gray  { background: #A9AFB2; }

.chart-wrapper {
  height: 280px;
}
</style>
