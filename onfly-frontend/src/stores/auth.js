import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || '',
    user: JSON.parse(localStorage.getItem('user')) || null,
  }),

  actions: {
    setToken(token) {
      this.token = token
      localStorage.setItem('token', token)
      this.fetchUser()
    },

    setUser(user) {
      this.user = user
      localStorage.setItem('user', JSON.stringify(user))
    },

    getToken() {
      return this.token
    },

    getUser() {
      return this.user
    },

    isAdmin() {
      return this.user?.is_admin === true
    },

    async logout() {
      try {
        await axios.post(`${import.meta.env.VITE_API_URL}/api/logout`, {}, {
          headers: { Authorization: `Bearer ${this.token}` },
        })
      } catch (err) {
        console.error('Erro ao fazer logout:', err)
      } finally {
        this.clearData()
      }
    },

    clearData() {
      this.token = ''
      this.user = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    },

    async fetchUser() {
      if (!this.token) return

      try {
        const { data } = await axios.get(`${import.meta.env.VITE_API_URL}/api/me`, {
          headers: { Authorization: `Bearer ${this.token}` },
        })
        this.setUser(data)
      } catch (err) {
        console.error('Erro ao buscar informações do usuário:', err)
      }
    },

    async refreshToken() {
      if (!this.token) return

      try {
        const { data } = await axios.post(`${import.meta.env.VITE_API_URL}/api/refresh`, {}, {
          headers: { Authorization: `Bearer ${this.token}` },
        })
        this.setToken(data.access_token)
      } catch (err) {
        console.error('Erro ao fazer refresh do token:', err)
        this.logout()
      }
    },

    configurarInterceptor() {
      axios.interceptors.response.use(
        response => response,
        async error => {
          const originalRequest = error.config
    
          if (error.response && error.response.status === 401 && !originalRequest._retry) {
            // Se o erro foi no refreshToken, já desloga
            if (originalRequest.url.includes('/refresh')) {
              this.clearData()
              window.location.href = '/login'
              return Promise.reject(error)
            }
    
            originalRequest._retry = true
    
            try {
              await this.refreshToken()
              originalRequest.headers['Authorization'] = `Bearer ${this.getToken()}`
              return axios(originalRequest)
            } catch (refreshError) {
              await this.logout()
              window.location.href = '/login'
              return Promise.reject(refreshError)
            }
          }
    
          return Promise.reject(error)
        }
      )
    }    
  },
})
