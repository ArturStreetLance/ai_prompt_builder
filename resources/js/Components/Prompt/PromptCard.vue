<template>
    <div
        class="group relative p-6 bg-gradient-to-br from-gray-800/80 to-gray-900/90 backdrop-blur-md
               border border-gray-700/30 rounded-2xl shadow-lg
               hover:shadow-xl hover:shadow-indigo-500/20 hover:border-indigo-500/30
               transition-all duration-300 ease-in-out cursor-move"
        :class="[
            `border-t-4 border-t-${categoryColor}-500`,
            { 'scale-95 opacity-50 border-dashed lift-effect': isDragging }
        ]"
        draggable="true"
        @dragstart="handleDragStart"
        @dragend="handleDragEnd"
        @click="$emit('click', prompt)"
    >
        <!-- Тень при отрыве -->
        <div v-if="isDragging" class="card-shadow"></div>

        <!-- Эффект отрыва при перетаскивании -->
        <div v-if="isDragging" class="tear-effect">
            <div class="tear-line"></div>
        </div>

        <!-- Верхняя часть карточки -->
        <div class="flex items-center gap-4 -mt-2 mb-4">
            <!-- Название -->
            <div class="shrink-0">
                <h3 class="text-lg font-semibold text-white group-hover:text-indigo-400 transition-colors">
                    {{ prompt.name }}
                </h3>
            </div>

            <!-- Рейтинг -->
            <div class="flex items-center gap-1 shrink-0">
                <n-rate
                    :value="localRating"
                    :readonly="isUpdating"
                    size="small"
                    @update:value="updateRating"
                >
                    <template #default="{ index }">
                        <n-icon 
                            :size="16" 
                            :class="[
                                index <= localRating ? 'text-yellow-400' : 'text-gray-400',
                                { 'opacity-50': isUpdating }
                            ]"
                        >
                            <Icon icon="carbon:star-filled" />
                        </n-icon>
                    </template>
                </n-rate>
                <span class="text-xs text-gray-400 min-w-[2rem]">
                    {{ localRating.toFixed(1) }}
                </span>
            </div>

            <!-- Категория -->
            <div class="shrink-0">
                <span class="text-xs text-gray-400 italic px-2 py-1 bg-gray-800/50 rounded-full">
                    {{ prompt.category }}
                </span>
            </div>

            <!-- Избранное -->
            <div class="shrink-0">
                <n-button
                    circle
                    size="tiny"
                    :type="localIsFavorite ? 'warning' : 'default'"
                    :disabled="isUpdating"
                    @click.stop="toggleFavorite"
                    class="border-none bg-transparent hover:scale-110 transition-transform relative"
                >
                    <template #icon>
                        <Icon
                            :icon="localIsFavorite ? 'carbon:star-filled' : 'carbon:star'"
                            :class="[
                                localIsFavorite ? 'text-yellow-400' : 'text-gray-400',
                                'group-hover:text-yellow-300 transition-colors text-lg',
                                { 'opacity-50': isUpdating }
                            ]"
                        />
                    </template>
                    
                    <!-- Индикатор загрузки -->
                    <div v-if="isUpdating" 
                         class="absolute inset-0 flex items-center justify-center">
                        <div class="w-4 h-4 border-2 border-t-transparent border-yellow-400 rounded-full animate-spin"></div>
                    </div>
                </n-button>
            </div>
        </div>

        <!-- Основной контент -->
        <div class="relative">
            <p :class="[
                'text-sm text-gray-300 transition-all duration-300',
                isExpanded ? 'line-clamp-none' : 'line-clamp-3'
            ]">
                {{ prompt.content }}
            </p>

            <!-- Градиент затухания текста -->
            <div v-if="!isExpanded"
                 class="absolute bottom-0 left-0 right-0 h-8 bg-gradient-to-t from-gray-900/90 to-transparent"
            />

            <!-- Кнопка "Показать больше" -->
            <button
                v-if="prompt.content.length > 150"
                @click.stop="toggleExpand"
                class="mt-2 text-xs text-indigo-400 hover:text-indigo-300 transition-colors"
            >
                {{ isExpanded ? 'Свернуть' : 'Показать больше' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { NBadge, NRate, NButton } from 'naive-ui'
import { useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import axios from 'axios'

const props = defineProps({
    prompt: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['update:rating', 'update:favorite'])
const isExpanded = ref(false)
const form = useForm({})
const isDragging = ref(false)
const isUpdating = ref(false)

// Локальное состояние для оптимистичных обновлений
const localRating = ref(props.prompt.rating)
const localIsFavorite = ref(props.prompt.is_favorite)

const toggleExpand = () => {
    isExpanded.value = !isExpanded.value
}

const toggleFavorite = async () => {
    if (isUpdating.value) return
    
    const previousState = localIsFavorite.value
    localIsFavorite.value = !previousState // Оптимистичное обновление
    isUpdating.value = true
    
    try {
        const response = await form.post(`/prompts/${props.prompt.id}/toggle-favorite`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                emit('update:favorite', localIsFavorite.value)
            },
            onError: (errors) => {
                // Откатываем изменения при ошибке
                localIsFavorite.value = previousState
                console.error('Ошибка обновления избранного:', errors)
            }
        })

        if (!response.success) {
            localIsFavorite.value = previousState
        }
    } catch (error) {
        console.error('Ошибка при обновлении избранного:', error)
        localIsFavorite.value = previousState
    } finally {
        isUpdating.value = false
    }
}

const updateRating = async (value) => {
    if (isUpdating.value) return
    
    const previousRating = localRating.value
    localRating.value = value // Оптимистичное обновление
    isUpdating.value = true
    
    // Логируем данные перед отправкой
    console.log('Отправляем данные:', {
        prompt_id: props.prompt.id,
        rating: value
    })
    
    try {
        form.transform(data => ({
            ...data,
            rating: value
        }))

        await form.post(`/prompts/${props.prompt.id}/update-rating`, {
            onSuccess: () => {
                console.log('Рейтинг успешно обновлен:', value)
                emit('update:rating', value)
            },
            onError: (errors) => {
                console.error('Ошибка валидации:', errors)
                localRating.value = previousRating
            }
        })
    } catch (error) {
        console.error('Ошибка при обновлении рейтинга:', error)
        localRating.value = previousRating
    } finally {
        isUpdating.value = false
    }
}

const categoryColor = computed(() => {
    const colors = {
        'Копирайтинг': 'sky',
        'Разработка': 'emerald',
        'Маркетинг': 'violet',
        'SEO': 'amber',
        'Творчество': 'rose'
    }
    return colors[props.prompt.category] || 'gray'
})

const handleDragStart = (event) => {
    isDragging.value = true
    event.dataTransfer.setData('text/plain', JSON.stringify(props.prompt))
    event.dataTransfer.effectAllowed = 'copy'
    
    // Создаем видимый элемент для dragImage
    const dragPreview = document.createElement('div')
    dragPreview.className = 'drag-preview'
    dragPreview.style.position = 'absolute'
    dragPreview.style.top = event.clientY + 'px'
    dragPreview.style.left = event.clientX + 'px'
    dragPreview.style.zIndex = '9999'
    dragPreview.style.pointerEvents = 'none'
    dragPreview.style.transform = 'translate(-50%, -50%)'
    
    const content = document.createElement('div')
    content.style.padding = '0.75rem 1rem'
    content.style.background = 'rgba(99, 102, 241, 0.95)'
    content.style.backdropFilter = 'blur(8px)'
    content.style.borderRadius = '0.5rem'
    content.style.color = '#FFFFFF'
    content.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.3)'
    content.style.border = '2px solid rgba(255, 255, 255, 0.1)'
    content.style.maxWidth = '300px'
    content.style.fontSize = '0.875rem'
    content.style.lineHeight = '1.25rem'
    content.style.transform = 'scale(0.8)'
    content.style.display = '-webkit-box'
    content.style.webkitLineClamp = '3'
    content.style.webkitBoxOrient = 'vertical'
    content.style.overflow = 'hidden'
    content.textContent = props.prompt.content
    
    dragPreview.appendChild(content)
    document.body.appendChild(dragPreview)
    
    // Устанавливаем dragImage
    event.dataTransfer.setDragImage(dragPreview, 10, 10)
    
    // Удаляем элемент после начала перетаскивания
    requestAnimationFrame(() => {
        document.body.removeChild(dragPreview)
    })
}

const handleDragEnd = () => {
    isDragging.value = false
}
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.i-carbon-star,
.i-carbon-star-filled {
    font-size: 1.2rem;
}

/* Анимация при наведении */
.group:hover {
    transform: translateY(-2px);
}

/* Плавное появление текста */
.line-clamp-none {
    animation: expandText 0.3s ease-out;
}

@keyframes expandText {
    from {
        max-height: 4.5em;
        opacity: 0.8;
    }
    to {
        max-height: 1000px;
        opacity: 1;
    }
}

.text-lg {
    font-size: 1.2rem;
}

/* Эффект отрыва */
.lift-effect {
    animation: liftCard 0.3s ease-out forwards;
    transform-origin: center bottom;
}

@keyframes liftCard {
    0% {
        transform: scale(1) rotate(0deg);
    }
    20% {
        transform: scale(1.02) rotate(-2deg);
    }
    100% {
        transform: scale(0.95) rotate(0deg);
    }
}

/* Тень при отрыве */
.card-shadow {
    position: absolute;
    inset: -5px;
    background: radial-gradient(
        circle at center,
        rgba(99, 102, 241, 0.2),
        transparent 70%
    );
    border-radius: inherit;
    z-index: -1;
    opacity: 0;
    animation: shadowPulse 1s ease-in-out infinite;
}

@keyframes shadowPulse {
    0%, 100% {
        opacity: 0.3;
        transform: translateY(5px) scale(1.05);
    }
    50% {
        opacity: 0.15;
        transform: translateY(8px) scale(1.1);
    }
}

/* Эффект отрывания бумаги */
.tear-effect {
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 4px;
    overflow: hidden;
}

.tear-line {
    position: absolute;
    top: 0;
    left: -10%;
    right: -10%;
    height: 100%;
    background-image: repeating-linear-gradient(
        45deg,
        transparent,
        transparent 2px,
        rgba(99, 102, 241, 0.3) 2px,
        rgba(99, 102, 241, 0.3) 4px
    );
    animation: tearAway 0.5s ease-in-out infinite;
}

@keyframes tearAway {
    0% {
        transform: translateX(-5%);
    }
    100% {
        transform: translateX(5%);
    }
}

/* Убираем старые стили превью */
.drag-preview, .floating-preview {
    display: none;
}

/* Анимация при перетаскивании */
.scale-95 {
    transform: scale(0.95);
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Стили для превью при перетаскивании */
.drag-preview {
    position: absolute;
    z-index: 9999;
    pointer-events: none;
    transform: translate(-50%, -50%);
    opacity: 1;
}

.drag-preview-content {
    padding: 0.75rem 1rem;
    background: rgba(99, 102, 241, 0.95);
    backdrop-filter: blur(8px);
    border-radius: 0.5rem;
    color: #ffffff !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    border: 2px solid rgba(255, 255, 255, 0.1);
    max-width: 300px;
    font-size: 0.875rem;
    line-height: 1.25rem;
    transform: scale(0.8);
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Убираем конфликтующие стили */
.drag-preview .text-gray-300,
.drag-preview p {
    color: #ffffff !important;
}

/* Добавим стили для анимации обновления рейтинга */
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.text-yellow-400 {
    animation: pulse 0.3s ease-in-out;
}

/* Стиль для индикатора загрузки */
@keyframes spin {
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Анимация для кнопки избранного */
.n-button:not(:disabled):hover .Icon {
    transform: scale(1.2);
}

.n-button:disabled {
    cursor: not-allowed;
    opacity: 0.7;
}
</style>

