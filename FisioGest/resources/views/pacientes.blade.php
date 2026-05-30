<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pacientes - FisioGest</title>
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
            <a href="/citas" style="display:flex;align-items:center;padding:12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:14px;">📅 <span style="margin-left:10px;">Citas</span></a>
            <a href="/pacientes" style="display:flex;align-items:center;padding:12px;color:#fff;background:#18181b;border-left:3px solid #22c55e;text-decoration:none;font-size:14px;font-weight:bold;">👥 <span style="margin-left:10px;">Pacientes</span></a>
            <a href="/inventario" style="display:flex;align-items:center;padding:12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:14px;">📦 <span style="margin-left:10px;">Inventario</span></a>
            <a href="/fisioterapeutas" style="display:flex;align-items:center;padding:12px;color:#a1a1aa;text-decoration:none;border-radius:6px;font-size:14px;">👨‍⚕️ <span style="margin-left:10px;">Fisioterapeutas</span></a>
        </nav>
    </div>

    {{-- ── ÁREA PRINCIPAL ── --}}
    <div style="flex:1;padding:40px;box-sizing:border-box;overflow-y:auto;">
        <h1 style="color:#4ade80;margin:0;">👥 Registro de Pacientes</h1>
        <p style="color:#a1a1aa;margin:5px 0 25px 0;">Administración de expedientes clínicos.</p>

        @if(session('success'))
            <div style="background:#14532d;border:1px solid #22c55e;color:#4ade80;padding:12px 16px;border-radius:6px;margin-bottom:20px;">✅ {{ session('success') }}</div>
        @endif

        {{-- Formulario Paciente (Con bypass de URL directo) --}}
        <div style="background:#18181b;padding:24px;border-radius:8px;max-width:600px;margin-bottom:36px;border:1px solid #27272a;">
            <h3 style="margin-top:0;color:#4ade80;">➕ Nuevo Paciente</h3>
            <form action="/pacientes" method="POST">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Nombre *</label>
                        <input type="text" name="nombre" required style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Apellido *</label>
                        <input type="text" name="apellido" required style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Fecha Nacimiento *</label>
                        <input type="date" name="fecha_nacimiento" required style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Género *</label>
                        <select name="genero" required style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                            <option value="masculino">Masculino</option>
                            <option value="femenino">Femenino</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Teléfono</label>
                    <input type="text" name="telefono" style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;margin-bottom:5px;font-size:13px;color:#a1a1aa;">Asignar Especialista Inicial</label>
                    <select name="fisioterapeuta_id" style="width:100%;padding:8px;background:#27272a;border:1px solid #3f3f46;color:#fff;border-radius:4px;box-sizing:border-box;">
                        <option value="">-- Sin especialista inicial --</option>
                        @foreach($fisioterapeutas as $ft)
                            <option value="{{ $ft->fisioterapeuta_id ?? $ft->id }}">{{ $ft->nombre }} {{ $ft->apellido }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" style="background:#22c55e;color:#000;border:none;padding:10px 20px;border-radius:4px;cursor:pointer;font-weight:bold;font-size:14px;">Guardar Paciente</button>
            </form>
        </div>

        {{-- Tabla --}}
        <div style="overflow-x:auto;border:1px solid #27272a;border-radius:8px;">
            <table style="width:100%;border-collapse:collapse;background:#18181b;">
                <thead>
                    <tr style="background:#27272a;color:#4ade80;text-align:left;">
                        <th style="padding:12px;">Paciente</th>
                        <th style="padding:12px;">Género</th>
                        <th style="padding:12px;">Teléfono</th>
                        <th style="padding:12px;">Especialista Asignado</th>
                    </tr>
                </thead>
                <tbody>
                    @isset($pacientes)
                        @forelse($pacientes as $p)
                            <tr style="border-bottom:1px solid #27272a;">
                                <td style="padding:12px;font-weight:600;">{{ $p->nombre }} {{ $p->apellido }}</td>
                                <td style="padding:12px;text-transform:capitalize;">{{ $p->genero }}</td>
                                <td style="padding:12px;color:#a1a1aa;">{{ $p->telefono ?? '—' }}</td>
                                <td style="padding:12px;color:#4ade80;">{{ $p->fisio_nombre ?? 'Ninguno' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="padding:24px;text-align:center;color:#71717a;">No hay pacientes registrados.</td></tr>
                        @endforelse
                    @endisset
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>