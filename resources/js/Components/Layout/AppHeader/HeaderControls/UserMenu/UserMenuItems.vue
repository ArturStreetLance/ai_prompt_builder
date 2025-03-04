<template>
  <n-dropdown
    trigger="click"
    :options="menuOptions"
    @select="handleSelect"
  >
    <slot />
  </n-dropdown>
</template>

<script setup>
import { h } from 'vue'
import { NDropdown } from 'naive-ui'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  user: Object
})

const menuOptions = [
  {
    label: () => h('div', { class: 'flex items-center px-2 py-1' }, [
      h('div', { class: 'text-sm font-medium' }, [props.user?.email || 'Пользователь'])
    ]),
    key: 'header'
  },
  { type: 'divider', key: 'd1' },
  {
    label: () => h('div', { class: 'flex items-center space-x-2' }, [
      h('div', { class: 'i-carbon-user-profile' }),
      h('span', 'Профиль')
    ]),
    key: 'profile'
  },
  // ... остальные пункты меню
]

const emit = defineEmits(['select'])

const handleSelect = (key) => {
  emit('select', key)
}
</script> 