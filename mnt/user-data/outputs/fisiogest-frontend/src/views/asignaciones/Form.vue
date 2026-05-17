<!-- src/views/asignaciones/Form.vue -->
<template>
  <AppLayout>
    <div class="page-header">
      <h1>{{ isEdit ? 'Editar asignación' : 'Nueva asignación de equipo' }}</h1>
      <RouterLink to="/asignaciones" class="btn-secondary">← Volver</RouterLink>
    </div>
    <div class="form-card">
      <form @submit.prevent="guardar">
        <div class="form-grid">
          <div class="form-group">
            <label>Paciente *</label>
            <select v-model="form.paciente_id" required>
              <option value="">Seleccionar</option>
              <option v-for="p in pacientes" :key="p.paciente_id" :value="p.paciente_id">
                {{ p.nombre }} {{ p.apellido }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Equipo *</label>
            <select v-model="form.inventario_id" required>
              <option value="">Seleccionar</option>
              <option v-for="i in inventario" :key="i.id_inventario" :value="i.id_inventario">
                {{ i.nombre }} ({{ i.tipo }})
              </option>
            </select>
          </div>
          <div class="form-group"><label>Fecha de asignación *</label><input v-model="form.fecha_asignacion" type="date" required /></div>
          <div class="form-group"><label>Fecha de devolución</label><input v-model="form.fecha_devolucion" type="date" /></div>
          <div class="form-group">
            <label>Estado</label>
            <select v-model="form.estado">
              <option value="activo">Activo</option>
              <option value="devuelto">Devuelto</option>
            </select>
          </div>
          <div class="form-group full-width"><label>Notas</label><textarea v-model="form.notas" rows="3" /></div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="loading">{{ loading ? 'Guardando...' : 'Guardar asignación' }}</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '@/components/AppLayout.vue'
import { asignacionService, pacienteService, inventarioService } from '@/services/api'

const route = useRoute(); const router = useRouter()
const isEdit = computed(() => !!route.params.id)
const form = ref({ paciente_id:'', inventario_id:'', fecha_asignacion:'', fecha_devolucion:'', estado:'activo', notas:'' })
const pacientes = ref([]); const inventario = ref([]); const loading = ref(false)

onMounted(async () => {
  const [p, i] = await Promise.all([pacienteService.getAll(), inventarioService.getAll()])
  pacientes.value = p.data; inventario.value = i.data
  if (isEdit.value) { const { data } = await asignacionService.getById(route.params.id); Object.assign(form.value, data) }
})
async function guardar() {
  loading.value = true
  try {
    if (isEdit.value) await asignacionService.update(route.params.id, form.value)
    else await asignacionService.create(form.value)
    router.push('/asignaciones')
  } finally { loading.value = false }
}
</script>
<style scoped>@import '@/assets/form.css';</style>
