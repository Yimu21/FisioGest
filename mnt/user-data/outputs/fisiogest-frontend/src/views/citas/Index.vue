<!-- src/views/citas/Index.vue -->
<template>
  <AppLayout>
    <div class="page-header">
      <h1>Citas</h1>
      <RouterLink to="/citas/nueva" class="btn-primary">+ Nueva cita</RouterLink>
    </div>
    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr><th>Fecha y hora</th><th>Paciente</th><th>Fisioterapeuta</th><th>Motivo</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
          <tr v-for="c in citas" :key="c.cita_id">
            <td>{{ formatFecha(c.fecha_hora) }}</td>
            <td>{{ c.paciente?.nombre }} {{ c.paciente?.apellido }}</td>
            <td>{{ c.fisioterapeuta?.nombre }} {{ c.fisioterapeuta?.apellido }}</td>
            <td>{{ c.motivo || '—' }}</td>
            <td><span :class="`estado-${c.estado}`">{{ c.estado }}</span></td>
            <td class="actions">
              <RouterLink :to="`/citas/${c.cita_id}/editar`" class="btn-edit">Editar</RouterLink>
              <button @click="eliminar(c.cita_id)" class="btn-delete">Eliminar</button>
            </td>
          </tr>
          <tr v-if="citas.length === 0">
            <td colspan="6" class="empty">No hay citas registradas.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import { citaService } from '@/services/api'

const citas = ref([])
onMounted(async () => { const { data } = await citaService.getAll(); citas.value = data })
async function eliminar(id) {
  if (!confirm('¿Eliminar esta cita?')) return
  await citaService.delete(id)
  citas.value = citas.value.filter(c => c.cita_id !== id)
}
function formatFecha(f) {
  return new Date(f).toLocaleString('es-SV', { dateStyle: 'medium', timeStyle: 'short' })
}
</script>

<style scoped>
@import '@/assets/table.css';
.estado-programada   { background: #dbeafe; color: #1d4ed8; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.8rem; }
.estado-atendida     { background: #dcfce7; color: #16a34a; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.8rem; }
.estado-cancelada    { background: #fee2e2; color: #dc2626; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.8rem; }
.estado-reprogramada { background: #fef9c3; color: #a16207; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.8rem; }
</style>
