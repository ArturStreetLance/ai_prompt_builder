<template>
  <div class="h-full flex flex-col">
    <h2 class="text-xl font-semibold text-white mb-4">Предпросмотр промпта</h2>

    <div
      class="flex-1 relative bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-lg p-4"
      :class="{
        'border-indigo-500/50 shadow-lg shadow-indigo-500/20 scale-[1.01]': isDragOver,
        'border-dashed': isDragOver,
        'collapsed': isCollapsed
      }"
      @click="toggleCollapse"
      @dragover.prevent="handleDragOver"
      @drop.prevent="handleDrop"
      @dragenter.prevent="handleDragEnter"
      @dragleave.prevent="handleDragLeave"
    >
      <!-- Плавающий текст при перетаскивании -->
      <div
        v-if="isDragging"
        class="absolute pointer-events-none transform-gpu"
        :class="{ 'opacity-0': !isDragging }"
        :style="{
          transform: `translate(${dragPosition.x}px, ${dragPosition.y}px)`
        }"
      >
        <div class="p-3 bg-indigo-600/95 backdrop-blur-sm rounded-lg shadow-lg border border-white/10 max-w-[300px] text-white scale-90">
          <div class="font-semibold">{{ draggedPrompt?.name }}</div>
          <div class="text-sm opacity-75 line-clamp-3">{{ draggedPrompt?.content }}</div>
        </div>
      </div>

      <!-- Оверлей при перетаскивании -->
      <div
        v-if="isDragOver"
        class="absolute inset-0 bg-indigo-500/10 rounded-lg pointer-events-none flex items-center justify-center"
      >
        <div class="text-indigo-300 text-lg font-medium animate-pulse">
          Отпустите для вставки промпта
        </div>
      </div>

      <n-input
        ref="textareaRef"
        v-model:value="content"
        type="textarea"
        placeholder="Перетащите сюда промпты или начните ввод..."
        :autosize="{ minRows: 8, maxRows: 100 }"
        @input="() => debounceResize(updateTextareaStyles)"
        class="resize-none"
      />

      <!-- Кнопки управления -->
      <div 
        v-if="content.trim()"
        class="absolute bottom-6 right-6 flex items-center gap-2"
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
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { NButton, NInput } from 'naive-ui'
import { useForm, router } from '@inertiajs/vue3'
import axios from 'axios'

const content = ref('')
const loading = ref(false)
const form = useForm({})
const textareaRef = ref(null)
const usedPrompts = ref([])
const lastCursorPosition = ref(0)
const isDragOver = ref(false)
const dragPosition = ref({ x: 0, y: 0 })
const draggedPrompt = ref(null)
const isDragging = ref(false)
const dragData = ref(null)
const resizeTimeout = ref(null)
const isCollapsed = ref(false)
const emit = defineEmits(['submit'])

// Добавим функцию для безопасного получения данных промпта
const getPromptData = (event) => {
  try {
    // Пробуем разные форматы данных
    const formats = ['application/json', 'text/plain']
    let data = null
    
    for (const format of formats) {
      try {
        const rawData = event.dataTransfer.getData(format)
        if (rawData) {
          data = JSON.parse(rawData)
          if (data && data.name && data.content) {
            return data
          }
        }
      } catch (e) {
        console.log(`Не удалось получить данные в формате ${format}`)
      }
    }
    
    return null
  } catch (e) {
    console.error('Ошибка при получении данных промпта:', e)
    return null
  }
}

const handleDragEnter = (event) => {
  event.preventDefault()
  isDragOver.value = true
  
  // Пытаемся получить данные промпта при первом входе
  if (!dragData.value) {
    const data = getPromptData(event)
    if (data) {
      dragData.value = JSON.stringify(data)
      draggedPrompt.value = data
      isDragging.value = true
    }
  }

  const textarea = event.target
  if (textarea.tagName === 'TEXTAREA') {
    const rect = textarea.getBoundingClientRect()
    const x = event.clientX - rect.left
    const y = event.clientY - rect.top
    
    const position = getTextPositionFromCoords(textarea, x, y)
    if (position !== -1) {
      lastCursorPosition.value = position
      textarea.setSelectionRange(position, position)
      textarea.focus()
    }
  }
}

const handleDragLeave = (event) => {
  event.preventDefault()
  const rect = event.target.getBoundingClientRect()
  const x = event.clientX
  const y = event.clientY
  
  if (x <= rect.left || x >= rect.right || y <= rect.top || y >= rect.bottom) {
    isDragOver.value = false
    isDragging.value = false
    // Не очищаем dragData здесь, только если действительно отменяем перетаскивание
    if (!isDragOver.value) {
      draggedPrompt.value = null
      dragData.value = null
    }
  }
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

const handleDragOver = (event) => {
  event.preventDefault()
  if (!isDragOver.value) {
    isDragOver.value = true
  }
  
  // Обновляем позицию относительно контейнера
  const rect = event.currentTarget.getBoundingClientRect()
  dragPosition.value = {
    x: event.clientX - rect.left,
    y: event.clientY - rect.top
  }
  
  // Пытаемся получить данные промпта только если его еще нет
  if (!dragData.value && !draggedPrompt.value) {
    const data = getPromptData(event)
    if (data) {
      dragData.value = JSON.stringify(data)
      draggedPrompt.value = data
      isDragging.value = true
    }
  }
}

const handleDrop = (event) => {
  event.preventDefault()
  isDragOver.value = false
  isDragging.value = false
  
  try {
    let prompt
    if (draggedPrompt.value) {
      prompt = draggedPrompt.value
    } else if (dragData.value) {
      prompt = JSON.parse(dragData.value)
    } else {
      prompt = getPromptData(event)
      if (!prompt) {
        throw new Error('Нет данных промпта')
      }
    }

    if (!prompt.name || !prompt.content) {
      throw new Error('Некорректные данные промпта')
    }

    const dropPoint = {
      x: event.clientX,
      y: event.clientY
    }
    
    const container = event.currentTarget
    if (!container) return
    
    // Сразу вставляем текст
    insertPromptAtCursor(prompt, getTextPositionFromCoords(container.querySelector('textarea'), dropPoint.x - container.getBoundingClientRect().left, dropPoint.y - container.getBoundingClientRect().top))

    // Добавляем промпт в список использованных
    addToUsedPrompts(prompt.id)
  } catch (e) {
    console.error('Ошибка при обработке перетаскивания:', e)
  } finally {
    draggedPrompt.value = null
    dragData.value = null
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
}

// Очистка контента
const clearContent = () => {
  content.value = ''
  usedPrompts.value = []
}

// Добавляем промпт в список использованных
const addToUsedPrompts = (promptId) => {
    if (!usedPrompts.value.includes(promptId)) {
        usedPrompts.value.push(promptId)
    }
}

// Обработчик отправки промпта
const handleSubmit = async () => {
    try {
        loading.value = true;
        await axios.post('/prompts/submit', {
            content: content.value,
            promptIds: usedPrompts.value
        }, {
            headers: {
                'X-XSRF-TOKEN': document.cookie.match(/XSRF-TOKEN=([\w-]+)/)?.[1],
            },
            withCredentials: true
        });
        
        // Очищаем поле контента и промпты
        content.value = '';
        usedPrompts.value = [];
        
        // Обновляем данные через Inertia
        router.visit(window.location.pathname, {
            only: ['popularPrompts'],
            preserveScroll: true,
            preserveState: true
        });
        
    } catch (error) {
        console.error('Ошибка при отправке промпта:', error);
    } finally {
        loading.value = false;
    }
}

// Добавляем функцию для дебаунса изменений размера
const debounceResize = (callback, delay = 100) => {
  if (resizeTimeout.value) {
    clearTimeout(resizeTimeout.value)
  }
  resizeTimeout.value = setTimeout(callback, delay)
}

// Обновляем стили для textarea
const updateTextareaStyles = () => {
  const textarea = textareaRef.value?.$el.querySelector('textarea')
  if (textarea) {
    textarea.style.height = 'auto'
    textarea.style.height = `${Math.min(textarea.scrollHeight, window.innerHeight * 0.8)}px`
  }
}

const toggleCollapse = (event) => {
  // Игнорируем клик по кнопкам и хендлерам
  if (
    event.target.closest('.favorite-button') ||
    event.target.closest('.drag-handle')
  ) {
    return
  }
  isCollapsed.value = !isCollapsed.value
}

// Добавляем хук жизненного цикла для очистки
onBeforeUnmount(() => {
  if (resizeTimeout.value) {
    clearTimeout(resizeTimeout.value)
  }
})
</script>

<style>
.resize-none {
  resize: none;
}

.resize-none:focus {
  outline: none;
  --tw-ring-color: rgba(99, 102, 241, 0.5);
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Стили для превью текста при перетаскивании */
.drag-text-preview {
    position: fixed;
    pointer-events: none;
    z-index: 9999;
    background: rgba(31, 41, 55, 0.95);
    backdrop-filter: blur(8px);
    border-radius: 0.5rem;
    padding: 0.75rem;
    max-width: 300px;
    color: white;
    font-size: 0.875rem;
    line-height: 1.4;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.1);
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transform: translate(-50%, -50%);
}

.prompt-badge {
    position: relative;
    width: 300px;
    background-color: rgba(31, 41, 55, 0.5);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(75, 85, 99, 0.5);
    border-radius: 0.5rem;
    overflow: hidden;
    cursor: pointer;
}

.prompt-badge:hover {
    border-color: rgba(99, 102, 241, 0.5);
    box-shadow: 0 0 20px rgba(99, 102, 241, 0.2);
}

.prompt-badge.expanded {
    width: 400px;
    z-index: 10;
}

.prompt-badge.collapsed .content {
    animation: collapseContent 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes collapseContent {
    0% {
        transform: scaleY(1);
        opacity: 1;
        max-height: 500px;
    }
    100% {
        transform: scaleY(0);
        opacity: 0;
        max-height: 0;
    }
}

.prompt-badge .content {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transform-origin: top;
    max-height: 500px;
}

.prompt-badge.expanded .content {
    -webkit-line-clamp: unset;
    animation: expandContent 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes expandContent {
    0% {
        transform: scaleY(0);
        opacity: 0;
        max-height: 0;
    }
    100% {
        transform: scaleY(1);
        opacity: 1;
        max-height: 500px;
    }
}

.prompt-badge .drag-handle {
    cursor: grab;
}

.prompt-badge .drag-handle:active {
    cursor: grabbing;
}

.prompt-badge .favorite-button {
    opacity: 0;
    transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.prompt-badge:hover .favorite-button {
    opacity: 1;
}

.prompt-badge .favorite-button.active {
    opacity: 1;
}
</style>
