<template>
  <ElCollapse
    v-model="activeNames"
    class="ivyforms-collapse ivyforms-width-100"
    v-bind="$attrs"
    :accordion="props.accordion"
  >
    <slot />
  </ElCollapse>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { Ref } from 'vue'

type Props = {
  accordion: boolean
}

const activeNames: Ref<string[]> = ref(['1'])
const props = withDefaults(defineProps<Props>(), {
  accordion: false,
})
</script>

<style scoped lang="scss">
.ivyforms-collapse {
  border: none;
}
</style>
