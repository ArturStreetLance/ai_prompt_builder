<template>
  <n-config-provider :theme="darkTheme">
    <n-message-provider>
      <n-layout class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
        <app-header :user="user" />
        
        <n-layout-content class="py-6">
          <slot />
        </n-layout-content>
        
        <app-footer />
      </n-layout>
    </n-message-provider>
  </n-config-provider>
</template>

<script setup>
import { darkTheme } from 'naive-ui'
import {
  NConfigProvider,
  NMessageProvider,
  NLayout,
  NLayoutContent,
} from 'naive-ui'
import AppHeader from '@/Components/Layout/AppHeader.vue'
import AppFooter from '@/Components/Layout/AppFooter.vue'

defineProps({
  user: Object
})
</script> 