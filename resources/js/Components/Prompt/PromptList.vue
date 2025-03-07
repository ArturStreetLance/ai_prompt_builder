<template>
  <div class="space-y-4">
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

    <div class="grid gap-4 grid-cols-1">
      <prompt-card
        v-for="prompt in prompts"
        :key="prompt.id"
        :prompt="prompt"
        @click="$emit('select', prompt)"
      />
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
import PromptCard from './PromptCard.vue'
import CreatePromptModal from './CreatePromptModal.vue'

defineProps({
  title: String,
  prompts: Array,
  showAddButton: {
    type: Boolean,
    default: false
  }
})

defineEmits(['select', 'refresh'])

const showCreateModal = ref(false)
</script> 