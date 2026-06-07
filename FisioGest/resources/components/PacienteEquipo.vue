<template>
  <PacienteLayout>
    <div class="page">
      <div class="page-header">
        <h1 class="page-title">Equipo Asignado</h1>
        <p class="page-subtitle">Equipos de fisioterapia asignados a tu tratamiento</p>
      </div>

      <div v-if="cargando" class="loading-state">
        <div class="spinner"></div>
        <span>Cargando equipo…</span>
      </div>

      <div v-else-if="equipos.length === 0" class="empty-card">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="1.5">
          <rect x="2" y="7" width="20" height="14" rx="2"/>
          <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
          <line x1="12" y1="12" x2="12" y2="16"/>
          <line x1="10" y1="14" x2="14" y2="14"/>
        </svg>
        <p>No tienes equipo asignado actualmente.</p>
        <span class="hint">Contacta a tu fisioterapeuta para más información.</span>
      </div>

      <div v-else class="equipos-list">
        <div v-for="e in equipos" :key="e.id_asignaciones" class="equipo-card">
          <div class="equipo-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="1.8">
              <rect x="2" y="7" width="20" height="14" rx="2"/>
              <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
            </svg>
          </div>
          <div class="equipo-info">
            <span class="equipo-nombre">{{ e.nombre }}</span>
            <span class="equipo-tipo">{{ e.tipo }}</span>
            <span v-if="e.notas" class="equipo-notas">{{ e.notas }}</span>
          </div>
          <div class="equipo-meta">
            <span class="estado-badge">Activo</span>
            <span class="equipo-fecha">Desde {{ formatFecha(e.fecha_asignacion) }}</span>
          </div>
        </div>
      </div>
    </div>
  </PacienteLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import PacienteLayout from './PacienteLayout.vue'
import { pacienteService } from '@/services/api'

const equipos  = ref([])
const cargando = ref(true)

onMounted(async () => {
  try {
    const res = await pacienteService.getMiEquipo()
    equipos.value = res.data
  } catch {}
  cargando.value = false
})

function formatFecha(f) {
  if (!f) return '—'
  return new Date(f + 'T12:00:00').toLocaleDateString('es', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<style scoped>
.page { display: flex; flex-direction: column; gap: 1.5rem; }
.page-header { display: flex; flex-direction: column; gap: 2px; }
.page-title  { font-size: 1.4rem; font-weight: 700; color: #f4f4f5; }
.page-subtitle { font-size: 0.85rem; color: #6b7280; }

.loading-state {
  display: flex; align-items: center; gap: 0.75rem;
  color: #6b7280; font-size: 0.85rem; padding: 2rem;
}
.spinner {
  width: 18px; height: 18px;
  border: 2px solid #1c1c1c; border-top-color: #4ade80;
  border-radius: 50%; animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.empty-card {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 12px;
  padding: 3rem 2rem;
  text-align: center;
  color: #4b5563;
  display: flex; flex-direction: column; align-items: center; gap: 0.6rem;
}
.empty-card p { font-size: 0.9rem; color: #6b7280; }
.hint { font-size: 0.78rem; color: #374151; }

.equipos-list { display: flex; flex-direction: column; gap: 0.75rem; }

.equipo-card {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  transition: border-color 0.15s;
}
.equipo-card:hover { border-color: #2a2a2a; }

.equipo-icon {
  width: 44px; height: 44px;
  background: rgba(7,68,52,0.25);
  border: 1px solid rgba(74,222,128,0.15);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

.equipo-info {
  display: flex; flex-direction: column; gap: 3px; flex: 1;
}
.equipo-nombre { font-size: 0.95rem; font-weight: 600; color: #e4e4e7; }
.equipo-tipo   { font-size: 0.78rem; color: #6b7280; text-transform: capitalize; }
.equipo-notas  { font-size: 0.75rem; color: #4b5563; margin-top: 2px; font-style: italic; }

.equipo-meta {
  display: flex; flex-direction: column; align-items: flex-end; gap: 4px;
  flex-shrink: 0;
}

.estado-badge {
  font-size: 0.7rem; font-weight: 600;
  background: rgba(74,222,128,0.1);
  border: 1px solid rgba(74,222,128,0.2);
  color: #4ade80;
  padding: 2px 10px; border-radius: 20px;
  text-transform: uppercase; letter-spacing: 0.05em;
}

.equipo-fecha { font-size: 0.72rem; color: #4b5563; }
</style>
