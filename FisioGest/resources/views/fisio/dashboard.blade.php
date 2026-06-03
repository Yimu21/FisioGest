<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FisioGest</title>
</head>
<body style="margin:0;padding:0;background:#0a0a0a;color:#fff;font-family:sans-serif;display:flex;min-height:100vh;">

    {{-- ── BARRA LATERAL ── --}}
    <div style="width:260px;background:#111111;border-right:1px solid #27272a;padding:24px;box-sizing:border-box;display:flex;flex-direction:column;gap:24px;min-height:100vh;position:sticky;top:0;height:100vh;">
        <div style="display:flex;align-items:center;gap:10px;">
            <span style="color:#4ade80;font-size:24px;font-weight:bold;">⚡</span>
            <h2 style="margin:0;font-size:20px;color:#4ade80;letter-spacing:1px;">FisioGest</h2>
        </div>

        <div style="background:#18181b;padding:12px;border-radius:8px;border:1px solid #27272a;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:38px;height:38px;background:#14532d;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;color:#4ade80;font-size:13px;flex-shrink:0;">AV</div>
                <div>
                    <p style="margin:0;font-size:13px;font-weight:600;color:#fff;">Dr. Alejandro Vargas</p>
                    <p style="margin:0;font-size:11px;color:#a1a1aa;">Fisioterapeuta</p>
                </div>
            </div>
        </div>

        <nav style="display:flex;flex-direction:column;gap:6px;">
            <a href="/fisio/dashboard" style="display:flex;align-items:center;gap:10px;padding:10px 12px;color:#fff;background:#18181b;border-left:3px solid #22c55e;text-decoration:none;font-size:14px;font-weight:600;border-radius:0 6px 6px 0;">🏠 <span>Dashboard</span></a>
            <a href="/fisio/pacientes" style="display:flex;align-items:center;gap:10px;padding:10px 12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:14px;">👥 <span>Pacientes</span></a>
            <a href="/fisio/asignaciones" style="display:flex;align-items:center;gap:10px;padding:10px 12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:14px;">📋 <span>Asignaciones</span></a>
            <a href="/fisio/citas" style="display:flex;align-items:center;gap:10px;padding:10px 12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:14px;">📅 <span>Citas</span></a>
        </nav>

        <div style="margin-top:auto;display:flex;flex-direction:column;gap:4px;padding-top:20px;border-top:1px solid #27272a;">
            <a href="/" style="display:flex;align-items:center;gap:10px;padding:9px 12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:13px;">⬅ <span>Vista Admin</span></a>
            <a href="#" style="display:flex;align-items:center;gap:10px;padding:9px 12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:13px;">🚪 <span>Cerrar Sesión</span></a>
        </div>
    </div>

    {{-- ── ÁREA PRINCIPAL ── --}}
    <div style="flex:1;padding:32px;box-sizing:border-box;overflow-y:auto;">

        {{-- Header --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
            <div>
                <h1 style="margin:0;font-size:22px;font-weight:700;color:#fff;">Fisioterapeuta Dashboard <span style="color:#4ade80;">({{ $startOfWeek->format('M Y') }})</span></h1>
                <p style="margin:4px 0 0;font-size:13px;color:#a1a1aa;">Nombre del Fisioterapeuta: <strong style="color:#e4e4e7;">Dr. Alejandro Vargas</strong></p>
            </div>
            <button onclick="document.getElementById('modalNuevaSesion').style.display='flex'" style="background:#22c55e;color:#000;border:none;padding:10px 20px;border-radius:6px;cursor:pointer;font-weight:bold;font-size:14px;">+ Nueva Sesión</button>
        </div>

        {{-- Resumen rápido --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:28px;">
            @php
                $stats = [
                    ['label' => 'Citas esta semana',   'value' => (string) $citas->count(),                                 'icon' => '📅', 'color' => '#4ade80'],
                    ['label' => 'Pacientes activos',    'value' => (string) $citas->pluck('paciente_id')->unique()->count(), 'icon' => '👥', 'color' => '#38bdf8'],
                    ['label' => 'Sesiones completadas', 'value' => (string) $citas->where('estado', 'atendida')->count(),   'icon' => '✅', 'color' => '#a78bfa'],
                    ['label' => 'Pendientes hoy',       'value' => (string) $pendientesHoy,                                  'icon' => '⏳', 'color' => '#fbbf24'],
                ];
            @endphp
            @foreach($stats as $s)
                <div style="background:#18181b;border:1px solid #27272a;border-radius:8px;padding:18px;">
                    <p style="margin:0;font-size:22px;">{{ $s['icon'] }}</p>
                    <p style="margin:8px 0 2px;font-size:26px;font-weight:700;color:{{ $s['color'] }};">{{ $s['value'] }}</p>
                    <p style="margin:0;font-size:12px;color:#71717a;">{{ $s['label'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Agenda Semanal --}}
        <div style="background:#18181b;border:1px solid #27272a;border-radius:10px;overflow:hidden;">
            <div style="padding:16px 20px;border-bottom:1px solid #27272a;display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <h2 style="margin:0;font-size:16px;font-weight:600;color:#e4e4e7;">Weekly Agenda</h2>
                    <p style="margin:2px 0 0;font-size:12px;color:#71717a;">Semana del {{ $startOfWeek->format('d M') }} al {{ $startOfWeek->copy()->addDays(6)->format('d M, Y') }}</p>
                </div>
                <div style="display:flex;gap:10px;">
                    <a href="/fisio/dashboard?week={{ $weekOffset - 1 }}" style="background:#27272a;border:1px solid #3f3f46;color:#a1a1aa;padding:5px 12px;border-radius:4px;cursor:pointer;font-size:13px;text-decoration:none;display:inline-block;">‹ Anterior</a>
                    <a href="/fisio/dashboard?week={{ $weekOffset + 1 }}" style="background:#27272a;border:1px solid #3f3f46;color:#a1a1aa;padding:5px 12px;border-radius:4px;cursor:pointer;font-size:13px;text-decoration:none;display:inline-block;">Siguiente ›</a>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;table-layout:fixed;min-width:820px;">
                    <thead>
                        <tr style="background:#111111;">
                            <th style="width:62px;padding:10px 6px;font-size:11px;color:#71717a;text-align:center;border-right:1px solid #27272a;border-bottom:1px solid #27272a;font-weight:500;">Hora</th>
                            @php
                            $dayLabels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
                            $dayKeys   = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
                            $days = [];
                            for ($i = 0; $i < 7; $i++) {
                                $d = $startOfWeek->copy()->addDays($i);
                                $days[] = ['label' => $dayLabels[$i] . ' ' . $d->format('d'), 'key' => $dayKeys[$i]];
                            }

                            $estadoColor = ['programada'=>'blue','atendida'=>'green','cancelada'=>'red','reprogramada'=>'yellow'];
                            $estadoLabel = ['programada'=>'Sesión de Terapia','atendida'=>'Completada','cancelada'=>'Cancelada','reprogramada'=>'Reprogramada'];

                            $agenda = array_fill_keys($dayKeys, []);
                            foreach ($citas as $cita) {
                                $dt  = \Carbon\Carbon::parse($cita->fecha_hora);
                                $idx = $dt->dayOfWeekIso - 1;
                                if ($idx < 0 || $idx > 6) continue;
                                $key = $dayKeys[$idx];
                                $min = (int) $dt->format('i');
                                $slot = $dt->format('H') . ':' . ($min < 30 ? '00' : '30');
                                $agenda[$key][$slot][] = [
                                    'patient' => $cita->nombre . ' ' . $cita->apellido,
                                    'type'    => $estadoLabel[$cita->estado] ?? ucfirst($cita->estado),
                                    'color'   => $estadoColor[$cita->estado] ?? 'blue',
                                    'time'    => $dt->format('H:i'),
                                ];
                            }

                            $colorMap = [
                                'blue'   => ['bg'=>'#1e3a8a','border'=>'#3b82f6','text'=>'#93c5fd','sub'=>'#7dd3fc'],
                                'yellow' => ['bg'=>'#78350f','border'=>'#f59e0b','text'=>'#fbbf24','sub'=>'#fde68a'],
                                'green'  => ['bg'=>'#14532d','border'=>'#22c55e','text'=>'#4ade80','sub'=>'#86efac'],
                                'red'    => ['bg'=>'#7f1d1d','border'=>'#dc2626','text'=>'#fca5a5','sub'=>'#fecaca'],
                            ];
                            $slots = ['08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30'];
                        @endphp
                            @foreach($days as $d)
                                @php $isToday = ($d['label'] === $dayLabels[array_search($d['key'], $dayKeys)] . ' ' . now()->format('d')) && $startOfWeek->copy()->addDays(array_search($d['key'], $dayKeys))->isSameDay(now()); @endphp
                                <th style="padding:10px 6px;font-size:12px;text-align:center;border-bottom:1px solid #27272a;border-right:1px solid #27272a;font-weight:600;{{ $isToday ? 'color:#4ade80;background:#0d2d1a;' : 'color:#a1a1aa;' }}">
                                    {{ $d['label'] }}{{ $isToday ? ' ●' : '' }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($slots as $slot)
                            <tr style="border-bottom:1px solid #1a1a1c;">
                                <td style="padding:4px 6px;font-size:10px;color:#52525b;text-align:center;border-right:1px solid #27272a;vertical-align:top;white-space:nowrap;">{{ $slot }}</td>
                                @foreach($days as $d)
                                    @php $key = $d['key']; @endphp
                                    <td style="padding:2px 3px;border-right:1px solid #1a1a1c;vertical-align:top;">
                                        @if(isset($agenda[$key][$slot]))
                                            @foreach($agenda[$key][$slot] as $appt)
                                                @php $c = $colorMap[$appt['color']]; @endphp
                                                <div style="background:{{ $c['bg'] }};border-left:3px solid {{ $c['border'] }};border-radius:3px;padding:3px 5px;margin-bottom:2px;cursor:pointer;">
                                                    <p style="margin:0;font-weight:600;color:{{ $c['text'] }};font-size:10px;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $appt['patient'] }}</p>
                                                    <p style="margin:0;color:{{ $c['sub'] }};font-size:9px;line-height:1.3;">{{ $appt['time'] }} · {{ $appt['type'] }}</p>
                                                </div>
                                            @endforeach
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($citas->isEmpty())
            <div style="text-align:center;padding:40px 20px;color:#52525b;">
                <p style="font-size:32px;margin:0 0 10px;">📅</p>
                <p style="font-size:14px;margin:0;color:#71717a;">No hay citas programadas para esta semana</p>
            </div>
            @endif
        </div>

        {{-- Leyenda --}}
        <div style="margin-top:14px;display:flex;gap:18px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#71717a;"><span style="width:12px;height:12px;background:#1e3a8a;border-left:3px solid #3b82f6;border-radius:2px;display:inline-block;"></span> Sesión de Terapia</div>
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#71717a;"><span style="width:12px;height:12px;background:#78350f;border-left:3px solid #f59e0b;border-radius:2px;display:inline-block;"></span> Reprogramada</div>
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#71717a;"><span style="width:12px;height:12px;background:#14532d;border-left:3px solid #22c55e;border-radius:2px;display:inline-block;"></span> Completada</div>
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#71717a;"><span style="width:12px;height:12px;background:#7f1d1d;border-left:3px solid #dc2626;border-radius:2px;display:inline-block;"></span> Cancelada</div>
        </div>
    </div>

    {{-- ── MODAL NUEVA SESIÓN ── --}}
    <div id="modalNuevaSesion" style="position:fixed;inset:0;background:rgba(0,0,0,.75);display:none;align-items:center;justify-content:center;z-index:100;">
        <div style="background:#18181b;border:1px solid #27272a;border-radius:8px;width:100%;max-width:440px;overflow:hidden;">
            <div style="padding:16px 24px;border-bottom:1px solid #27272a;display:flex;justify-content:space-between;align-items:center;background:#111111;">
                <h3 style="margin:0;font-size:17px;font-weight:700;color:#fff;">📝 Registrar Nueva Sesión</h3>
                <button onclick="document.getElementById('modalNuevaSesion').style.display='none'" style="background:none;border:none;color:#a1a1aa;cursor:pointer;font-size:22px;font-weight:bold;">&times;</button>
            </div>
            <form action="/fisio/sesiones" method="POST" style="padding:24px;display:flex;flex-direction:column;gap:14px;">
                @csrf
                <div>
                    <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Paciente *</label>
                    <select name="paciente_id" required style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                        <option value="">-- Seleccione un paciente --</option>
                        @isset($pacientes)
                            @foreach($pacientes as $p)
                                <option value="{{ $p->paciente_id ?? $p->id }}">{{ $p->nombre }} {{ $p->apellido }}</option>
                            @endforeach
                        @endisset
                        <option value="d1">Elena Rodriguez</option>
                        <option value="d2">Carlos Pérez</option>
                        <option value="d3">Diana Torres</option>
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Fecha *</label>
                        <input type="date" name="fecha" required style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Hora *</label>
                        <input type="time" name="hora" required style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                    </div>
                </div>
                <div>
                    <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Tipo de Sesión</label>
                    <select name="tipo" style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                        <option value="terapia">Sesión de Terapia</option>
                        <option value="evaluacion">Evaluación Inicial</option>
                        <option value="seguimiento">Seguimiento</option>
                        <option value="alta">Alta Clínica</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Notas de la Sesión</label>
                    <textarea name="notas" rows="3" placeholder="Observaciones y avances..." style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;resize:none;box-sizing:border-box;"></textarea>
                </div>
                <div style="padding-top:12px;border-top:1px solid #27272a;display:flex;justify-content:flex-end;gap:10px;">
                    <button type="button" onclick="document.getElementById('modalNuevaSesion').style.display='none'" style="background:#27272a;color:#e4e4e7;border:1px solid #3f3f46;padding:8px 16px;border-radius:6px;cursor:pointer;">Cancelar</button>
                    <button type="submit" style="background:#22c55e;color:#000;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-weight:bold;">Guardar Sesión</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
