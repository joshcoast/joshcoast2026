<template>
  <IvyPopover
    ref="popoverRef"
    class="ivyforms-link-popover"
    trigger="click"
    popper-background="level-2-foreground"
    width="250"
    @show="onShow"
    @hide="onHide"
  >
    <div class="ivyforms-flex ivyforms-gap-8 ivyforms-mt-4">
      <IvyTextInput
        v-model="href"
        placeholder="Enter Link"
        autofocus
        :error="linkError"
        @blur="validateLink"
        @input="clearError"
      />
    </div>
    <div v-if="linkError" class="ivyforms-error-message ivyforms-ml-8 ivyforms-mt-4">
      {{ linkError }}
    </div>

    <IvyCheckbox v-model="openInNewTab" class="ivyforms-ml-8">
      {{ getLabel('open_in_new_tab') }}
    </IvyCheckbox>

    <div
      class="ivyforms-flex ivyforms-justify-content-between ivyforms-align-self-stretch ivyforms-gap-16 ivyforms-mt-4"
    >
      <IvyButtonAction
        class=""
        priority="white"
        type="fill"
        size="d"
        :disabled="!editor.isActive('link')"
        @click.stop.prevent="unlink"
      >
        {{ getLabel('clear') }}
      </IvyButtonAction>

      <IvyButtonAction class="" size="d" @click.prevent="applyLink">
        {{ getLabel('save') }}
      </IvyButtonAction>
    </div>

    <template #reference>
      <IvyButtonOption
        size="s"
        type="ghost"
        priority="white"
        icon-start-category="text-formatting"
        icon-start="link"
        :active="editor.isActive('link')"
        @click.stop.prevent
      />
    </template>
  </IvyPopover>
</template>

<script setup lang="ts">
import { ref, unref } from 'vue'
import { Editor } from '@tiptap/vue-3'
import IvyButtonOption from '@/views/_components/button/IvyButtonOption.vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import IvyTextInput from '@/views/_components/input/IvyTextInput.vue'
import IvyCheckbox from '@/views/_components/checkbox/IvyCheckbox.vue'
import { useLabels } from '@/composables/useLabels'
const { getLabel } = useLabels()

const props = defineProps<{
  editor: Editor
}>()

const popoverRef = ref(null)
const href = ref<string>('')
const openInNewTab = ref<boolean>(false)
const linkError = ref<string>('')

const isValidUrl = (url: string): boolean => {
  if (!url || url.trim() === '') {
    return false
  }

  // Allow relative URLs (starting with /, #, or ./)
  if (url.startsWith('/') || url.startsWith('#') || url.startsWith('./') || url.startsWith('../')) {
    return true
  }

  // Check for valid URL pattern
  const urlPattern = /^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([/\w .-]*)*\/?$/i
  const emailPattern = /^mailto:[^\s@]+@[^\s@]+\.[^\s@]+$/i
  const telPattern = /^tel:\+?[\d\s\-()]+$/i

  return urlPattern.test(url) || emailPattern.test(url) || telPattern.test(url)
}

const validateLink = () => {
  if (!href.value) {
    linkError.value = getLabel('link_required') || 'Link is required'
    return false
  }

  if (!isValidUrl(href.value)) {
    linkError.value = getLabel('invalid_link')
    return false
  }

  linkError.value = ''
  return true
}

const clearError = () => {
  if (linkError.value) {
    linkError.value = ''
  }
}

const applyLink = () => {
  if (!validateLink()) {
    return
  }

  let finalHref = href.value.trim()

  // Auto-add https:// if no protocol is specified and it's not a relative/special URL
  if (
    !finalHref.startsWith('http://') &&
    !finalHref.startsWith('https://') &&
    !finalHref.startsWith('mailto:') &&
    !finalHref.startsWith('tel:') &&
    !finalHref.startsWith('/') &&
    !finalHref.startsWith('#') &&
    !finalHref.startsWith('./') &&
    !finalHref.startsWith('../')
  ) {
    finalHref = 'https://' + finalHref
  }

  props.editor
    .chain()
    .focus()
    .extendMarkRange('link')
    .setLink({
      href: finalHref,
      target: openInNewTab.value === true ? '_blank' : '_self',
    })
    .run()
  unref(popoverRef).hide()
}

const unlink = () => {
  props.editor.chain().focus().extendMarkRange('link').unsetLink().run()
  unref(popoverRef).hide()
}

const onShow = () => {
  const currentValue = props.editor.getAttributes('link')
  href.value = currentValue.href
  openInNewTab.value = currentValue.target === '_blank'
}

const onHide = () => {
  href.value = ''
  openInNewTab.value = false
  linkError.value = ''
}
</script>

<style lang="scss" scoped>
.ivyforms-error-message {
  color: var(--map-status-error-symbol-0);
  font-size: 12px;
  line-height: 1.4;
}
</style>
