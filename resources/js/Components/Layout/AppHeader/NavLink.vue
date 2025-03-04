<template>
  <n-button text class="!text-gray-300 hover:!text-white">
    <template #icon>
      <div :class="icon" class="mr-1" />
    </template>
    {{ text }}
  </n-button>
</template>

<script setup>
import { NButton } from 'naive-ui'

defineProps({
  icon: String,
  text: String,
})
</script> 