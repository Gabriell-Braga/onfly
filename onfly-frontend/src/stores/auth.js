import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || '',
    user: JSON.parse(localStorage.getItem('user')) || null,
  }),

  actions: {
    // Salvar o token
    setToken(token) {
      this.token = token
      localStorage.setItem('token', token)
      this.fetchUser()
    },

    // Salvar as informações do usuário
    setUser(user) {
      this.user = user
      localStorage.setItem('user', JSON.stringify(user))
    },

    // Retornar token
    getToken() {
      return this.token
    },

    // Retornar usuário
    getUser() {
      return this.user
    },

    isAdmin() {
      return this.user?.is_admin === true
    },

    // Logout
    async logout() {
      try{
        await axios.post(`${import.meta.env.VITE_API_URL}/api/logout`, {}, {
          headers: {
            Authorization: `Bearer ${this.token}`,
          },
        })
      }catch(err){
        console.error('Erro ao fazer logout:', err)
      }finally{
        this.token = ''
        this.user = null
        localStorage.removeItem('token')
        localStorage.removeItem('user')
      }
    },

    // Buscar informações atualizadas do usuário (me)
    async fetchUser() {
      if (!this.token) return

      try {
        const { data } = await axios.get(`${import.meta.env.VITE_API_URL}/api/me`, {
          headers: {
            Authorization: `Bearer ${this.token}`,
          },
        })
        this.setUser(data)
      } catch (err) {
        console.error('Erro ao buscar informações do usuário:', err)
      }
    },

    // Fazer refresh do token
    async refreshToken() {
      if (!this.token) return

      try {
        const { data } = await axios.post(`${import.meta.env.VITE_API_URL}/api/refresh`, {}, {
          headers: {
            Authorization: `Bearer ${this.token}`,
          },
        })
        this.setToken(data.access_token)
      } catch (err) {
        console.error('Erro ao fazer refresh do token:', err)
        this.logout()
      }
    }
  },
})
