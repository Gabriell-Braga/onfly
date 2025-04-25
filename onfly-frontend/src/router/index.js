// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import mitt from 'mitt'
import Login from '@/views/Login.vue'
import Register from '@/views/Register.vue'
import Dashboard from '@/views/Dashboard.vue'

// Crie as rotas conforme seu app
const routes = [
  { path: '/', name: 'Login', component: Login },
  { path: '/login', name: 'Login', component: Login },
  { path: '/register', name: 'Register', component: Register },
  { path: '/dashboard', name: 'Dashboard', component: Dashboard },
]

// Crie o roteador
const router = createRouter({
  history: createWebHistory(),
  routes
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
