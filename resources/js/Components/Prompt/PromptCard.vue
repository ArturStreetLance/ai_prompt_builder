<template>
  <div
    class="p-4 bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-lg shadow-lg hover:shadow-indigo-500/20 transition-all cursor-move"
    :class="[`border-l-4 border-l-${categoryColor}-500`]"
    draggable="true"
    @dragstart="handleDragStart"
    @click="$emit('click', prompt)"
  >
    <div class="flex items-start justify-between">
      <h3 class="text-lg font-medium text-white">{{ prompt.name }}</h3>
      <n-badge :value="prompt.usage_count" type="info" />
    </div>
    
    <p class="mt-2 text-sm text-gray-300 line-clamp-2">
      {{ prompt.content }}
    </p>
    
    <div class="mt-3 flex items-center justify-between">
      <span class="text-xs text-gray-400">
        {{ prompt.category }}
      </span>
      <div class="flex items-center space-x-2">
        <n-rate :value="prompt.rating" readonly size="small" />
        <span class="text-xs text-gray-400">
          {{ prompt.rating }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { NBadge, NRate } from 'naive-ui'

const props = defineProps({
  prompt: {
    type: Object,
    required: true
  }
})

const categoryColor = computed(() => {
  const colors = {
    'Копирайтинг': 'blue',
    'Разработка': 'green',
    'Маркетинг': 'purple',
    'SEO': 'orange',
    'Творчество': 'pink'
  }
  return colors[props.prompt.category] || 'gray'
})

const handleDragStart = (event) => {
  event.dataTransfer.setData('text/plain', JSON.stringify(props.prompt))
  event.dataTransfer.effectAllowed = 'copy'
}
</script> 