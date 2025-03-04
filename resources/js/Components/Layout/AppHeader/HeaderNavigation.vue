<template>
  <nav class="flex-1 flex items-center justify-center space-x-6">
    <nav-link 
      v-for="item in navigationItems" 
      :key="item.key"
      :icon="item.icon"
      :text="item.text"
    />
  </nav>
</template>

<script setup>
import NavLink from './NavLink.vue'

const navigationItems = [
  { key: 'prompts', icon: 'i-carbon-document', text: 'Мои промпты' },
  { key: 'favorites', icon: 'i-carbon-star', text: 'Избранное' },
  { key: 'popular', icon: 'i-carbon-trending-topic', text: 'Популярные' },
]
</script> 