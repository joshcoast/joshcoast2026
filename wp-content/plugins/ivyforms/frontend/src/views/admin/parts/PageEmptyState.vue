<template>
  <div :class="['ivyforms-page-empty-state', `ivyforms-page-empty-state__${props.image}`]">
    <EmptyStateFieldsImage v-if="props.image === 'fields'" />
    <EmptyStateFieldsOptionsImage v-if="props.image === 'fields-options'" />
    <EmptyStateFormBuilderImage v-else-if="props.image === 'form-builder' && isLight()" />
    <EmptyStateFormBuilderDarkImage v-else-if="props.image === 'form-builder' && isDark()" />
    <EmptyStateFiltersImage v-else-if="props.image === 'filters'" />
    <EmptyStateNotFoundImage v-else-if="props.image === 'not-found'" />
    <div class="ivyforms-page-empty-state__text">
      <span class="medium-16">{{ props.title }}</span>
      <span class="ivyforms-page-empty-state__text__subtitle regular-14">
        {{ props.subtitle }}
      </span>
    </div>
    <slot name="actionButton" />
  </div>
</template>

<script setup lang="ts">
import EmptyStateFieldsImage from '@/assets/images/empty-state/empty-state-fields.svg?component'
import EmptyStateFieldsOptionsImage from '@/assets/images/empty-state/empty-state-fields-options.svg?component'
import EmptyStateFormBuilderImage from '@/assets/images/empty-state/empty-state-form-builder.svg?component'
import EmptyStateFormBuilderDarkImage from '@/assets/images/empty-state/empty-state-form-builder-dark.svg?component'
import EmptyStateFiltersImage from '@/assets/images/empty-state/empty-state-filters.svg?component'
import EmptyStateNotFoundImage from '@/assets/images/empty-state/empty-state-not-found.svg?component'
import { useTheme } from '@/composables/useTheme'

interface Props {
  title: string
  subtitle?: string
  image?: 'fields' | 'fields-options' | 'form-builder' | 'not-found' | 'filters'
}

const props = withDefaults(defineProps<Props>(), {
  title: '',
  subtitle: '',
  image: 'fields',
})

// Use the shared theme composable
const { isDark, isLight } = useTheme()
</script>

<style scoped lang="scss">
// Page Empty State
.ivyforms-page-empty-state {
  display: flex;
  padding: 32px;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 24px;
  align-self: stretch;

  &__form-builder {
    padding: 42px 12px;
    gap: 16px;
  }

  // Text
  &__text {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    text-align: center;
    color: var(--map-base-dusk-symbol-2);

    // Subtitle
    &__subtitle {
      color: var(--map-base-text--1);
    }
  }
}
</style>
