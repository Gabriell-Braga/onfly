<template>
    <LoadingOverlay :show="loading" />
    <div class="w-full flex items-center justify-center fixed left-0 top-0 z-10">
      <header
        :class="[
          'fixed px-6 py-1.5 backdrop-blur-md shadow-lg flex items-center justify-between bg-primary-600 transition-all duration-500 ease-in-out',
          isScrolled ? 'top-0 w-full translate-x-0 rounded-none px-8' : 'rounded-2xl w-[95%] top-6'
        ]"
      >
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
  </div>
</template>
  
<script setup>
    import { ref, onMounted, onUnmounted } from 'vue'
    import { useRouter } from 'vue-router'
    import { useAuthStore } from '@/stores/auth'
    import LoadingOverlay from '@/components/LoadingOverlay.vue'

    const router = useRouter()
    const auth = useAuthStore()
    const loading = ref(false)
    const isScrolled = ref(false)

    const handleScroll = () => {
      isScrolled.value = window.scrollY > 10 // Quando rolar mais de 10px
      console.log('isScrolled:', window.scrollY)
    }

    const handleLogout = async () => {
        try {
            loading.value = true
            await auth.logout()
            router.push('/login')
        } catch (error) {
            console.error('Erro ao deslogar:', error)
            router.push('/login')
        } finally {
            loading.value = false
        }
    }

    onMounted(() => {
      window.addEventListener('scroll', handleScroll)
    })

    onUnmounted(() => {
      window.removeEventListener('scroll', handleScroll)
    })
</script>

  