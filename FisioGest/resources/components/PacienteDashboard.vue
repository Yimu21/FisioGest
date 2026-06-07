<template>
  <PacienteLayout>
    <div class="page">
      <div class="page-header">
        <div>
          <h1 class="page-title">Dashboard</h1>
          <p class="page-subtitle">Bienvenido, {{ nombrePaciente }}</p>
        </div>
      </div>

      <!-- Estado de carga -->
      <div v-if="cargando" class="loading-state">
        <div class="spinner"></div>
        <span>Cargando tu información…</span>
      </div>

      <template v-else>
        <!-- ── Próxima cita ── -->
        <section class="section">
          <h2 class="section-title">Próxima Cita</h2>
          <div v-if="proximaCita" class="proxima-cita-card">
            <div class="cita-icon-col">
              <div class="cita-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2">
                  <rect x="3" y="4" width="18" height="18" rx="2"/>
                  <line x1="16" y1="2" x2="16" y2="6"/>
                  <line x1="8" y1="2" x2="8" y2="6"/>
                  <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
              </div>
            </div>
            <div class="cita-detail">
              <div class="cita-fecha">{{ formatFecha(proximaCita.fecha_hora) }}</div>
              <div class="cita-hora">{{ formatHora(proximaCita.fecha_hora) }}</div>
              <div class="cita-fisio" v-if="proximaCita.fisioterapeuta_nombre">
                Con <strong>{{ proximaCita.fisioterapeuta_nombre }}</strong>
                <span v-if="proximaCita.especialidad" class="cita-esp"> · {{ proximaCita.especialidad }}</span>
              </div>
              <div v-if="proximaCita.motivo" class="cita-motivo">{{ proximaCita.motivo }}</div>
            </div>
            <div class="cita-badge programada">Programada</div>
          </div>
          <div v-else class="empty-state">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="1.5">
              <rect x="3" y="4" width="18" height="18" rx="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <p>No tienes citas programadas</p>
            <router-link to="/paciente/agendar" class="btn-primary-sm">Agendar cita</router-link>
          </div>
        </section>

        <!-- ── Historial de citas ── -->
        <section class="section">
          <h2 class="section-title">Historial de Citas</h2>
          <div v-if="historial.length === 0" class="empty-state">
            <p>Sin historial de citas aún.</p>
          </div>
          <div v-else class="table-wrapper">
            <table class="table">
              <thead>
                <tr>
                  <th>Fecha y Hora</th>
                  <th>Fisioterapeuta</th>
                  <th>Motivo</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="cita in historial" :key="cita.cita_id">
                  <td>{{ formatFechaCorta(cita.fecha_hora) }}</td>
                  <td>{{ cita.fisioterapeuta_nombre || '—' }}</td>
                  <td class="motivo-col">{{ cita.motivo || '—' }}</td>
                  <td><span class="badge" :class="cita.estado">{{ labelEstado(cita.estado) }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- ── Notificaciones recientes ── -->
        <section class="section">
          <h2 class="section-title">Notificaciones Recientes</h2>
          <div v-if="notificaciones.length === 0" class="empty-state">
            <p>Sin notificaciones.</p>
          </div>
          <div v-else class="notif-list-inline">
            <div
              v-for="n in notificaciones.slice(0, 5)"
              :key="n.notificacion_id"
              class="notif-row"
              :class="{ unread: !n.leida }"
            >
              <div class="notif-dot-col">
                <span v-if="!n.leida" class="dot"></span>
              </div>
              <div class="notif-content">
                <p class="notif-title-text">{{ n.titulo }}</p>
                <p class="notif-msg-text">{{ n.mensaje }}</p>
              </div>
              <span class="notif-time">{{ formatNotifTime(n.created_at) }}</span>
            </div>
          </div>
        </section>
      </template>
    </div>
  </PacienteLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import PacienteLayout from './PacienteLayout.vue'
import { getUser, pacienteService, pacienteNotificacionesService } from '@/services/api'

const citas         = ref([])
const notificaciones = ref([])
const cargando      = ref(true)

const currentUser    = computed(() => getUser())
const nombrePaciente = computed(() => currentUser.value?.nombre?.split(' ')[0] ?? 'Paciente')

const proximaCita = computed(() => {
  const ahora = new Date()
  return citas.value
    .filter(c => c.estado === 'programada' && new Date(c.fecha_hora) >= ahora)
    .sort((a, b) => new Date(a.fecha_hora) - new Date(b.fecha_hora))[0] ?? null
})

const historial = computed(() =>
  citas.value
    .filter(c => c.estado !== 'programada' || new Date(c.fecha_hora) < new Date())
    .sort((a, b) => new Date(b.fecha_hora) - new Date(a.fecha_hora))
    .slice(0, 10)
)

onMounted(async () => {
  try {
    const [resCitas, resNotif] = await Promise.all([
      pacienteService.getMisCitas(),
      pacienteNotificacionesService.getAll(),
    ])
    citas.value         = resCitas.data
    notificaciones.value = resNotif.data
  } catch {}
  cargando.value = false
})

function formatFecha(ts) {
  if (!ts) return ''
  const iso = ts.includes('T') ? ts : ts.replace(' ', 'T')
  return new Date(iso).toLocaleDateString('es', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}

function formatHora(ts) {
  if (!ts) return ''
  const iso = ts.includes('T') ? ts : ts.replace(' ', 'T')
  return new Date(iso).toLocaleTimeString('es', { hour: '2-digit', minute: '2-digit' })
}

function formatFechaCorta(ts) {
  if (!ts) return ''
  const iso = ts.includes('T') ? ts : ts.replace(' ', 'T')
  return new Date(iso).toLocaleString('es', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function formatNotifTime(ts) {
  if (!ts) return ''
  const iso = ts.includes('T') ? ts : ts.replace(' ', 'T')
  return new Date(iso).toLocaleString('es', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}

function labelEstado(e) {
  return { programada: 'Programada', atendida: 'Atendida', cancelada: 'Cancelada', reprogramada: 'Reprogramada' }[e] ?? e
}
</script>

<style scoped>
.page { display: flex; flex-direction: column; gap: 1.75rem; }

.page-header { display: flex; align-items: flex-start; justify-content: space-between; }
.page-title  { font-size: 1.4rem; font-weight: 700; color: #f4f4f5; }
.page-subtitle { font-size: 0.85rem; color: #6b7280; margin-top: 2px; }

.section { display: flex; flex-direction: column; gap: 0.85rem; }
.section-title { font-size: 0.95rem; font-weight: 600; color: #d1d5db; }

/* Próxima cita */
.proxima-cita-card {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  display: flex;
  align-items: flex-start;
  gap: 1.25rem;
}

.cita-icon-col { flex-shrink: 0; }
.cita-icon {
  width: 48px; height: 48px;
  background: rgba(7,68,52,0.3);
  border: 1px solid rgba(74,222,128,0.15);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
}

.cita-detail { flex: 1; }
.cita-fecha { font-size: 1rem; font-weight: 600; color: #f4f4f5; text-transform: capitalize; }
.cita-hora  { font-size: 1.4rem; font-weight: 700; color: #4ade80; line-height: 1.2; }
.cita-fisio { font-size: 0.82rem; color: #9ca3af; margin-top: 0.4rem; }
.cita-fisio strong { color: #d1d5db; }
.cita-esp   { color: #6b7280; }
.cita-motivo { font-size: 0.78rem; color: #6b7280; margin-top: 2px; font-style: italic; }

.cita-badge {
  flex-shrink: 0;
  font-size: 0.72rem; font-weight: 600;
  padding: 0.3rem 0.8rem;
  border-radius: 99px;
  align-self: flex-start;
}

/* Estado vacío */
.empty-state {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  color: #4b5563;
  font-size: 0.85rem;
  display: flex; flex-direction: column; align-items: center; gap: 0.75rem;
}

.btn-primary-sm {
  background: rgba(7,68,52,0.4);
  border: 1px solid rgba(74,222,128,0.25);
  color: #4ade80;
  font-size: 0.8rem;
  padding: 0.45rem 1rem;
  border-radius: 7px;
  text-decoration: none;
  transition: background 0.15s;
}
.btn-primary-sm:hover { background: rgba(7,68,52,0.6); }

/* Tabla */
.table-wrapper {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 12px;
  overflow: hidden;
}

.table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.table th {
  background: #0d0d0d;
  color: #6b7280;
  font-weight: 600;
  text-align: left;
  padding: 0.7rem 1rem;
  border-bottom: 1px solid #1c1c1c;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}
.table td { padding: 0.75rem 1rem; color: #d1d5db; border-bottom: 1px solid #141414; }
.table tr:last-child td { border-bottom: none; }
.table tr:hover td { background: rgba(255,255,255,0.02); }
.motivo-col { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Badge estado */
.badge { font-size: 0.7rem; font-weight: 600; padding: 0.25rem 0.7rem; border-radius: 99px; }
.badge.programada, .cita-badge.programada { background: rgba(59,130,246,0.15); color: #60a5fa; }
.badge.atendida   { background: rgba(74,222,128,0.12); color: #4ade80; }
.badge.cancelada  { background: rgba(239,68,68,0.12);  color: #f87171; }
.badge.reprogramada { background: rgba(251,191,36,0.12); color: #fbbf24; }

/* Notificaciones inline */
.notif-list-inline {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 12px;
  overflow: hidden;
}

.notif-row {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid #141414;
}
.notif-row:last-child { border-bottom: none; }
.notif-row.unread { background: rgba(74,222,128,0.03); }

.notif-dot-col { width: 16px; padding-top: 4px; flex-shrink: 0; display: flex; align-items: flex-start; }
.dot { width: 7px; height: 7px; border-radius: 50%; background: #4ade80; }

.notif-content { flex: 1; min-width: 0; }
.notif-title-text { font-size: 0.8rem; font-weight: 600; color: #e4e4e7; margin-bottom: 2px; }
.notif-msg-text   { font-size: 0.75rem; color: #9ca3af; line-height: 1.4; }
.notif-time       { font-size: 0.68rem; color: #4b5563; white-space: nowrap; flex-shrink: 0; }

/* Carga */
.loading-state {
  display: flex; align-items: center; gap: 0.75rem;
  color: #6b7280; font-size: 0.85rem; padding: 2rem;
}
.spinner {
  width: 18px; height: 18px;
  border: 2px solid #1c1c1c;
  border-top-color: #4ade80;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
