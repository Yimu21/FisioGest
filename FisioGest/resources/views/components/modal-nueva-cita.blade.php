<div id="modalNuevaCita" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 p-4 {{ $errors->any() ? '' : 'hidden' }}">
    <div class="bg-[#18181b] border border-gray-800 text-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        
        <div class="px-6 py-4 flex justify-between items-center border-b border-gray-800 bg-[#1c1c1e]">
            <h3 class="text-lg font-semibold text-white">Agendar Nueva Cita</h3>
            <button type="button" onclick="document.getElementById('modalNuevaCita').classList.add('hidden')" class="text-gray-400 hover:text-white text-xl">
                &times;
            </button>
        </div>

        <form action="{{ route('citas.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Seleccionar Paciente</label>
                <select name="paciente_id" class="w-full px-3 py-2 bg-[#27272a] border border-gray-700 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 text-sm">
                    <option value="">-- Seleccione un paciente --</option>
                    @foreach($pacientes as $paciente)
                        <option value="{{ $paciente->id_paciente }}">{{ $paciente->nombre_completo }} (DUI: {{ $paciente->dui }})</option>
                    @endforeach
                </select>
                @error('paciente_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Asignar Fisioterapeuta</label>
                <select name="fisioterapeuta_id" class="w-full px-3 py-2 bg-[#27272a] border border-gray-700 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 text-sm">
                    <option value="">-- Seleccione un especialista activo --</option>
                    @foreach($fisioterapeutas as $ft)
                        <option value="{{ $ft->id_fisioterapeuta }}">{{ $ft->nombre_completo }} - {{ $ft->especialidad }}</option>
                    @endforeach
                </select>
                @error('fisioterapeuta_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Fecha</label>
                    <input type="date" name="fecha_cita" value="{{ old('fecha_cita') }}"
                           class="w-full px-3 py-2 bg-[#27272a] border border-gray-700 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 text-sm" />
                    @error('fecha_cita') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Hora</label>
                    <input type="time" name="hora_cita" value="{{ old('hora_cita') }}"
                           class="w-full px-3 py-2 bg-[#27272a] border border-gray-700 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 text-sm" />
                    @error('hora_cita') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Motivo / Observaciones Clínicas</label>
                <textarea name="observaciones" rows="2" placeholder="Ej. Evaluación inicial por esguince de tobillo..."
                          class="w-full px-3 py-2 bg-[#27272a] border border-gray-700 rounded-md text-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 text-sm"></textarea>
            </div>

            <div class="flex space-x-3 pt-4 border-t border-gray-800">
                <button type="submit" class="flex-1 py-2 bg-[#064e3b] hover:bg-[#047857] text-white font-medium rounded-md transition duration-200">
                    Agendar Cita
                </button>
                <button type="button" onclick="document.getElementById('modalNuevaCita').classList.add('hidden')" class="flex-1 py-2 bg-[#27272a] hover:bg-[#3f3f46] text-gray-300 font-medium rounded-md transition duration-200">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>