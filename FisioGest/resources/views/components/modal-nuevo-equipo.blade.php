<div style="padding: 16px 24px; border-bottom: 1px solid #27272a; display: flex; justify-content: space-between; align-items: center; background: #111111;">
    <h3 style="margin: 0; font-size: 18px; font-weight: bold; color: #fff;">
        📅 Agendar Nueva Cita
    </h3>
    <button type="button" onclick="cerrarModalCita()" style="background: none; border: none; color: #a1a1aa; cursor: pointer; font-size: 24px; font-weight: bold;">&times;</button>
</div>

{{-- FIX DEFINITIVO: Cambiado route('citas.store') por url fija '/citas' para evitar fallas de RouteNotFoundException --}}
<form action="/citas" method="POST" style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
    @csrf

    <div>
        <label style="display: block; margin-bottom: 5px; font-size: 13px; color: #a1a1aa;">Seleccionar Paciente <span style="color:#f87171;">*</span></label>
        <select name="paciente_id" required style="width: 100%; padding: 8px; background: #27272a; border: 1px solid #3f3f46; color: #fff; border-radius: 4px; box-sizing: border-box;">
            <option value="">-- Seleccione un paciente --</option>
            @isset($pacientes)
                @foreach($pacientes as $paciente)
                    <option value="{{ $paciente->paciente_id ?? $paciente->id }}">
                        {{ $paciente->nombre }} {{ $paciente->apellido }}
                    </option>
                @endforeach
            @endisset
        </select>
    </div>

    <div>
        <label style="display: block; margin-bottom: 5px; font-size: 13px; color: #a1a1aa;">Asignar Fisioterapeuta *</label>
        <select name="fisioterapeuta_id" required style="width: 100%; padding: 8px; background: #27272a; border: 1px solid #3f3f46; color: #fff; border-radius: 4px; box-sizing: border-box;">
            <option value="">-- Seleccione un especialista --</option>
            @isset($fisioterapeutas)
                @foreach($fisioterapeutas as $ft)
                    <option value="{{ $ft->fisioterapeuta_id ?? $ft->id }}">
                        {{ $ft->nombre }} {{ $ft->apellido }}
                    </option>
                @endforeach
            @endisset
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
        <label style="display: block; margin-bottom: 5px; font-size: 13px; color: #a1a1aa;">Motivo de la Consulta</label>
        <textarea name="motivo" rows="3" placeholder="Ej. Terapia lumbar..." style="width: 100%; padding: 8px; background: #27272a; border: 1px solid #3f3f46; color: #fff; border-radius: 4px; box-sizing: border-box; resize: none;"></textarea>
    </div>

    <div style="padding-top: 14px; border-top: 1px solid #27272a; display: flex; justify-content: flex-end; gap: 12px;">
        <button type="button" onclick="cerrarModalCita()" style="background: #27272a; color: #e4e4e7; border: 1px solid #3f3f46; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 14px;">Cancelar</button>
        <button type="submit" style="background: #22c55e; color: #000; border: none; padding: 8px 16px;