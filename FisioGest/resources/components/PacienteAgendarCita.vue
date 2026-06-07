<template>
  <PacienteLayout>
    <div class="page">
      <div class="page-header">
        <h1 class="page-title">Agendar Cita</h1>
        <p class="page-subtitle">Elige la fecha y hora con tu especialista asignado</p>
      </div>

      <!-- Cargando -->
      <div v-if="cargando" class="loading-state">
        <div class="spinner"></div><span>Cargando…</span>
      </div>

      <!-- Sin fisio asignado -->
      <div v-else-if="!fisio" class="empty-card">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="1.5">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
        </svg>
        <p>Aún no tienes un especialista asignado.</p>
        <span class="hint">Contacta a la clínica para que te asignen un fisioterapeuta.</span>
      </div>

      <template v-else>

        <!-- Card del fisio asignado -->
        <div class="fisio-bar">
          <div class="fb-avatar">{{ initials(fisio) }}</div>
          <div class="fb-info">
            <span class="fb-nombre">{{ fisio.nombre }} {{ fisio.apellido }}</span>
            <span class="fb-esp">{{ fisio.especialidad }}</span>
          </div>
          <span class="fb-badge">Tu especialista</span>
        </div>

        <!-- Horario de atención -->
        <div class="horario-info" v-if="horarioFisio">
          <div class="horario-title">Horario de atención:</div>
          <div class="dias-horario">
            <span
              v-for="(conf, dia) in horarioNormalizado"
              :key="dia"
              class="dia-chip"
              :class="{ activo: conf.activo }"
            >
              {{ nombreDia(dia) }}
              <span v-if="conf.activo" class="dia-horas"> {{ conf.entrada }}–{{ conf.salida }}</span>
            </span>
          </div>
        </div>

        <!-- Formulario -->
        <section class="section" v-if="paso === 1">
          <div class="fecha-row">
            <div class="field">
              <label>Fecha</label>
              <input
                v-model="form.fecha"
                type="date"
                :min="hoy"
                @change="onFechaChange"
                required
              />
              <span v-if="avisoFecha" class="field-aviso">{{ avisoFecha }}</span>
            </div>
            <div v-if="form.fecha && confDia" class="rango-label">
              Hora &nbsp;<strong>{{ confDia.entrada }} – {{ confDia.salida }}</strong>
            </div>
          </div>

          <!-- Grilla de slots -->
          <div v-if="form.fecha && !avisoFecha">
            <div v-if="cargandoSlots" class="loading-state" style="padding:1rem">
              <div class="spinner"></div><span>Verificando disponibilidad…</span>
            </div>
            <div v-else-if="slotsDelDia.length === 0" class="empty-card" style="padding:1.5rem">
              <p>No hay horarios disponibles para este día.</p>
            </div>
            <div v-else class="slots-grid">
              <button
                v-for="slot in slotsDelDia"
                :key="slot.hora"
                class="slot-btn"
                :class="{
                  ocupado:    slot.ocupado,
                  seleccionado: form.hora === slot.hora && !slot.ocupado,
                }"
                :disabled="slot.ocupado"
                @click="form.hora = slot.hora"
              >
                <span class="slot-hora">{{ formatHora(slot.hora) }}</span>
                <span v-if="slot.ocupado" class="slot-tag ocupado-tag">Ocupado</span>
              </button>
            </div>
            <p v-if="!form.hora && !cargandoSlots && slotsDelDia.some(s => !s.ocupado)" class="slots-hint">
              Selecciona una hora disponible.
            </p>
          </div>

          <!-- Motivo -->
          <div class="field" v-if="form.fecha && form.hora">
            <label>Motivo (opcional)</label>
            <input v-model="form.motivo" type="text" placeholder="Describe el motivo de tu cita" />
          </div>

          <div v-if="msg" class="alert" :class="msgType">{{ msg }}</div>

          <div class="step-footer">
            <button
              class="btn-primary"
              :disabled="!form.fecha || !form.hora || agendando"
              @click="confirmarCita"
            >
              <span v-if="agendando" class="btn-spinner"></span>
              {{ agendando ? 'Agendando…' : 'Confirmar cita' }}
            </button>
          </div>
        </section>

        <!-- Confirmación -->
        <section class="section" v-if="paso === 2">
          <div class="confirmacion-card">
            <div class="confirmacion-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
              </svg>
            </div>
            <h2 class="confirmacion-title">¡Cita agendada!</h2>
            <p class="confirmacion-msg">Tu cita fue programada correctamente.</p>
            <div class="confirmacion-detalle">
              <div class="det-row"><span>Especialista</span><strong>{{ fisio.nombre }} {{ fisio.apellido }}</strong></div>
              <div class="det-row"><span>Fecha</span><strong>{{ formatFecha(form.fecha) }}</strong></div>
              <div class="det-row"><span>Hora</span><strong>{{ formatHora(form.hora) }}</strong></div>
              <div class="det-row" v-if="form.motivo"><span>Motivo</span><strong>{{ form.motivo }}</strong></div>
            </div>
            <div class="confirmacion-btns">
              <router-link to="/paciente/dashboard" class="btn-primary">Ver mi dashboard</router-link>
              <button class="btn-secondary" @click="nuevaCita">Agendar otra cita</button>
            </div>
          </div>
        </section>

      </template>
    </div>
  </PacienteLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import PacienteLayout from './PacienteLayout.vue'
import { pacienteService } from '@/services/api'

const paso         = ref(1)
const fisio        = ref(null)
const cargando     = ref(true)
const cargandoSlots = ref(false)
const agendando    = ref(false)
const msg          = ref('')
const msgType      = ref('success')
const avisoFecha   = ref('')
const slotsOcupados = ref([])

const form = ref({ fecha: '', hora: '', motivo: '' })
const hoy  = new Date().toISOString().split('T')[0]

onMounted(async () => {
  try {
    const res = await pacienteService.getMiFisioterapeuta()
    fisio.value = res.data
  } catch {}
  cargando.value = false
})

// ── Horario ──────────────────────────────────────────────────────────────
const horarioFisio = computed(() => fisio.value?.horario ?? null)

const mapaAdmin = { 'Lunes':'lun','Martes':'mar','Miércoles':'mie','Jueves':'jue','Viernes':'vie','Sábado':'sab','Domingo':'dom' }
const mapaDiasSemana = { 0:'dom', 1:'lun', 2:'mar', 3:'mie', 4:'jue', 5:'vie', 6:'sab' }

const horarioNormalizado = computed(() => {
  const h = horarioFisio.value
  if (!h) return {}
  const result = {}
  for (const [k, v] of Object.entries(h)) {
    const clave = mapaAdmin[k] ?? k
    result[clave] = {
      activo:  v.activo ?? false,
      entrada: v.entrada ?? v.inicio ?? '—',
      salida:  v.salida ?? v.fin ?? '—',
    }
  }
  return result
})

function nombreDia(clave) {
  return { lun:'Lun', mar:'Mar', mie:'Mié', jue:'Jue', vie:'Vie', sab:'Sáb', dom:'Dom' }[clave] ?? clave
}

// Config del día seleccionado
const confDia = computed(() => {
  if (!form.value.fecha) return null
  const ts  = new Date(form.value.fecha + 'T12:00:00')
  const key = mapaDiasSemana[ts.getDay()]
  return horarioNormalizado.value[key] ?? null
})

// ── Slots ─────────────────────────────────────────────────────────────────
const todosSlots = (() => {
  const slots = []
  for (let h = 7; h < 19; h++) {
    slots.push(`${String(h).padStart(2,'0')}:00`)
    slots.push(`${String(h).padStart(2,'0')}:30`)
  }
  return slots
})()

const slotsDelDia = computed(() => {
  const conf = confDia.value
  if (!conf || !conf.activo) return []

  return todosSlots
    .filter(slot => {
      if (conf.entrada && conf.entrada !== '—' && slot < conf.entrada) return false
      if (conf.salida  && conf.salida  !== '—' && slot >= conf.salida) return false
      return true
    })
    .map(slot => ({
      hora:   slot,
      ocupado: slotsOcupados.value.includes(slot),
    }))
})

async function onFechaChange() {
  avisoFecha.value  = ''
  form.value.hora   = ''
  slotsOcupados.value = []

  if (!form.value.fecha) return

  const conf = confDia.value
  if (!conf || !conf.activo) {
    avisoFecha.value = `El especialista no atiende ese día. Elige otra fecha.`
    return
  }

  // Cargar slots ocupados
  cargandoSlots.value = true
  try {
    const res = await pacienteService.getSlotsOcupados(fisio.value.fisioterapeuta_id, form.value.fecha)
    slotsOcupados.value = res.data
  } catch {}
  cargandoSlots.value = false
}

async function confirmarCita() {
  if (!form.value.fecha || !form.value.hora) return
  agendando.value = true
  msg.value = ''
  try {
    await pacienteService.agendarCita({
      fisioterapeuta_id: fisio.value.fisioterapeuta_id,
      fecha_hora: `${form.value.fecha} ${form.value.hora}:00`,
      motivo: form.value.motivo || null,
    })
    paso.value = 2
  } catch (err) {
    msg.value = err?.response?.data?.message ?? 'Error al agendar la cita.'
    msgType.value = 'error'
  }
  agendando.value = false
}

function nuevaCita() {
  paso.value = 1
  form.value = { fecha: '', hora: '', motivo: '' }
  msg.value  = ''
  avisoFecha.value = ''
  slotsOcupados.value = []
}

function formatFecha(f) {
  if (!f) return ''
  return new Date(f + 'T12:00:00').toLocaleDateString('es', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}

function formatHora(h) {
  if (!h) return ''
  const [hh, mm] = h.split(':')
  const hora = parseInt(hh)
  const sufijo = hora < 12 ? 'a.m.' : 'p.m.'
  const h12 = hora === 0 ? 12 : hora > 12 ? hora - 12 : hora
  return `${h12}:${mm} ${sufijo}`
}

function initials(f) {
  if (!f) return '?'
  return `${f.nombre?.[0] ?? ''}${f.apellido?.[0] ?? ''}`.toUpperCase()
}
</script>

<style scoped>
.page { display: flex; flex-direction: column; gap: 1.5rem; }
.page-header { display: flex; flex-direction: column; gap: 2px; }
.page-title  { font-size: 1.4rem; font-weight: 700; color: #f4f4f5; }
.page-subtitle { font-size: 0.85rem; color: #6b7280; }

.loading-state {
  display: flex; align-items: center; gap: 0.75rem;
  color: #6b7280; font-size: 0.85rem; padding: 1.5rem;
}
.spinner {
  width: 18px; height: 18px;
  border: 2px solid #1c1c1c; border-top-color: #4ade80;
  border-radius: 50%; animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.empty-card {
  background: #111111; border: 1px solid #1c1c1c;
  border-radius: 12px; padding: 3rem 2rem;
  text-align: center; color: #6b7280;
  display: flex; flex-direction: column; align-items: center; gap: 0.6rem;
}
.empty-card p { font-size: 0.9rem; }
.hint { font-size: 0.78rem; color: #374151; }

/* Card del fisio */
.fisio-bar {
  display: flex; align-items: center; gap: 0.85rem;
  background: rgba(7,68,52,0.1); border: 1px solid rgba(74,222,128,0.15);
  border-radius: 12px; padding: 1rem 1.25rem;
}
.fb-avatar {
  width: 40px; height: 40px; background: rgba(7,68,52,0.4);
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  color: #4ade80; font-size: 14px; font-weight: 700; flex-shrink: 0;
}
.fb-info { display: flex; flex-direction: column; gap: 2px; flex: 1; }
.fb-nombre { font-size: 0.9rem; font-weight: 600; color: #e4e4e7; }
.fb-esp    { font-size: 0.78rem; color: #4ade80; }
.fb-badge {
  font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;
  background: rgba(74,222,128,0.08); border: 1px solid rgba(74,222,128,0.15);
  color: #4ade80; padding: 3px 10px; border-radius: 20px; flex-shrink: 0;
}

/* Horario chips */
.horario-info { background: #0d0d0d; border: 1px solid #1c1c1c; border-radius: 8px; padding: 0.85rem 1rem; }
.horario-title { font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.6rem; }
.dias-horario  { display: flex; flex-wrap: wrap; gap: 0.4rem; }
.dia-chip { font-size: 0.72rem; padding: 0.25rem 0.6rem; border-radius: 6px; border: 1px solid #1c1c1c; color: #4b5563; background: #111; }
.dia-chip.activo { border-color: rgba(74,222,128,0.2); color: #4ade80; background: rgba(7,68,52,0.15); }
.dia-horas { color: #6b7280; }

/* Formulario */
.section { display: flex; flex-direction: column; gap: 1.25rem; }

.fecha-row {
  display: flex; align-items: flex-end; gap: 1.5rem; flex-wrap: wrap;
}

.field { display: flex; flex-direction: column; gap: 0.4rem; }
.field label {
  font-size: 0.78rem; font-weight: 600;
  color: #9ca3af; text-transform: uppercase; letter-spacing: 0.04em;
}
.field input {
  background: #0d0d0d; border: 1px solid #222;
  border-radius: 8px; color: #e4e4e7;
  font-size: 0.875rem; padding: 0.6rem 0.85rem;
  outline: none; font-family: inherit; transition: border-color 0.15s;
}
.field input:focus { border-color: #4ade80; }
.field input::placeholder { color: #374151; }
.field-aviso { font-size: 0.75rem; color: #f87171; }

.rango-label {
  font-size: 0.85rem; color: #4ade80; padding-bottom: 0.6rem;
}
.rango-label strong { color: #4ade80; font-weight: 700; }

/* Grilla de slots */
.slots-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 0.6rem;
}

.slot-btn {
  display: flex; align-items: center; justify-content: space-between;
  gap: 0.5rem;
  background: #111111; border: 1px solid #222;
  border-radius: 10px; padding: 0.65rem 1rem;
  cursor: pointer; font-family: inherit;
  transition: border-color 0.15s, background 0.15s;
  text-align: left;
}
.slot-btn:not(.ocupado):hover {
  border-color: rgba(74,222,128,0.3);
  background: rgba(7,68,52,0.1);
}
.slot-btn.seleccionado {
  border-color: rgba(74,222,128,0.5);
  background: rgba(7,68,52,0.25);
}
.slot-btn.ocupado {
  cursor: not-allowed;
  opacity: 0.5;
}

.slot-hora {
  font-size: 0.85rem; font-weight: 500; color: #e4e4e7;
}
.slot-btn.ocupado .slot-hora {
  text-decoration: line-through; color: #4b5563;
}
.slot-btn.seleccionado .slot-hora { color: #4ade80; }

.slot-tag {
  font-size: 0.65rem; font-weight: 600; text-transform: uppercase;
  padding: 2px 7px; border-radius: 20px; letter-spacing: 0.04em;
  flex-shrink: 0;
}
.ocupado-tag { background: rgba(107,114,128,0.15); color: #6b7280; border: 1px solid #222; }

.slots-hint { font-size: 0.78rem; color: #f59e0b; margin-top: 0.25rem; }

.alert { padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.82rem; }
.alert.success { background: rgba(74,222,128,0.1); color: #4ade80; border: 1px solid rgba(74,222,128,0.2); }
.alert.error   { background: rgba(239,68,68,0.1); color: #f87171; border: 1px solid rgba(239,68,68,0.2); }

.step-footer { display: flex; gap: 0.75rem; justify-content: flex-end; }

.btn-primary {
  display: flex; align-items: center; gap: 0.5rem;
  background: rgba(7,68,52,0.5); border: 1px solid rgba(74,222,128,0.3);
  color: #4ade80; font-size: 0.85rem; font-weight: 600;
  padding: 0.6rem 1.4rem; border-radius: 8px;
  cursor: pointer; font-family: inherit; text-decoration: none; transition: background 0.15s;
}
.btn-primary:hover:not(:disabled) { background: rgba(7,68,52,0.7); }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-secondary {
  background: transparent; border: 1px solid #222;
  color: #6b7280; font-size: 0.85rem; font-weight: 500;
  padding: 0.6rem 1.2rem; border-radius: 8px;
  cursor: pointer; font-family: inherit; transition: background 0.15s, color 0.15s;
}
.btn-secondary:hover { background: rgba(255,255,255,0.04); color: #9ca3af; }

.btn-spinner {
  width: 14px; height: 14px;
  border: 2px solid rgba(74,222,128,0.3); border-top-color: #4ade80;
  border-radius: 50%; animation: spin 0.7s linear infinite;
}

/* Confirmación */
.confirmacion-card {
  background: #111111; border: 1px solid rgba(74,222,128,0.15);
  border-radius: 12px; padding: 2.5rem 2rem;
  display: flex; flex-direction: column; align-items: center; gap: 1rem; text-align: center;
}
.confirmacion-icon {
  width: 64px; height: 64px; background: rgba(7,68,52,0.3); border: 1px solid rgba(74,222,128,0.2);
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
}
.confirmacion-title { font-size: 1.3rem; font-weight: 700; color: #f4f4f5; }
.confirmacion-msg   { font-size: 0.85rem; color: #6b7280; }
.confirmacion-detalle {
  background: #0d0d0d; border: 1px solid #1c1c1c; border-radius: 10px;
  padding: 1rem 1.25rem; width: 100%; max-width: 360px;
  display: flex; flex-direction: column; gap: 0.6rem;
}
.det-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.82rem; }
.det-row span   { color: #6b7280; }
.det-row strong { color: #e4e4e7; font-weight: 600; }
.confirmacion-btns { display: flex; gap: 0.75rem; flex-wrap: wrap; justify-content: center; margin-top: 0.5rem; }
</style>
