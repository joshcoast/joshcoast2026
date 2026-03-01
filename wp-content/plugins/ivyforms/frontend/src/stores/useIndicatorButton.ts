import { reactive } from 'vue'

export const useIndicatorState = () => {
  const indicatorState = reactive({
    isButtonActive: false,
  })

  return {
    indicatorState,
  }
}
