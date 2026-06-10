<template>
  <PacienteLayout>
    <div class="page">
      <div class="page-header">
        <h1 class="page-title">Información Personal</h1>
        <p class="page-subtitle">Actualiza tus datos de contacto y personales</p>
      </div>

      <div v-if="cargando" class="loading-state">
        <div class="spinner"></div>
        <span>Cargando…</span>
      </div>

      <form v-else class="form-card" @submit.prevent="guardar">
        <div class="form-grid">
          <div class="field">
            <label>Nombre</label>
            <input v-model="form.nombre" type="text" required placeholder="Nombre" />
          </div>
          <div class="field">
            <label>Apellido</label>
            <input v-model="form.apellido" type="text" required placeholder="Apellido" />
          </div>
          <div class="field">
            <label>Fecha de Nacimiento</label>
            <input v-model="form.fecha_nacimiento" type="date" required />
          </div>
          <div class="field">
            <label>Género</label>
            <select v-model="form.genero" required>
              <option value="">Seleccionar</option>
              <option value="masculino">Masculino</option>
              <option value="femenino">Femenino</option>
              <option value="otro">Otro</option>
            </select>
          </div>
          <div class="field">
            <label>Teléfono</label>
            <input v-model="form.telefono" type="tel" placeholder="Teléfono" />
          </div>
          <div class="field field-full">
            <label>Dirección</label>
            <input v-model="form.direccion" type="text" placeholder="Dirección" />
          </div>
        </div>

        <div class="field field-full diagnostico-field" v-if="form.diagnostico">
          <label>Diagnóstico</label>
          <textarea v-model="form.diagnostico" readonly rows="3" class="readonly-area"></textarea>
          <span class="field-hint">El diagnóstico es gestionado por tu fisioterapeuta.</span>
        </div>

        <div v-if="msg" class="alert" :class="msgType">{{ msg }}</div>

        <div class="form-footer">
          <button type="submit" class="btn-primary" :disabled="guardando">
            <span v-if="guardando" class="btn-spinner"></span>
            {{ guardando ? 'Guardando…' : 'Guardar cambios' }}
          </button>
        </div>
      </form>
    </div>
  </PacienteLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import PacienteLayout from './PacienteLayout.vue'
import { pacienteService } from '@/services/api'

const cargando = ref(true)
const guardando = ref(false)
const msg     = ref('')
const msgType = ref('success')

const form = ref({
  nombre: '', apellido: '', fecha_nacimiento: '', genero: '',
  telefono: '', direccion: '', diagnostico: '',
})

onMounted(async () => {
  try {
    const res = await pacienteService.getMiPerfil()
    if (res.data) {
      const p = res.data
      form.value = {
        nombre:           p.nombre           ?? '',
        apellido:         p.apellido         ?? '',
        fecha_nacimiento: p.fecha_nacimiento  ?? '',
        genero:           p.genero           ?? '',
        telefono:         p.telefono         ?? '',
        direccion:        p.direccion        ?? '',
        diagnostico:      p.diagnostico      ?? '',
      }
    }
  } catch {}
  cargando.value = false
})

async function guardar() {
  guardando.value = true
  msg.value = ''
  try {
    await pacienteService.updateMiPerfil(form.value)
    msg.value = 'Perfil actualizado correctamente.'
    msgType.value = 'success'
  } catch (err) {
    msg.value = err?.response?.data?.message ?? 'Error al guardar.'
    msgType.value = 'error'
  }
  guardando.value = false
}
</script>

<style scoped>
.page { display: flex; flex-direction: column; gap: 1.5rem; }
.page-header { display: flex; flex-direction: column; gap: 2px; }
.page-title  { font-size: 1.4rem; font-weight: 700; color: #f4f4f5; }
.page-subtitle { font-size: 0.85rem; color: #6b7280; }

.loading-state {
  display: flex; align-items: center; gap: 0.75rem;
  color: #6b7280; font-size: 0.85rem; padding: 2rem;
}
.spinner {
  width: 18px; height: 18px;
  border: 2px solid #1c1c1c; border-top-color: #4ade80;
  border-radius: 50%; animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.form-card {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 12px;
  padding: 1.75rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.field { display: flex; flex-direction: column; gap: 0.4rem; }
.field-full { grid-column: 1 / -1; }

.field label {
  font-size: 0.78rem; font-weight: 600;
  color: #9ca3af; text-transform: uppercase; letter-spacing: 0.04em;
}

.field input,
.field select {
  background: #0d0d0d;
  border: 1px solid #222;
  border-radius: 8px;
  color: #e4e4e7;
  font-size: 0.875rem;
  padding: 0.6rem 0.85rem;
  outline: none;
  font-family: inherit;
  transition: border-color 0.15s;
}
.field input:focus, .field select:focus { border-color: #4ade80; }
.field input::placeholder { color: #374151; }
.field select option { background: #1a1a1a; }

.readonly-area {
  background: #0a0a0a;
  border: 1px solid #1c1c1c;
  border-radius: 8px;
  color: #6b7280;
  font-size: 0.875rem;
  padding: 0.6rem 0.85rem;
  resize: none;
  font-family: inherit;
}

.field-hint { font-size: 0.72rem; color: #374151; margin-top: 2px; }

.diagnostico-field { margin-top: 0.25rem; }

.alert {
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.82rem;
}
.alert.success { background: rgba(74,222,128,0.1); color: #4ade80; border: 1px solid rgba(74,222,128,0.2); }
.alert.error   { background: rgba(239,68,68,0.1);  color: #f87171; border: 1px solid rgba(239,68,68,0.2); }

.form-footer { display: flex; justify-content: flex-end; }

.btn-primary {
  display: flex; align-items: center; gap: 0.5rem;
  background: rgba(7,68,52,0.5);
  border: 1px solid rgba(74,222,128,0.3);
  color: #4ade80;
  font-size: 0.85rem; font-weight: 600;
  padding: 0.6rem 1.4rem;
  border-radius: 8px;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.15s;
}
.btn-primary:hover:not(:disabled) { background: rgba(7,68,52,0.7); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-spinner {
  width: 14px; height: 14px;
  border: 2px solid rgba(74,222,128,0.3); border-top-color: #4ade80;
  border-radius: 50%; animation: spin 0.7s linear infinite;
}
</style>
