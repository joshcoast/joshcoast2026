<template>
  <div
    :class="[
      'ivyforms-pro-badge',
      'ivyforms-flex',
      'ivyforms-justify-content-center',
      'ivyforms-align-items-center',
      `ivyforms-pro-badge--size-${props.size}`,
      `ivyforms-pro-badge--image-${props.image}`,
    ]"
  >
    <component :is="currentProBolt" v-if="props.image === 'pro-bolt'" />
    <component :is="currentProText" v-if="props.image === 'pro-text'" />
    <component :is="currentProTextWithBolt" v-if="props.image === 'pro-text-with-bolt'" />
    <component :is="currentProBoltNoBackground" v-if="props.image === 'pro-bolt-no-background'" />
    <component :is="currentProTextNoBackground" v-if="props.image === 'pro-text-no-background'" />
    <component
      :is="currentProTextWithBoltNoBackground"
      v-if="props.image === 'pro-text-with-bolt-no-background'"
    />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import ProBoltLight from '@/assets/images/pro-badge/pro-bolt.svg?component'
import ProBoltDark from '@/assets/images/pro-badge/pro-bolt-dark.svg?component'
import ProTextLight from '@/assets/images/pro-badge/pro-text.svg?component'
import ProTextDark from '@/assets/images/pro-badge/pro-text-dark.svg?component'
import ProTextWithBoltLight from '@/assets/images/pro-badge/pro-text-with-bolt.svg?component'
import ProTextWithBoltDark from '@/assets/images/pro-badge/pro-text-with-bolt-dark.svg?component'
import ProBoltNoBackground from '@/assets/images/pro-badge/pro-bolt-no-background.svg?component'
import ProTextNoBackground from '@/assets/images/pro-badge/pro-text-no-background.svg?component'
import ProTextWithBoltNoBackground from '@/assets/images/pro-badge/pro-text-with-bolt-no-background.svg?component'
import { useTheme } from '@/composables/useTheme'

const { isDark } = useTheme()
interface Props {
  image?:
    | 'pro-bolt'
    | 'pro-text'
    | 'pro-text-with-bolt'
    | 'pro-bolt-no-background'
    | 'pro-text-no-background'
    | 'pro-text-with-bolt-no-background'
  size?: 's' | 'd' | 'l'
}

const props = withDefaults(defineProps<Props>(), {
  image: 'pro-text-with-bolt',
  size: 'd',
})

const currentProBolt = computed(() => (isDark() ? ProBoltDark : ProBoltLight))
const currentProText = computed(() => (isDark() ? ProTextDark : ProTextLight))
const currentProTextWithBolt = computed(() =>
  isDark() ? ProTextWithBoltDark : ProTextWithBoltLight,
)

const currentProBoltNoBackground = computed(() => ProBoltNoBackground)
const currentProTextNoBackground = computed(() => ProTextNoBackground)
const currentProTextWithBoltNoBackground = computed(() => ProTextWithBoltNoBackground)
</script>

<style scoped lang="scss">
.ivyforms-pro-badge {
  &--size-s {
    height: 16px;
    svg {
      transform: scale(0.8);
      border-radius: 50%;
    }
  }

  &--size-d {
    height: 28px;
    svg {
      border-radius: 50%;
    }
  }

  &--size-l {
    height: 24px;
    svg {
      transform: scale(1.2);
    }
  }
}
</style>
