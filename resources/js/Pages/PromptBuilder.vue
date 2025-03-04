<template>
  <app-layout :user="user">
    <div class="max-w-7xl mx-auto px-4">
      <div class="grid grid-cols-12 gap-6">
        <!-- Левая колонка - Мои промпты -->
        <div class="col-span-12 lg:col-span-3">
          <prompt-list
            title="Мои промпты"
            :prompts="prompts"
            :show-add-button="true"
            @select="handlePromptSelect"
          />
        </div>

        <!-- Центральная колонка - Предпросмотр -->
        <div class="col-span-12 lg:col-span-6">
          <prompt-preview ref="previewRef" />
        </div>

        <!-- Правая колонка - Популярные промпты -->
        <div class="col-span-12 lg:col-span-3">
          <prompt-list
            title="Популярные промпты"
            :prompts="popularPrompts"
            @select="handlePromptSelect"
          />
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PromptList from '@/Components/Prompt/PromptList.vue'
import PromptPreview from '@/Components/Prompt/PromptPreview.vue'

defineProps({
  user: Object,
  prompts: Array,
  popularPrompts: Array,
})

const previewRef = ref(null)

const handlePromptSelect = (prompt) => {
  if (previewRef.value) {
    previewRef.value.title = prompt.name
    previewRef.value.content = prompt.content
  }
}
</script>

<style>
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Стилизация скроллбара */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.3);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.5);
}
</style>
