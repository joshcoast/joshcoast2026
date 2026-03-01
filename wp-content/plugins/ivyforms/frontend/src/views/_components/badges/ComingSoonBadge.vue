<template>
  <div :class="['ivyforms-coming-soon-badge', `ivyforms-coming-soon-badge_size__${props.size}`]">
    <component :is="currentArrowText" v-if="props.image === 'coming-soon-arrow-text'" />
    <component :is="currentArrow" v-if="props.image === 'coming-soon-arrow'" />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

import ComingSoonArrowTextLight from '@/assets/images/coming-soon/coming-soon-arrow-text-light.svg?component'
import ComingSoonArrowTextDark from '@/assets/images/coming-soon/coming-soon-arrow-text-dark.svg?component'
import ComingSoonArrowLight from '@/assets/images/coming-soon/coming-soon-arrow-light.svg?component'
import ComingSoonArrowDark from '@/assets/images/coming-soon/coming-soon-arrow-dark.svg?component'
import { useTheme } from '@/composables/useTheme'

const { isDark } = useTheme()
interface Props {
  image?: 'coming-soon-arrow-text' | 'coming-soon-arrow'
  size?: 's' | 'd' | 'l'
}

const props = withDefaults(defineProps<Props>(), {
  image: 'coming-soon-arrow-text',
  size: 'd',
})

const currentArrowText = computed(() =>
  isDark() ? ComingSoonArrowTextDark : ComingSoonArrowTextLight,
)

const currentArrow = computed(() => (isDark() ? ComingSoonArrowDark : ComingSoonArrowLight))
</script>

<style scoped lang="scss">
.ivyforms-coming-soon-badge {
  display: flex;
  justify-content: center;
  align-items: center;

  &.ivyforms-coming-soon-badge_size__s {
    height: 16px;
    svg {
      transform: scale(0.8);
    }
  }

  &.ivyforms-coming-soon-badge_size__d {
    height: 28px;
  }

  &.ivyforms-coming-soon-badge_size__l {
    height: 24px;
    svg {
      transform: scale(1.2);
    }
  }
}
</style>
