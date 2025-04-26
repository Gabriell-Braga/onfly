<template>
    <LoadingOverlay :show="loading" />
    <div class="flex min-h-full w-full justify-center xl:justify-between items-center">
        <div class="flex h-full w-4/5 flex-col justify-center items-center px-6 py-12 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                <img class="h-20 w-auto" src="/logo-onfly.webp" alt="Your Company" />
                <h2 class="mt-10 text-start text-2xl font-semibold tracking-tight text-gray-900">
                Entre com sua conta
                </h2>
            </div>
        
            <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
                <form @submit.prevent="handleLogin" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-900">Email</label>
                    <div class="mt-2">
                    <input
                        v-model="email"
                        type="email"
                        name="email"
                        id="email"
                        autocomplete="email"
                        required
                        class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 sm:text-sm"
                    />
                    </div>
                </div>
        
                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-gray-900">Senha</label>
                    </div>
                    <div class="mt-2 relative">
                        <input
                        :type="showPassword ? 'text' : 'password'"
                        v-model="password"
                        name="password"
                        id="password"
                        autocomplete="current-password"
                        required
                        class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 sm:text-sm"
                        />
                        <button
                        type="button"
                        class="absolute inset-y-0 right-2 flex items-center text-sm text-gray-500"
                        @click="showPassword = !showPassword"
                        >
                            <component :is="showPassword ? EyeOff : Eye" class="h-5 w-5 text-primary-600" />
                        </button>
                    </div>
                </div>
        
                <div>
                    <button
                    type="submit"
                    class="flex w-full justify-center rounded-md bg-primary-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
                    :disabled="loading"
                    >
                    {{ loading ? 'Entrando...' : 'Entrar' }}
                    </button>
                </div>
                </form>
        
                <p v-if="error" class="mt-4 text-sm text-red-600 text-start">{{ error }}</p>
        
                <p class="mt-10 text-start text-sm text-gray-500">
                    Sem conta?
                    <router-link to="/register" class="font-semibold text-primary-600 hover:text-primary-500">Crie uma agora.</router-link>
                </p>
            </div>
        </div>
        <img src="/login-banner.jpg" alt="" class="w-full max-h-lvh object-cover object-top hidden xl:block">
    </div>
</template>
  
<script setup>
  import { ref } from 'vue'
  import { useRouter } from 'vue-router'
  import axios from 'axios'
  import { useAuthStore } from '@/stores/auth'
  import LoadingOverlay from '@/components/LoadingOverlay.vue'
  import { Eye, EyeOff } from 'lucide-vue-next'
  
  const email = ref('')
  const password = ref('')
  const showPassword = ref(false)
  const error = ref('')
  const loading = ref(false)
  const router = useRouter()
  const auth = useAuthStore()
  
  const handleLogin = async () => {
    error.value = ''
    loading.value = true
  
    try {
        const api = import.meta.env.VITE_API_URL
        const { data } = await axios.post(api+'/api/login', {
            email: email.value,
            password: password.value,
        })
        auth.setToken(data.access_token)
        router.push('/dashboard')
    } catch (err) {
        console.error(err)
        error.value = err.response?.data?.message || 'Erro ao fazer login. Verifique suas credenciais.';
    } finally {
        setTimeout(() => {
            loading.value = false
        }, 800);
    }
  }
</script>