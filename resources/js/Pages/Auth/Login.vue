<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500">
    <div class="w-full max-w-md px-8 py-10 mx-4">
      <!-- Логотип или название -->
      <div class="text-center mb-10">
        <h1 class="text-4xl font-bold text-white mb-2">AI Prompt Builder</h1>
        <p class="text-indigo-100">Создавайте мощные промпты для AI</p>
      </div>

      <!-- Карточка входа -->
      <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 shadow-xl border border-white/20">
        <h2 class="text-2xl font-semibold text-white mb-6 text-center">
          Добро пожаловать
        </h2>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Сообщение об ошибке -->
          <div v-if="form.errors.email"
               class="bg-red-500/10 border border-red-500/50 rounded-lg p-3 text-red-200 text-sm">
            {{ form.errors.email }}
          </div>

          <!-- Email поле -->
          <div class="space-y-2">
            <label for="email" class="block text-sm font-medium text-indigo-100">
              Email
            </label>
            <div class="relative">
              <input
                v-model="form.email"
                id="email"
                type="email"
                required
                class="w-full px-4 py-3 bg-white/10 border border-white/10 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                placeholder="your@email.com"
              />
<!--              <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-5 w-5 text-indigo-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                </svg>
              </div>-->
            </div>
          </div>

          <!-- Пароль поле -->
          <div class="space-y-2">
            <label for="password" class="block text-sm font-medium text-indigo-100">
              Пароль
            </label>
            <div class="relative">
              <input
                v-model="form.password"
                id="password"
                type="password"
                required
                class="w-full px-4 py-3 bg-white/10 border border-white/10 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                placeholder="••••••••"
              />
<!--              <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-5 w-5 text-indigo-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>-->
            </div>
          </div>

          <!-- Кнопка входа -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-lg hover:from-indigo-500 hover:to-purple-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="form.processing" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Вход...
            </span>
            <span v-else>Войти</span>
          </button>

          <!-- Дополнительные опции -->
          <div class="flex items-center justify-between text-sm">
            <a href="#" class="text-indigo-200 hover:text-white transition-colors">
              Забыли пароль?
            </a>
            <a href="/register" class="text-indigo-200 hover:text-white transition-colors">
              Регистрация
            </a>
          </div>
        </form>
      </div>

      <!-- Футер -->
      <div class="mt-8 text-center text-sm text-indigo-200">
        <p>Защищено шифрованием. Мы никогда не передаем ваши данные третьим лицам.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  email: '',
  password: '',
})

const submit = () => {
  form.post('/login', {
    preserveScroll: true,
    onError: () => {
      form.reset('password')
    }
  })
}
</script>

<style>
/* Добавляем плавные переходы для всех интерактивных элементов */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Добавляем эффект свечения для полей ввода при фокусе */
input:focus {
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.5);
}

/* Улучшаем читаемость placeholder текста */
::placeholder {
  opacity: 0.5;
}
</style>
