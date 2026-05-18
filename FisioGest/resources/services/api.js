import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: { 'Accept': 'application/json' },
})

export const citaService = {
  getAll: () => api.get('/citas'),
}

export const pacienteService = {
  getAll: () => api.get('/pacientes'),
}

export const inventarioService = {
  getAll: () => api.get('/inventario'),
}
