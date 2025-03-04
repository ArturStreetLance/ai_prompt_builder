<template>
  <div v-if="modelValue" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg">
      <h3 class="text-lg font-semibold mb-4">Введите имя промпта</h3>
      <input 
        v-model="name" 
        type="text" 
        class="border p-2 rounded w-full mb-4"
        placeholder="Название промпта"
      >
      <div class="flex justify-end gap-2">
        <button 
          @click="$emit('update:modelValue', false)" 
          class="px-4 py-2 text-gray-600"
        >
          Отмена
        </button>
        <button 
          @click="save" 
          class="px-4 py-2 bg-blue-500 text-white rounded"
        >
          Сохранить
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps(['modelValue'])
const emit = defineEmits(['update:modelValue', 'save'])

const name = ref('')

const save = () => {
  emit('save', name.value)
  name.value = ''
  emit('update:modelValue', false)
}
</script> 