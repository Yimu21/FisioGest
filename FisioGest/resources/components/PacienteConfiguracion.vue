<template>
  <PacienteLayout>
    <div class="page">
      <div class="page-header">
        <h1 class="page-title">Configuración</h1>
        <p class="page-subtitle">Gestiona la seguridad de tu cuenta</p>
      </div>

      <!-- Cambiar contraseña -->
      <div class="config-card">
        <div class="card-header">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <h2 class="card-title">Cambiar Contraseña</h2>
        </div>

        <form @submit.prevent="cambiarContrasena" class="form-fields">
          <div class="field">
            <label>Contraseña actual</label>
            <div class="pass-wrap">
              <input v-model="contrasena.actual" :type="showPassActual ? 'text' : 'password'" required placeholder="••••••••" />
              <button type="button" class="pass-eye" @click="showPassActual = !showPassActual" tabindex="-1">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <template v-if="showPassActual">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                  </template>
                  <template v-else>
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </template>
                </svg>
              </button>
            </div>
          </div>
          <div class="field">
            <label>Nueva contraseña</label>
            <div class="pass-wrap">
              <input v-model="contrasena.nueva" :type="showPassNueva ? 'text' : 'password'" required placeholder="Mínimo 8 caracteres" />
              <button type="button" class="pass-eye" @click="showPassNueva = !showPassNueva" tabindex="-1">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <template v-if="showPassNueva">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                  </template>
                  <template v-else>
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </template>
                </svg>
              </button>
            </div>
          </div>
          <div class="field">
            <label>Confirmar nueva contraseña</label>
            <div class="pass-wrap">
              <input v-model="contrasena.confirmar" :type="showPassConfirm ? 'text' : 'password'" required placeholder="Repetir nueva contraseña" />
              <button type="button" class="pass-eye" @click="showPassConfirm = !showPassConfirm" tabindex="-1">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <template v-if="showPassConfirm">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                  </template>
                  <template v-else>
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </template>
                </svg>
              </button>
            </div>
          </div>

          <div v-if="msg" class="alert" :class="msgType">{{ msg }}</div>

          <div class="form-footer">
            <button type="submit" class="btn-primary" :disabled="guardando">
              <span v-if="guardando" class="btn-spinner"></span>
              {{ guardando ? 'Guardando…' : 'Actualizar contraseña' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Info de cuenta -->
      <div class="config-card info-card">
        <div class="card-header">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <h2 class="card-title card-title-muted">Información de la cuenta</h2>
        </div>
        <p class="info-text">
          Tu correo electrónico y datos médicos son gestionados por la clínica.
          Si necesitas actualizarlos, contacta directamente con tu fisioterapeuta o con el equipo de administración.
        </p>
      </div>
    </div>
  </PacienteLayout>
</template>

<script setup>
import { ref } from 'vue'
import PacienteLayout from './PacienteLayout.vue'
import { pacienteService } from '@/services/api'

const guardando      = ref(false)
const msg            = ref('')
const msgType        = ref('success')
const showPassActual  = ref(false)
const showPassNueva   = ref(false)
const showPassConfirm = ref(false)

const contrasena = ref({ actual: '', nueva: '', confirmar: '' })

async function cambiarContrasena() {
  msg.value = ''

  if (contrasena.value.nueva.length < 8) {
    msg.value = 'La nueva contraseña debe tener al menos 8 caracteres.'
    msgType.value = 'error'
    return
  }

  if (contrasena.value.nueva !== contrasena.value.confirmar) {
    msg.value = 'Las contraseñas no coinciden.'
    msgType.value = 'error'
    return
  }

  guardando.value = true
  try {
    await pacienteService.updateMiCuenta({
      contrasena_actual: contrasena.value.actual,
      contrasena_nueva:  contrasena.value.nueva,
    })
    msg.value = 'Contraseña actualizada correctamente.'
    msgType.value = 'success'
    contrasena.value = { actual: '', nueva: '', confirmar: '' }
  } catch (err) {
    msg.value = err?.response?.data?.message ?? 'Error al actualizar la contraseña.'
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

.config-card {
  background: #111111;
  border: 1px solid #1c1c1c;
  border-radius: 12px;
  padding: 1.5rem 1.75rem;
  max-width: 520px;
  display: flex; flex-direction: column; gap: 1.25rem;
}

.card-header {
  display: flex; align-items: center; gap: 0.6rem;
  padding-bottom: 0.9rem;
  border-bottom: 1px solid #1c1c1c;
}

.card-title {
  font-size: 0.95rem; font-weight: 600; color: #d1d5db;
}
.card-title-muted { color: #6b7280; }

.form-fields { display: flex; flex-direction: column; gap: 1rem; }

.field { display: flex; flex-direction: column; gap: 0.4rem; }

.field label {
  font-size: 0.78rem; font-weight: 600;
  color: #9ca3af; text-transform: uppercase; letter-spacing: 0.04em;
}

.field input {
  background: #0d0d0d; border: 1px solid #222;
  border-radius: 8px; color: #e4e4e7;
  font-size: 0.875rem; padding: 0.6rem 0.85rem;
  outline: none; font-family: inherit;
  transition: border-color 0.15s;
}
.field input:focus { border-color: #4ade80; }
.field input::placeholder { color: #374151; }

.alert {
  padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.82rem;
}
.alert.success { background: rgba(74,222,128,0.1); color: #4ade80; border: 1px solid rgba(74,222,128,0.2); }
.alert.error   { background: rgba(239,68,68,0.1); color: #f87171; border: 1px solid rgba(239,68,68,0.2); }

.form-footer { display: flex; justify-content: flex-end; }

.btn-primary {
  display: flex; align-items: center; gap: 0.5rem;
  background: rgba(7,68,52,0.5); border: 1px solid rgba(74,222,128,0.3);
  color: #4ade80; font-size: 0.85rem; font-weight: 600;
  padding: 0.6rem 1.4rem; border-radius: 8px;
  cursor: pointer; font-family: inherit;
  transition: background 0.15s;
}
.btn-primary:hover:not(:disabled) { background: rgba(7,68,52,0.7); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-spinner {
  width: 14px; height: 14px;
  border: 2px solid rgba(74,222,128,0.3); border-top-color: #4ade80;
  border-radius: 50%; animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.info-card { max-width: 520px; }
.info-text { font-size: 0.82rem; color: #6b7280; line-height: 1.6; }

.pass-wrap { position: relative; display: flex; }
.pass-wrap input { flex: 1; padding-right: 2.5rem !important; }
.pass-eye {
  position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer;
  color: #6b7280; padding: 0.2rem; display: flex; align-items: center;
  transition: color 0.15s;
}
.pass-eye:hover { color: #d1d5db; }
</style>
