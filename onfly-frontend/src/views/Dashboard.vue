<template>
    <LoadingOverlay :show="loading" />
    <SolicitarViagemModal
      :show="showModal"
      @close="showModal = false"
      @viagemSolicitada="buscarViagens"
    />
    <TicketModal
        :show="showTicket"
        :id="viagemSelecionada"
        @close="showTicket = false"
    />
    <div class="min-h-screen bg-no-repeat bg-cover pt-24 xl:px-32">
      <Header />
      <div class="flex flex-col items-center px-6 py-20 xl:px-32 w-full">
        <div class="flex flex-col md:flex-row justify-center md:justify-between items-center md:items-end gap-10 mb-10 w-full">
            <!-- Saudação -->
            <div class="w-full xl:w-2/5 text-start mb-6 xl:mb-0">
                <h1 class="text-5xl xl:text-6xl font-bold text-primary-700 mb-2">
                    {{ isAdmin ? 'Painel Administrativo' : 'Minhas Viagens' }}
                </h1>

                <p class="text-base text-gray-600 mb-8 mt-10">
                    Olá, <b>{{ nomeUsuario || 'usuário' }}!</b> 👋<br />
                    <span v-if="isAdmin">
                        Aqui você pode gerenciar todas as viagens solicitadas pelos usuários, acompanhar seus status e aprovar ou cancelar pedidos diretamente. Utilize os filtros para encontrar viagens específicas e agilizar o processo de aprovação.
                    </span>
                    <span v-else>
                        Aqui você pode visualizar suas viagens solicitadas, acompanhar o status de aprovação e gerenciar seus pedidos de forma simples e rápida. Utilize os filtros para encontrar viagens específicas ou planejar sua próxima jornada.
                    </span>
                </p>

                <div v-if="!isAdmin" class="flex justify-start mt-10">
                    <button
                    @click="showModal = true"
                    class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                    Solicitar Nova Viagem
                    </button>
                </div>
            </div>

            <!-- Caixa de Filtros -->
            <div class="bg-white/80 backdrop-blur-md rounded-xl shadow-lg p-6 border-2 border-primary-600 w-full md:w-[420px]">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Filtrar Viagens</h2>
                <form @submit.prevent="aplicarFiltros" class="space-y-4">
                
                    <!-- Cidade -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Destino</label>
                        <input
                        id="cidade"
                        v-model="filtroCidade"
                        type="text"
                        placeholder="Cidade"
                        class="block w-full rounded-md border-gray-300 shadow-md focus:border-primary-500 focus:ring-primary-500 sm:text-sm relative px-4 py-2 outline-primary-700"
                        @input="filtrarCidades"
                        />
                        <ul v-if="cidadesFiltradas.length && selectAberto" class="absolute z-50 w-90 bg-white mt-2 border border-gray-300 rounded-md max-h-40 overflow-y-auto">
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

                    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center">
                        <!-- Data de Ida -->
                        <div class="w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Ida</label>
                            <input
                            type="date"
                            v-model="dataInicio"
                            class="block w-full rounded-md border-gray-300 shadow-md focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-4 py-2 outline-primary-700"
                            />
                        </div>
                        <!-- Data de Volta -->
                        <div class="w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Volta</label>
                            <input
                            type="date"
                            v-model="dataFim"
                            :min="dataInicio"
                            class="block w-full rounded-md border-gray-300 shadow-md focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-4 py-2 outline-primary-700"
                            />
                        </div>
                    </div>
                    

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                        v-model="status"
                        class="block w-full rounded-md border-gray-300 shadow-md focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-4 py-2 outline-primary-700"
                        >
                        <option value="" selected>Todos</option>
                        <option value="solicitado">Solicitado</option>
                        <option value="aprovado">Aprovado</option>
                        <option value="cancelado">Cancelado</option>
                        </select>
                    </div>

                    <p v-if="error" class="mt-4 text-sm text-red-600 text-start">{{ error }}</p>

                    <!-- Botão -->
                    <div class="flex justify-end w-full">
                        <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        >
                        Aplicar Filtros
                        </button>
                    </div>

                </form>
            </div>
        </div>

  
        <div class="w-full overflow-x-auto rounded-lg bg-white/80 shadow-xl border-2 border-primary-600 mt-20">
          <table class="min-w-full text-sm text-left text-gray-800">
            <thead class="uppercase text-xs text-primary-50 border-b border-primary-600 bg-primary-600">
              <tr>
                <th scope="col" class="px-6 py-3 cursor-pointer select-none hover:underline" @click="ordenarPor('id')">
                    <div class="flex items-center gap-1">
                        ID
                        <span v-if="campoOrdenacao === 'id'">
                            <svg v-if="ordemAscendente" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </div>
                </th>
                <th scope="col" class="px-6 py-3 cursor-pointer select-none hover:underline" @click="ordenarPor('nome_solicitante')">
                    <div class="flex items-center gap-1">
                        Solicitante
                        <span v-if="campoOrdenacao === 'nome_solicitante'">
                            <svg v-if="ordemAscendente" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </div>
                </th>
                <th scope="col" class="px-6 py-3 cursor-pointer select-none hover:underline" @click="ordenarPor('destino')">
                    <div class="flex items-center gap-1">
                        Destino
                        <span v-if="campoOrdenacao === 'destino'">
                            <svg v-if="ordemAscendente" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </div>
                </th>
                <th scope="col" class="px-6 py-3 cursor-pointer select-none hover:underline" @click="ordenarPor('data_ida')">
                    <div class="flex items-center gap-1">
                        Data Ida
                        <span v-if="campoOrdenacao === 'data_ida'">
                            <svg v-if="ordemAscendente" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </div>
                </th>
                <th scope="col" class="px-6 py-3 cursor-pointer select-none hover:underline" @click="ordenarPor('data_volta')">
                    <div class="flex items-center gap-1">
                        Data Volta
                        <span v-if="campoOrdenacao === 'data_volta'">
                            <svg v-if="ordemAscendente" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </div>
                </th>
                <th scope="col" class="px-6 py-3 cursor-pointer select-none hover:underline" @click="ordenarPor('status')">
                    <div class="flex items-center gap-1">
                        Status
                        <span v-if="campoOrdenacao === 'status'">
                            <svg v-if="ordemAscendente" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </div>
                </th>
                <th scope="col" class="px-6 py-3 text-right">Ação</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="viagem in viagens"
                v-if="!LoadingTable"
                :key="viagem.id"
                class="border-b last:border-0 border-gray-200 hover:bg-gray-200 transition"
              >
                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ viagem.id }}</td>
                <td class="px-6 py-4">{{ viagem.nome_solicitante }}</td>
                <td class="px-6 py-4">{{ viagem.destino }}</td>
                <td class="px-6 py-4">{{ formatarData(viagem.data_ida) }}</td>
                <td class="px-6 py-4">{{ formatarData(viagem.data_volta) }}</td>
                <td class="px-6 py-4 capitalize">
                    <div class="flex justify-start items-center gap-2">
                        <span class="relative flex size-3">
                            <span
                                v-if="viagem.status === 'solicitado'"
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-yellow-400 opacity-75"
                            ></span>
                            <span
                                :class="[
                                'relative inline-flex size-3 rounded-full',
                                viagem.status === 'aprovado' ? 'bg-green-500' :
                                viagem.status === 'cancelado' ? 'bg-red-500' :
                                'bg-yellow-400'
                                ]"
                            ></span>
                        </span>

                        <div v-if="isAdmin" class="flex items-center">
                            <select
                                v-model="viagem.status"
                                @change="atualizarStatus(viagem.id, viagem.status)"
                                :disabled="isDataIdaPassada(viagem.data_ida)"
                                class="ml-2 rounded-md border-gray-300 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500 outline-primary-600 cursor-pointer p-1 disabled:cursor-not-allowed disabled:bg-gray-100"
                            >
                                <option value="solicitado" disabled>Solicitado</option>
                                <option value="aprovado">Aprovado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>

                        <span v-else>{{ viagem.status }}</span>

                    </div>
                </td>
                <td class="px-6 py-4 text-right">
                    <button
                        @click="abrirTicket(viagem.id)"
                        class="text-primary-600 hover:text-primary-800 font-semibold"
                    >
                        Detalhes
                    </button>
                </td>
              </tr>
              <tr v-if="viagens.length === 0 && !LoadingTable">
                <td colspan="7" class="text-center text-gray-500 px-6 py-6">Nenhuma viagem encontrada.</td>
              </tr>
              <tr v-if="LoadingTable">
                <td colspan="7" class="text-center text-gray-500 px-6 py-6">
                    <div class="flex justify-center items-center min-h-[50px]">
                        <div class="relative w-10 h-10">
                            <!-- Anel base (claro) -->
                            <div class="absolute inset-0 rounded-full border-5 border-blue-200"></div>
                            <!-- Anel giratório (escuro) -->
                            <div class="absolute inset-0 rounded-full border-5 border-primary-600 border-t-transparent animate-spin"></div>
                        </div>
                    </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div
    v-if="showSnack"
    :class="[
        'fixed bottom-6 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-md shadow-lg text-white text-sm text-center font-semibold transition-all duration-300 z-50',
        snackType === 'success' ? 'bg-blue-600' : 'bg-red-600'
    ]"
    >
        {{ snackMessage }}
    </div>
</template>
  
<script setup>
    import { ref, onMounted, onUnmounted, watch } from 'vue'
    import Header from '@/components/Header.vue'
    import axios from 'axios'
    import { useAuthStore } from '@/stores/auth'
    import LoadingOverlay from '@/components/LoadingOverlay.vue'
    import SolicitarViagemModal from '@/components/SolicitarViagemModal.vue'
    import TicketModal from '@/components/TicketModal.vue'

    const auth = useAuthStore()
    const nomeUsuario = ref('')
    const isAdmin = ref(false)

    watch(
        () => auth.user,
        (novoUser) => {
            if (novoUser) {
                nomeUsuario.value = novoUser.name
                isAdmin.value = novoUser.is_admin === true
            }
        },
        { immediate: true }
    )
    
    // dados
    const viagens = ref([])
    const viagensFiltradas = ref([])
    const cidades = ref([])
    const dataInicio = ref('')
    const dataFim = ref('')
    const status = ref('')
    const error = ref(null)
    const filtroCidade = ref('')
    const cidadesFiltradas = ref([])
    const cidadeSelecionada = ref(null)
    const loading = ref(false)
    const selectAberto = ref(false)
    const showModal = ref(false)
    const showTicket = ref(false)
    const viagemSelecionada = ref(null)
    const LoadingTable = ref(false)
    const campoOrdenacao = ref('id')
    const ordemAscendente = ref(true)
    const snackMessage = ref('')
    const snackType = ref('') // 'success' ou 'error'
    const showSnack = ref(false)
    let snackTimeout = null
    let viagensInterval = null

    const filtros = ref({
        destino: '',
        data_inicio: '',
        data_fim: '',
        status: ''
    })


    const buscarViagens = async () => {
        try {
            const token = localStorage.getItem('token')
            const { data } = await axios.get(`${import.meta.env.VITE_API_URL}/api/viagens`, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
                params: filtros.value,
            })
            viagens.value = data
            viagensFiltradas.value = data
        } catch (error) {
            // console.error('Erro ao buscar viagens:', error)
        }
    }


    const ordenarPor = (campo) => {
        if (campoOrdenacao.value === campo) {
            ordemAscendente.value = !ordemAscendente.value
        } else {
            campoOrdenacao.value = campo
            ordemAscendente.value = true
        }

        viagens.value.sort((a, b) => {
            let aCampo = a[campo]
            let bCampo = b[campo]

            if (campo.includes('data')) {
                aCampo = new Date(aCampo)
                bCampo = new Date(bCampo)
            }

            if (aCampo < bCampo) return ordemAscendente.value ? -1 : 1
            if (aCampo > bCampo) return ordemAscendente.value ? 1 : -1
            return 0
        })
    }


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

    // Aplicar filtros
    const aplicarFiltros = async () => {

        if(filtroCidade.value) {
            const cidadeValida = cidades.value.some(cidade => {
                const nomeFormatado = `${cidade.nome} - ${cidade.microrregiao.mesorregiao.UF.sigla}`
                return nomeFormatado.toLowerCase() === filtroCidade.value.toLowerCase()
            })
            if (!cidadeValida) {
                error.value = 'Cidade inválida. Por favor, selecione uma cidade da lista.'
                return
            }
        }

        error.value = null
        LoadingTable.value = true

        filtros.value = {
            destino: filtroCidade.value || '',
            data_inicio: dataInicio.value || '',
            data_fim: dataFim.value || '',
            status: status.value || ''
        }

        campoOrdenacao.value = 'id'
        ordemAscendente.value = true
        await buscarViagens()
        LoadingTable.value = false
    }

    const atualizarStatus = async (id, novoStatus) => {
        try {
            loading.value = true

            const token = localStorage.getItem('token')
            await axios.patch(`${import.meta.env.VITE_API_URL}/api/viagens/${id}/status`, {
                status: novoStatus
            }, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            })

            await buscarViagens()

            exibirSnack('Status atualizado com sucesso!', 'success')
        } catch (error) {
            await buscarViagens()
            console.error('Erro ao atualizar status:', error)
            exibirSnack(error.response?.data?.message, 'error')
        } finally {
            loading.value = false
        }
    }

    const exibirSnack = (mensagem, tipo) => {
        if (snackTimeout) {
            clearTimeout(snackTimeout)
        }

        snackMessage.value = mensagem
        snackType.value = tipo
        showSnack.value = true

        snackTimeout = setTimeout(() => {
            showSnack.value = false
        }, 5000)
    }


    const abrirTicket = (viagem) => {
        viagemSelecionada.value = viagem
        showTicket.value = true
    }

    const isDataIdaPassada = (dataIda) => {
        if (!dataIda) return false
        const hoje = new Date()
        const data = new Date(dataIda)
        hoje.setHours(0, 0, 0, 0)
        data.setHours(0, 0, 0, 0)
        return data < hoje
    }

  
    const formatarData = (dataStr) => {
        const [ano, mes, dia] = dataStr.split('-')
        return `${dia}/${mes}/${ano}`
    }

    const fecharSelectAoClicarFora = (event) => {
    const target = event.target
        if (!target.closest('#cidade') && !target.closest('#lista-cidades')) {
            selectAberto.value = false
        }
    }

    onMounted(() => {
        document.addEventListener('click', fecharSelectAoClicarFora)
        viagensInterval = setInterval(() => {
            buscarViagens()
        }, 10000)
    })

    onUnmounted(() => {
        document.removeEventListener('click', fecharSelectAoClicarFora)
        clearInterval(viagensInterval)
    })
    
    const loadData = async () => {
        loading.value = true
        try {
            await buscarViagens()
            await carregarCidades()
        } catch (error) {
            console.error('Erro ao carregar dados:', error)
        } finally {
            loading.value = false
        }
    }

    loadData()
</script>
  