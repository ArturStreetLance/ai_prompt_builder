<template>
  <n-modal 
    v-model:show="show" 
    preset="card" 
    title="Создать промпт"
    :mask-closable="false"
    @close="handleClose"
  >
    <n-form 
      ref="formRef" 
      :model="formData" 
      :rules="rules"
      label-placement="left"
      label-width="100"
      require-mark-placement="right-hanging"
      size="medium"
    >
      <n-form-item label="Название" path="name">
        <n-input 
          v-model:value="formData.name" 
          placeholder="Введите название промпта"
          maxlength="100"
          show-count
          clearable
        />
      </n-form-item>

      <n-form-item label="Категория" path="category">
        <n-select
          v-model:value="formData.category"
          :options="categoryOptions"
          placeholder="Выберите категорию"
          clearable
        />
      </n-form-item>

      <n-form-item label="Текст промпта" path="content">
        <n-input
          v-model:value="formData.content"
          type="textarea"
          placeholder="Введите текст промпта"
          :autosize="{ minRows: 3, maxRows: 6 }"
          show-count
          clearable
        />
      </n-form-item>

      <n-form-item label="Публичный" path="is_public">
        <n-switch v-model:value="formData.is_public">
          <template #checked>Да</template>
          <template #unchecked>Нет</template>
        </n-switch>
      </n-form-item>
    </n-form>

    <template #footer>
      <div class="flex justify-end space-x-4">
        <n-button @click="handleClose" :disabled="loading">Отмена</n-button>
        <n-button 
          type="primary" 
          :loading="loading" 
          @click="handleSubmit"
          class="bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700"
        >
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
  name: [
    { required: true, message: 'Введите название промпта' },
    { min: 3, message: 'Минимальная длина названия - 3 символа' },
    { max: 100, message: 'Максимальная длина названия - 100 символов' }
  ],
  category: { required: true, message: 'Выберите категорию' },
  content: [
    { required: true, message: 'Введите текст промпта' },
    { min: 10, message: 'Минимальная длина текста - 10 символов' }
  ]
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
            preserveScroll: true,
            onSuccess: (page) => {
              show.value = false
              message.success('Промпт успешно создан')
              emit('created', page.props.prompt)
              // Очищаем форму
              Object.assign(formData, {
                name: '',
                category: null,
                content: '',
                is_public: true
              })
            },
            onError: (errors) => {
              if (errors.message) {
                message.error(errors.message)
              } else {
                message.error('Пожалуйста, проверьте правильность заполнения полей')
              }
            }
          })
      } catch (error) {
        message.error('Произошла ошибка при создании промпта')
      } finally {
        loading.value = false
      }
    } else {
      message.warning('Пожалуйста, заполните все обязательные поля')
    }
  })
}

const handleClose = () => {
  show.value = false
  Object.assign(formData, {
    name: '',
    category: null,
    content: '',
    is_public: true
  })
  formRef.value?.restoreValidation()
}
</script>
