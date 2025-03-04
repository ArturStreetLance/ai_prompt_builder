<template>
  <app-layout :user="user">
    <div class="max-w-7xl mx-auto px-4">
      <div class="grid grid-cols-12 gap-4 lg:gap-6">
        <!-- Левая панель -->
        <div class="col-span-12 lg:col-span-3 space-y-4">
          <div class="bg-gray-800/50 backdrop-blur-sm rounded-lg border border-gray-700/50 overflow-hidden">
            <div class="p-3 border-b border-gray-700/50">
              <div class="flex items-center justify-between">
                <h2 class="text-sm font-medium text-white">Мои промпты</h2>
                <button class="p-1 text-indigo-400 hover:text-indigo-300 rounded-md hover:bg-indigo-500/10">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                </button>
              </div>
              <div class="mt-2">
                <input
                  type="text"
                  placeholder="Поиск промптов..."
                  class="w-full text-sm bg-gray-700/50 border border-gray-600/50 rounded-md px-3 py-1.5 text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                >
              </div>
            </div>

            <div class="p-2 max-h-[calc(100vh-220px)] overflow-y-auto">
              <div class="space-y-1.5">
                <div
                  v-for="prompt in prompts"
                  :key="prompt.id"
                  class="group bg-gray-700/30 hover:bg-gray-700/50 p-2.5 rounded-md cursor-move border border-gray-600/30 hover:border-gray-600/50 transition-all"
                  draggable="true"
                  @dragstart="dragStart($event, prompt)"
                >
                  <h3 class="text-sm font-medium text-gray-200 mb-1">{{ prompt.name }}</h3>
                  <p class="text-xs text-gray-400 line-clamp-2">{{ prompt.content }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Центральная панель -->
        <div class="col-span-12 lg:col-span-6 space-y-4">
          <div
            class="min-h-[300px] lg:min-h-[400px] bg-gray-800/50 backdrop-blur-sm rounded-lg border-2 border-dashed border-gray-700/50 p-4"
            @drop="onDrop($event)"
            @dragover.prevent
          >
            <div v-if="promptBlocks.length === 0" class="h-full flex items-center justify-center">
              <p class="text-gray-500 text-sm">Перетащите промпты сюда или создайте новый</p>
            </div>
            <div
              v-for="(block, index) in promptBlocks"
              :key="index"
              class="group bg-gray-700/40 p-3 rounded-md mb-2 border border-gray-600/50"
            >
              <div class="flex items-start justify-between">
                <p class="text-sm text-gray-200">{{ block.content }}</p>
                <button
                  @click="removeBlock(index)"
                  class="ml-2 p-1 text-gray-400 hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <div class="bg-gray-800/50 backdrop-blur-sm rounded-lg border border-gray-700/50 p-4">
            <textarea
              v-model="newPrompt"
              rows="3"
              class="w-full text-sm bg-gray-700/50 border border-gray-600/50 rounded-md px-3 py-2 text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-indigo-500 resize-none"
              placeholder="Введите ваш промпт здесь..."
            ></textarea>
            <div class="flex justify-end mt-3 space-x-3">
              <button
                @click="clearPrompt"
                class="px-3 py-1.5 text-xs text-gray-400 hover:text-gray-300"
              >
                Очистить
              </button>
              <button
                @click="addPrompt"
                class="inline-flex items-center px-3 py-1.5 text-xs bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 hover:text-indigo-200 rounded-md border border-indigo-500/30 transition-all"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Добавить
              </button>
            </div>
          </div>
        </div>

        <!-- Правая панель -->
        <div class="col-span-12 lg:col-span-3">
          <div class="bg-gray-800/50 backdrop-blur-sm rounded-lg border border-gray-700/50">
            <div class="p-3 border-b border-gray-700/50">
              <h2 class="text-sm font-medium text-white">Популярные промпты</h2>
            </div>
            <div class="p-2 max-h-[calc(100vh-220px)] overflow-y-auto">
              <div class="space-y-2">
                <div
                  v-for="prompt in popularPrompts"
                  :key="prompt.id"
                  class="bg-gray-700/30 p-2.5 rounded-md border border-gray-600/30"
                >
                  <div class="flex items-start justify-between">
                    <div>
                      <h3 class="text-sm font-medium text-gray-200 mb-1">{{ prompt.name }}</h3>
                      <p class="text-xs text-gray-400 line-clamp-2">{{ prompt.content }}</p>
                    </div>
                    <div class="flex items-center text-yellow-400/90 ml-2">
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                      </svg>
                      <span class="ml-1 text-xs">{{ prompt.rating }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  user: Object,
  prompts: Array,
  popularPrompts: Array
})

const promptBlocks = ref([])
const newPrompt = ref('')

const dragStart = (event, prompt) => {
  event.dataTransfer.setData('prompt', JSON.stringify(prompt))
}

const onDrop = (event) => {
  const prompt = JSON.parse(event.dataTransfer.getData('prompt'))
  promptBlocks.value.push(prompt)
}

const removeBlock = (index) => {
  promptBlocks.value.splice(index, 1)
}

const addPrompt = () => {
  if (newPrompt.value.trim()) {
    promptBlocks.value.push({
      content: newPrompt.value
    })
    newPrompt.value = ''
  }
}

const clearPrompt = () => {
  newPrompt.value = ''
}

const logout = () => {
  const form = useForm({})
  form.post('/logout')
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
