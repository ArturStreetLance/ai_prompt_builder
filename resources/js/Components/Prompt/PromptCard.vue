<template>
    <div
        class="group relative p-6 bg-gradient-to-br from-gray-800/80 to-gray-900/90 backdrop-blur-md
               border border-gray-700/30 rounded-2xl shadow-lg
               hover:shadow-xl hover:shadow-indigo-500/20 hover:border-indigo-500/30
               transition-all duration-300 ease-in-out cursor-move"
        :class="[`border-t-4 border-t-${categoryColor}-500`]"
        draggable="true"
        @dragstart="handleDragStart"
        @click="$emit('click', prompt)"
    >
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
    event.dataTransfer.setData('text/plain', JSON.stringify(props.prompt))
    event.dataTransfer.effectAllowed = 'copy'
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
</style>
