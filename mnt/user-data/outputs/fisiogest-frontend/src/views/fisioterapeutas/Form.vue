<!-- src/views/fisioterapeutas/Form.vue -->
<template>
  <AppLayout>
    <div class="page-header">
      <h1>{{ isEdit ? 'Editar fisioterapeuta' : 'Nuevo fisioterapeuta' }}</h1>
      <RouterLink to="/fisioterapeutas" class="btn-secondary">← Volver</RouterLink>
    </div>
    <div class="form-card">
      <form @submit.prevent="guardar">
        <div class="form-grid">
          <div class="form-group"><label>Nombre *</label><input v-model="form.nombre" required /></div>
          <div class="form-group"><label>Apellido *</label><input v-model="form.apellido" required /></div>
          <div class="form-group"><label>Especialidad</label><input v-model="form.especialidad" placeholder="Ej. Rehabilitación deportiva" /></div>
          <div class="form-group"><label>Teléfono</label><input v-model="form.telefono" placeholder="0000-0000" /></div>
          <div class="form-group">
            <label>Estado</label>
            <select v-model="form.activo">
              <option :value="true">Activo</option>
              <option :value="false">Inactivo</option>
            </select>
          </div>
        </div>
        <p v-if="error" class="error-msg">{{ error }}</p>
        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="loading">
            {{ loading ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '@/components/AppLayout.vue'
import { fisioterapeutaService } from '@/services/api'

const route = useRoute(); const router = useRouter()
const isEdit = computed(() => !!route.params.id)
const form = ref({ nombre: '', apellido: '', especialidad: '', telefono: '', activo: true })
const loading = ref(false); const error = ref('')

onMounted(async () => {
  if (isEdit.value) {
    const { data } = await fisioterapeutaService.getById(route.params.id)
    Object.assign(form.value, data)
  }
})

async function guardar() {
  loading.value = true; error.value = ''
  try {
    if (isEdit.value) await fisioterapeutaService.update(route.params.id, form.value)
    else await fisioterapeutaService.create({ ...form.value, usuario_id: 1 })
    router.push('/fisioterapeutas')
  } catch { error.value = 'Error al guardar.' } finally { loading.value = false }
}
</script>
<style scoped>@import '@/assets/form.css';</style>
