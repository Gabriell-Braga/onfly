// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import mitt from 'mitt'
import Login from '@/views/Login.vue'
import Register from '@/views/Register.vue'
import Dashboard from '@/views/Dashboard.vue'
import { useAuthStore } from '@/stores/auth'

// Crie as rotas conforme seu app
const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name: 'Login', component: Login },
  { path: '/register', name: 'Register', component: Register },
  { path: '/dashboard', name: 'Dashboard', component: Dashboard },
]

// Crie o roteador
const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  const isAuthenticated = !!auth.getToken()

  if (to.path === '/dashboard' && !isAuthenticated) {
    // Não está logado e tentou acessar dashboard
    return next('/login')
  }

  if ((to.path === '/login' || to.path === '/register') && isAuthenticated) {
    // Já está logado e tentou acessar login/register
    return next('/dashboard')
  }

  // Em qualquer outro caso, libera normalmente
  return next()
})

// Crie um event bus (podemos exportá-lo para que outros componentes o usem)
export const eventBus = mitt()

// Defina um guarda de navegação que bloqueia por 500ms enquanto a transição inicia
router.beforeEach((to, from, next) => {
  // Emite o evento para iniciar a transição (exibe a faixa)
  eventBus.emit('transition-start')
  // Aguarda 500ms antes de prosseguir
  setTimeout(() => {
    next()
  }, 800)
})

// Após a mudança de rota, emite o evento para finalizar a transição (ocultar a faixa)
router.afterEach(() => {
  eventBus.emit('transition-end')
})

export default router
