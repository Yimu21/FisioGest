<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Citas - FisioGest</title>
</head>
<body style="margin:0;padding:0;background:#0a0a0a;color:#fff;font-family:sans-serif;display:flex;min-height:100vh;">

    {{-- ── BARRA LATERAL IZQUIERDA ── --}}
    <div style="width:260px;background:#111111;border-right:1px solid #27272a;padding:24px;box-sizing:border-box;display:flex;flex-direction:column;gap:30px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <span style="color:#4ade80;font-size:24px;font-weight:bold;">⚡</span>
            <h2 style="margin:0;font-size:20px;color:#4ade80;letter-spacing:1px;">FisioGest</h2>
        </div>

        <nav style="display:flex;flex-direction:column;gap:8px;">
            <a href="/" style="display:flex;align-items:center;padding:12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:14px;">🏠 <span style="margin-left:10px;">Dashboard / Inicio</span></a>
            <a href="/citas" style="display:flex;align-items:center;padding:12px;color:#fff;background:#18181b;border-left:3px solid #22c55e;text-decoration:none;font-size:14px;font-weight:bold;">📅 <span style="margin-left:10px;">Citas</span></a>
            <a href="/pacientes" style="display:flex;align-items:center;padding:12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:14px;">👥 <span style="margin-left:10px;">Pacientes</span></a>
            <a href="/inventario" style="display:flex;align-items:center;padding:12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:14px;">📦 <span style="margin-left:10px;">Inventario</span></a>
            <a href="/fisioterapeutas" style="display:flex;align-items:center;padding:12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:14px;">👨‍⚕️ <span style="margin-left:10px;">Fisioterapeutas</span></a>
        </nav>
    </div>

    {{-- ── ÁREA DE CONTENIDO ── --}}
    <div style="flex:1;padding:40px;box-sizing:border-box;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <div>
                <h1 style="color:#4ade80;margin:0;">📅 Agenda de Citas</h1>
                <p style="color:#a1a1aa;margin:5px 0 0 0;">Gestión de turnos y atención de pacientes.</p>
            </div>
            <button onclick="abrirModalCita()" style="background:#22c55e;color:#000;border:none;padding:12px 20px;border-radius:6px;cursor:pointer;font-weight:bold;font-size:14px;">➕ Agendar Nueva Cita</button>
        </div>

        <hr style="border-color:#27272a; margin-bottom:30px;">

        @if(session('success'))
            <div style="background:#14532d;border:1px solid #22c55e;color:#4ade80;padding:12px 16px;border-radius:6px;margin-bottom:20px;">✅ {{ session('success') }}</div>
        @endif

        <h3 style="color:#4ade80;margin-bottom:14px;">📋 Historial y Próximos Turnos</h3>

        <div style="overflow-x:auto;border:1px solid #27272a;border-radius:8px;">
            <table style="width:100%;border-collapse:collapse;background:#18181b;">
                <thead>
                    <tr style="background:#27272a;color:#4ade80;text-align:left;">
                        <th style="padding:12px;">ID</th>
                        <th style="padding:12px;">Paciente</th>
                        <th style="padding:12px;">Fisioterapeuta</th>
                        <th style="padding:12px;">Fecha y Hora</th>
                        <th style="padding:12px;">Motivo</th>
                        <th style="padding:12px;">Estado</th>
                        <th style="padding:12px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @isset($citas)
                        @forelse($citas as $cita)
                            <tr style="border-bottom:1px solid #27272a;">
                                <td style="padding:12px;color:#71717a;">{{ $cita->cita_id }}</td>
                                <td style="padding:12px;font-weight:600;">{{ $cita->paciente_nombre ?? 'Desconocido' }}</td>
                                <td style="padding:12px;">{{ $cita->fisio_nombre ?? 'Sin asignar' }}</td>
                                <td style="padding:12px;color:#38bdf8;">{{ $cita->fecha_hora }}</td>
                                <td style="padding:12px;color:#e4e4e7;">{{ $cita->motivo ?? '—' }}</td>
                                <td style="padding:12px;">
                                    <span style="padding:4px 8px;border-radius:12px;font-size:12px;font-weight:bold;background:#1e3a8a;color:#93c5fd;">{{ ucfirst($cita->estado) }}</span>
                                </td>
                                <td style="padding:12px;">
                                    <form action="/citas/{{ $cita->cita_id }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background:#dc2626;color:#fff;border:none;padding:5px 10px;border-radius:4px;cursor:pointer;font-size:12px;">🗑 Cancelar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="padding:24px;text-align:center;color:#71717a;">No hay citas agendadas.</td></tr>
                        @endforelse
                    @endisset
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── MODAL DIRECTO INTEGRADO CON DESPLEGABLE FLEXIBLE ── --}}
    <div id="modalNuevaCita" style="position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: none; align-items: center; justify-content: center; z-index: 100;">
        <div style="background: #18181b; border: 1px solid #27272a; border-radius: 8px; width: 100%; max-width: 450px; box-sizing: border-box; overflow: hidden;">
            <div style="padding: 16px 24px; border-bottom: 1px solid #27272a; display: flex; justify-content: space-between; align-items: center; background: #111111;">
                <h3 style="margin: 0; font-size: 18px; font-weight: bold; color: #fff;">📅 Agendar Nueva Cita</h3>
                <button type="button" onclick="cerrarModalCita()" style="background: none; border: none; color: #a1a1aa; cursor: pointer; font-size: 24px; font-weight: bold;">&times;</button>
            </div>

            <form action="/citas" method="POST" style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
                @csrf
                <div>
                    <label style="display: block; margin-bottom: 5px; font-size: 13px; color: #a1a1aa;">Seleccionar Paciente *</label>
                    <select name="paciente_id" required style="width: 100%; padding: 8px; background: #27272a; border: 1px solid #3f3f46; color: #fff; border-radius: 4px; box-sizing: border-box;">
                        <option value="">-- Seleccione un paciente --</option>
                        @foreach($pacientes as $paciente)
                            <option value="{{ $paciente->paciente_id ?? $paciente->id }}">{{ $paciente->nombre }} {{ $paciente->apellido }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; font-size: 13px; color: #a1a1aa;">Asignar Fisioterapeuta *</label>
                    <select name="fisioterapeuta_id" required style="width: 100%; padding: 8px; background: #27272a; border: 1px solid #3f3f46; color: #fff; border-radius: 4px; box-sizing: border-box;">
                        <option value="">-- Seleccione un especialista --</option>
                        @foreach($fisioterapeutas as $ft)
                            <option value="{{ $ft->fisioterapeuta_id ?? $ft->id_fisioterapeuta ?? $ft->id }}">{{ $ft->nombre }} {{ $ft->apellido }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px; color: #a1a1aa;">Fecha *</label>
                        <input type="date" name="fecha" required style="width: 100%; padding: 8px; background: #27272a; border: 1px solid #3f3f46; color: #fff; border-radius: 4px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px; color: #a1a1aa;">Hora *</label>
                        <input type="time" name="hora" required style="width: 100%; padding: 8px; background: #27272a; border: 1px solid #3f3f46; color: #fff; border-radius: 4px; box-sizing: border-box;">
                    </div>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; font-size: 13px; color: #a1a1aa;">Motivo</label>
                    <textarea name="motivo" rows="3" placeholder="Ej. Terapia..." style="width: 100%; padding: 8px; background: #27272a; border: 1px solid #3f3f46; color: #fff; border-radius: 4px; resize: none; box-sizing: border-box;"></textarea>
                </div>

                <div style="padding-top: 14px; border-top: 1px solid #27272a; display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" onclick="cerrarModalCita()" style="background: #27272a; color: #e4e4e7; border: 1px solid #3f3f46; padding: 8px 16px; border-radius: 6px; cursor: pointer;">Cancelar</button>
                    <button type="submit" style="background: #22c55e; color: #000; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: bold;">Guardar Cita</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function abrirModalCita() { const m = document.getElementById('modalNuevaCita'); if(m) m.style.display = 'flex'; }
    function cerrarModalCita() { const m = document.getElementById('modalNuevaCita'); if(m) m.style.display = 'none'; }
    </script>
</body>
</html>