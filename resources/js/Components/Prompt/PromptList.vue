<template>
  <div class="w-full">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold text-white">{{ title }}</h2>
      <n-button 
        v-if="showAddButton" 
        size="small" 
        type="primary"
        @click="showCreateModal = true"
      >
        <template #icon>
          <div class="i-carbon-add" />
        </template>
        Создать промпт
      </n-button>
    </div>

    <div class="flex flex-wrap gap-4">
      <PromptBadge
        v-for="prompt in prompts"
        :key="prompt.id"
        :prompt="prompt"
        @click="$emit('select', prompt)"
        @toggle-favorite="$emit('toggle-favorite', prompt)"
      />

      <div 
        v-if="!prompts.length" 
        class="w-full flex flex-col items-center justify-center p-8 text-center"
      >
        <Icon 
          icon="carbon:document-blank" 
          class="text-4xl text-gray-400 mb-2" 
        />
        <p class="text-gray-400">Нет доступных промптов</p>
      </div>
    </div>

    <create-prompt-modal
      v-model="showCreateModal"
      @created="$emit('refresh')"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { NButton } from 'naive-ui'
import { Icon } from '@iconify/vue'
import PromptBadge from './PromptBadge.vue'
import CreatePromptModal from './CreatePromptModal.vue'

defineProps({
  title: String,
  prompts: {
    type: Array,
    required: true
  },
  showAddButton: {
    type: Boolean,
    default: false
  }
})

defineEmits(['select', 'refresh', 'toggle-favorite'])

const showCreateModal = ref(false)
</script>

<style>
.prompt-enter-active,
.prompt-leave-active {
  transition: all 0.3s ease-out;
}

.prompt-enter-from,
.prompt-leave-to {
  opacity: 0;
  transform: translateY(2rem);
}

::-webkit-scrollbar {
  height: 0.375rem;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.3);
  border-radius: 9999px;
}

::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.5);
}
</style> 