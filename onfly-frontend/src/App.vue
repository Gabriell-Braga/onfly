<!-- src/App.vue -->
<template>
  <!-- Componente de transição global -->
  <RouteTransition />
  
  <!-- O router-view pode ter sua própria transição se desejado -->
  <router-view />
</template>

<script setup>
import RouteTransition from '@/components/RouteTransition.vue'
</script>
