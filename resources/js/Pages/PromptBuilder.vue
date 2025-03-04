<template>
  <div class="min-h-screen bg-gray-100">
    <nav class="bg-white shadow mb-8">
      <div class="container mx-auto px-4 py-4">
        <h1 class="text-xl font-semibold">Конструктор промптов</h1>
      </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
      <div class="grid grid-cols-12 gap-6">
        <!-- Сайдбар с шаблонами -->
        <div class="col-span-3 bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold mb-4">Шаблоны</h2>
          <div class="space-y-2">
            <div
              v-for="template in templates"
              :key="template.id"
              class="p-2 bg-gray-50 rounded cursor-move"
              draggable="true"
              @dragstart="dragStart($event, template)"
            >
              {{ template.name }}
            </div>
          </div>
        </div>

        <!-- Основная область конструктора -->
        <div 
          class="col-span-9 bg-white rounded-lg shadow p-4"
          @drop="onDrop($event)"
          @dragover.prevent
        >
          <h2 class="text-lg font-semibold mb-4">Конструктор промптов</h2>
          
          <div class="min-h-[400px] border-2 border-dashed border-gray-300 rounded-lg p-4">
            <div v-for="(block, index) in promptBlocks" :key="index" class="mb-2">
              <div class="flex items-center gap-2 p-2 bg-gray-50 rounded">
                <div class="flex-1">{{ block.content }}</div>
                <button @click="removeBlock(index)" class="text-red-500">
                  Удалить
                </button>
              </div>
            </div>
          </div>

          <!-- Предпросмотр -->
          <div class="mt-4">
            <h3 class="font-semibold mb-2">Предпросмотр:</h3>
            <div class="p-4 bg-gray-50 rounded">
              {{ compiledPrompt }}
            </div>
          </div>

          <div class="mt-4">
            <button 
              @click="showDialog = true" 
              class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            >
              Сохранить промпт
            </button>
          </div>

          <!-- Добавим секцию сохраненных промптов -->
          <div class="mt-6">
            <h3 class="font-semibold mb-2">Сохраненные промпты:</h3>
            <div class="space-y-2">
              <div v-for="prompt in savedPrompts" :key="prompt.id" 
                   class="p-4 bg-white rounded-lg shadow">
                <h4 class="font-medium">{{ prompt.name }}</h4>
                <p class="text-sm text-gray-600">{{ prompt.compiled_content }}</p>
                <button @click="loadSavedPrompt(prompt)" 
                        class="mt-2 text-blue-500 hover:text-blue-600">
                  Загрузить
                </button>
              </div>
            </div>
          </div>

          <!-- Добавить кнопки в интерфейс -->
          <div class="flex gap-2 mb-4">
            <button @click="exportPrompts" class="px-4 py-2 bg-green-500 text-white rounded">
              Экспорт
            </button>
            <label class="px-4 py-2 bg-blue-500 text-white rounded cursor-pointer">
              Импорт
              <input type="file" class="hidden" accept=".json" @change="importPrompts">
            </label>
          </div>

          <TemplateSearch 
            :categories="categories"
            @search="handleSearch"
            @filter="handleFilter"
          />
        </div>
      </div>
    </div>

    <!-- Добавить уведомления -->
    <div v-if="error" class="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded">
      {{ error }}
    </div>
    <div v-if="success" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded">
      {{ success }}
    </div>

    <PromptNameDialog 
      v-model="showDialog"
      @save="savePrompt"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import PromptNameDialog from '@/Components/PromptNameDialog.vue'
import { useHistory } from '@/Composables/useHistory'
import { useHotkeys } from '@/Composables/useHotkeys'
import TemplateSearch from '@/Components/TemplateSearch.vue'

const templates = ref([])
const promptBlocks = ref([])
const savedPrompts = ref([])
const error = ref(null)
const success = ref(null)
const showDialog = ref(false)

const history = useHistory([])
const filteredTemplates = ref([])

// Загрузка шаблонов
const loadTemplates = async () => {
  try {
    const response = await fetch('/api/prompt-templates')
    const data = await response.json()
    templates.value = data.data
  } catch (error) {
    console.error('Ошибка загрузки шаблонов:', error)
  }
}

// Drag & Drop функционал
const dragStart = (event, template) => {
  event.dataTransfer.setData('template', JSON.stringify(template))
}

const onDrop = (event) => {
  const template = JSON.parse(event.dataTransfer.getData('template'))
  promptBlocks.value.push(template)
}

const removeBlock = (index) => {
  promptBlocks.value.splice(index, 1)
}

// Компиляция финального промпта
const compiledPrompt = computed(() => {
  return promptBlocks.value
    .map(block => block.content)
    .join('\n')
})

const showError = (message) => {
  error.value = message
  setTimeout(() => error.value = null, 3000)
}

const showSuccess = (message) => {
  success.value = message
  setTimeout(() => success.value = null, 3000)
}

const savePrompt = async (name) => {
  try {
    const response = await fetch('/api/prompt-templates/compile', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        template_ids: promptBlocks.value.map(block => block.id),
        name
      })
    });
    
    const result = await response.json();
    await loadSavedPrompts();
    showSuccess('Промпт успешно сохранен')
  } catch (error) {
    showError('Ошибка при сохранении промпта')
    console.error('Ошибка сохранения промпта:', error);
  }
};

const loadSavedPrompts = async () => {
  try {
    const response = await fetch('/api/prompt-templates/saved');
    const data = await response.json();
    savedPrompts.value = data;
  } catch (error) {
    console.error('Ошибка загрузки сохраненных промптов:', error);
  }
};

const loadSavedPrompt = (prompt) => {
  promptBlocks.value = templates.value.filter(t => 
    prompt.template_ids.includes(t.id)
  );
};

// Поиск и фильтрация
const handleSearch = (query) => {
  filteredTemplates.value = templates.value.filter(template =>
    template.name.toLowerCase().includes(query.toLowerCase()) ||
    template.content.toLowerCase().includes(query.toLowerCase())
  )
}

const handleFilter = (categories) => {
  filteredTemplates.value = templates.value.filter(template =>
    template.categories.some(cat => categories.includes(cat.id))
  )
}

// История действий
const handleBlocksChange = () => {
  history.push(promptBlocks.value)
}

// Горячие клавиши
useHotkeys({
  'ctrl+z': () => {
    const previous = history.undo()
    if (previous) promptBlocks.value = previous
  },
  'ctrl+y': () => {
    const next = history.redo()
    if (next) promptBlocks.value = next
  },
  'ctrl+s': () => showDialog.value = true
})

// Экспорт/Импорт
const exportPrompts = () => {
  const data = {
    prompts: promptBlocks.value,
    version: '1.0',
    exported_at: new Date().toISOString()
  }
  const blob = new Blob([JSON.stringify(data)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'prompts.json'
  a.click()
}

const importPrompts = async (event) => {
  const file = event.target.files[0]
  if (file) {
    try {
      const content = await file.text()
      const data = JSON.parse(content)
      // Отправка на сервер для импорта
      const response = await fetch('/api/prompt-templates/import', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
      })
      await loadTemplates()
      showSuccess('Промпты успешно импортированы')
    } catch (error) {
      showError('Ошибка при импорте промптов')
    }
  }
}

// Загрузка данных при монтировании
onMounted(() => {
  loadTemplates()
  loadSavedPrompts()
})
</script> 