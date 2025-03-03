<template>
  <div class="min-h-screen bg-gray-100">
    <div class="container mx-auto px-4 py-8">
      <div class="grid grid-cols-12 gap-6">
        <!-- Сайдбар с шаблонами -->
        <div class="col-span-3 bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold mb-4">Шаблоны</h2>
          <div class="space-y-2">
            <div
              v-for="template in templates"
              :key="template.id"
              class="p-2 bg-gray-50 rounded cursor-move"
              draggable="true"
              @dragstart="dragStart($event, template)"
            >
              {{ template.name }}
            </div>
          </div>
        </div>

        <!-- Основная область конструктора -->
        <div 
          class="col-span-9 bg-white rounded-lg shadow p-4"
          @drop="onDrop($event)"
          @dragover.prevent
        >
          <h2 class="text-lg font-semibold mb-4">Конструктор промптов</h2>
          
          <div class="min-h-[400px] border-2 border-dashed border-gray-300 rounded-lg p-4">
            <div v-for="(block, index) in promptBlocks" :key="index" class="mb-2">
              <div class="flex items-center gap-2 p-2 bg-gray-50 rounded">
                <div class="flex-1">{{ block.content }}</div>
                <button @click="removeBlock(index)" class="text-red-500">
                  Удалить
                </button>
              </div>
            </div>
          </div>

          <!-- Предпросмотр -->
          <div class="mt-4">
            <h3 class="font-semibold mb-2">Предпросмотр:</h3>
            <div class="p-4 bg-gray-50 rounded">
              {{ compiledPrompt }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const templates = ref([])
const promptBlocks = ref([])

// Загрузка шаблонов
const loadTemplates = async () => {
  try {
    const response = await fetch('/api/prompt-templates')
    templates.value = await response.json()
  } catch (error) {
    console.error('Ошибка загрузки шаблонов:', error)
  }
}

// Drag & Drop функционал
const dragStart = (event, template) => {
  event.dataTransfer.setData('template', JSON.stringify(template))
}

const onDrop = (event) => {
  const template = JSON.parse(event.dataTransfer.getData('template'))
  promptBlocks.value.push(template)
}

const removeBlock = (index) => {
  promptBlocks.value.splice(index, 1)
}

// Компиляция финального промпта
const compiledPrompt = computed(() => {
  return promptBlocks.value
    .map(block => block.content)
    .join('\n')
})

// Загрузка данных при монтировании
onMounted(() => {
  loadTemplates()
})
</script> 