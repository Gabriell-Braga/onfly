import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || '',
  }),
  actions: {
    setToken(token) {
      this.token = token
      localStorage.setItem('token', token)
    },
    getToken() {
      return this.token || localStorage.getItem('token')
    },
    logout() {
      this.token = ''
      localStorage.removeItem('token')
    },
  },
})
