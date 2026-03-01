<template>
  <IvyPopover
    ref="popoverRef"
    trigger="click"
    popper-background="level-1-foreground"
    class="ivyforms-flex ivyforms-flex-direction-column ivyforms-align-items-start"
    width="36"
  >
    <template #reference>
      <IvyButtonOption
        size="s"
        type="ghost"
        priority="white"
        icon-start-category="text-formatting"
        :icon-start="`text-align-${editor.getAttributes(editor.state.selection.$anchor.node().type.name).textAlign || 'left'}`"
        @click.stop.prevent
      />
    </template>

    <IvyButtonOption
      size="s"
      type="ghost"
      priority="white"
      icon-start-category="text-formatting"
      icon-start="text-align-left"
      :active="editor.isActive({ textAlign: 'left' })"
      @click.stop.prevent="setTextAlignment('left')"
    />

    <IvyButtonOption
      size="s"
      type="ghost"
      priority="white"
      icon-start-category="text-formatting"
      icon-start="text-align-right"
      :active="editor.isActive({ textAlign: 'right' })"
      @click.stop.prevent="setTextAlignment('right')"
    />

    <IvyButtonOption
      size="s"
      type="ghost"
      priority="white"
      icon-start-category="text-formatting"
      icon-start="text-align-center"
      :active="editor.isActive({ textAlign: 'center' })"
      @click.stop.prevent="setTextAlignment('center')"
    />

    <IvyButtonOption
      size="s"
      type="ghost"
      priority="white"
      icon-start-category="text-formatting"
      icon-start="text-align-justify"
      :active="editor.isActive({ textAlign: 'justify' })"
      @click.stop.prevent="setTextAlignment('justify')"
    />
  </IvyPopover>
</template>

<script setup lang="ts">
import { Editor } from '@tiptap/vue-3'
import { type Ref, ref } from 'vue'

const props = defineProps<{
  editor: Editor
}>()

const popoverRef: Ref = ref(null)

const setTextAlignment = (alignment: string) => {
  props.editor.chain().focus().setTextAlign(alignment).run()
  popoverRef.value.hide()
}
</script>
<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;

.ivyforms-text-align-popover {
  .ivyforms-button-option {
    margin-right: 0;
  }
}
</style>
