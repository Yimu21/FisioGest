<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - FisioGest</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#121214] text-white min-h-screen font-sans">

    <div class="p-6 max-w-7xl mx-auto space-y-6">
        
        <div class="flex justify-between items-center bg-[#1c1c1e] p-6 rounded-xl border border-gray-800 shadow-md">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-wide">Inventario</h1>
                <p class="text-sm text-gray-400 mt-1">Control de insumos clínicos y herramientas de rehabilitación.</p>
            </div>
            
            <button type="button" 
                    onclick="document.getElementById('modalNuevoEquipo').classList.remove('hidden')"
                    class="bg-[#064e3b] text-[#4ade80] border border-[#064e3b] hover:bg-[#047857] hover:text-white px-5 py-2.5 rounded-lg transition duration-200 font-medium text-sm shadow-sm">
                + Añadir Nuevo Equipo
            </button>
        </div>

        @if(session('success'))
            <div class="bg-[#064e3b] border border-[#047857] text-[#4ade80] px-4 py-3 rounded-lg text-sm font-medium animate-pulse">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-[#1c1c1e] rounded-xl border border-gray-800 shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800">
                <h2 class="text-lg font-semibold text-gray-300">Items Registrados</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#27272a] text-gray-400 text-xs uppercase tracking-wider border-b border-gray-800">
                            <th class="px-6 py-3.5 font-semibold">Equipo</th>
                            <th class="px-6 py-3.5 font-semibold">Tipo</th>
                            <th class="px-6 py-3.5 font-semibold">Cantidad Total</th>
                            <th class="px-6 py-3.5 font-semibold">Estado</th>
                            <th class="px-6 py-3.5 font-semibold text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800 text-sm">
                        {{-- Recorremos dinámicamente los items que envía el controlador desde SQLite --}}
                        @forelse($items as $item)
                            <tr class="hover:bg-[#27272a] transition duration-150">
                                <td class="px-6 py-4 font-medium text-white">{{ $item->nombre }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 bg-[#27272a] text-gray-300 rounded-md text-xs font-medium border border-gray-700 uppercase">
                                        {{ $item->tipo }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-300">{{ $item->cantidad }} unidades</td>
                                <td class="px-6 py-4">
                                    @if($item->estado === 'disponible' || $item->estado === 'Available')
                                        <span class="inline-flex items-center text-emerald-400 text-xs font-medium">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2"></span> Available
                                        </span>
                                    @elseif($item->estado === 'baja' || $item->estado === 'Stock Bajo')
                                        <span class="inline-flex items-center text-amber-500 text-xs font-medium">
                                            <span class="w-2 h-2 rounded-full bg-amber-500 mr-2"></span> Stock Bajo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-blue-400 text-xs font-medium">
                                            <span class="w-2 h-2 rounded-full bg-blue-400 mr-2"></span> {{ $item->estado }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center space-x-2">
                                    <button class="bg-gray-800 text-gray-300 px-3 py-1.5 rounded hover:bg-gray-700 transition text-xs font-medium">Edit</button>
                                    <button class="bg-[#0b2f23] text-[#4ade80] px-3 py-1.5 rounded hover:bg-[#0fd46f] hover:text-black transition text-xs font-medium">Assign</button>
                                </td>
                            </tr>
                        @empty
                            {{-- Estado vacío si no hay registros en la base de datos --}}
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 font-medium">
                                    No hay equipos clínicos registrados actualmente. Haz clic en "Añadir Nuevo Equipo" para empezar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @include('components.modal-nuevo-equipo')

</body>
</html>