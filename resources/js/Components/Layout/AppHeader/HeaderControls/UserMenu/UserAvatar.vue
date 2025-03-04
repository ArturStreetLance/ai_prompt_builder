<template>
  <div class="flex items-center space-x-3 cursor-pointer">
    <span v-if="showEmail" class="text-sm text-gray-300 hidden md:inline-block">
      {{ email }}
    </span>
    <n-avatar
      :src="avatarUrl"
      round
      :size="32"
      class="!bg-gray-800 !border-gray-700"
    />
  </div>
</template>

<script setup>
import { NAvatar } from 'naive-ui'

const props = defineProps({
  email: String,
  avatarUrl: String,
  showEmail: {
    type: Boolean,
    default: true
  }
})
</script> 