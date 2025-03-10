<template>
    <div 
    style="width: max-content;"
        class="group relative transition-all duration-300 ease-out cursor-pointer shadow-lg min-w-0 w-fit"
        :class="[
            isExpanded ? 'fixed inset-4 z-50 bg-gray-900/50' : '',
            { 'scale-95 opacity-50 border-dashed lift-effect': isDragging }
        ]"
        draggable="true"
        @dragstart="handleDragStart"
        @dragend="handleDragEnd"
        @click="isExpanded = !isExpanded"
    >
        <div v-if="isDragging" class="card-shadow"></div>
        <div v-if="isDragging" class="tear-effect">
            <div class="tear-line"></div>
        </div>
        <!-- Бейдж -->
        <div v-if="!isExpanded"
            class="flex items-center h-12 border-[3px] rounded-full w-max"
            :class="[badgeStyle.bg, badgeStyle.border]"
        >
            <!-- Избранное -->
            <n-button
                circle
                size="small"
                :type="localIsFavorite ? 'warning' : 'default'"
                class="border-none bg-white/20 backdrop-blur-sm hover:scale-110 transition-transform shadow-md"
                style="margin-right: 10px;"
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
                :class="['text-base font-bold truncate', badgeStyle.text]"
                style="margin-right: 10px;"
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
                        class="border-none hover:scale-110 transition-transform shadow-md flex-shrink-0"
                        style="margin-right: 10px;"
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
                <div class="flex flex-col gap-4 pl-[46px]" @click.stop>
                    <p class="prompt-content-style text-sm text-gray-600 dark:text-gray-300 whitespace-pre-wrap">
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
    isExpanded.value = true
    
    event.dataTransfer.setData('text/plain', JSON.stringify(props.prompt))
    event.dataTransfer.effectAllowed = 'copy'
    
    // Создаем видимый элемент для dragImage
    const dragPreview = document.createElement('div')
    dragPreview.className = 'drag-preview'
    dragPreview.style.position = 'absolute'
    dragPreview.style.top = '-9999px' // Скрываем превью за пределами экрана
    dragPreview.style.left = '-9999px'
    
    document.body.appendChild(dragPreview)
    event.dataTransfer.setDragImage(dragPreview, 10, 10)
    
    requestAnimationFrame(() => {
        document.body.removeChild(dragPreview)
    })
}

const handleDragEnd = () => {
    isDragging.value = false
    isExpanded.value = false // Закрываем карточку после перетаскивания
}
</script>

<style scoped>
.prompt-content-style {
    display: block;
    padding: 12px 16px;
    border-radius: 12px;
    background: rgba(0, 0, 0, 0.03);
    transition: all 0.3s ease-out;
}

.dark .prompt-content-style {
    background: rgba(255, 255, 255, 0.03);
}
/* Стили для звезд рейтинга */
:deep(.n-rate) {
    display: inline-flex;
    padding: 4px 12px;
    border-radius: 9999px;
    transition: all 0.3s ease-out;
}

/* Убираем стандартные стили кнопки */
:deep(.n-button) {
    min-width: auto;
    width: 32px;
    height: 32px;
    padding: 0;
    transition: all 0.3s ease-out;
}

:deep(.n-button.n-button--small) {
    width: 36px;
    height: 36px;
}

/* Анимации для трансформации */
.group {
    transform-origin: top left;
}

.group > div {
    transform-origin: top left;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Анимация для контента */
[v-if] {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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
        rgba(255, 255, 255, 0.2),
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
        rgba(255, 255, 255, 0.2) 2px,
        rgba(255, 255, 255, 0.2) 4px
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
</style> 