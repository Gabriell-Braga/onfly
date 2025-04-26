<template>
    <LoadingOverlay :show="loading" />
    <div class="min-h-screen bg-no-repeat bg-cover pt-24 xl:px-32">
      <Header />
      <div class="flex flex-col items-center px-6 py-20 xl:px-32 w-full">
        <div class="flex flex-col xl:flex-row justify-between items-center mb-10 w-full">
            <!-- Saudação -->
            <div class="w-full xl:w-2/5 text-start mb-6 xl:mb-0">
                <h1 class="text-6xl font-bold text-primary-600 mb-2">Minhas Viagens</h1>
                <p class="text-base text-gray-600 mb-8 mt-10">
                    Olá, {{ user?.name || 'usuário' }}! 👋<br />
                    Aqui você pode visualizar todas as suas viagens solicitadas, acompanhar o status de aprovação
                    e gerenciar seus pedidos de forma simples e rápida. Utilize os filtros para encontrar viagens específicas ou planeje sua próxima jornada.
                </p>
                <div class="flex justify-start mt-10">
                    <button
                        @click="solicitarViagem"
                        class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        Solicitar Nova Viagem
                    </button>
                </div>
            </div>

            <!-- Caixa de Filtros -->
            <div class="bg-white/80 backdrop-blur-md rounded-xl shadow-lg p-6 border-2 border-primary-600">
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
                        class="block w-[200px] xl:w-[400px] rounded-md border-gray-300 shadow-md focus:border-primary-500 focus:ring-primary-500 sm:text-sm relative px-4 py-2"
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
                            class="block w-full rounded-md border-gray-300 shadow-md focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-4 py-2"
                            />
                        </div>
                        <!-- Data de Volta -->
                        <div class="w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Volta</label>
                            <input
                            type="date"
                            v-model="dataFim"
                            :min="dataInicio"
                            class="block w-full rounded-md border-gray-300 shadow-md focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-4 py-2"
                            />
                        </div>
                    </div>
                    

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                        v-model="status"
                        class="block w-full rounded-md border-gray-300 shadow-md focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-4 py-2"
                        >
                        <option value="" selected>Todos</option>
                        <option value="solicitado">Solicitado</option>
                        <option value="aprovado">Aprovado</option>
                        <option value="cancelado">Cancelado</option>
                        </select>
                    </div>

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
            <thead class="uppercase text-xs text-gray-50 border-b border-primary-600 bg-primary-600">
              <tr>
                <th scope="col" class="px-6 py-3">Solicitante</th>
                <th scope="col" class="px-6 py-3">Destino</th>
                <th scope="col" class="px-6 py-3">Data Ida</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3 text-right">Ação</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="viagem in viagens"
                :key="viagem.id"
                class="border-b hover:bg-gray-100 transition"
              >
                <td class="px-6 py-4">{{ viagem.nome_solicitante }}</td>
                <td class="px-6 py-4">{{ viagem.destino }}</td>
                <td class="px-6 py-4">{{ formatarData(viagem.data_ida) }}</td>
                <td class="px-6 py-4 capitalize">{{ viagem.status }}</td>
                <td class="px-6 py-4 text-right">
                  <button
                    @click="handleDelete(viagem.id)"
                    class="text-blue-600 hover:text-blue-800 font-medium"
                  >
                    Excluir
                  </button>
                </td>
              </tr>
              <tr v-if="viagens.length === 0">
                <td colspan="5" class="text-center text-gray-500 px-6 py-6">Nenhuma viagem encontrada.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
</template>
  
<script setup>
    import { ref, onMounted, onUnmounted } from 'vue'
    import Header from '@/components/Header.vue'
    import axios from 'axios'
    import { useAuthStore } from '@/stores/auth'
    import LoadingOverlay from '@/components/LoadingOverlay.vue'

    const auth = useAuthStore()
    
    // dados
    const viagens = ref([])
    const user = ref(null)
    const cidades = ref([])
    const filtroCidade = ref('')
    const cidadesFiltradas = ref([])
    const cidadeSelecionada = ref(null)
    const loading = ref(false)
    const selectAberto = ref(false)
    
    const carregarViagens = async () => {
        try {
        const token = localStorage.getItem('token')
        const { data } = await axios.get(`${import.meta.env.VITE_API_URL}/api/viagens`, {
            headers: {
            Authorization: `Bearer ${auth.getToken()}`,
            },
        })
        viagens.value = data
        } catch (error) {
        console.error('Erro ao carregar viagens:', error)
        }
    }
  
    const carregarUsuario = async () => {
        try {
        const token = localStorage.getItem('token')
        const { data } = await axios.get(`${import.meta.env.VITE_API_URL}/api/me`, {
            headers: {
            Authorization: `Bearer ${auth.getToken()}`,
            },
        })
        user.value = data
        } catch (err) {
            console.error('Erro ao buscar usuário:', err)
        }
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

    // Aplicar filtros (exemplo de função)
    const aplicarFiltros = () => {
        console.log('Cidade selecionada:', cidadeSelecionada.value)
        // Implementar lógica de filtragem das viagens com base na cidadeSelecionada
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
    })

    onUnmounted(() => {
        document.removeEventListener('click', fecharSelectAoClicarFora)
    })
    
    loading.value = true

    onMounted(async () => {
    try {
        await carregarUsuario()
        await carregarViagens()
        await carregarCidades()
    } catch (error) {
        console.error('Erro ao carregar dados:', error)
    } finally {
        loading.value = false
    }
    })
</script>
  