<template>
  <n-layout-footer bordered class="bg-gray-900/95 backdrop-blur-md border-t border-gray-800">
    <div class="max-w-7xl mx-auto px-4 py-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Информация -->
        <div class="space-y-4">
          <h3 class="text-lg font-semibold text-white">AI Prompt Builder</h3>
          <p class="text-sm text-gray-400">
            Создавайте, редактируйте и делитесь промптами для искусственного интеллекта
          </p>
        </div>

        <!-- Ссылки -->
        <div class="space-y-4">
          <h3 class="text-sm font-semibold text-gray-300">Полезные ссылки</h3>
          <div class="grid grid-cols-2 gap-2">
            <n-button text class="!text-gray-400 hover:!text-white justify-start">
              Документация
            </n-button>
            <n-button text class="!text-gray-400 hover:!text-white justify-start">
              API
            </n-button>
            <n-button text class="!text-gray-400 hover:!text-white justify-start">
              Примеры
            </n-button>
            <n-button text class="!text-gray-400 hover:!text-white justify-start">
              Блог
            </n-button>
          </div>
        </div>

        <!-- Социальные сети -->
        <div class="space-y-4">
          <h3 class="text-sm font-semibold text-gray-300">Присоединяйтесь</h3>
          <div class="flex space-x-4">
            <n-button circle class="!bg-gray-800 hover:!bg-gray-700 !border-gray-700">
              <template #icon>
                <div class="i-carbon-logo-github text-gray-300" />
              </template>
            </n-button>
            <n-button circle class="!bg-gray-800 hover:!bg-gray-700 !border-gray-700">
              <template #icon>
                <div class="i-carbon-logo-twitter text-gray-300" />
              </template>
            </n-button>
            <n-button circle class="!bg-gray-800 hover:!bg-gray-700 !border-gray-700">
              <template #icon>
                <div class="i-carbon-logo-discord text-gray-300" />
              </template>
            </n-button>
          </div>
        </div>
      </div>

      <div class="mt-8 pt-6 border-t border-gray-800">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
          <p class="text-sm text-gray-400">
            © {{ new Date().getFullYear() }} AI Prompt Builder. Все права защищены.
          </p>
          <div class="flex space-x-6">
            <n-button text size="tiny" class="!text-gray-400 hover:!text-white">
              Конфиденциальность
            </n-button>
            <n-button text size="tiny" class="!text-gray-400 hover:!text-white">
              Условия
            </n-button>
          </div>
        </div>
      </div>
    </div>
  </n-layout-footer>
</template>

<script setup>
import { NLayoutFooter, NButton } from 'naive-ui'
</script> 