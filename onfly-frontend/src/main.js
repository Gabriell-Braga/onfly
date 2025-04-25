import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { createPinia } from 'pinia'
import axios from 'axios'
import './assets/style.css'

// Cria instância da aplicação
const app = createApp(App)

// Cria e usa o Pinia antes de usar qualquer store
const pinia = createPinia()
app.use(pinia)

// Só agora podemos importar e usar a store
import { useAuthStore } from '@/stores/auth'
const auth = useAuthStore()

// Configura o Axios para enviar o token em todas as requisições
axios.interceptors.request.use(config => {
  if (auth.token) {
    config.headers.Authorization = `Bearer ${auth.token}`
  }
  return config
})

// Usa o roteador e monta o app
app.use(router)
app.mount('#app')
