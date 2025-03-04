<template>
  <n-layout-header bordered class="bg-gray-900/95 backdrop-blur-md border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-4 h-16">
      <div class="flex items-center justify-between h-full">
        <!-- Лого (прижат влево) -->
        <div class="w-1/4">
          <n-button text class="!text-2xl !text-white hover:!text-indigo-400 transition-colors">
            <template #icon>
              <div class="i-carbon-api mr-2" />
            </template>
            AI Prompt
          </n-button>
        </div>

        <!-- Навигация (по центру) -->
        <nav class="flex-1 flex items-center justify-center space-x-6">
          <n-button text class="!text-gray-300 hover:!text-white">
            <template #icon>
              <div class="i-carbon-document mr-1" />
            </template>
            Мои промпты
          </n-button>
          <n-button text class="!text-gray-300 hover:!text-white">
            <template #icon>
              <div class="i-carbon-star mr-1" />
            </template>
            Избранное
          </n-button>
          <n-button text class="!text-gray-300 hover:!text-white">
            <template #icon>
              <div class="i-carbon-trending-topic mr-1" />
            </template>
            Популярные
          </n-button>
        </nav>

        <!-- Правая часть (прижата вправо) -->
        <div class="w-1/4 flex items-center justify-end space-x-4">
          <!-- Поиск -->
          <n-button circle class="!bg-gray-800 hover:!bg-gray-700 !border-gray-700">
            <template #icon>
              <div class="i-carbon-search text-gray-300" />
            </template>
          </n-button>

          <!-- Уведомления -->
          <n-badge :value="3" processing>
            <n-button circle class="!bg-gray-800 hover:!bg-gray-700 !border-gray-700">
              <template #icon>
                <div class="i-carbon-notification text-gray-300" />
              </template>
            </n-button>
          </n-badge>

          <!-- Профиль -->
          <n-dropdown trigger="click" :options="userMenuOptions" @select="handleSelect">
            <div class="flex items-center space-x-3 cursor-pointer">
              <span class="text-sm text-gray-300 hidden md:inline-block">
                {{ user?.email }}
              </span>
              <n-avatar
                :src="user?.profile?.avatar || '/default-avatar.png'"
                round
                :size="32"
                class="!bg-gray-800 !border-gray-700"
              />
            </div>
          </n-dropdown>
        </div>
      </div>
    </div>
  </n-layout-header>
</template>

<script setup>
import { h } from 'vue'
import { useForm } from '@inertiajs/vue3'
import {
  NLayoutHeader,
  NButton,
  NDropdown,
  NBadge,
  NAvatar,
  useMessage,
} from 'naive-ui'

const message = useMessage()

const props = defineProps({
  user: Object
})

const userMenuOptions = [
  {
    label: () =>
      h(
        'div',
        {
          class: 'flex items-center px-2 py-1',
        },
        [
          h('div', { class: 'text-sm font-medium' }, [
            props.user?.email || 'Пользователь',
          ]),
        ]
      ),
    key: 'header',
  },
  {
    type: 'divider',
    key: 'd1',
  },
  {
    label: () =>
      h(
        'div',
        {
          class: 'flex items-center space-x-2',
        },
        [
          h('div', { class: 'i-carbon-user-profile' }),
          h('span', 'Профиль'),
        ]
      ),
    key: 'profile',
  },
  {
    label: () =>
      h(
        'div',
        {
          class: 'flex items-center space-x-2',
        },
        [
          h('div', { class: 'i-carbon-settings' }),
          h('span', 'Настройки'),
        ]
      ),
    key: 'settings',
  },
  {
    type: 'divider',
    key: 'd2',
  },
  {
    label: () =>
      h(
        'div',
        {
          class: 'flex items-center space-x-2 text-red-500',
        },
        [
          h('div', { class: 'i-carbon-logout' }),
          h('span', 'Выйти'),
        ]
      ),
    key: 'logout',
  },
]

const handleSelect = (key) => {
  if (key === 'logout') {
    const form = useForm({})
    form.post('/logout')
  } else if (key === 'settings') {
    message.info('Настройки (в разработке)')
  } else if (key === 'profile') {
    window.location.href = '/profile'
  }
}
</script> 