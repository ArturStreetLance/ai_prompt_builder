<template>
  <div class="h-full flex flex-col">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold text-white">Предпросмотр промпта</h2>
      <n-button type="primary" :loading="loading" @click="handleSubmit">
        Отправить
      </n-button>
    </div>

    <div
      class="flex-1 bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-lg p-4"
      @dragover.prevent
      @drop="handleDrop"
    >
      <n-input
        v-model:value="title"
        placeholder="Название промпта"
        class="mb-4"
      />

      <n-input-group>
<!--        <n-text-area
          v-model:value="content"
          placeholder="Введите или перетащите сюда промпт..."
          :autosize="{ minRows: 10, maxRows: 20 }"
          @drop="handleTextAreaDrop"
          @dragover.prevent
        />-->
          <n-input
              v-model:value="content"
              placeholder="Введите или перетащите сюда промпт..."
              type="textarea"
              :autosize="{
            maxRows: 100,
          }"
              @drop="handleTextAreaDrop"
              @dragover.prevent
          />
      </n-input-group>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { NButton, NInput, NInputGroup } from 'naive-ui'

const title = ref('')
const content = ref('')
const loading = ref(false)

const handleDrop = (event) => {
  event.preventDefault()
  try {
    const prompt = JSON.parse(event.dataTransfer.getData('text/plain'))
    title.value = prompt.name
    content.value = prompt.content
  } catch (e) {
    console.error('Ошибка при обработке перетаскивания:', e)
  }
}

const handleTextAreaDrop = (event) => {
  event.preventDefault()
  try {
    const prompt = JSON.parse(event.dataTransfer.getData('text/plain'))
    const target = event.target
    const startPos = target.selectionStart
    const endPos = target.selectionEnd

    content.value =
      content.value.substring(0, startPos) +
      prompt.content +
      content.value.substring(endPos)
  } catch (e) {
    console.error('Ошибка при обработке перетаскивания в текстовое поле:', e)
  }
}

const handleSubmit = async () => {
  loading.value = true
  try {
    // Здесь будет логика отправки промпта
    await new Promise(resolve => setTimeout(resolve, 1000))
  } finally {
    loading.value = false
  }
}
</script>
