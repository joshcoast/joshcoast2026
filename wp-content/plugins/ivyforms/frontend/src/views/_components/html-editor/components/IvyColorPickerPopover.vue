<template>
  <IvyPopover
    ref="popoverRef"
    popper-background="level-2-foreground"
    trigger="click"
    :width="296"
    @show="onShow()"
  >
    <div class="ivyforms-color-set">
      <div
        v-for="color in colorSet"
        :key="color"
        :style="{
          'background-color': color,
          border: color === '#FFFFFF' ? '1px solid var(--map-base-dusk-stroke--2)' : 'none',
          width: color === '#FFFFFF' ? '24px' : '26px',
          height: color === '#FFFFFF' ? '24px' : '26px',
        }"
        class="ivyforms-color-set__item"
        :class="{ 'color--selected': hexColor === color }"
        @mousedown.prevent
        @click.stop="apply(color)"
      />
    </div>

    <IvyForm>
      <div class="ivyforms-hex-color ivyforms-mx-12">
        <IvyFormItem ref="hexFormItemRef" label="Hex">
          <IvyTextInput ref="hexInputRef" v-model="hexColor" autofocus maxlength="7" />
        </IvyFormItem>
      </div>

      <div
        class="ivyforms-colorpicker-buttons ivyforms-flex ivyforms-gap-8 ivyforms-align-self-stretch ivyforms-my-10 ivyforms-align-items-center ivyforms-justify-content-end"
      >
        <IvyButtonAction type="border" size="s" @click.prevent="reset">
          {{ getLabel('reset') }}
        </IvyButtonAction>

        <IvyButtonAction size="s" @click.prevent="save">
          {{ getLabel('save') }}
        </IvyButtonAction>
      </div>
    </IvyForm>

    <template #reference>
      <slot name="reference" />
    </template>
  </IvyPopover>
</template>

<script setup lang="ts">
import { ref, unref } from 'vue'
import { Editor, getMarkAttributes } from '@tiptap/vue-3'
import IvyForm from '@/views/_components/form/IvyForm.vue'
import IvyFormItem from '@/views/_components/form/IvyFormItem.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'
import { useLabels } from '@/composables/useLabels'
import { colorSet } from '@/constants/colorSet'

const { getLabel } = useLabels()

const props = defineProps<{
  editor: Editor
  textStyleProperty: 'color' | 'backgroundColor'
}>()

const hexFormItemRef = ref(null)
const hexInputRef = ref(null)
const popoverRef = ref(null)
const hexColor = ref(null)

const onShow = () => {
  hexColor.value =
    getMarkAttributes(props.editor.state, 'textStyle')[props.textStyleProperty] || null
  hexFormItemRef.value.updateHasValue()
}

const apply = (color: string) => {
  hexColor.value = color
  hexFormItemRef.value.updateHasValue()

  if (props.textStyleProperty === 'color') {
    props.editor.chain().focus().setColor(color).run()
  } else {
    props.editor.chain().focus().setTextBackgroundColor(color).run()
  }
}

const save = () => {
  hexInputRef.value.focus()

  if (props.textStyleProperty === 'color') {
    props.editor.chain().focus().setColor(hexColor.value).run()
  } else {
    props.editor.chain().focus().setTextBackgroundColor(hexColor.value).run()
  }

  unref(popoverRef).hide()
}

const reset = () => {
  hexColor.value = null
  hexFormItemRef.value.updateHasValue()

  if (props.textStyleProperty === 'color') {
    props.editor.chain().focus().unsetColor().run()
  } else {
    props.editor.chain().focus().unsetTextBackgroundColor().run()
  }
}
</script>

<style lang="scss">
@use 'sass:list' as *;
@use 'sass:meta' as *;
.ivyforms-color-set {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;

  &__item {
    height: 26px;
    width: 26px;
    border-radius: 50%;
    cursor: pointer;
    opacity: 1;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;

    &:hover {
      opacity: 0.7;
    }

    &.color--selected:after {
      font-size: 20px;
      color: var(--primitive-white);
    }
  }
}
</style>
