<!-- src/views/inventario/Index.vue -->
<template>
  <AppLayout>
    <div class="page-header">
      <h1>Inventario</h1>
      <RouterLink to="/inventario/nuevo" class="btn-primary">+ Nuevo equipo</RouterLink>
    </div>
    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr><th>Nombre</th><th>Tipo</th><th>Marca/Modelo</th><th>Estado</th><th>Cantidad</th><th>Acciones</th></tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id_inventario">
            <td><strong>{{ item.nombre }}</strong></td>
            <td>{{ item.tipo }}</td>
            <td>{{ [item.marca, item.modelo].filter(Boolean).join(' / ') || '—' }}</td>
            <td><span :class="`estado-${item.estado}`">{{ item.estado }}</span></td>
            <td>{{ item.cantidad }}</td>
            <td class="actions">
              <RouterLink :to="`/inventario/${item.id_inventario}/editar`" class="btn-edit">Editar</RouterLink>
              <button @click="eliminar(item.id_inventario)" class="btn-delete">Eliminar</button>
            </td>
          </tr>
          <tr v-if="items.length === 0">
            <td colspan="6" class="empty">No hay equipos registrados.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import { inventarioService } from '@/services/api'

const items = ref([])
onMounted(async () => { const { data } = await inventarioService.getAll(); items.value = data })
async function eliminar(id) {
  if (!confirm('¿Eliminar este equipo?')) return
  await inventarioService.delete(id)
  items.value = items.value.filter(i => i.id_inventario !== id)
}
</script>

<style scoped>
@import '@/assets/table.css';
.estado-disponible   { background: #dcfce7; color: #16a34a; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.8rem; }
.estado-en_uso       { background: #dbeafe; color: #1d4ed8; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.8rem; }
.estado-mantenimiento{ background: #fef9c3; color: #a16207; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.8rem; }
.estado-baja         { background: #fee2e2; color: #dc2626; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.8rem; }
</style>
