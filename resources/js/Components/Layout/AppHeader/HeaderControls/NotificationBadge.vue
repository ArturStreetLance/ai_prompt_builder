<template>
  <n-badge :value="count" processing>
    <n-button circle class="!bg-gray-800 hover:!bg-gray-700 !border-gray-700">
      <template #icon>
        <div class="i-carbon-notification text-gray-300" />
      </template>
    </n-button>
  </n-badge>
</template>

<script setup>
import { NBadge, NButton } from 'naive-ui'

defineProps({
  count: {
    type: Number,
    default: 0
  }
})
</script> 