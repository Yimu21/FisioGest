<!-- src/views/asignaciones/Index.vue -->
<template>
  <AppLayout>
    <div class="page-header">
      <h1>Asignaciones de equipo</h1>
      <RouterLink to="/asignaciones/nueva" class="btn-primary">+ Nueva asignación</RouterLink>
    </div>
    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr><th>Paciente</th><th>Equipo</th><th>F. Asignación</th><th>F. Devolución</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
          <tr v-for="a in asignaciones" :key="a.id_asignaciones">
            <td>{{ a.paciente?.nombre }} {{ a.paciente?.apellido }}</td>
            <td>{{ a.equipo?.nombre }}</td>
            <td>{{ a.fecha_asignacion }}</td>
            <td>{{ a.fecha_devolucion || '—' }}</td>
            <td><span :class="`estado-${a.estado}`">{{ a.estado }}</span></td>
            <td class="actions">
              <RouterLink :to="`/asignaciones/${a.id_asignaciones}/editar`" class="btn-edit">Editar</RouterLink>
              <button @click="eliminar(a.id_asignaciones)" class="btn-delete">Eliminar</button>
            </td>
          </tr>
          <tr v-if="asignaciones.length === 0">
            <td colspan="6" class="empty">No hay asignaciones registradas.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import { asignacionService } from '@/services/api'

const asignaciones = ref([])
onMounted(async () => { const { data } = await asignacionService.getAll(); asignaciones.value = data })
async function eliminar(id) {
  if (!confirm('¿Eliminar esta asignación?')) return
  await asignacionService.delete(id)
  asignaciones.value = asignaciones.value.filter(a => a.id_asignaciones !== id)
}
</script>

<style scoped>
@import '@/assets/table.css';
.estado-activo   { background: #dcfce7; color: #16a34a; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.8rem; }
.estado-devuelto { background: #e5e7eb; color: #374151; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.8rem; }
</style>
