<!-- src/views/sesiones/Form.vue -->
<template>
  <AppLayout>
    <div class="page-header">
      <h1>{{ isEdit ? 'Editar sesión' : 'Nueva sesión de terapia' }}</h1>
      <RouterLink to="/sesiones" class="btn-secondary">← Volver</RouterLink>
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
          <div class="form-group"><label>Fecha *</label><input v-model="form.fecha" type="date" required /></div>
          <div class="form-group"><label>Duración (minutos)</label><input v-model.number="form.duracion_minutos" type="number" min="1" /></div>
          <div class="form-group full-width"><label>Tipo de terapia *</label><input v-model="form.tipo_terapia" required placeholder="Ej. Electroterapia, Masoterapia..." /></div>
          <div class="form-group">
            <label>Evolución</label>
            <select v-model="form.evolucion">
              <option value="mejora">Mejora</option>
              <option value="estable">Estable</option>
              <option value="retroceso">Retroceso</option>
            </select>
          </div>
          <div class="form-group full-width"><label>Observaciones clínicas</label><textarea v-model="form.observaciones" rows="4" /></div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="loading">{{ loading ? 'Guardando...' : 'Guardar sesión' }}</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '@/components/AppLayout.vue'
import { sesionService, pacienteService, fisioterapeutaService } from '@/services/api'

const route = useRoute(); const router = useRouter()
const isEdit = computed(() => !!route.params.id)
const form = ref({ paciente_id:'', fisioterapeuta_id:'', fecha:'', duracion_minutos:60, tipo_terapia:'', observaciones:'', evolucion:'estable' })
const pacientes = ref([]); const fisios = ref([]); const loading = ref(false)

onMounted(async () => {
  const [p, f] = await Promise.all([pacienteService.getAll(), fisioterapeutaService.getAll()])
  pacientes.value = p.data; fisios.value = f.data
  if (isEdit.value) { const { data } = await sesionService.getById(route.params.id); Object.assign(form.value, data) }
})
async function guardar() {
  loading.value = true
  try {
    if (isEdit.value) await sesionService.update(route.params.id, form.value)
    else await sesionService.create(form.value)
    router.push('/sesiones')
  } finally { loading.value = false }
}
</script>
<style scoped>@import '@/assets/form.css';</style>
