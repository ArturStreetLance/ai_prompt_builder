<template>
    <div 
        class="group relative transition-all duration-300 ease-out cursor-pointer shadow-lg w-max"
        :class="[
            isExpanded ? 'fixed inset-4 z-50 bg-gray-900/50' : '',
            { 'opacity-50': isDragging }
        ]"
        draggable="true"
        @dragstart="handleDragStart"
        @dragend="handleDragEnd"
        @click="isExpanded = !isExpanded"
    >
        <!-- Бейдж -->
        <div v-if="!isExpanded"
            class="flex items-center h-12 border-[3px] rounded-full w-max relative overflow-hidden"
            :class="[badgeStyle.bg, badgeStyle.border]"
        >
            <!-- Прогресс бар -->
            <div v-if="isDragging" class="absolute left-0 top-0 h-1 bg-white/30">
                <div class="h-full bg-white/60 progress-animation"></div>
            </div>
            
            <!-- Избранное -->
            <n-button
                circle
                size="small"
                :type="localIsFavorite ? 'warning' : 'default'"
                class="border-none bg-white/20 backdrop-blur-sm hover:scale-110 transition-transform shadow-md mr-2.5"
                @click.stop="handleFavoriteToggle"
            >
                <template #icon>
                    <Icon
                        :icon="localIsFavorite ? 'carbon:star-filled' : 'carbon:star'"
                        :class="[
                            localIsFavorite ? 'text-yellow-400' : badgeStyle.text,
                            'transition-colors text-lg'
                        ]"
                    />
                </template>
            </n-button>

            <!-- Название -->
            <span 
                :class="['text-base font-bold truncate mr-2.5', badgeStyle.text]"
            >
                {{ prompt.name }}
            </span>

            <!-- Рейтинг в бейдже -->
            <n-rate 
                :value="localRating" 
                readonly
                size="small"
                class="bg-white/20 backdrop-blur-sm rounded-full px-3 py-1 shadow-md"
            >
                <template #default="{ index }">
                    <n-icon 
                        :size="14" 
                        :class="index <= localRating ? 'text-yellow-400' : badgeStyle.text"
                    >
                        <Icon icon="carbon:star-filled" />
                    </n-icon>
                </template>
            </n-rate>
        </div>

        <!-- Карточка -->
        <div v-else
            class="w-full bg-white dark:bg-gray-800 rounded-xl overflow-hidden"
        >
            <!-- Градиентная полоса -->
            <div class="w-full h-2" :class="badgeStyle.bg"></div>
            
            <div class="p-4 flex flex-col gap-4">
                <!-- Избранное и название -->
                <div class="flex items-center">
                    <n-button
                        circle
                        size="small"
                        :type="localIsFavorite ? 'warning' : 'default'"
                        class="border-none hover:scale-110 transition-transform shadow-md flex-shrink-0 mr-2.5"
                        @click.stop="handleFavoriteToggle"
                    >
                        <template #icon>
                            <Icon
                                :icon="localIsFavorite ? 'carbon:star-filled' : 'carbon:star'"
                                :class="[
                                    localIsFavorite ? 'text-yellow-400' : 'text-gray-400',
                                    'transition-colors text-lg'
                                ]"
                            />
                        </template>
                    </n-button>

                    <span class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ prompt.name }}
                    </span>
                </div>

                <!-- Контент и рейтинг -->
                <div class="flex flex-col gap-4 " @click.stop>
                    <p class="block p-3 rounded-xl bg-black/[0.03] dark:bg-white/[0.03] transition-all duration-300 ease-out text-sm text-gray-600 dark:text-gray-300 whitespace-pre-wrap">
                        {{ prompt.content }}
                    </p>

                    <!-- Рейтинг в карточке -->
                    <n-rate 
                        :value="localRating" 
                        :readonly="isUpdating"
                        size="small"
                        class="w-fit bg-gray-100 dark:bg-gray-700/50 rounded-full px-3 py-1"
                        @click.stop
                        @update:value="handleRatingChange"
                    >
                        <template #default="{ index }">
                            <n-icon 
                                :size="14" 
                                :class="[
                                    index <= localRating ? 'text-yellow-400' : 'text-gray-400',
                                    { 'opacity-50': isUpdating }
                                ]"
                            >
                                <Icon icon="carbon:star-filled" />
                            </n-icon>
                        </template>
                    </n-rate>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { NRate, NButton, NCollapseTransition } from 'naive-ui'
import { Icon } from '@iconify/vue'
import axios from 'axios'

const props = defineProps({
    prompt: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['toggle-favorite', 'update-rating'])
const isDragging = ref(false)
const isExpanded = ref(false)
const isUpdating = ref(false)
const localRating = ref(props.prompt.rating)
const localIsFavorite = ref(props.prompt.is_favorite)

const handleRatingChange = async (value) => {
    if (isUpdating.value) return
    isUpdating.value = true
    
    try {
        const response = await axios.post(`/prompts/${props.prompt.id}/update-rating`, {
            rating: value
        })
        if (response.data.success) {
            localRating.value = value
            emit('update-rating', value)
        }
    } catch (error) {
        console.error('Error updating rating:', error)
    } finally {
        isUpdating.value = false
    }
}

const handleFavoriteToggle = async () => {
    if (isUpdating.value) return
    isUpdating.value = true
    
    try {
        const response = await axios.post(`/prompts/${props.prompt.id}/toggle-favorite`)
        if (response.data.success) {
            localIsFavorite.value = response.data.is_favorite
            emit('toggle-favorite', response.data.is_favorite)
            props.prompt.is_favorite = response.data.is_favorite
        }
    } catch (error) {
        console.error('Error toggling favorite:', error)
    } finally {
        isUpdating.value = false
    }
}

// Цветовые схемы для бейджей
const colorSchemes = {
    'Копирайтинг': {
        bg: 'bg-gradient-to-r from-blue-800 via-blue-600 to-blue-500',
        text: 'text-white',
        border: 'border-blue-400',
        popupBorder: 'border-blue-500'
    },
    'Разработка': {
        bg: 'bg-gradient-to-r from-emerald-800 via-emerald-600 to-emerald-500',
        text: 'text-white',
        border: 'border-emerald-400',
        popupBorder: 'border-emerald-500'
    },
    'Маркетинг': {
        bg: 'bg-gradient-to-r from-violet-800 via-violet-600 to-violet-500',
        text: 'text-white',
        border: 'border-violet-400',
        popupBorder: 'border-violet-500'
    },
    'SEO': {
        bg: 'bg-gradient-to-r from-amber-600 via-amber-500 to-amber-400',
        text: 'text-gray-900',
        border: 'border-amber-300',
        popupBorder: 'border-amber-400'
    },
    'Творчество': {
        bg: 'bg-gradient-to-r from-rose-800 via-rose-600 to-rose-500',
        text: 'text-white',
        border: 'border-rose-400',
        popupBorder: 'border-rose-500'
    }
}

const badgeStyle = computed(() => {
    return colorSchemes[props.prompt.category] || {
        bg: 'bg-gradient-to-r from-gray-700 to-gray-600',
        text: 'text-white',
        border: 'border-gray-500/30',
        popupBorder: 'border-gray-600/20'
    }
})

const handleDragStart = (event) => {
    isDragging.value = true
    isExpanded.value = false
    
    event.dataTransfer.setData('text/plain', JSON.stringify(props.prompt))
    event.dataTransfer.effectAllowed = 'copy'
    
    // Создаем превью с текстом промпта
    const dragPreview = document.createElement('div')
    dragPreview.className = 'drag-text-preview'
    dragPreview.textContent = props.prompt.content
    
    document.body.appendChild(dragPreview)
    
    // Обновляем позицию превью при перетаскивании
    const updatePreviewPosition = (e) => {
        if (dragPreview) {
            dragPreview.style.left = (e.clientX + 10) + 'px'
            dragPreview.style.top = (e.clientY + 10) + 'px'
        }
    }
    
    document.addEventListener('dragover', updatePreviewPosition)
    
    // Очищаем при завершении
    const cleanup = () => {
        document.removeEventListener('dragover', updatePreviewPosition)
        if (dragPreview && dragPreview.parentNode) {
            dragPreview.parentNode.removeChild(dragPreview)
        }
        
        // Создаем анимацию падения на месте курсора
        const dropEffect = document.createElement('div')
        dropEffect.className = 'drop-animation'
        dropEffect.textContent = props.prompt.name
        dropEffect.style.left = (event.clientX - 50) + 'px'
        dropEffect.style.top = event.clientY + 'px'
        document.body.appendChild(dropEffect)
        
        // Удаляем эффект после завершения анимации
        dropEffect.addEventListener('animationend', () => {
            dropEffect.remove()
        })
    }
    
    document.addEventListener('dragend', cleanup, { once: true })
    document.addEventListener('drop', cleanup, { once: true })
    
    // Скрываем стандартный превью
    const emptyImg = new Image()
    emptyImg.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
    event.dataTransfer.setDragImage(emptyImg, 0, 0)
}

const handleDragEnd = () => {
    isDragging.value = false
    isExpanded.value = false
}
</script>

<style>
.prompt-badge {
  position: relative;
  width: 300px;
  background-color: rgba(31, 41, 55, 0.5);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(75, 85, 99, 0.5);
  border-radius: 0.5rem;
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

.prompt-badge:hover {
  transform: scale(1.02);
  border-color: rgba(99, 102, 241, 0.5);
  box-shadow: 0 0 20px rgba(99, 102, 241, 0.2);
}

.prompt-badge.expanded {
  width: 400px;
  z-index: 10;
}

.prompt-badge.dragging {
  opacity: 0.5;
}

.prompt-badge .content {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.prompt-badge.expanded .content {
  -webkit-line-clamp: unset;
}

.prompt-badge .drag-handle {
  cursor: grab;
}

.prompt-badge .drag-handle:active {
  cursor: grabbing;
}

.prompt-badge .favorite-button {
  opacity: 0;
  transition: opacity 0.2s ease-in-out;
}

.prompt-badge:hover .favorite-button {
  opacity: 1;
}

.prompt-badge .favorite-button.active {
  opacity: 1;
}

/* Анимации для трансформации */
.group {
  transform-origin: top left;
  transition: all 0.3s ease-out;
}

.group > div {
  transform-origin: top left;
  transition: all 0.3s ease-out;
}

/* Анимация для контента */
[v-if] {
  animation: fade-in 0.3s ease-out;
}

/* Эффект отрыва */
.lift-effect {
  opacity: 0.5;
}

/* Тень при отрыве */
.card-shadow {
  position: absolute;
  inset: -5px;
  background: radial-gradient(circle at center, rgba(255, 255, 255, 0.2), transparent 70%);
  border-radius: inherit;
  z-index: -1;
  opacity: 0;
  animation: shadow-pulse 1s ease-in-out infinite;
}

/* Утилиты для фонов */
.bg-tear-pattern {
  background-image: repeating-linear-gradient(
    45deg,
    transparent,
    transparent 2px,
    rgba(255, 255, 255, 0.2) 2px,
    rgba(255, 255, 255, 0.2) 4px
  );
}

.bg-gradient-radial {
  background: radial-gradient(circle at center, var(--tw-gradient-from), var(--tw-gradient-to) 70%);
}

/* Анимации */
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
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
}

/* Анимация падения при drop */
.drop-animation {
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
    animation: drop-fall 0.6s cubic-bezier(.17,.67,.83,.67) forwards;
}

@keyframes drop-fall {
    0% {
        opacity: 1;
        transform: translateY(-20px);
    }
    70% {
        opacity: 0.7;
        transform: translateY(0);
    }
    100% {
        opacity: 0;
        transform: translateY(10px);
    }
}
</style> 