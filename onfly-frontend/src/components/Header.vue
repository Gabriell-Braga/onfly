<template>
    <header class="fixed top-6 left-1/2 -translate-x-1/2 z-10 w-[95%] px-6 py-1.5 backdrop-blur-md rounded-2xl shadow-lg flex items-center justify-between bg-primary-600">
      <nav class="px-4 lg:px-6 py-4 w-full">
        <div class="flex flex-wrap justify-between items-center">
          <!-- Logo -->
          <router-link to="/dashboard" class="flex items-center">
            <img src="/logo-onfly-white.png" class="mr-3 h-8 sm:h-12" alt="Onfly Logo" />
          </router-link>
  
          <!-- Botões direita -->
          <div class="flex items-center lg:order-2">
            <button
              @click="handleLogout"
              class="flex items-center gap-2 text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg px-4 py-2 focus:outline-none"
            >
              Log out
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7"></path>
              </svg>
            </button>           
          </div>
        </div>
      </nav>
    </header>
</template>
  
<script setup>
    import { useRouter } from 'vue-router'
    import { useAuthStore } from '@/stores/auth'

    const router = useRouter()
    const auth = useAuthStore()

    const handleLogout = async () => {
        try {
            router.push('/login')
            await auth.logout()
        } catch (error) {
            console.error('Erro ao deslogar:', error)
        }
    }
</script>

  