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
  getAll:  () => api.get('/inventario'),
  create:  (data) => api.post('/inventario', data),
}
