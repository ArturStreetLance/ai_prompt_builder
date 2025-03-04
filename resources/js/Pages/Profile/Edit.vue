<template>
  <app-layout :user="user">
    <div class="max-w-7xl mx-auto px-4">
      <div class="grid grid-cols-12 gap-6">
        <!-- Левая колонка - Основная информация -->
        <div class="col-span-12 md:col-span-4">
          <n-card class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50">
            <template #header>
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Профиль</h3>
                <n-button size="small" type="primary" @click="saveProfile">
                  Сохранить
                </n-button>
              </div>
            </template>

            <!-- Аватар -->
            <div class="flex flex-col items-center space-y-4 mb-6">
              <n-avatar
                :src="avatarPreview || user.avatar || '/default-avatar.png'"
                :size="100"
                round
              />
              <n-upload
                accept="image/*"
                :show-file-list="false"
                @change="handleAvatarUpload"
              >
                <n-button size="small">Изменить фото</n-button>
              </n-upload>
            </div>

            <!-- Форма профиля -->
            <n-form
              ref="formRef"
              :model="profile"
              :rules="rules"
            >
              <n-form-item label="Имя" path="name">
                <n-input v-model:value="profile.name" placeholder="Ваше имя" />
              </n-form-item>

              <n-form-item label="Email" path="email">
                <n-input v-model:value="profile.email" placeholder="your@email.com" />
              </n-form-item>

              <n-form-item label="Био" path="bio">
                <n-input
                  v-model:value="profile.bio"
                  type="textarea"
                  placeholder="Расскажите о себе"
                />
              </n-form-item>

              <n-form-item label="Сайт" path="website">
                <n-input v-model:value="profile.website" placeholder="https://your-site.com" />
              </n-form-item>
            </n-form>
          </n-card>
        </div>

        <!-- Правая колонка - Статистика и настройки -->
        <div class="col-span-12 md:col-span-8 space-y-6">
          <!-- Статистика -->
          <n-card class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50">
            <template #header>
              <h3 class="text-lg font-medium text-white">Статистика</h3>
            </template>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="p-4 bg-gray-700/30 rounded-lg">
                <div class="text-2xl font-bold text-indigo-400">{{ stats.promptsCreated }}</div>
                <div class="text-sm text-gray-400">Создано промптов</div>
              </div>
              <div class="p-4 bg-gray-700/30 rounded-lg">
                <div class="text-2xl font-bold text-indigo-400">{{ stats.totalUsage }}</div>
                <div class="text-sm text-gray-400">Использований</div>
              </div>
              <div class="p-4 bg-gray-700/30 rounded-lg">
                <div class="text-2xl font-bold text-indigo-400">{{ stats.rating }}</div>
                <div class="text-sm text-gray-400">Средний рейтинг</div>
              </div>
            </div>
          </n-card>

          <!-- Настройки -->
          <n-card class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50">
            <template #header>
              <h3 class="text-lg font-medium text-white">Настройки</h3>
            </template>
            <n-form>
              <n-form-item label="Уведомления">
                <n-switch v-model:value="settings.notifications" />
              </n-form-item>
              <n-form-item label="Публичный профиль">
                <n-switch v-model:value="settings.publicProfile" />
              </n-form-item>
              <n-form-item label="Тема">
                <n-select
                  v-model:value="settings.theme"
                  :options="themeOptions"
                />
              </n-form-item>
            </n-form>
          </n-card>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import { ref } from 'vue'
import { useMessage } from 'naive-ui'
import AppLayout from '@/Layouts/AppLayout.vue'
import {
  NCard,
  NButton,
  NForm,
  NFormItem,
  NInput,
  NAvatar,
  NUpload,
  NSwitch,
  NSelect,
} from 'naive-ui'

const props = defineProps({
  profile: Object,
  user: Object,
})

const message = useMessage()
const formRef = ref(null)
const avatarPreview = ref(null)

// Данные профиля
const profile = ref({
  name: props.user?.name || '',
  email: props.user?.email || '',
  bio: props.profile?.bio || '',
  website: props.profile?.website || '',
  notifications_enabled: props.profile?.notifications_enabled ?? true,
  public_profile: props.profile?.public_profile ?? false,
  theme: props.profile?.theme || 'dark',
})

// Статистика
const stats = ref({
  promptsCreated: props.profile?.prompts_created || 0,
  totalUsage: props.profile?.total_usage || 0,
  rating: props.profile?.rating || 0,
})

// Настройки
const settings = ref({
  notifications: true,
  publicProfile: false,
  theme: 'dark',
})

const themeOptions = [
  { label: 'Темная', value: 'dark' },
  { label: 'Светлая', value: 'light' },
  { label: 'Системная', value: 'system' },
]

// Правила валидации
const rules = {
  name: {
    required: true,
    message: 'Пожалуйста, введите имя',
    trigger: 'blur',
  },
  email: {
    required: true,
    message: 'Пожалуйста, введите email',
    trigger: 'blur',
    type: 'email',
  },
}

// Обработка загрузки аватара
const handleAvatarUpload = ({ file }) => {
  const reader = new FileReader()
  reader.onload = (e) => {
    avatarPreview.value = e.target.result
  }
  reader.readAsDataURL(file.file)
}

// Сохранение профиля
const saveProfile = () => {
  formRef.value?.validate((errors) => {
    if (!errors) {
      // Здесь будет логика сохранения
      message.success('Профиль успешно обновлен')
    }
  })
}
</script> 