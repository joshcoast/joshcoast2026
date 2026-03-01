<template>
  <IvySelectInput
    ref="elInputRef"
    v-model="selectedFontFamily"
    :placeholder="'Select font'"
    :clearable="false"
    :disabled="false"
    :multiple="false"
    :class="{ 'is-secondary': isSecondary }"
    :aria-label="props.ariaLabel || getLabel('font_family')"
    @change="setFontFamily"
  >
    <IvySelectOption
      v-for="font in fonts"
      :key="font"
      :value="font"
      :label="font"
      :class="{ 'is-secondary': isSecondary }"
    >
      <span :style="{ fontFamily: font }">{{ font }}</span>
    </IvySelectOption>
  </IvySelectInput>
</template>

<script setup lang="ts">
import { Editor } from '@tiptap/vue-3'
import { ref } from 'vue'
import { useLabels } from '@/composables/useLabels'
const { getLabel } = useLabels()

const isSecondary = ref(true)
const props = defineProps<{
  editor: Editor
  ariaLabel?: string
}>()

const fonts = ['Inter', 'Comic Sans MS', 'Sans Serif', 'serif', 'monospace', 'cursive']
const selectedFontFamily = ref<string>('Sans Serif')

// Funkcija koja postavlja fontFamily u editor
const setFontFamily = (fontFamily: string) => {
  props.editor.chain().focus().setFontFamily(fontFamily).run()
}
</script>
