<template>
  <div class="h-full flex flex-col">
    <h2 class="text-xl font-semibold text-white mb-4">Предпросмотр промпта</h2>

    <div
      class="flex-1 relative bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-lg p-4"
      @dragover.prevent
    >
      <n-input
        ref="textareaRef"
        v-model:value="content"
        type="textarea"
        placeholder="Перетащите сюда промпты или начните ввод..."
        :autosize="{ minRows: 8, maxRows: 100 }"
        @drop.prevent="handleDrop"
        @dragover.prevent
        @dragenter.prevent="handleDragEnter"
        @dragleave.prevent="handleDragLeave"
      />

      <!-- Кнопки управления -->
      <div 
        v-if="content.trim()"
        class="absolute bottom-6 right-6 flex items-center gap-2 transition-opacity duration-200"
        :class="content.trim() ? 'opacity-100' : 'opacity-0'"
      >
        <n-button 
          type="default" 
          @click="clearContent"
          class="bg-gray-700/50 hover:bg-gray-600/50 text-gray-300"
        >
          Очистить
        </n-button>
        <n-button 
          type="primary" 
          :loading="loading" 
          @click="handleSubmit"
          class="bg-indigo-600/90 hover:bg-indigo-500/90"
        >
          Отправить
        </n-button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { NButton, NInput } from 'naive-ui'
import { useForm } from '@inertiajs/vue3'

const content = ref('')
const loading = ref(false)
const form = useForm({})
const textareaRef = ref(null)
const usedPrompts = ref(new Set())
const lastCursorPosition = ref(0)

// Сохраняем позицию курсора при перемещении мыши над textarea
const handleDragEnter = (event) => {
  const textarea = event.target
  if (textarea.tagName === 'TEXTAREA') {
    // Получаем позицию курсора относительно текста
    const rect = textarea.getBoundingClientRect()
    const x = event.clientX - rect.left
    const y = event.clientY - rect.top
    
    // Находим позицию в тексте на основе координат мыши
    const position = getTextPositionFromCoords(textarea, x, y)
    if (position !== -1) {
      lastCursorPosition.value = position
      // Устанавливаем курсор в эту позицию
      textarea.setSelectionRange(position, position)
      textarea.focus()
    }
  }
}

const handleDragLeave = () => {
  // Можно добавить дополнительную логику при необходимости
}

// Функция для определения позиции в тексте на основе координат
const getTextPositionFromCoords = (textarea, x, y) => {
  // Создаем временный элемент для измерения текста
  const div = document.createElement('div')
  div.style.cssText = window.getComputedStyle(textarea, null).cssText
  div.style.height = 'auto'
  div.style.position = 'absolute'
  div.style.visibility = 'hidden'
  div.style.whiteSpace = 'pre-wrap'
  document.body.appendChild(div)

  const text = textarea.value
  let line = 0
  let char = 0
  let found = false
  let position = 0

  // Перебираем строки текста
  const lines = text.split('\n')
  for (let i = 0; i < lines.length; i++) {
    div.textContent = lines[i]
    const lineHeight = div.offsetHeight
    if (y <= (i + 1) * lineHeight) {
      line = i
      // Находим символ в строке на основе x-координаты
      let left = 0
      for (let j = 0; j < lines[i].length; j++) {
        div.textContent = lines[i].substring(0, j + 1)
        if (x <= div.offsetWidth) {
          char = j
          found = true
          break
        }
      }
      if (!found) char = lines[i].length
      break
    }
    position += lines[i].length + 1 // +1 для учета символа новой строки
  }

  document.body.removeChild(div)

  if (!found) {
    return text.length
  }

  // Вычисляем итоговую позицию
  return position + char
}

// Обработка drop события
const handleDrop = (event) => {
  event.preventDefault()
  try {
    const prompt = JSON.parse(event.dataTransfer.getData('text/plain'))
    insertPromptAtCursor(prompt, lastCursorPosition.value)
  } catch (e) {
    console.error('Ошибка при обработке перетаскивания:', e)
  }
}

// Вставка промпта в позицию курсора
const insertPromptAtCursor = (prompt, position = null) => {
  const textarea = textareaRef.value?.$el.querySelector('textarea')
  if (!textarea) return

  const cursorPos = position ?? textarea.selectionStart
  const textBefore = content.value.substring(0, cursorPos)
  const textAfter = content.value.substring(cursorPos)
  
  // Добавляем пробелы вокруг текста, если нужно
  const needSpaceBefore = textBefore && !textBefore.endsWith(' ')
  const needSpaceAfter = textAfter && !textAfter.startsWith(' ')
  
  const separator = needSpaceBefore ? ' ' : ''
  const endSeparator = needSpaceAfter ? ' ' : ''
  
  content.value = textBefore + separator + prompt.content + endSeparator + textAfter

  // Устанавливаем курсор после вставленного текста
  const newPosition = cursorPos + separator.length + prompt.content.length + endSeparator.length
  setTimeout(() => {
    textarea.setSelectionRange(newPosition, newPosition)
    textarea.focus()
  })

  // Просто добавляем промпт в список использованных, без increment-usage
  if (!usedPrompts.value.has(prompt.id)) {
    usedPrompts.value.add(prompt.id)
  }
}

// Очистка контента
const clearContent = () => {
  content.value = ''
  usedPrompts.value.clear()
}

const handleSubmit = async () => {
  if (!content.value.trim()) return
  
  loading.value = true
  try {
    // Сначала увеличиваем счетчики использования для всех промптов
    const incrementPromises = Array.from(usedPrompts.value).map(promptId => 
      form.post(`/prompts/${promptId}/increment-usage`)
    )
    await Promise.all(incrementPromises)

    // Затем отправляем сформированный текст
    await form.post('/prompts/submit', {
      content: content.value,
      promptIds: Array.from(usedPrompts.value)
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

.n-input :deep(textarea) {
  min-height: 200px !important;
  cursor: text !important;
  padding-bottom: 60px !important; /* Место для кнопок */
  font-size: 1.1rem !important;
  line-height: 1.6 !important;
}

/* Стили для кнопок */
.n-button {
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.2s ease;
}

.n-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
</style>
