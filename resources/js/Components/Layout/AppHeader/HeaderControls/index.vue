<template>
  <div class="w-1/4 flex items-center justify-end space-x-4">
    <search-button />
    <notification-badge :count="notificationCount" />
    <user-menu-items :user="user" @select="handleMenuSelect">
      <user-avatar
        :email="user?.email"
        :avatar-url="user?.profile?.avatar || '/default-avatar.png'"
      />
    </user-menu-items>
  </div>
</template>

<script setup>
import { useMessage } from 'naive-ui'
import SearchButton from './SearchButton.vue'
import NotificationBadge from './NotificationBadge.vue'
import UserMenuItems from './UserMenu/UserMenuItems.vue'
import UserAvatar from './UserMenu/UserAvatar.vue'

const props = defineProps({
  user: Object,
  notificationCount: {
    type: Number,
    default: 0
  }
})

const message = useMessage()

const handleMenuSelect = (key) => {
  switch (key) {
    case 'logout':
      const form = useForm({})
      form.post('/logout')
      break
    case 'profile':
      window.location.href = '/profile'
      break
    case 'settings':
      message.info('Настройки (в разработке)')
      break
  }
}
</script> 