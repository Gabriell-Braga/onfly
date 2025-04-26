<!-- src/components/RouteTransition.vue -->
<template>
  <transition name="diagonal-slide">
    <div v-if="active" class="fixed -left-20 -top-[500px] z-60 pointer-events-none transform rotate-[5deg] bg-primary-600 w-[200vw] h-[400vh]"></div>
  </transition>
</template>

<script setup>
  import { ref, onMounted, onUnmounted } from 'vue'
  import { eventBus } from '@/router'

  // Estado para controlar a exibição do overlay
  const active = ref(false)

  // Quando a transição iniciar, torna o overlay ativo
  const onStart = () => {
    active.value = true
  }

  // Quando a transição terminar, desativa o overlay
  const onEnd = () => {
    active.value = false
  }

  // Registra os eventos quando o componente for montado
  onMounted(() => {
    eventBus.on('transition-start', onStart)
    eventBus.on('transition-end', onEnd)
  })

  // Remove os eventos quando o componente for desmontado
  onUnmounted(() => {
    eventBus.off('transition-start', onStart)
    eventBus.off('transition-end', onEnd)
  })
</script>

<style scoped>
  /* Defina a transição para a faixa */
  .diagonal-slide-enter-active,
  .diagonal-slide-leave-active {
    transition: transform 2.5s ease;
  }
  .diagonal-slide-enter-from {
    transform: translateX(100%) rotate(5deg);
  }
  .diagonal-slide-enter-to {
    transform: translateX(-100%) rotate(5deg);
  }


  .diagonal-slide-leave-from {
    transform: translateX(-100%) rotate(5deg);
  }
  .diagonal-slide-leave-to {
    transform: translateX(-200%) rotate(5deg);
  }
</style>
