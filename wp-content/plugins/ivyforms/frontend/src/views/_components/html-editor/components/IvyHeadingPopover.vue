<template>
  <IvySelectInput
    v-model="selectedLevel"
    :placeholder="'Select Heading Level'"
    :clearable="false"
    :disabled="false"
    :multiple="false"
    :class="{ 'is-secondary': isSecondary }"
    :aria-label="props.ariaLabel || getLabel('heading_level')"
    @change="toggleHeading"
  >
    <IvySelectOption
      :value="0"
      :label="getLabel('paragraph')"
      :class="{ 'is-secondary': isSecondary }"
    >
      <p>Paragraph</p>
    </IvySelectOption>
    <IvySelectOption
      v-for="level in 6"
      :key="level"
      :value="level"
      :label="'Heading ' + level"
      :class="{ 'is-secondary': isSecondary }"
    >
      <component :is="'h' + level" data-item-type="heading">Heading {{ level }}</component>
    </IvySelectOption>
  </IvySelectInput>
</template>

<script setup lang="ts">
import { Editor } from '@tiptap/vue-3'
import type { Level } from '@tiptap/extension-heading'
import { ref } from 'vue'
import { useLabels } from '@/composables/useLabels'
const { getLabel } = useLabels()

const props = defineProps<{
  editor: Editor
  ariaLabel?: string
}>()

const isSecondary = ref(true)
const selectedLevel = ref<string>('Paragraph')

const toggleHeading = (level: Level) => {
  if (level > 0) {
    props.editor.commands.toggleHeading({ level })

    return
  }

  props.editor.commands.setParagraph()
}
</script>

<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;
.ivyforms-dropdown-item__label > * {
  color: var(--map-base-text-0);
  margin: 0;
}
</style>
