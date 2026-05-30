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
          <input
            id="contrasena"
            v-model="form.contrasena"
            type="password"
            placeholder="••••••••"
            required
          />
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
import { authService } from '@/services/api'

const router   = useRouter()
const form     = ref({ correo: '', contrasena: '' })
const errorMsg = ref('')
const loading  = ref(false)

async function handleLogin() {
  loading.value  = true
  errorMsg.value = ''
  try {
    const res = await authService.login(form.value.correo, form.value.contrasena)
    localStorage.setItem('token', res.data.token)
    router.push('/dashboard')
  } catch {
    errorMsg.value = 'Correo o contraseña incorrectos.'
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
</style>
