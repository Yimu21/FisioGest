<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas - FisioGest</title>
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
            <a href="/fisio/dashboard" style="display:flex;align-items:center;gap:10px;padding:10px 12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:14px;">🏠 <span>Dashboard</span></a>
            <a href="/fisio/pacientes" style="display:flex;align-items:center;gap:10px;padding:10px 12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:14px;">👥 <span>Pacientes</span></a>
            <a href="/fisio/asignaciones" style="display:flex;align-items:center;gap:10px;padding:10px 12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:14px;">📋 <span>Asignaciones</span></a>
            <a href="/fisio/citas" style="display:flex;align-items:center;gap:10px;padding:10px 12px;color:#fff;background:#18181b;border-left:3px solid #22c55e;text-decoration:none;font-size:14px;font-weight:600;border-radius:0 6px 6px 0;">📅 <span>Citas</span></a>
        </nav>

        <div style="margin-top:auto;display:flex;flex-direction:column;gap:4px;padding-top:20px;border-top:1px solid #27272a;">
            <a href="/" style="display:flex;align-items:center;gap:10px;padding:9px 12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:13px;">⬅ <span>Vista Admin</span></a>
            <a href="#" style="display:flex;align-items:center;gap:10px;padding:9px 12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:13px;">🚪 <span>Cerrar Sesión</span></a>
        </div>
    </div>

    {{-- ── ÁREA PRINCIPAL ── --}}
    <div style="flex:1;padding:32px;box-sizing:border-box;overflow-y:auto;">

        {{-- Header --}}
        <div style="margin-bottom:24px;">
            <h1 style="margin:0;font-size:22px;font-weight:700;color:#fff;">📅 Citas Registradas <span style="font-size:16px;color:#71717a;font-weight:400;">(Historial)</span></h1>
            <p style="margin:4px 0 0;font-size:13px;color:#a1a1aa;">Registro histórico de todas las citas brindadas por <strong style="color:#e4e4e7;">Dr. Alejandro Vargas</strong>.</p>
        </div>

        {{-- Filtros --}}
        <div style="display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap;">
            <select style="background:#18181b;border:1px solid #27272a;color:#a1a1aa;padding:8px 12px;border-radius:6px;font-size:13px;cursor:pointer;">
                <option>Filtrar por Paciente ▼</option>
                <option>Elena Rodriguez</option>
                <option>Carlos Pérez</option>
                <option>Diana Torres</option>
                <option>Creuss Rodriguez</option>
            </select>
            <select style="background:#18181b;border:1px solid #27272a;color:#a1a1aa;padding:8px 12px;border-radius:6px;font-size:13px;cursor:pointer;">
                <option>Filtrar por Fecha ▼</option>
                <option>Este mes</option>
                <option>Último mes</option>
                <option>Último trimestre</option>
                <option>Este año</option>
            </select>
            <select style="background:#18181b;border:1px solid #27272a;color:#a1a1aa;padding:8px 12px;border-radius:6px;font-size:13px;cursor:pointer;">
                <option>Filtrar por Terapeuta ▼</option>
                <option>Dr. Alejandro Vargas</option>
                <option>Dra. María Llynn</option>
            </select>
        </div>

        {{-- Tarjetas de citas --}}
        @php
            $citasDemo = [
                [
                    'paciente'   => 'Elena Rodriguez',
                    'pid'        => 'ID: ER0011',
                    'initials'   => 'ER',
                    'iColor'     => '#1e3a8a',
                    'fecha'      => '12 Nov 2023 (10:09h)',
                    'duracion'   => '45 min',
                    'terapeuta'  => 'Dr. Alejandro Vargas',
                    'estado'     => 'Completada',
                    'estadoBg'   => '#14532d',
                    'estadoText' => '#4ade80',
                    'diagnostico'=> 'Esguince LCL',
                    'notas'      => 'Paciente presenta mejoría notable en rango de movimiento. Se continúan ejercicios de fortalecimiento.',
                ],
                [
                    'paciente'   => 'Creuss Rodriguez',
                    'pid'        => 'ID: CR0015',
                    'initials'   => 'CR',
                    'iColor'     => '#4c1d95',
                    'fecha'      => '10 Nov 2023 (19:09h)',
                    'duracion'   => '45 min',
                    'terapeuta'  => 'Dr. Alejandro Vargas',
                    'estado'     => 'Rescheduled',
                    'estadoBg'   => '#78350f',
                    'estadoText' => '#fbbf24',
                    'diagnostico'=> 'Esguince LCL',
                    'notas'      => 'Cita reprogramada por solicitud del paciente. Pendiente confirmación de nueva fecha.',
                ],
                [
                    'paciente'   => 'Carlos Pérez',
                    'pid'        => 'ID: CP025',
                    'initials'   => 'CP',
                    'iColor'     => '#78350f',
                    'fecha'      => '12 Nov 2023 (10:09h)',
                    'duracion'   => '45 min',
                    'terapeuta'  => 'Dr. Alejandro Vargas',
                    'estado'     => 'Completada',
                    'estadoBg'   => '#14532d',
                    'estadoText' => '#4ade80',
                    'diagnostico'=> 'Esguince LCL',
                    'notas'      => 'Progreso satisfactorio. Se indica reducción de frecuencia de sesiones a 2 por semana.',
                ],
                [
                    'paciente'   => 'Carlos Pérez',
                    'pid'        => 'ID: CP025',
                    'initials'   => 'CP',
                    'iColor'     => '#78350f',
                    'fecha'      => '10 Nov 2023 (10:09h)',
                    'duracion'   => '45 min',
                    'terapeuta'  => 'Dr. Alejandro Vargas',
                    'estado'     => 'Rescheduled',
                    'estadoBg'   => '#78350f',
                    'estadoText' => '#fbbf24',
                    'diagnostico'=> 'Esguince LCL',
                    'notas'      => 'Sesión reprogramada. Paciente reporta molestia adicional en zona lumbar.',
                ],
                [
                    'paciente'   => 'Diana Torres',
                    'pid'        => 'ID: DT044',
                    'initials'   => 'DT',
                    'iColor'     => '#1e3a8a',
                    'fecha'      => '08 Nov 2023 (09:00h)',
                    'duracion'   => '60 min',
                    'terapeuta'  => 'Dr. Alejandro Vargas',
                    'estado'     => 'Completada',
                    'estadoBg'   => '#14532d',
                    'estadoText' => '#4ade80',
                    'diagnostico'=> 'Rehabilitación Deportiva',
                    'notas'      => 'Evaluación inicial. Se establece plan de tratamiento de 12 semanas.',
                ],
            ];
        @endphp

        <div style="display:flex;flex-direction:column;gap:14px;">

            {{-- Citas demo --}}
            @foreach($citasDemo as $c)
                <div style="background:#18181b;border:1px solid #27272a;border-radius:10px;padding:20px;display:flex;gap:20px;">

                    {{-- Avatar --}}
                    <div style="flex-shrink:0;">
                        <div style="width:50px;height:50px;background:{{ $c['iColor'] }};border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:16px;">{{ $c['initials'] }}</div>
                    </div>

                    {{-- Info izquierda --}}
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap;">
                            <p style="margin:0;font-size:14px;font-weight:700;color:#e4e4e7;">Paciente: {{ $c['paciente'] }} <span style="font-weight:400;color:#52525b;font-size:12px;">({{ $c['pid'] }})</span></p>
                        </div>
                        <p style="margin:0 0 4px;font-size:12px;color:#a1a1aa;">📅 Fecha de Cita: <span style="color:#38bdf8;">{{ $c['fecha'] }}</span></p>
                        <p style="margin:0 0 8px;font-size:12px;color:#a1a1aa;">👨‍⚕️ Terapeuta: <span style="color:#4ade80;">{{ $c['terapeuta'] }}</span></p>

                        {{-- Diagnóstico --}}
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
                            <span style="font-size:11px;color:#52525b;">Diagnóstico Asociado:</span>
                            <span style="background:#27272a;color:#a1a1aa;font-size:11px;padding:2px 8px;border-radius:4px;border:1px solid #3f3f46;">{{ $c['diagnostico'] }}</span>
                        </div>

                        {{-- Notas de evaluación --}}
                        <details style="cursor:pointer;">
                            <summary style="font-size:12px;color:#a1a1aa;list-style:none;display:flex;align-items:center;gap:4px;">
                                <span style="color:#52525b;">▶</span> Notas de Evaluación:
                                <span style="color:#71717a;font-size:11px;">+ ver</span>
                            </summary>
                            <p style="margin:6px 0 0 14px;font-size:12px;color:#71717a;line-height:1.5;">{{ $c['notas'] }}</p>
                        </details>
                    </div>

                    {{-- Info derecha --}}
                    <div style="flex-shrink:0;text-align:right;display:flex;flex-direction:column;align-items:flex-end;gap:10px;">
                        <span style="padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;background:{{ $c['estadoBg'] }};color:{{ $c['estadoText'] }};">{{ $c['estado'] }}</span>
                        <div>
                            <p style="margin:0;font-size:11px;color:#52525b;">Duración</p>
                            <p style="margin:2px 0 0;font-size:14px;font-weight:600;color:#e4e4e7;">{{ $c['duracion'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Citas reales de BD si existen --}}
            @isset($citas)
                @forelse($citas as $cita)
                    @php
                        $nombre = $cita->paciente_nombre ?? 'Paciente';
                        $initials = strtoupper(substr($nombre, 0, 2));
                        $estado = ucfirst($cita->estado ?? 'pendiente');
                        $esBg = $cita->estado === 'completada' ? '#14532d' : ($cita->estado === 'cancelada' ? '#7f1d1d' : '#1e3a8a');
                        $esTxt = $cita->estado === 'completada' ? '#4ade80' : ($cita->estado === 'cancelada' ? '#fca5a5' : '#93c5fd');
                    @endphp
                    <div style="background:#18181b;border:1px solid #27272a;border-radius:10px;padding:20px;display:flex;gap:20px;">
                        <div style="width:50px;height:50px;background:#27272a;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:#a1a1aa;font-size:16px;flex-shrink:0;">{{ $initials }}</div>
                        <div style="flex:1;">
                            <p style="margin:0 0 4px;font-size:14px;font-weight:700;color:#e4e4e7;">Paciente: {{ $nombre }} <span style="font-weight:400;color:#52525b;font-size:12px;">(ID: {{ $cita->paciente_id }})</span></p>
                            <p style="margin:0 0 4px;font-size:12px;color:#a1a1aa;">📅 Fecha: <span style="color:#38bdf8;">{{ $cita->fecha_hora }}</span></p>
                            <p style="margin:0;font-size:12px;color:#a1a1aa;">Motivo: {{ $cita->motivo ?? '—' }}</p>
                        </div>
                        <div style="flex-shrink:0;">
                            <span style="padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;background:{{ $esBg }};color:{{ $esTxt }};">{{ $estado }}</span>
                        </div>
                    </div>
                @empty
                @endforelse
            @endisset

        </div>
    </div>

</body>
</html>
