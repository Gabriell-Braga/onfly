<template>
    <LoadingOverlay :show="loading" />
    <TicketModal
        :show="showTicket"
        :id="viagemSelecionada"
        @close="showTicket = false"
    />
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
          <div class="flex items-end lg:order-2 relative gap-10">
            <!-- Sino de notificações -->
            <div class="relative">
              <button @click.stop="toggleNotifications" class="relative text-white hover:text-gray-200 focus:outline-none notificacoes-botao">
                <Bell class="w-6 h-6" />

                <!-- Badge com animação se houver notificações não lidas -->
                <div v-if="unreadCount > 0" class="absolute -top-1 -right-1 flex">
                  <span class="relative flex size-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-yellow-500"></span>
                  </span>
                </div>
              </button>

              <!-- Popup de notificações -->
              <div 
                v-if="showNotifications" 
                class="absolute mt-2 w-[90vw] max-w-[380px] bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden z-50 notificacoes-popup
                      right-0 md:right-0 md:translate-x-0 left-1/2 -translate-x-1/2 md:left-auto md:transform-none"
              >
                <div v-if="notifications.length > 0" class="max-h-80 overflow-y-auto">
                  <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="p-4 hover:bg-gray-200 border-b border-gray-300 last:border-0 flex justify-between items-center"
                  >
                    <div class="flex flex-col">
                      <div class="flex items-center gap-2">
                        <p class="text-gray-800 font-semibold">{{ notification.title }}</p>
                        <span class="relative flex size-3" v-if="!notification.read">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-yellow-500"></span>
                        </span>
                      </div>
                      <p class="text-xs text-gray-600">{{ notification.message }}</p>
                      <p class="text-[10px] text-gray-500 mt-1">{{ formatarDataBR(notification.sent_at) }}</p>
                    </div>

                    <div class="flex flex-col gap-2 ml-4">
                      <button @click.stop="visualizarNotificacao(notification.viagem_id, notification.id)" class="text-primary-600 hover:text-primary-800 text-xs font-semibold"><Eye class="w-5 h-5" /></button>
                      <button @click.stop="excluirNotificacao(notification.id)" class="text-red-500 hover:text-red-700 text-xs font-semibold"><Trash2 class="w-5 h-5" /></button>
                    </div>
                  </div>
                </div>
                <div v-else class="p-4 text-sm text-gray-500 text-center">Nenhuma notificação.</div>
              </div>
            </div>

            <!-- Botão de logout -->
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
    import axios from 'axios'
    import { Bell, Eye, Trash2 } from 'lucide-vue-next'
    import TicketModal from '@/components/TicketModal.vue'


    const router = useRouter()
    const auth = useAuthStore()
    const loading = ref(false)
    const isScrolled = ref(false)
    const notifications = ref([])
    const showNotifications = ref(false)
    const showTicket = ref(false)
    const viagemSelecionada = ref(null)
    const unreadCount = ref(0)
    let notificationInterval = null

    const formatarDataBR = (dataISO) => {
      const data = new Date(dataISO)
      const offset = data.getTimezoneOffset() * 60000
      const dataLocal = new Date(data.getTime() - offset)
      return dataLocal.toLocaleString('pt-BR')
    }

    const buscarNotificacoes = async () => {
      try {
        const token = localStorage.getItem('token')
        const { data } = await axios.get(`${import.meta.env.VITE_API_URL}/api/notifications`, {
          headers: { Authorization: `Bearer ${token}` }
        })
        notifications.value = data
        unreadCount.value = data.filter(n => !n.read).length
      } catch (error) {
        // console.error('Erro ao buscar notificações:', error)
      }
    }

    const visualizarNotificacao = async (viagemId, id) => {
      try {
        viagemSelecionada.value = viagemId
        showTicket.value = true
        const token = localStorage.getItem('token')
        await axios.patch(`${import.meta.env.VITE_API_URL}/api/notifications/${id}/read`, {}, {
          headers: { Authorization: `Bearer ${token}` }
        })
        await buscarNotificacoes()
      } catch (error) {
        console.error('Erro ao marcar notificação como lida:', error)
      }
    }

    const excluirNotificacao = async (id) => {
      try {
        loading.value = true
        const token = localStorage.getItem('token')
        await axios.delete(`${import.meta.env.VITE_API_URL}/api/notifications/${id}`, {
          headers: { Authorization: `Bearer ${token}` }
        })
        await buscarNotificacoes()
      } catch (error) {
        console.error('Erro ao excluir notificação:', error)
      } finally {
        loading.value = false
      }
    }

    const toggleNotifications = () => {
      showNotifications.value = !showNotifications.value
    }

    const handleScroll = () => {
      isScrolled.value = window.scrollY > 10 // Quando rolar mais de 10px
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

    const fecharAoClicarFora = (event) => {
      if (!event.target.closest('.notificacoes-popup') && !event.target.closest('.notificacoes-botao')) {
        showNotifications.value = false
      }
    }

    onMounted(() => {
      window.addEventListener('scroll', handleScroll)
      buscarNotificacoes()
      notificationInterval = setInterval(buscarNotificacoes, 10000)
      window.addEventListener('click', fecharAoClicarFora)
    })

    onUnmounted(() => {
      window.removeEventListener('scroll', handleScroll)
      clearInterval(notificationInterval)
      window.removeEventListener('click', fecharAoClicarFora)
    })
</script>

  