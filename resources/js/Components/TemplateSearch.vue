<template>
  <div class="mb-4">
    <input 
      v-model="search"
      type="text"
      placeholder="Поиск шаблонов..."
      class="w-full p-2 border rounded"
      @input="$emit('search', search)"
    />
    <div class="mt-2 flex gap-2 flex-wrap">
      <button 
        v-for="category in categories"
        :key="category.id"
        @click="toggleCategory(category.id)"
        :class="[
          'px-2 py-1 rounded text-sm',
          selectedCategories.includes(category.id)
            ? 'bg-blue-500 text-white'
            : 'bg-gray-200'
        ]"
      >
        {{ category.name }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const search = ref('')
const selectedCategories = ref([])
const props = defineProps(['categories'])
const emit = defineEmits(['search', 'filter'])

const toggleCategory = (categoryId) => {
  const index = selectedCategories.value.indexOf(categoryId)
  if (index === -1) {
    selectedCategories.value.push(categoryId)
  } else {
    selectedCategories.value.splice(index, 1)
  }
  emit('filter', selectedCategories.value)
}
</script> 