<template>
  <div class="h-full flex flex-col">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold text-white">Предпросмотр промпта</h2>
      <div class="flex items-center space-x-2">
        <n-button type="default" @click="clearContent">
          Очистить
        </n-button>
        <n-button type="primary" :loading="loading" @click="handleSubmit">
          Отправить
        </n-button>
      </div>
    </div>

    <!-- Список добавленных промптов -->
    <div class="mb-4 flex flex-wrap gap-2" v-if="addedPrompts.length > 0">
      <div v-for="(prompt, index) in addedPrompts" 
           :key="index"
           class="flex items-center gap-2 px-3 py-1.5 bg-gray-700/50 rounded-full"
      >
        <span class="text-sm text-gray-300 truncate max-w-[150px]">
          {{ prompt.name }}
        </span>
        <button @click="removePrompt(index)" 
                class="text-gray-400 hover:text-red-400 transition-colors">
          <Icon icon="carbon:close" class="text-lg" />
        </button>
      </div>
    </div>

    <div
      class="flex-1 bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-lg p-4"
      @dragover.prevent
      @drop="handleDrop"
    >
      <n-input
        v-model:value="content"
        type="textarea"
        placeholder="Перетащите сюда промпты или начните ввод..."
        :autosize="{ maxRows: 100 }"
        @drop="handleTextAreaDrop"
        @dragover.prevent
      />
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { NButton, NInput } from 'naive-ui'
import { useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const content = ref('')
const loading = ref(false)
const form = useForm({})
const addedPrompts = ref([])

// Обработка перетаскивания в область
const handleDrop = (event) => {
  event.preventDefault()
  try {
    const prompt = JSON.parse(event.dataTransfer.getData('text/plain'))
    addPrompt(prompt)
  } catch (e) {
    console.error('Ошибка при обработке перетаскивания:', e)
  }
}

// Обработка перетаскивания в текстовое поле
const handleTextAreaDrop = (event) => {
  event.preventDefault()
  try {
    const prompt = JSON.parse(event.dataTransfer.getData('text/plain'))
    const target = event.target
    const startPos = target.selectionStart
    const endPos = target.selectionEnd
    
    // Добавляем промпт в список и в контент
    addPrompt(prompt)
    
    // Увеличиваем счетчик использования
    form.post(`/prompts/${prompt.id}/increment-usage`)
  } catch (e) {
    console.error('Ошибка при обработке перетаскивания в текстовое поле:', e)
  }
}

// Добавление промпта
const addPrompt = (prompt) => {
  // Проверяем, не добавлен ли уже этот промпт
  if (!addedPrompts.value.find(p => p.id === prompt.id)) {
    addedPrompts.value.push(prompt)
    updateContent()
  }
}

// Удаление промпта
const removePrompt = (index) => {
  addedPrompts.value.splice(index, 1)
  updateContent()
}

// Обновление контента на основе добавленных промптов
const updateContent = () => {
  content.value = addedPrompts.value
    .map(prompt => prompt.content)
    .join('\n\n')
}

// Очистка всего контента
const clearContent = () => {
  content.value = ''
  addedPrompts.value = []
}

const handleSubmit = async () => {
  if (!content.value.trim()) return
  
  loading.value = true
  try {
    await form.post('/prompts/submit', {
      content: content.value,
      promptIds: addedPrompts.value.map(p => p.id)
    })
    clearContent()
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.n-input {
  background: transparent !important;
}
</style>
