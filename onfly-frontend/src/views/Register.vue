<template>
    <LoadingOverlay :show="loading" />
    <div class="flex min-h-lvh w-full justify-center xl:justify-between items-center">
      <div class="flex h-full w-4/5 flex-col justify-center items-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
          <img class="h-20 w-auto" src="/logo-onfly.webp" alt="Onfly Logo" />
          <h2 class="mt-10 text-start text-2xl font-semibold tracking-tight text-gray-900">
            Crie sua conta
          </h2>
        </div>
  
        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
          <form @submit.prevent="handleRegister" class="space-y-6">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-900">Nome</label>
              <div class="mt-2">
                <input
                  v-model="name"
                  type="text"
                  name="name"
                  id="name"
                  required
                  class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:outline-primary-600 sm:text-sm"
                />
              </div>
              <p v-if="errorName" class="text-sm text-red-500 mt-1">{{ errorName }}</p>
            </div>
  
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
                  class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:outline-primary-600 sm:text-sm"
                />
              </div>
              <p v-if="errorEmail" class="text-sm text-red-500 mt-1">{{ errorEmail }}</p>
            </div>
  
            <div>
              <label for="password" class="block text-sm font-medium text-gray-900">Senha</label>
              <div class="mt-2 relative">
                <input
                  :type="showPassword ? 'text' : 'password'"
                  v-model="password"
                  name="password"
                  id="password"
                  autocomplete="new-password"
                  required
                  class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:outline-primary-600 sm:text-sm"
                />
                <button
                  type="button"
                  class="absolute inset-y-0 right-2 flex items-center text-sm text-gray-500"
                  @click="showPassword = !showPassword"
                >
                    <component :is="showPassword ? EyeOff : Eye" class="h-5 w-5 text-primary-600" />
                </button>                
              </div>
              <p v-if="errorPassword" class="text-sm text-red-500 mt-1">{{ errorPassword }}</p>
            </div>
  
            <div>
              <label for="confirmPassword" class="block text-sm font-medium text-gray-900">Confirmar Senha</label>
              <div class="mt-2 relative">
                <input
                  :type="showConfirm ? 'text' : 'password'"
                  v-model="confirmPassword"
                  name="confirmPassword"
                  id="confirmPassword"
                  autocomplete="new-password"
                  required
                  class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:outline-primary-600 sm:text-sm"
                />
                <button
                  type="button"
                  class="absolute inset-y-0 right-2 flex items-center text-sm text-gray-500"
                  @click="showConfirm = !showConfirm"
                >
                    <component :is="showConfirm ? EyeOff : Eye" class="h-5 w-5 text-primary-600" />
                </button>
              </div>
              <p v-if="errorConfirm" class="text-sm text-red-500 mt-1">{{ errorConfirm }}</p>
            </div>
  
            <div>
              <button
                type="submit"
                class="flex w-full justify-center rounded-md bg-primary-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
                :disabled="loading"
              >
                {{ loading ? 'Cadastrando...' : 'Cadastrar' }}
              </button>
            </div>
          </form>
  
          <p v-if="error" class="mt-4 text-sm text-red-600 text-start">{{ error }}</p>
  
          <p class="mt-10 text-start text-sm text-gray-500">
            Já tem uma conta?
            <router-link to="/login" class="font-semibold text-primary-600 hover:text-primary-500">Entrar</router-link>
          </p>
        </div>
      </div>
      <img src="/register-banner.jpg" alt="" class="w-full max-h-lvh object-cover object-top hidden xl:block" />
    </div>
</template>
  
<script setup>
  import { ref } from 'vue'
  import { useRouter } from 'vue-router'
  import axios from 'axios'
  import { useAuthStore } from '@/stores/auth'
  import LoadingOverlay from '@/components/LoadingOverlay.vue'
  import { Eye, EyeOff } from 'lucide-vue-next'

  
  const name = ref('')
  const email = ref('')
  const password = ref('')
  const confirmPassword = ref('')
  const error = ref('')
  const errorName = ref('')
  const errorEmail = ref('')
  const errorPassword = ref('')
  const errorConfirm = ref('')
  const showPassword = ref(false)
  const showConfirm = ref(false)
  const loading = ref(false)
  const router = useRouter()
  const auth = useAuthStore()
  
  const handleRegister = async () => {
    // Resetar erros
    error.value = ''
    errorName.value = ''
    errorEmail.value = ''
    errorPassword.value = ''
    errorConfirm.value = ''
    loading.value = true
  
    // Validação de senha
    const passwordRegex = /^(?=.*[a-zA-Z])(?=.*\d).{6,}$/
    if (!passwordRegex.test(password.value)) {
      errorPassword.value = 'A senha deve conter pelo menos 6 caracteres, incluindo letras e números.'
      loading.value = false
      return
    }
  
    if (password.value !== confirmPassword.value) {
      errorConfirm.value = 'As senhas não coincidem.'
      loading.value = false
      return
    }
  
    try {
      const api = import.meta.env.VITE_API_URL
      const { data } = await axios.post(api+'/api/register', {
        name: name.value,
        email: email.value,
        password: password.value,
      })
      auth.setToken(data.access_token)
      router.push('/dashboard')
    } catch (err) {
      console.error(err)
      if (err.response?.status === 422) {
        const errors = err.response.data.errors
        errorName.value = errors.name?.[0] || ''
        errorEmail.value = errors.email?.[0] || ''
        errorPassword.value = errors.password?.[0] || ''
      } else {
        error.value = err.response?.data?.message || 'Erro ao registrar.'
      }
    } finally {
      setTimeout(() => {
            loading.value = false
        }, 800);
    }
  }
</script>
  