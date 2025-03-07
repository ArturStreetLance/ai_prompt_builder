<template>
  <n-modal v-model:show="show" preset="card" title="Создать промпт">
    <n-form ref="formRef" :model="formData" :rules="rules">
      <n-form-item label="Название" path="name">
        <n-input v-model:value="formData.name" placeholder="Введите название промпта" />
      </n-form-item>

      <n-form-item label="Категория" path="category">
        <n-select
          v-model:value="formData.category"
          :options="categoryOptions"
          placeholder="Выберите категорию"
        />
      </n-form-item>

      <n-form-item label="Текст промпта" path="content">
        <n-input
          v-model:value="formData.content"
          type="textarea"
          placeholder="Введите текст промпта"
          :autosize="{ minRows: 3, maxRows: 6 }"
        />
      </n-form-item>

      <n-form-item label="Публичный" path="is_public">
        <n-switch v-model:value="formData.is_public" />
      </n-form-item>
    </n-form>

    <template #footer>
      <div class="flex justify-end space-x-4">
        <n-button @click="show = false">Отмена</n-button>
        <n-button type="primary" :loading="loading" @click="handleSubmit">
          Сохранить
        </n-button>
      </div>
    </template>
  </n-modal>
</template>

<script setup>
import { ref, reactive,computed } from 'vue'
import { useMessage } from 'naive-ui'
import { useForm } from '@inertiajs/vue3'
import {
  NModal,
  NForm,
  NFormItem,
  NInput,
  NSelect,
  NSwitch,
  NButton
} from 'naive-ui'

const props = defineProps({
  modelValue: Boolean
})

const emit = defineEmits(['update:modelValue', 'created'])

const show = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const formRef = ref(null)
const loading = ref(false)
const message = useMessage()

const formData = reactive({
  name: '',
  category: null,
  content: '',
  is_public: true
})

const categoryOptions = [
  { label: 'Копирайтинг', value: 'Копирайтинг' },
  { label: 'Разработка', value: 'Разработка' },
  { label: 'Маркетинг', value: 'Маркетинг' },
  { label: 'SEO', value: 'SEO' },
  { label: 'Творчество', value: 'Творчество' }
]

const rules = {
  name: { required: true, message: 'Введите название промпта' },
  category: { required: true, message: 'Выберите категорию' },
  content: { required: true, message: 'Введите текст промпта' }
}

const form = useForm({
  name: '',
  category: '',
  content: '',
  is_public: true
})

const handleSubmit = () => {
  formRef.value?.validate(async (errors) => {
    if (!errors) {
      loading.value = true
      try {
        await form
          .transform((data) => ({
            ...data,
            ...formData
          }))
          .post('/prompts', {
            onSuccess: () => {
              show.value = false
              message.success('Промпт успешно создан')
              emit('created')
              // Очищаем форму
              Object.assign(formData, {
                name: '',
                category: null,
                content: '',
                is_public: true
              })
            }
          })
      } finally {
        loading.value = false
      }
    }
  })
}
</script>
