import { onMounted, onUnmounted } from 'vue'

export function useHotkeys(bindings) {
  const handleKeydown = (event) => {
    const key = event.key.toLowerCase()
    const ctrl = event.ctrlKey || event.metaKey

    Object.entries(bindings).forEach(([hotkey, callback]) => {
      const [modifier, letter] = hotkey.toLowerCase().split('+')
      if (
        (modifier === 'ctrl' && ctrl && key === letter) ||
        (!modifier && key === hotkey)
      ) {
        event.preventDefault()
        callback()
      }
    })
  }

  onMounted(() => {
    window.addEventListener('keydown', handleKeydown)
  })

  onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown)
  })
} 