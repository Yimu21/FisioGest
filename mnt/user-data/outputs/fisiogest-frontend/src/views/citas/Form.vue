<!-- src/views/citas/Form.vue -->
<template>
  <AppLayout>
    <div class="page-header">
      <h1>{{ isEdit ? 'Editar cita' : 'Nueva cita' }}</h1>
      <RouterLink to="/citas" class="btn-secondary">← Volver</RouterLink>
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
            <label>Fisioterapeuta *</label>
            <select v-model="form.fisioterapeuta_id" required>
              <option value="">Seleccionar</option>
              <option v-for="f in fisios" :key="f.fisioterapeuta_id" :value="f.fisioterapeuta_id">
                {{ f.nombre }} {{ f.apellido }}
              </option>
            </select>
          </div>
          <div class="form-group"><label>Fecha y hora *</label><input v-model="form.fecha_hora" type="datetime-local" required /></div>
          <div class="form-group">
            <label>Estado</label>
            <select v-model="form.estado">
              <option value="programada">Programada</option>
              <option value="atendida">Atendida</option>
              <option value="cancelada">Cancelada</option>
              <option value="reprogramada">Reprogramada</option>
            </select>
          </div>
          <div class="form-group full-width"><label>Motivo</label><input v-model="form.motivo" placeholder="Motivo de la consulta" /></div>
          <div class="form-group full-width"><label>Observaciones</label><textarea v-model="form.observaciones" rows="3" /></div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="loading">{{ loading ? 'Guardando...' : 'Guardar cita' }}</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '@/components/AppLayout.vue'
import { citaService, pacienteService, fisioterapeutaService } from '@/services/api'

const route = useRoute(); const router = useRouter()
const isEdit = computed(() => !!route.params.id)
const form = ref({ paciente_id:'', fisioterapeuta_id:'', fecha_hora:'', motivo:'', estado:'programada', observaciones:'' })
const pacientes = ref([]); const fisios = ref([]); const loading = ref(false)

onMounted(async () => {
  const [p, f] = await Promise.all([pacienteService.getAll(), fisioterapeutaService.getAll()])
  pacientes.value = p.data; fisios.value = f.data
  if (isEdit.value) { const { data } = await citaService.getById(route.params.id); Object.assign(form.value, data) }
})
async function guardar() {
  loading.value = true
  try {
    if (isEdit.value) await citaService.update(route.params.id, form.value)
    else await citaService.create(form.value)
    router.push('/citas')
  } finally { loading.value = false }
}
</script>
<style scoped>@import '@/assets/form.css';</style>
