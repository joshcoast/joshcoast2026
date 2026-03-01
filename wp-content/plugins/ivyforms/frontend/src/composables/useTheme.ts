import { ref, computed } from 'vue'
import { useCookies } from '@vueuse/integrations/useCookies'
import { eventBus } from '@/utils/eventBus'
import type { ThemeType } from '@/types/theme'

// Create a singleton theme state that can be shared across all components
const themeState = ref<ThemeType>('light')
const cookies = useCookies(['ivyforms_theme'])

// Initialize the theme from cookies
const savedTheme = cookies.get('ivyforms_theme')
if (savedTheme && (savedTheme === 'light' || savedTheme === 'dark')) {
  themeState.value = savedTheme
}

// Function to set the theme and broadcast the change
const setTheme = (value: ThemeType) => {
  themeState.value = value
  cookies.set('ivyforms_theme', value)

  // Update body classes for theme styling
  if (value === 'dark') {
    document.body.classList.add('ivyforms-theme-dark')
  } else {
    document.body.classList.remove('ivyforms-theme-dark')
  }

  // Broadcast theme change
  eventBus.emit('theme-changed', value)
}

/**
 * Composable function that provides access to the current theme state
 * and helper functions for theme management
 */
export function useTheme() {
  // Create a computed ref for v-model compatibility
  const theme = computed({
    get: () => themeState.value,
    set: (value) => setTheme(value),
  })

  return {
    // Raw ref - can be used with v-model
    theme,
    // Helper functions for checking theme
    isDark: () => themeState.value === 'dark',
    isLight: () => themeState.value === 'light',
    // Function to explicitly set the theme
    setTheme,
    getTheme: () => themeState.value,
  }
}
