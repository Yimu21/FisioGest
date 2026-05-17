<!-- src/views/inventario/Form.vue -->
<template>
  <AppLayout>
    <div class="page-header">
      <h1>{{ isEdit ? 'Editar equipo' : 'Nuevo equipo' }}</h1>
      <RouterLink to="/inventario" class="btn-secondary">← Volver</RouterLink>
    </div>
    <div class="form-card">
      <form @submit.prevent="guardar">
        <div class="form-grid">
          <div class="form-group full-width"><label>Nombre del equipo *</label><input v-model="form.nombre" required /></div>
          <div class="form-group">
            <label>Tipo *</label>
            <select v-model="form.tipo" required>
              <option value="">Seleccionar</option>
              <option value="protesis">Prótesis</option>
              <option value="ortesis">Órtesis</option>
              <option value="maquina">Máquina</option>
              <option value="otro">Otro</option>
            </select>
          </div>
          <div class="form-group">
            <label>Estado</label>
            <select v-model="form.estado">
              <option value="disponible">Disponible</option>
              <option value="en_uso">En uso</option>
              <option value="mantenimiento">Mantenimiento</option>
              <option value="baja">Baja</option>
            </select>
          </div>
          <div class="form-group"><label>Marca</label><input v-model="form.marca" /></div>
          <div class="form-group"><label>Modelo</label><input v-model="form.modelo" /></div>
          <div class="form-group"><label>Cantidad</label><input v-model.number="form.cantidad" type="number" min="0" /></div>
          <div class="form-group full-width"><label>Descripción</label><textarea v-model="form.descripcion" rows="3" /></div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="loading">{{ loading ? 'Guardando...' : 'Guardar' }}</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '@/components/AppLayout.vue'
import { inventarioService } from '@/services/api'

const route = useRoute(); const router = useRouter()
const isEdit = computed(() => !!route.params.id)
const form = ref({ nombre:'', tipo:'', marca:'', modelo:'', estado:'disponible', cantidad:1, descripcion:'' })
const loading = ref(false)

onMounted(async () => {
  if (isEdit.value) { const { data } = await inventarioService.getById(route.params.id); Object.assign(form.value, data) }
})
async function guardar() {
  loading.value = true
  try {
    if (isEdit.value) await inventarioService.update(route.params.id, form.value)
    else await inventarioService.create(form.value)
    router.push('/inventario')
  } finally { loading.value = false }
}
</script>
<style scoped>@import '@/assets/form.css';</style>
