import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: { 'Accept': 'application/json' },
})

// Adjunta el token en cada petición si existe
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// ── Helpers de sesión ────────────────────────────────────────────────────────
export function saveUser(user, token) {
  localStorage.setItem('token', token)
  localStorage.setItem('user', JSON.stringify(user))
}

export function getUser() {
  try { return JSON.parse(localStorage.getItem('user') || 'null') } catch { return null }
}

export function clearUser() {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
}

export function isAuthenticated() {
  return !!localStorage.getItem('token')
}

export function getUserRole() {
  const u = getUser()
  return u?.rol ?? null
}

// ── Servicios ────────────────────────────────────────────────────────────────
export const authService = {
  login:  (correo, contrasena) => api.post('/login', { correo, contraseña: contrasena }),
  logout: () => api.post('/logout'),
}

export const citaService = {
  getAll:       () => api.get('/citas'),
  create:       (data) => api.post('/citas', data),
  update:       (id, data) => api.put(`/citas/${id}`, data),
  updateEstado: (id, estado) => api.patch(`/citas/${id}`, { estado }),
}

export const pacienteService = {
  getAll:  () => api.get('/pacientes'),
  create:  (data) => api.post('/pacientes', data),
  update:  (id, data) => api.put(`/pacientes/${id}`, data),
  delete:  (id) => api.delete(`/pacientes/${id}`),
}

export const inventarioService = {
  getAll:  ()         => api.get('/inventario'),
  create:  (data)     => api.post('/inventario', data),
  update:  (id, data) => api.put(`/inventario/${id}`, data),
  delete:  (id)       => api.delete(`/inventario/${id}`),
}

export const fisioterapeutaService = {
  getAll: () => api.get('/fisioterapeutas'),
}

// Endpoints exclusivos del fisioterapeuta autenticado (filtrados en backend)
export const fisioService = {
  misPacientes:    () => api.get('/fisio/mis-pacientes'),
  misCitas:        () => api.get('/fisio/mis-citas'),
  misAsignaciones: () => api.get('/fisio/mis-asignaciones'),
}

export const asignacionesService = {
  getAll:  ()     => api.get('/asignaciones'),
  create:  (data) => api.post('/asignaciones', data),
  liberar: (id)   => api.patch(`/asignaciones/${id}/liberar`),
}
