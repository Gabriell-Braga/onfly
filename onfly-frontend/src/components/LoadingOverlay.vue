<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-white/60 backdrop-blur-xs">
      <div class="relative w-16 h-16">
        <!-- Anel base (claro) -->
        <div class="absolute inset-0 rounded-full border-8 border-blue-200"></div>
        <!-- Anel giratório (escuro) -->
        <div class="absolute inset-0 rounded-full border-8 border-primary-600 border-t-transparent animate-spin"></div>
      </div>
    </div>
  </template>
  
  <script setup>
  defineProps({
    show: Boolean
  })
  </script>
  