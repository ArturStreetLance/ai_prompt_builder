<template>
  <n-layout-header bordered class="bg-gray-900/95 backdrop-blur-md border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-4 h-16">
      <div class="flex items-center justify-between h-full">
        <header-logo />
        <header-navigation />
        <header-controls
          :user="user"
          :notification-count="3"
        />
      </div>
    </div>
  </n-layout-header>
</template>

<script setup>
import { NLayoutHeader } from 'naive-ui'
import HeaderLogo from './HeaderLogo.vue'
import HeaderNavigation from './HeaderNavigation.vue'
import HeaderControls from './HeaderControls/index.vue'

defineProps({
  user: Object
})
</script> 