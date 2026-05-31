<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignaciones - FisioGest</title>
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
            <a href="/fisio/asignaciones" style="display:flex;align-items:center;gap:10px;padding:10px 12px;color:#fff;background:#18181b;border-left:3px solid #22c55e;text-decoration:none;font-size:14px;font-weight:600;border-radius:0 6px 6px 0;">📋 <span>Asignaciones</span></a>
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
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <div>
                <h1 style="margin:0;font-size:22px;font-weight:700;color:#fff;">📋 Asignaciones de Equipo <span style="color:#4ade80;">(May 2026)</span></h1>
                <p style="margin:4px 0 0;font-size:13px;color:#a1a1aa;">Equipos clínicos asignados a tus pacientes activos.</p>
            </div>
            <button onclick="document.getElementById('modalNuevaAsignacion').style.display='flex'" style="background:#22c55e;color:#000;border:none;padding:10px 20px;border-radius:6px;cursor:pointer;font-weight:bold;font-size:14px;">+ Nueva Asignación</button>
        </div>

        {{-- Filtros --}}
        <div style="display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap;">
            <select style="background:#18181b;border:1px solid #27272a;color:#a1a1aa;padding:8px 12px;border-radius:6px;font-size:13px;cursor:pointer;">
                <option>Filter by Equipo ▼</option>
                <option>Prótesis Dinámica</option>
                <option>Órtesis</option>
                <option>Cisco Elástico</option>
                <option>Columna TLSO</option>
            </select>
            <select style="background:#18181b;border:1px solid #27272a;color:#a1a1aa;padding:8px 12px;border-radius:6px;font-size:13px;cursor:pointer;">
                <option>Filter by Paciente ▼</option>
                <option>Elena Rodriguez</option>
                <option>Carlos Pérez</option>
                <option>Diana Torres</option>
            </select>
            <select style="background:#18181b;border:1px solid #27272a;color:#a1a1aa;padding:8px 12px;border-radius:6px;font-size:13px;cursor:pointer;">
                <option>Filter by Estado ▼</option>
                <option>En Uso (Activo)</option>
                <option>Terminado (Done)</option>
                <option>Mantenimiento Pendiente</option>
            </select>
        </div>

        {{-- Tarjetas de asignaciones --}}
        @php
            $asignaciones = [
                [
                    'equipo'   => 'Prótesis Dinámica de Miembro Inferior',
                    'icon'     => '🦿',
                    'paciente' => 'Elena Rodriguez',
                    'pid'      => 'ID: ER001',
                    'inicio'   => '15 Oct 2023',
                    'fin'      => '15 Oct 2025',
                    'duracion' => '15 Oct 2023 – 15 Oct 2025',
                    'tiempo'   => '31 días',
                    'terapeuta'=> 'Dr. Alejandro Vargas',
                    'estado'   => 'En Uso',
                    'estadoColor' => ['bg'=>'#14532d','text'=>'#4ade80','dot'=>'#22c55e'],
                ],
                [
                    'equipo'   => 'Órtesis Dinámica de Mano Muñeca',
                    'icon'     => '🖐',
                    'paciente' => 'Carlos Pérez',
                    'pid'      => 'ID: CP025',
                    'inicio'   => '15 Nov 2023',
                    'fin'      => '25 Nov 2023',
                    'duracion' => '15 Nov 2023 – 25 Nov 2023',
                    'tiempo'   => '7 días',
                    'terapeuta'=> 'Dra. María Llynn',
                    'estado'   => 'Terminado',
                    'estadoColor' => ['bg'=>'#78350f','text'=>'#fbbf24','dot'=>'#f59e0b'],
                ],
                [
                    'equipo'   => 'Órtesis Modular KAFO de Fibra de C.',
                    'icon'     => '🦵',
                    'paciente' => 'Carlos Pérez',
                    'pid'      => 'ID: CP025',
                    'inicio'   => '10 Nov 2023',
                    'fin'      => '20 Nov 2023',
                    'duracion' => '10 Nov 2023 – 20 Nov 2023',
                    'tiempo'   => '7 días',
                    'terapeuta'=> 'Dr. Alejandro Vargas',
                    'estado'   => 'Mantenimiento Pendiente',
                    'estadoColor' => ['bg'=>'#1e3a8a','text'=>'#93c5fd','dot'=>'#3b82f6'],
                ],
                [
                    'equipo'   => 'Órtesis de Columna TLSO Personaliz.',
                    'icon'     => '🩺',
                    'paciente' => 'Carlos Pérez',
                    'pid'      => 'ID: CP025',
                    'inicio'   => '10 Nov 2023',
                    'fin'      => '20 Nov 2023',
                    'duracion' => '10 Nov 2023 – 20 Nov 2023',
                    'tiempo'   => '7 días',
                    'terapeuta'=> 'Dr. Alejandro Vargas',
                    'estado'   => 'Mantenimiento Pendiente',
                    'estadoColor' => ['bg'=>'#1e3a8a','text'=>'#93c5fd','dot'=>'#3b82f6'],
                ],
            ];
        @endphp

        <div style="display:flex;flex-direction:column;gap:14px;">
            @foreach($asignaciones as $a)
                <div style="background:#18181b;border:1px solid #27272a;border-radius:10px;padding:20px;display:flex;align-items:center;gap:20px;">

                    {{-- Icono del equipo --}}
                    <div style="width:56px;height:56px;background:#27272a;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:26px;flex-shrink:0;border:1px solid #3f3f46;">
                        {{ $a['icon'] }}
                    </div>

                    {{-- Info equipo --}}
                    <div style="flex:1;min-width:0;">
                        <p style="margin:0 0 4px;font-size:14px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Equipo: {{ $a['equipo'] }}</p>
                        <p style="margin:0 0 2px;font-size:12px;color:#a1a1aa;">Paciente: <span style="color:#e4e4e7;">{{ $a['paciente'] }}</span> <span style="color:#52525b;">({{ $a['pid'] }})</span></p>
                        <p style="margin:0;font-size:12px;color:#a1a1aa;">Terapeuta: <span style="color:#4ade80;">{{ $a['terapeuta'] }}</span></p>
                    </div>

                    {{-- Duración --}}
                    <div style="min-width:180px;flex-shrink:0;">
                        <p style="margin:0 0 2px;font-size:11px;color:#52525b;">Duración</p>
                        <p style="margin:0;font-size:12px;color:#e4e4e7;">{{ $a['duracion'] }}</p>
                    </div>

                    {{-- Tiempo restante --}}
                    <div style="min-width:100px;flex-shrink:0;">
                        <p style="margin:0 0 2px;font-size:11px;color:#52525b;">Tiempo Restante</p>
                        <p style="margin:0;font-size:13px;font-weight:600;color:#38bdf8;">{{ $a['tiempo'] }}</p>
                    </div>

                    {{-- Estado --}}
                    <div style="flex-shrink:0;">
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:20px;font-size:11px;font-weight:600;background:{{ $a['estadoColor']['bg'] }};color:{{ $a['estadoColor']['text'] }};">
                            <span style="width:7px;height:7px;background:{{ $a['estadoColor']['dot'] }};border-radius:50%;display:inline-block;"></span>
                            {{ $a['estado'] }}
                        </span>
                    </div>
                </div>
            @endforeach

            {{-- Asignaciones reales de BD si existen --}}
            @isset($items)
                @forelse($items as $item)
                    <div style="background:#18181b;border:1px solid #27272a;border-radius:10px;padding:20px;display:flex;align-items:center;gap:20px;">
                        <div style="width:56px;height:56px;background:#27272a;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:26px;flex-shrink:0;border:1px solid #3f3f46;">🩺</div>
                        <div style="flex:1;">
                            <p style="margin:0 0 4px;font-size:14px;font-weight:600;color:#fff;">{{ $item->nombre }}</p>
                            <p style="margin:0;font-size:12px;color:#a1a1aa;">Tipo: {{ $item->tipo ?? '—' }} · Cantidad: {{ $item->cantidad ?? '—' }}</p>
                        </div>
                        <span style="padding:5px 12px;border-radius:20px;font-size:11px;font-weight:600;background:{{ $item->estado === 'disponible' ? '#14532d' : '#1e3a8a' }};color:{{ $item->estado === 'disponible' ? '#4ade80' : '#93c5fd' }};">{{ ucfirst($item->estado ?? 'N/A') }}</span>
                    </div>
                @empty
                @endforelse
            @endisset
        </div>
    </div>

    {{-- ── MODAL NUEVA ASIGNACIÓN ── --}}
    <div id="modalNuevaAsignacion" style="position:fixed;inset:0;background:rgba(0,0,0,.75);display:none;align-items:center;justify-content:center;z-index:100;">
        <div style="background:#18181b;border:1px solid #27272a;border-radius:8px;width:100%;max-width:460px;overflow:hidden;">
            <div style="padding:16px 24px;border-bottom:1px solid #27272a;display:flex;justify-content:space-between;align-items:center;background:#111111;">
                <h3 style="margin:0;font-size:17px;font-weight:700;color:#fff;">📋 Nueva Asignación de Equipo</h3>
                <button onclick="document.getElementById('modalNuevaAsignacion').style.display='none'" style="background:none;border:none;color:#a1a1aa;cursor:pointer;font-size:22px;font-weight:bold;">&times;</button>
            </div>
            <form style="padding:24px;display:flex;flex-direction:column;gap:14px;">
                @csrf
                <div>
                    <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Equipo *</label>
                    <select required style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                        <option value="">-- Seleccione un equipo --</option>
                        <option>Prótesis Dinámica de Miembro Inferior</option>
                        <option>Órtesis de Mano Muñeca</option>
                        <option>Cisco Elástico</option>
                        <option>Columna TLSO</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Paciente *</label>
                    <select required style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                        <option value="">-- Seleccione un paciente --</option>
                        <option>Elena Rodriguez (ER001)</option>
                        <option>Carlos Pérez (CP025)</option>
                        <option>Diana Torres (DT044)</option>
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Fecha Inicio *</label>
                        <input type="date" required style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Fecha Fin *</label>
                        <input type="date" required style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                    </div>
                </div>
                <div>
                    <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Estado</label>
                    <select style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                        <option value="activo">En Uso (Activo)</option>
                        <option value="mantenimiento">Mantenimiento Pendiente</option>
                        <option value="terminado">Terminado</option>
                    </select>
                </div>
                <div style="padding-top:12px;border-top:1px solid #27272a;display:flex;justify-content:flex-end;gap:10px;">
                    <button type="button" onclick="document.getElementById('modalNuevaAsignacion').style.display='none'" style="background:#27272a;color:#e4e4e7;border:1px solid #3f3f46;padding:8px 16px;border-radius:6px;cursor:pointer;">Cancelar</button>
                    <button type="submit" style="background:#22c55e;color:#000;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-weight:bold;">Guardar</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
