<template>
  <div :class="`ivyforms-logo ${props.orientation}`">
    <LogoHorizontal v-if="props.orientation === 'horizontal'" />
    <LogoVertical v-else-if="props.orientation === 'vertical'" />
    <LogoOnly v-else />
  </div>
</template>

<script setup lang="ts">
import LogoHorizontal from '@/assets/images/logos/logo-horizontal.svg'
import LogoVertical from '@/assets/images/logos/logo-vertical.svg'
import LogoOnly from '@/assets/images/logos/logo-only.svg'

type Props = {
  orientation: 'horizontal' | 'vertical' | 'logoOnly'
}

const props = withDefaults(defineProps<Props>(), {
  orientation: 'horizontal',
})
</script>

<style lang="scss">
// Logo
.ivyforms-logo {
  display: flex;
  align-items: center;
}
</style>
