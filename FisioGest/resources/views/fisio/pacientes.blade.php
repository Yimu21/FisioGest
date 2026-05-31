<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pacientes - FisioGest</title>
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
            <a href="/fisio/pacientes" style="display:flex;align-items:center;gap:10px;padding:10px 12px;color:#fff;background:#18181b;border-left:3px solid #22c55e;text-decoration:none;font-size:14px;font-weight:600;border-radius:0 6px 6px 0;">👥 <span>Pacientes</span></a>
            <a href="/fisio/asignaciones" style="display:flex;align-items:center;gap:10px;padding:10px 12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:14px;">📋 <span>Asignaciones</span></a>
            <a href="/fisio/citas" style="display:flex;align-items:center;gap:10px;padding:10px 12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:14px;">📅 <span>Citas</span></a>
        </nav>

        <div style="margin-top:auto;display:flex;flex-direction:column;gap:4px;padding-top:20px;border-top:1px solid #27272a;">
            <a href="/" style="display:flex;align-items:center;gap:10px;padding:9px 12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:13px;">⬅ <span>Vista Admin</span></a>
            <a href="#" style="display:flex;align-items:center;gap:10px;padding:9px 12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:13px;">🚪 <span>Cerrar Sesión</span></a>
        </div>
    </div>

    {{-- ── CONTENIDO PRINCIPAL (dos paneles) ── --}}
    <div style="flex:1;display:flex;overflow:hidden;height:100vh;">

        {{-- ── PANEL IZQUIERDO: lista de pacientes ── --}}
        <div style="width:290px;background:#0f0f0f;border-right:1px solid #27272a;display:flex;flex-direction:column;overflow:hidden;">
            <div style="padding:18px 20px;border-bottom:1px solid #27272a;display:flex;justify-content:space-between;align-items:center;">
                <h2 style="margin:0;font-size:15px;color:#e4e4e7;font-weight:600;">Paciente</h2>
                <button onclick="document.getElementById('modalNuevoPaciente').style.display='flex'" style="background:#22c55e;color:#000;border:none;padding:5px 12px;border-radius:4px;cursor:pointer;font-weight:bold;font-size:12px;">+ Nuevo</button>
            </div>

            <div style="overflow-y:auto;flex:1;">
                @php
                    $demoPacientes = [
                        (object)['id'=>'ER001','nombre'=>'Elena','apellido'=>'Rodriguez','diagnostico'=>'Fractura de Rodilla LCL','fisio'=>'Dr. Alejandro Vargas','nacimiento'=>'23 de enero, 2003','genero'=>'Femenino','estado'=>'Activo','color'=>'#1e3a8a','initials'=>'ER'],
                        (object)['id'=>'CR002','nombre'=>'Creuss','apellido'=>'Rodriguez','diagnostico'=>'Traumatología','fisio'=>'Dr. Alejandro Vargas','nacimiento'=>'14 de marzo, 1995','genero'=>'Masculino','estado'=>'Activo','color'=>'#4c1d95','initials'=>'CR'],
                        (object)['id'=>'CP025','nombre'=>'Carlos','apellido'=>'Pérez','diagnostico'=>'Esguince LCL','fisio'=>'Dr. Alejandro Vargas','nacimiento'=>'08 de junio, 1988','genero'=>'Masculino','estado'=>'Activo','color'=>'#78350f','initials'=>'CP'],
                        (object)['id'=>'DT044','nombre'=>'Diana','apellido'=>'Torres','diagnostico'=>'Rehabilitación Deportiva','fisio'=>'Dr. Alejandro Vargas','nacimiento'=>'30 de septiembre, 2001','genero'=>'Femenino','estado'=>'En pausa','color'=>'#1e3a8a','initials'=>'DT'],
                    ];
                    $selectedId = request()->query('id', 'ER001');
                @endphp

                @foreach($demoPacientes as $p)
                    @php $isActive = $p->id === $selectedId; @endphp
                    <a href="/fisio/pacientes?id={{ $p->id }}" style="display:block;padding:14px 20px;border-bottom:1px solid #1a1a1c;text-decoration:none;{{ $isActive ? 'background:#18181b;border-left:3px solid #22c55e;' : '' }}transition:background .15s;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:38px;height:38px;background:{{ $p->color }};border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:bold;color:#fff;flex-shrink:0;">{{ $p->initials }}</div>
                            <div style="min-width:0;">
                                <p style="margin:0;font-size:13px;font-weight:600;color:{{ $isActive ? '#fff' : '#a1a1aa' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $p->nombre }} {{ $p->apellido }}</p>
                                <p style="margin:0;font-size:11px;color:#52525b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $p->diagnostico }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach

                @isset($pacientes)
                    @foreach($pacientes as $p)
                        @php
                            $initials = strtoupper(substr($p->nombre ?? 'P', 0, 1) . substr($p->apellido ?? 'A', 0, 1));
                            $pid = $p->paciente_id ?? $p->id ?? 'DB';
                            $isActive = (string)$pid === (string)$selectedId;
                        @endphp
                        <a href="/fisio/pacientes?id={{ $pid }}" style="display:block;padding:14px 20px;border-bottom:1px solid #1a1a1c;text-decoration:none;{{ $isActive ? 'background:#18181b;border-left:3px solid #22c55e;' : '' }}">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:38px;height:38px;background:#27272a;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:bold;color:#a1a1aa;flex-shrink:0;">{{ $initials }}</div>
                                <div style="min-width:0;">
                                    <p style="margin:0;font-size:13px;font-weight:600;color:{{ $isActive ? '#fff' : '#a1a1aa' }};">{{ $p->nombre }} {{ $p->apellido }}</p>
                                    <p style="margin:0;font-size:11px;color:#52525b;">ID: {{ $pid }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                @endisset
            </div>
        </div>

        {{-- ── PANEL DERECHO: detalle del paciente seleccionado ── --}}
        <div style="flex:1;overflow-y:auto;padding:28px;background:#0a0a0a;">
            @php
                $pSeleccionado = collect($demoPacientes)->firstWhere('id', $selectedId);
                $sesiones = [
                    ['fecha'=>'31 dic 2023','color'=>'#ffffff','colorLabel'=>'WHITE (#FFFFFF)','notas'=>'Evaluación inicial completada. Movilidad reducida en articulación derecha.'],
                    ['fecha'=>'17 feb 2023','color'=>'#000000','colorLabel'=>'BLACK (#000000)','notas'=>'Progreso notable en rango de movimiento. Continuar ejercicios de fortalecimiento.'],
                    ['fecha'=>'15 mar 2023','color'=>'#4ade80','colorLabel'=>'GREEN (#4ADE80)','notas'=>'Alta movilidad recuperada. Reducción de inflamación.'],
                    ['fecha'=>'10 abr 2023','color'=>'#38bdf8','colorLabel'=>'BLUE (#38BDF8)','notas'=>'Sesión de mantenimiento. Paciente refiere mejoría significativa.'],
                ];
                $asignEquipos = [
                    ['nombre'=>'Prótesis de Rodilla A1','id'=>'ID: PDA-001','dosis'=>'Dr#: 2','alerta'=>true,'alertaMsg'=>'Pedir Más'],
                    ['nombre'=>'Cisco Elástico Roja','id'=>'ID: CER-003','dosis'=>'Dr#: 3','alerta'=>false,'alertaMsg'=>'Agregar Nuevo Equipo'],
                ];
            @endphp

            @if($pSeleccionado)
                {{-- Aviso de solo lectura --}}
                <div style="background:#1c1c1e;border:1px solid #27272a;border-radius:6px;padding:10px 14px;margin-bottom:22px;display:flex;align-items:center;gap:8px;">
                    <span style="font-size:14px;">🔒</span>
                    <p style="margin:0;font-size:12px;color:#71717a;">Vista de solo lectura — no modificable.</p>
                </div>

                {{-- Header del paciente --}}
                <div style="background:#18181b;border:1px solid #27272a;border-radius:10px;padding:24px;margin-bottom:22px;">
                    <div style="display:flex;align-items:flex-start;gap:20px;">
                        <div style="width:72px;height:72px;background:{{ $pSeleccionado->color }};border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:700;color:#fff;flex-shrink:0;">{{ $pSeleccionado->initials }}</div>
                        <div style="flex:1;">
                            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px;">
                                <h2 style="margin:0;font-size:20px;font-weight:700;color:#fff;">{{ $pSeleccionado->nombre }} {{ $pSeleccionado->apellido }}</h2>
                                <span style="padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;{{ $pSeleccionado->estado === 'Activo' ? 'background:#14532d;color:#4ade80;' : 'background:#78350f;color:#fbbf24;' }}">{{ $pSeleccionado->estado }}</span>
                            </div>
                            <p style="margin:0 0 12px;font-size:13px;color:#a1a1aa;">Fisioterapia de Alejandro Vargas</p>

                            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                                <div>
                                    <p style="margin:0;font-size:11px;color:#52525b;">Nombre Completo</p>
                                    <p style="margin:2px 0 0;font-size:13px;color:#e4e4e7;">{{ $pSeleccionado->nombre }} {{ $pSeleccionado->apellido }}</p>
                                </div>
                                <div>
                                    <p style="margin:0;font-size:11px;color:#52525b;">Género</p>
                                    <p style="margin:2px 0 0;font-size:13px;color:#e4e4e7;">{{ $pSeleccionado->genero }}</p>
                                </div>
                                <div>
                                    <p style="margin:0;font-size:11px;color:#52525b;">Nacimiento</p>
                                    <p style="margin:2px 0 0;font-size:13px;color:#e4e4e7;">{{ $pSeleccionado->nacimiento }}</p>
                                </div>
                                <div>
                                    <p style="margin:0;font-size:11px;color:#52525b;">Fisioterapeuta</p>
                                    <p style="margin:2px 0 0;font-size:13px;color:#4ade80;">{{ $pSeleccionado->fisio }}</p>
                                </div>
                                <div>
                                    <p style="margin:0;font-size:11px;color:#52525b;">Diagnóstico</p>
                                    <p style="margin:2px 0 0;font-size:13px;color:#e4e4e7;">{{ $pSeleccionado->diagnostico }}</p>
                                </div>
                                <div>
                                    <p style="margin:0;font-size:11px;color:#52525b;">ID Paciente</p>
                                    <p style="margin:2px 0 0;font-size:13px;color:#a1a1aa;font-family:monospace;">{{ $pSeleccionado->id }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cuerpo: historial + equipos --}}
                <div style="display:grid;grid-template-columns:1fr 280px;gap:20px;">

                    {{-- Historial de sesiones --}}
                    <div style="background:#18181b;border:1px solid #27272a;border-radius:10px;overflow:hidden;">
                        <div style="padding:16px 20px;border-bottom:1px solid #27272a;">
                            <h3 style="margin:0;font-size:15px;font-weight:600;color:#e4e4e7;">Historial de Sesiones de Terapia</h3>
                        </div>
                        <div style="padding:16px 20px;display:flex;flex-direction:column;gap:12px;">
                            @foreach($sesiones as $s)
                                <div style="display:flex;align-items:flex-start;gap:14px;">
                                    <div style="display:flex;flex-direction:column;align-items:center;gap:4px;flex-shrink:0;">
                                        <p style="margin:0;font-size:11px;color:#71717a;white-space:nowrap;">{{ $s['fecha'] }}</p>
                                        <div style="width:40px;height:40px;background:{{ $s['color'] }};border-radius:6px;border:1px solid #3f3f46;flex-shrink:0;"></div>
                                        <p style="margin:0;font-size:9px;color:#52525b;text-align:center;max-width:60px;line-height:1.3;">{{ $s['colorLabel'] }}</p>
                                    </div>
                                    <div style="flex:1;background:#111111;border-radius:6px;padding:10px 12px;border:1px solid #27272a;">
                                        <p style="margin:0;font-size:12px;color:#a1a1aa;line-height:1.5;">{{ $s['notas'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Asignaciones de equipo --}}
                    <div style="display:flex;flex-direction:column;gap:14px;">
                        <div style="background:#18181b;border:1px solid #27272a;border-radius:10px;overflow:hidden;">
                            <div style="padding:14px 16px;border-bottom:1px solid #27272a;">
                                <h3 style="margin:0;font-size:14px;font-weight:600;color:#e4e4e7;">Asignaciones de Equipo</h3>
                                <p style="margin:2px 0 0;font-size:11px;color:#52525b;">Equipo en un equipo activo.</p>
                            </div>
                            <div style="padding:14px 16px;display:flex;flex-direction:column;gap:10px;">
                                @foreach($asignEquipos as $eq)
                                    <div style="background:#111111;border:1px solid #27272a;border-radius:8px;padding:12px;">
                                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                            <span style="font-size:18px;">🦿</span>
                                            <div>
                                                <p style="margin:0;font-size:12px;font-weight:600;color:#e4e4e7;">{{ $eq['nombre'] }}</p>
                                                <p style="margin:0;font-size:10px;color:#52525b;">{{ $eq['id'] }} &nbsp;·&nbsp; {{ $eq['dosis'] }}</p>
                                            </div>
                                        </div>
                                        @if($eq['alerta'])
                                            <button style="width:100%;background:#78350f;border:1px solid #f59e0b;color:#fbbf24;padding:5px;border-radius:4px;cursor:pointer;font-size:11px;font-weight:600;">⚠ {{ $eq['alertaMsg'] }}</button>
                                        @else
                                            <button style="width:100%;background:#18181b;border:1px solid #27272a;color:#a1a1aa;padding:5px;border-radius:4px;cursor:pointer;font-size:11px;">+ {{ $eq['alertaMsg'] }}</button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>

            @else
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:70vh;color:#52525b;text-align:center;">
                    <p style="font-size:48px;margin:0;">👥</p>
                    <p style="font-size:16px;margin:12px 0 4px;color:#71717a;">Selecciona un paciente</p>
                    <p style="font-size:13px;margin:0;color:#52525b;">Haz clic en un paciente de la lista para ver su historial.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ── MODAL NUEVO PACIENTE ── --}}
    <div id="modalNuevoPaciente" style="position:fixed;inset:0;background:rgba(0,0,0,.75);display:none;align-items:center;justify-content:center;z-index:100;">
        <div style="background:#18181b;border:1px solid #27272a;border-radius:8px;width:100%;max-width:480px;overflow:hidden;">
            <div style="padding:16px 24px;border-bottom:1px solid #27272a;display:flex;justify-content:space-between;align-items:center;background:#111111;">
                <h3 style="margin:0;font-size:17px;font-weight:700;color:#fff;">👥 Registrar Nuevo Paciente</h3>
                <button onclick="document.getElementById('modalNuevoPaciente').style.display='none'" style="background:none;border:none;color:#a1a1aa;cursor:pointer;font-size:22px;font-weight:bold;">&times;</button>
            </div>
            <form action="/pacientes" method="POST" style="padding:24px;display:flex;flex-direction:column;gap:14px;">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Nombre *</label>
                        <input type="text" name="nombre" required style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Apellido *</label>
                        <input type="text" name="apellido" required style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Fecha Nacimiento *</label>
                        <input type="date" name="fecha_nacimiento" required style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Género *</label>
                        <select name="genero" required style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                            <option value="femenino">Femenino</option>
                            <option value="masculino">Masculino</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Teléfono</label>
                    <input type="text" name="telefono" style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                </div>
                <div style="padding-top:12px;border-top:1px solid #27272a;display:flex;justify-content:flex-end;gap:10px;">
                    <button type="button" onclick="document.getElementById('modalNuevoPaciente').style.display='none'" style="background:#27272a;color:#e4e4e7;border:1px solid #3f3f46;padding:8px 16px;border-radius:6px;cursor:pointer;">Cancelar</button>
                    <button type="submit" style="background:#22c55e;color:#000;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-weight:bold;">Guardar</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
