<template>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <h1>FisioGest</h1>
        <p>Sistema de Gestión Fisioterapéutica</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="correo">Correo electrónico</label>
          <input
            id="correo"
            v-model="form.correo"
            type="email"
            placeholder="correo@ejemplo.com"
            required
          />
        </div>

        <div class="form-group">
          <label for="contrasena">Contraseña</label>
          <div class="pass-wrap">
            <input
              id="contrasena"
              v-model="form.contrasena"
              :type="showPass ? 'text' : 'password'"
              placeholder="••••••••"
              required
            />
            <button type="button" class="pass-eye" @click="showPass = !showPass" tabindex="-1">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <template v-if="showPass">
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

        <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>

        <button type="submit" class="btn-login" :disabled="loading">
          {{ loading ? 'Verificando...' : 'Iniciar sesión' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { authService, saveUser, clearUser } from '@/services/api'

const router   = useRouter()
const form     = ref({ correo: '', contrasena: '' })
const errorMsg = ref('')
const loading  = ref(false)
const showPass = ref(false)

async function handleLogin() {
  loading.value  = true
  errorMsg.value = ''
  try {
    const res  = await authService.login(form.value.correo, form.value.contrasena)
    const user  = res.data.user
    const token = res.data.token

    saveUser(user, token)

    if (user.rol === 'paciente') {
      await router.push('/paciente/dashboard')
    } else if (user.rol === 'fisioterapeuta') {
      await router.push('/fisio/dashboard')
    } else if (user.rol === 'admin') {
      await router.push('/dashboard')
    } else {
      clearUser()
      errorMsg.value = 'Rol de usuario no reconocido. Contacta al administrador.'
    }
  } catch (err) {
    const msg = err?.response?.data?.errors?.correo?.[0]
             ?? err?.response?.data?.message
             ?? 'Correo o contraseña incorrectos.'
    errorMsg.value = msg
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #074434;
}

.login-card {
  background: #FFFFFF;
  border-radius: 12px;
  box-shadow: 0 6px 32px rgba(0, 0, 0, 0.25);
  padding: 2.5rem 2rem;
  width: 100%;
  max-width: 400px;
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-header h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #074434;
  margin-bottom: 0.25rem;
}

.login-header p {
  color: #A9AFB2;
  font-size: 0.9rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #000000;
}

.form-group input {
  padding: 0.65rem 0.75rem;
  border: 1px solid #A9AFB2;
  border-radius: 6px;
  font-size: 1rem;
  background: #F5F5F5;
  color: #000000;
  outline: none;
  transition: border-color 0.2s, background 0.2s;
}

.form-group input:focus {
  border-color: #074434;
  background: #FFFFFF;
  box-shadow: 0 0 0 3px rgba(7, 68, 52, 0.12);
}

.error-msg {
  color: #dc2626;
  font-size: 0.875rem;
  text-align: center;
  background: #fee2e2;
  padding: 0.5rem;
  border-radius: 6px;
}

.btn-login {
  background: #074434;
  color: #FFFFFF;
  border: none;
  border-radius: 6px;
  padding: 0.75rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-login:hover {
  background: #053325;
}

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
