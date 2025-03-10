<template>
    <button
        @click="$emit('click')"
        class="fixed bottom-8 right-8 flex items-center gap-2 px-6 py-3
               bg-gradient-to-r from-indigo-500 to-indigo-600
               hover:from-indigo-600 hover:to-indigo-700
               text-white font-medium rounded-xl shadow-lg
               hover:shadow-indigo-500/30 transform hover:scale-105
               transition-all duration-300 ease-out
               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-900"
    >
        <Icon icon="carbon:add" class="text-xl" />
        <span>Создать промпт</span>
        
        <!-- Эффект пульсации -->
        <div class="absolute inset-0 rounded-xl">
            <div class="absolute inset-0 rounded-xl bg-indigo-400 animate-ping opacity-20"></div>
        </div>
    </button>
</template>

<script setup>
import { Icon } from '@iconify/vue'

defineEmits(['click'])
</script>

<style scoped>
/* Анимация пульсации */
@keyframes ping {
    75%, 100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

.animate-ping {
    animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style> 