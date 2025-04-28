<template>
    <LoadingOverlay :show="loading" />
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
    >
    <div
        class="bg-white rounded-xl shadow-lg p-8 w-5/6 md:w-full max-w-md relative overflow-hidden transition-all duration-500 ease-in-out"
        :style="{ height: success ? '150px' : '450px' }"
    >
        <!-- Botão de fechar -->
        <button
          @click="$emit('close'); success = false;"
          class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"
        >
          ✕
        </button>
  
        <h2 v-if="!success" class="text-2xl font-bold text-gray-800 mb-6">Solicitar Viagem</h2>
  
        <!-- Formulário -->
        <form v-if="!success" @submit.prevent="handleSolicitarViagem" class="space-y-7">
          <!-- Nome do solicitante -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Solicitante</label>
            <input
              v-model="novaViagem.nomeSolicitante"
              type="text"
              required
              :readonly="!outraPessoa"
              class="block w-full rounded-md border-gray-300 shadow-md focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-4 py-2 outline-primary-700"
            />
            <div class="flex items-center gap-2 mt-3">
              <input
                id="outraPessoa"
                v-model="outraPessoa"
                @click="novaViagem.nomeSolicitante = !outraPessoa ? '' : auth.getUser()?.name"
                type="checkbox"
                class="h-4 w-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
              />
              <label for="outraPessoa" class="text-sm text-gray-600">Solicitar para outra pessoa</label>
            </div>
          </div>
  
          <!-- Destino -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Destino</label>
            <input
              id="cidade"
              v-model="filtroCidade"
              required
              type="text"
              placeholder="Cidade"
              class="block w-full rounded-md border-gray-300 shadow-md focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-4 py-2 outline-primary-700"
              @input="filtrarCidades"
            />
            <ul
              v-if="cidadesFiltradas.length && selectAberto"
              id="lista-cidades"
              class="absolute z-50 w-90 bg-white mt-2 border border-gray-300 rounded-md max-h-40 overflow-y-auto"
            >
              <li
                v-for="cidade in cidadesFiltradas"
                :key="cidade.id"
                @click="selecionarCidade(cidade)"
                class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
              >
                {{ cidade.nome }} - {{ cidade.microrregiao.mesorregiao.UF.sigla }}
              </li>
            </ul>
          </div>
  
          <!-- Datas -->
          <div class="flex gap-4">
            <div class="w-1/2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Data de Ida</label>
              <input
                v-model="novaViagem.dataIda"
                type="date"
                required
                class="block w-full rounded-md border-gray-300 shadow-md focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-4 py-2 outline-primary-700"
              />
            </div>
            <div class="w-1/2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Data de Volta</label>
              <input
                v-model="novaViagem.dataVolta"
                type="date"
                :min="novaViagem.dataIda"
                required
                class="block w-full rounded-md border-gray-300 shadow-md focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-4 py-2 outline-primary-700"
              />
            </div>
          </div>
  
          <!-- Erro -->
          <p v-if="error" class="mt-4 text-sm text-red-600 text-start">{{ error }}</p>
  
          <!-- Botão enviar -->
          <div class="flex justify-end mt-6 w-full">
            <button
              type="submit"
              class="inline-flex w-full justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              Enviar Solicitação
            </button>
          </div>
        </form>
  
        <!-- Sucesso -->
        <div v-else class="flex flex-col items-center justify-center text-center transition-all duration-500 h-full">
          <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-16 h-16 rounded-full bg-green-500">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <div>
              <h3 class="text-xl font-semibold text-green-600">Viagem solicitada!</h3>
            </div>
          </div>
        </div>
  
      </div>
    </div>
</template>
  
<script setup>
    import { ref, onMounted, onUnmounted } from 'vue'
    import axios from 'axios'
    import { useAuthStore } from '@/stores/auth'
    import LoadingOverlay from '@/components/LoadingOverlay.vue'
    import { watch } from 'vue'

    const error = ref(null)
    const success = ref(false)
    const loading = ref(false)

    const props = defineProps({
        show: {
        type: Boolean,
        required: true
        }
    })
    
    const emit = defineEmits(['close', 'viagemSolicitada'])

    const novaViagem = ref({
        nomeSolicitante: '',
        dataIda: '',
        dataVolta: '',
    })

    const auth = useAuthStore()
    const outraPessoa = ref(false)

    watch(
        () => auth.user, 
        (novoUser) => {
            if (novoUser && !novaViagem.value.nomeSolicitante) {
                novaViagem.value.nomeSolicitante = novoUser.name
            }
        },
        { immediate: true }
    )

    const cidades = ref([])
    const filtroCidade = ref('')
    const cidadesFiltradas = ref([])
    const cidadeSelecionada = ref(null)
    const selectAberto = ref(false)

    // Carregar cidades do IBGE
    const carregarCidades = async () => {
        try {
            const { data } = await axios.get('https://servicodados.ibge.gov.br/api/v1/localidades/municipios')
            cidades.value = data
        } catch (error) {
            console.error('Erro ao carregar cidades:', error)
        }
    }

    // Filtrar cidades com base no input
    const filtrarCidades = () => {
        const termo = filtroCidade.value.toLowerCase()
        cidadesFiltradas.value = cidades.value.filter(cidade =>
            cidade.nome.toLowerCase().includes(termo)
        )
        selectAberto.value = cidadesFiltradas.value.length > 0
    }

    // Selecionar cidade da lista filtrada
    const selecionarCidade = (cidade) => {
        filtroCidade.value = `${cidade.nome} - ${cidade.microrregiao.mesorregiao.UF.sigla}`
        cidadeSelecionada.value = cidade
        cidadesFiltradas.value = []
        selectAberto.value = false
    }

    carregarCidades()    
    
    const handleSolicitarViagem = async () => {
        try {
            loading.value = true
            // Verificação se cidade está dentro das cidades do IBGE
            const cidadeValida = cidades.value.some(cidade => {
                const nomeFormatado = `${cidade.nome} - ${cidade.microrregiao.mesorregiao.UF.sigla}`
                return nomeFormatado.toLowerCase() === filtroCidade.value.toLowerCase()
            })

            if (!cidadeValida) {
                error.value = 'Cidade inválida. Por favor, selecione uma cidade da lista.'
                return
            }

            const token = localStorage.getItem('token')
            await axios.post(`${import.meta.env.VITE_API_URL}/api/viagens`, {
                nome_solicitante: novaViagem.value.nomeSolicitante,
                destino: filtroCidade.value,
                data_ida: novaViagem.value.dataIda,
                data_volta: novaViagem.value.dataVolta,
                }, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            })

            success.value = true
            emit('viagemSolicitada')
            limparFormulario()

        } catch (err) {
            console.error('Erro ao solicitar viagem:', err)
            if (err.response && err.response.status === 422) {
                error.value = 'Erro ao solicitar viagem. Verifique os dados e tente novamente.'
            } else {
                error.value = 'Erro ao solicitar viagem. Tente novamente mais tarde.'
            }
        } finally {
            loading.value = false
        }
    }

    
    const limparFormulario = () => {
        filtroCidade.value = ''
        novaViagem.value = {
        nomeSolicitante: auth.getUser()?.name || '',
        dataIda: '',
        dataVolta: '',
        }
    }

    const fecharSelectAoClicarFora = (event) => {
    const target = event.target
        if (!target.closest('#cidade') && !target.closest('#lista-cidades')) {
            selectAberto.value = false
        }
    }

    onMounted(() => {
        document.addEventListener('click', fecharSelectAoClicarFora)
    })

    onUnmounted(() => {
        document.removeEventListener('click', fecharSelectAoClicarFora)
    })
</script>
  