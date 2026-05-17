<!-- src/views/sesiones/Index.vue -->
<template>
  <AppLayout>
    <div class="page-header">
      <h1>Sesiones de terapia</h1>
      <RouterLink to="/sesiones/nueva" class="btn-primary">+ Nueva sesión</RouterLink>
    </div>
    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr><th>Fecha</th><th>Paciente</th><th>Fisioterapeuta</th><th>Tipo terapia</th><th>Duración</th><th>Evolución</th><th>Acciones</th></tr>
        </thead>
        <tbody>
          <tr v-for="s in sesiones" :key="s.id_sesion_terapia">
            <td>{{ s.fecha }}</td>
            <td>{{ s.paciente?.nombre }} {{ s.paciente?.apellido }}</td>
            <td>{{ s.fisioterapeuta?.nombre }} {{ s.fisioterapeuta?.apellido }}</td>
            <td>{{ s.tipo_terapia }}</td>
            <td>{{ s.duracion_minutos }} min</td>
            <td><span :class="`evolucion-${s.evolucion}`">{{ s.evolucion }}</span></td>
            <td class="actions">
              <RouterLink :to="`/sesiones/${s.id_sesion_terapia}/editar`" class="btn-edit">Editar</RouterLink>
              <button @click="eliminar(s.id_sesion_terapia)" class="btn-delete">Eliminar</button>
            </td>
          </tr>
          <tr v-if="sesiones.length === 0">
            <td colspan="7" class="empty">No hay sesiones registradas.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import { sesionService } from '@/services/api'

const sesiones = ref([])
onMounted(async () => { const { data } = await sesionService.getAll(); sesiones.value = data })
async function eliminar(id) {
  if (!confirm('¿Eliminar esta sesión?')) return
  await sesionService.delete(id)
  sesiones.value = sesiones.value.filter(s => s.id_sesion_terapia !== id)
}
</script>

<style scoped>
@import '@/assets/table.css';
.evolucion-mejora   { background: #dcfce7; color: #16a34a; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.8rem; }
.evolucion-estable  { background: #dbeafe; color: #1d4ed8; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.8rem; }
.evolucion-retroceso{ background: #fee2e2; color: #dc2626; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.8rem; }
</style>
