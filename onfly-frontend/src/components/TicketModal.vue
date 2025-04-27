<template>
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
    >
      <div class="bg-white rounded-xl shadow-lg p-8 w-5/6 md:w-full max-w-2xl relative">
  
        <!-- Botão fechar -->
        <button
          @click="$emit('close')"
          class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"
        >
          ✕
        </button>
  
        <h2 class="text-2xl font-bold text-primary-700 text-start">Solicitação de Viagem - #{{ props.id }}</h2>

        <div v-if="loading" class="flex justify-center items-center min-h-[200px]">
            <div class="relative w-12 h-12">
                <!-- Anel base (claro) -->
                <div class="absolute inset-0 rounded-full border-6 border-blue-200"></div>
                <!-- Anel giratório (escuro) -->
                <div class="absolute inset-0 rounded-full border-6 border-primary-600 border-t-transparent animate-spin"></div>
            </div>
        </div>
  
        <!-- Conteúdo -->
        <div v-else-if="viagem" class="flex flex-col md:flex-row justify-between gap-6">
          <div class="text-sm text-gray-700 leading-6 flex flex-col justify-between">
            <p><b>Solicitante:</b> {{ viagem.nome_solicitante }}</p>
            <div class="flex flex-col justify-end">
                <div class="mt-8 mb-6">
                    <span class="text-2xl">{{ viagem.destino }}</span>
                    <p><b>Data de Ida:</b> {{ formatarData(viagem.data_ida) }}</p>
                    <p><b>Data de Volta:</b> {{ formatarData(viagem.data_volta) }}</p>
                </div>
                
                <span
                    class="px-3 py-1 rounded text-white text-xs font-semibold capitalize text-center"
                    :class="{
                        'bg-green-500': viagem.status === 'aprovado',
                        'bg-yellow-400': viagem.status === 'solicitado',
                        'bg-red-500': viagem.status === 'cancelado'
                    }"
                    >
                    {{ viagem.status }}
                </span>
            </div>
          </div>
  
          <!-- QR Code -->
          <div class="flex justify-center mt-6">
            <qrcode-vue
              v-if="qrValue"
              :value="qrValue"
              :size="160"
              class="shadow-lg rounded-lg"
            />
          </div>
        </div>
  
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, watch } from 'vue'
  import axios from 'axios'
  import QrcodeVue from 'qrcode.vue'
  
  const props = defineProps({
    show: { type: Boolean, required: true },
    id: { type: Number, required: true }
  })
  
  const emit = defineEmits(['close'])
  
  const viagem = ref(null)
  const loading = ref(false)
  
  const formatarData = (dataStr) => {
    if (!dataStr) return ''
    const [ano, mes, dia] = dataStr.split('-')
    return `${dia}/${mes}/${ano}`
  }
  
  // Conteúdo do QR Code
  const qrValue = computed(() => {
    if (!viagem.value) return ''
  
    return JSON.stringify({
      solicitante: viagem.value.nome_solicitante,
      destino: viagem.value.destino,
      data_ida: viagem.value.data_ida,
      data_volta: viagem.value.data_volta,
      status: viagem.value.status
    })
  })
  
  // Buscar viagem no backend quando abrir
  const buscarViagem = async () => {
    if (!props.id) return
  
    try {
      loading.value = true
  
      const token = localStorage.getItem('token')
      const { data } = await axios.get(`${import.meta.env.VITE_API_URL}/api/viagens/${props.id}`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })
  
      viagem.value = data
    } catch (error) {
      console.error('Erro ao buscar detalhes da viagem:', error)
    } finally {
      loading.value = false
    }
  }
  
  // Toda vez que o modal abrir (show=true), buscar viagem
  watch(() => props.show, (novoValor) => {
    if (novoValor) {
      buscarViagem()
    }
  })
  </script>
  