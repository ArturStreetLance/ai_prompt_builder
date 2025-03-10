<template>
  <div class="h-full flex flex-col">
    <h2 class="text-xl font-semibold text-white mb-4">Предпросмотр промпта</h2>

    <div
      class="flex-1 relative bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-lg p-4 transition-all duration-300"
      :class="{
        'border-indigo-500/50 shadow-lg shadow-indigo-500/20 scale-[1.01]': isDragOver,
        'border-dashed': isDragOver
      }"
      @dragover.prevent="handleDragOver"
      @drop.prevent="handleDrop"
      @dragenter.prevent="handleDragEnter"
      @dragleave.prevent="handleDragLeave"
    >
      <!-- Плавающий текст при перетаскивании -->
      <div
        v-if="isDragging"
        class="floating-text"
        :style="{
          '--x': dragPosition.x + 'px',
          '--y': dragPosition.y + 'px'
        }"
      >
        <div class="floating-content">
          <div class="font-semibold">{{ draggedPrompt?.name }}</div>
          <div class="text-sm opacity-75">{{ draggedPrompt?.content }}</div>
        </div>
      </div>

      <!-- Оверлей при перетаскивании -->
      <div
        v-if="isDragOver"
        class="absolute inset-0 bg-indigo-500/10 rounded-lg pointer-events-none
               flex items-center justify-center"
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
        class="resize-observer-optimized"
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
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { NButton, NInput } from 'naive-ui'
import { useForm } from '@inertiajs/vue3'

const content = ref('')
const loading = ref(false)
const form = useForm({})
const textareaRef = ref(null)
const usedPrompts = ref(new Set())
const lastCursorPosition = ref(0)
const isDragOver = ref(false)
const dragPosition = ref({ x: 0, y: 0 })
const draggedPrompt = ref(null)
const isDragging = ref(false)
const dragData = ref(null)
const resizeTimeout = ref(null)

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
      // Последняя попытка получить данные
      prompt = getPromptData(event)
      if (!prompt) {
        throw new Error('Нет данных промпта')
      }
    }

    // Проверяем, что у промпта есть необходимые поля
    if (!prompt.name || !prompt.content) {
      throw new Error('Некорректные данные промпта')
    }

    const dropPoint = {
      x: event.clientX,
      y: event.clientY
    }
    
    // Получаем контейнер заранее
    const container = event.currentTarget
    if (!container) return
    
    // Создаем элемент для анимации падения
    const fallEffect = document.createElement('div')
    fallEffect.className = 'fall-effect'
    fallEffect.innerHTML = `
      <div class="fall-content">
        <div class="font-semibold">${prompt.name}</div>
        <div class="text-sm opacity-75">${prompt.content.substring(0, 50)}...</div>
      </div>
    `
    
    // Устанавливаем начальную позицию
    const rect = container.getBoundingClientRect()
    const startX = dropPoint.x - rect.left
    const startY = dropPoint.y - rect.top
    
    fallEffect.style.setProperty('--start-x', `${startX}px`)
    fallEffect.style.setProperty('--start-y', `${startY}px`)
    
    // Добавляем элемент
    container.appendChild(fallEffect)
    
    // Запускаем анимацию падения и вставляем текст
    setTimeout(() => {
      if (fallEffect && fallEffect.parentNode) {
        fallEffect.parentNode.removeChild(fallEffect)
      }

      insertPromptAtCursor(prompt, getTextPositionFromCoords(container.querySelector('textarea'), startX, startY))
      
      // Добавляем эффект волны
      const rippleEffect = document.createElement('div')
      rippleEffect.className = 'ripple-effect'
      rippleEffect.style.setProperty('--x', `${startX}px`)
      rippleEffect.style.setProperty('--y', `${startY}px`)
      
      container.appendChild(rippleEffect)
      setTimeout(() => {
        if (rippleEffect && rippleEffect.parentNode) {
          rippleEffect.parentNode.removeChild(rippleEffect)
        }
      }, 1000)
    }, 300)
  } catch (e) {
    console.error('Ошибка при обработке перетаскивания:', e)
  } finally {
    // Очищаем все данные
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

// Добавляем хук жизненного цикла для очистки
onBeforeUnmount(() => {
  if (resizeTimeout.value) {
    clearTimeout(resizeTimeout.value)
  }
})
</script>

<style scoped>
/* Стили для оптимизации ResizeObserver */
.resize-observer-optimized {
  contain: content;
  overflow: hidden;
}

.resize-observer-optimized :deep(textarea) {
  min-height: 200px !important;
  cursor: text !important;
  padding-bottom: 60px !important;
  font-size: 1.1rem !important;
  line-height: 1.6 !important;
  transition: height 0.2s ease;
  will-change: height;
  overflow-y: hidden;
}

/* Остальные стили остаются без изменений */
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

/* Анимация при наведении */
.scale-\[1\.01\] {
  transform: scale(1.01);
}

/* Анимация пульсации текста */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Плавающий текст при перетаскивании */
.floating-text {
  position: absolute;
  top: 0;
  left: 0;
  pointer-events: none;
  z-index: 9999;
  transform: translate(
    calc(var(--x) - 50%),
    calc(var(--y) - 50%)
  );
  transition: transform 0.1s ease-out;
}

.floating-content {
  padding: 0.75rem 1rem;
  background: rgba(99, 102, 241, 0.9);
  backdrop-filter: blur(8px);
  border-radius: 0.5rem;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  max-width: 300px;
  transform-origin: center;
  animation: floatPulse 2s ease-in-out infinite;
}

@keyframes floatPulse {
  0%, 100% {
    transform: translateY(0) scale(1);
  }
  50% {
    transform: translateY(-4px) scale(1.02);
  }
}

/* Анимация падения */
.fall-effect {
  position: absolute;
  top: 0;
  left: 0;
  pointer-events: none;
  z-index: 9999;
  transform: translate(
    calc(var(--start-x) - 50%),
    calc(var(--start-y) - 50%)
  );
  animation: fallDown 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

.fall-content {
  padding: 0.75rem 1rem;
  background: rgba(99, 102, 241, 0.9);
  backdrop-filter: blur(8px);
  border-radius: 0.5rem;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  max-width: 300px;
  transform-origin: center;
  animation: fallRotate 0.3s ease-out forwards;
}

@keyframes fallDown {
  0% {
    transform: translate(
      calc(var(--start-x) - 50%),
      calc(var(--start-y) - 50%)
    );
  }
  100% {
    transform: translate(
      calc(var(--start-x) - 50%),
      calc(var(--start-y) - 50%) translateY(10px)
    );
    opacity: 0;
  }
}

@keyframes fallRotate {
  0% {
    transform: rotate(0deg) scale(1);
  }
  100% {
    transform: rotate(10deg) scale(0.8);
  }
}

/* Эффект волны при вставке */
.ripple-effect {
  position: absolute;
  top: var(--y);
  left: var(--x);
  width: 4px;
  height: 4px;
  background: rgba(99, 102, 241, 0.5);
  border-radius: 50%;
  transform: translate(-50%, -50%);
  animation: ripple 1s cubic-bezier(0, 0, 0.2, 1);
}

@keyframes ripple {
  0% {
    width: 0;
    height: 0;
    opacity: 0.5;
    filter: brightness(2);
  }
  100% {
    width: 400px;
    height: 400px;
    opacity: 0;
    filter: brightness(1);
  }
}
</style>
