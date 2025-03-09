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
                <n-rate :value="prompt.rating" readonly size="small" />
                <span class="text-xs text-gray-400 min-w-[2rem]">
                    {{ prompt.rating }}
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
                    :type="prompt.is_favorite ? 'warning' : 'default'"
                    @click.stop="toggleFavorite"
                    class="border-none bg-transparent hover:scale-110 transition-transform"
                >
                    <template #icon>
                        <Icon
                            :icon="prompt.is_favorite ? 'carbon:star-filled' : 'carbon:star'"
                            :class="[
                                prompt.is_favorite ? 'text-yellow-400' : 'text-gray-400',
                                'group-hover:text-yellow-300 transition-colors text-lg'
                            ]"
                        />
                    </template>
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

const props = defineProps({
    prompt: {
        type: Object,
        required: true
    }
})

const isExpanded = ref(false)
const form = useForm({})
const isDragging = ref(false)

const toggleExpand = () => {
    isExpanded.value = !isExpanded.value
}

const toggleFavorite = () => {
    form.post(`/prompts/${props.prompt.id}/toggle-favorite`)
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
    
    // Создаем невидимый элемент для dragImage
    const emptyImage = document.createElement('div')
    emptyImage.style.display = 'none'
    document.body.appendChild(emptyImage)
    event.dataTransfer.setDragImage(emptyImage, 0, 0)
    
    // Удаляем элемент после начала перетаскивания
    setTimeout(() => {
        document.body.removeChild(emptyImage)
    }, 0)
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
</style>

