<!-- src/views/fisioterapeutas/Index.vue -->
<template>
  <AppLayout>
    <div class="page-header">
      <h1>Fisioterapeutas</h1>
      <RouterLink to="/fisioterapeutas/nuevo" class="btn-primary">+ Nuevo</RouterLink>
    </div>
    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Especialidad</th>
            <th>Teléfono</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="f in fisios" :key="f.fisioterapeuta_id">
            <td><strong>{{ f.nombre }} {{ f.apellido }}</strong></td>
            <td>{{ f.especialidad || '—' }}</td>
            <td>{{ f.telefono || '—' }}</td>
            <td>
              <span :class="f.activo ? 'badge-active' : 'badge-inactive'">
                {{ f.activo ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td class="actions">
              <RouterLink :to="`/fisioterapeutas/${f.fisioterapeuta_id}/editar`" class="btn-edit">Editar</RouterLink>
              <button @click="eliminar(f.fisioterapeuta_id)" class="btn-delete">Eliminar</button>
            </td>
          </tr>
          <tr v-if="fisios.length === 0">
            <td colspan="5" class="empty">No hay fisioterapeutas registrados.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import { fisioterapeutaService } from '@/services/api'

const fisios = ref([])
onMounted(async () => {
  const { data } = await fisioterapeutaService.getAll()
  fisios.value = data
})
async function eliminar(id) {
  if (!confirm('¿Eliminar este fisioterapeuta?')) return
  await fisioterapeutaService.delete(id)
  fisios.value = fisios.value.filter(f => f.fisioterapeuta_id !== id)
}
</script>

<style scoped>
@import '@/assets/table.css';
.badge-active   { background: #dcfce7; color: #16a34a; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.8rem; }
.badge-inactive { background: #fee2e2; color: #dc2626; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.8rem; }
</style>
