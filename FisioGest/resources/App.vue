<template>
  <div class="flex min-h-screen bg-zinc-950 text-white font-sans">
    
    <aside class="w-64 bg-zinc-900 border-r border-zinc-800 p-6 flex flex-col gap-8">
      
      <div class="flex items-center space-x-2 text-green-400">
        <span class="text-2xl font-bold">⚡</span>
        <h2 class="text-xl font-bold tracking-wider text-white">FisioGest</h2>
      </div>

      <nav class="flex flex-col space-y-2">
        
        <router-link to="/dashboard" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-zinc-800 hover:text-white transition-all duration-200">
          <span class="text-xl">🏠</span>
          <span class="font-medium">Dashboard</span>
        </router-link>

        <router-link to="/citas" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-zinc-800 hover:text-white transition-all duration-200">
          <span class="text-xl">📅</span>
          <span class="font-medium">Citas</span>
        </router-link>

        <a href="/pacientes" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-zinc-800 hover:text-white transition-all duration-200">
          <span class="text-xl">👥</span>
          <span class="font-medium">Pacientes</span>
        </a>

        <router-link to="/fisioterapeutas" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-zinc-800 hover:text-white transition-all duration-200">
          <span class="text-xl">👨‍⚕️</span>
          <span class="font-medium">Fisioterapeutas</span>
        </router-link>

        <router-link to="/inventario" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-zinc-800 hover:text-white transition-all duration-200">
          <span class="text-xl">📦</span>
          <span class="font-medium">Inventario</span>
        </router-link>

      </nav>
    </aside>

    <main class="flex-1 p-10 bg-zinc-950">
      <router-view></router-view>
    </main>

  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>

<style scoped>
/* Estilos personalizados */
</style>
