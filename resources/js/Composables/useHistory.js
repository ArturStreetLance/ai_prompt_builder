import { ref } from 'vue'

export function useHistory(initial = []) {
  const history = ref([initial])
  const currentIndex = ref(0)

  const push = (state) => {
    history.value = history.value.slice(0, currentIndex.value + 1)
    history.value.push(JSON.parse(JSON.stringify(state)))
    currentIndex.value++
  }

  const undo = () => {
    if (currentIndex.value > 0) {
      currentIndex.value--
      return JSON.parse(JSON.stringify(history.value[currentIndex.value]))
    }
  }

  const redo = () => {
    if (currentIndex.value < history.value.length - 1) {
      currentIndex.value++
      return JSON.parse(JSON.stringify(history.value[currentIndex.value]))
    }
  }

  return {
    push,
    undo,
    redo,
    canUndo: () => currentIndex.value > 0,
    canRedo: () => currentIndex.value < history.value.length - 1
  }
} 