<template>
  <div
    class="ivyforms-form-builder-page__section-header ivyforms-flex ivyforms-flex-1 ivyforms-p-12"
  >
    <div class="ivyforms-form-builder-page__section-header__left">
      <!--   Add tabs   -->
    </div>
    <div class="ivyforms-form-builder-page__section-header__right">
      <IvyTooltip :content="getLabel('copy_shortcode')" placement="top" theme="inverted">
        <IvyButtonAction
          size="s"
          priority="tertiary"
          type="fill"
          icon-only
          icon-start="code"
          icon-start-category="global"
          icon-start-type="fill"
          @click.stop="copyShortcodeToClipboard"
        />
      </IvyTooltip>
    </div>
  </div>
</template>
<script setup lang="ts">
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import IvyMessage from '@/views/_components/message/ivyMessage.ts'
import { useLabels } from '@/composables/useLabels.ts'
import { useWcagColors } from '@/composables/useWcagColors'

const { getLabel } = useLabels()
const { startWatching } = useWcagColors()
startWatching()

const formBuilderStore = useFormBuilder()
const copyShortcodeToClipboard = () => {
  if (!formBuilderStore.formId) {
    IvyMessage({
      message: getLabel('form_id_not_found'),
      type: 'error',
    })
    return
  }
  const shortcode = `[ivyforms id=${formBuilderStore.formId}]`
  if (navigator.clipboard) {
    navigator.clipboard.writeText(shortcode).then(() => {
      IvyMessage({
        message: getLabel('shortcode_copied'),
        type: 'success',
      })
    })
  } else {
    // Fallback for insecure contexts
    const textarea = document.createElement('textarea')
    textarea.value = shortcode
    textarea.style.position = 'fixed' // avoid scrolling
    textarea.style.opacity = '0'
    document.body.appendChild(textarea)
    textarea.select()
    try {
      document.execCommand('copy')
      IvyMessage({
        message: getLabel('shortcode_copied'),
        type: 'success',
      })
    } catch (err) {
      IvyMessage({
        message: `${getLabel('error_refreshing')} ${err}`,
        type: 'error',
      })
    }
    document.body.removeChild(textarea)
  }
}
</script>
<style scoped lang="scss">
.ivyforms-form-builder-page__section-header {
  flex-direction: row; // Changed to row to align items horizontally
  justify-content: space-between; // This will space out left and right sections
  align-items: center; // Center items vertically
  align-self: stretch;
  border-radius: 12px;
  max-height: 40px;
  background: var(--map-ground-level-1-foreground);
  /* Shadow/Sheet */
  box-shadow:
    0 10px 10px -2px rgba(18, 26, 38, 0.05),
    0px 4px 3px 0px rgba(18, 26, 38, 0.03);

  &__left,
  &__right {
    display: flex;
    gap: 8px;
    flex: 1 1 50%; // Make both sections flex-grow and flex-shrink with 50% base width
    min-width: 0; // Allow sections to shrink below their content size if needed
  }

  &__left {
    align-items: center;
    justify-content: flex-start;
  }

  &__right {
    align-items: center;
    justify-content: flex-end;
  }
}
</style>
