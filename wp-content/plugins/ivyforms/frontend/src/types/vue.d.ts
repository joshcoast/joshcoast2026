import { useLabels } from '@/composables/useLabels'

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $labels: ReturnType<typeof useLabels>
  }
}
