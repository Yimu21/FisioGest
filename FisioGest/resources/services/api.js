import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: { 'Accept': 'application/json' },
})

export const citaService = {
  getAll:  () => api.get('/citas'),
  create:  (data) => api.post('/citas', data),
}

export const pacienteService = {
  getAll:  () => api.get('/pacientes'),
  create:  (data) => api.post('/pacientes', data),
}

export const inventarioService = {
  getAll:  () => api.get('/inventario'),
  create:  (data) => api.post('/inventario', data),
}
