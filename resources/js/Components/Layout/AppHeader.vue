<template>
  <n-layout-header bordered class="bg-gray-900/95 backdrop-blur-md border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-4 h-16">
      <div class="flex items-center justify-between h-full">
        <!-- Левая часть -->
        <div class="flex items-center space-x-8">
          <!-- Лого -->
          <n-button text class="!text-2xl !text-white hover:!text-indigo-400 transition-colors">
            <template #icon>
              <div class="i-carbon-api mr-2" />
            </template>
            AI Prompt Builder
          </n-button>
          
          <!-- Навигация -->
          <nav class="hidden md:flex items-center space-x-4">
            <n-button text class="!text-gray-300 hover:!text-white">
              <template #icon>
                <div class="i-carbon-document mr-1" />
              </template>
              Промпты
            </n-button>
            <n-button text class="!text-gray-300 hover:!text-white">
              <template #icon>
                <div class="i-carbon-star mr-1" />
              </template>
              Избранное
            </n-button>
          </nav>
        </div>

        <!-- Правая часть -->
        <div class="flex items-center space-x-4">
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
            <n-button circle class="!bg-gray-800 hover:!bg-gray-700 !border-gray-700">
              <template #icon>
                <div class="i-carbon-user-avatar text-gray-300" />
              </template>
            </n-button>
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
  }
}
</script> 