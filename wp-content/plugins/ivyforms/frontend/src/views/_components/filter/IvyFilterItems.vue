<template>
  <div>
    <!-- Options -->
    <template v-if="!loading">
      <div
        v-for="(option, index) in filteredOptions"
        :key="index"
        class="ivyforms-filter-options"
        :class="{ 'ivyforms-filter-options--parent': option.children }"
      >
        <!-- Item Or Parent -->
        <div
          class="ivyforms-filter-item"
          :class="{
            'ivyforms-filter-item--parent': option.children,
            'is-selected': isOptionSelected(option) && !Array.isArray(props.selectedItems),
          }"
          @click="option.children ? undefined : onSelect(option)"
        >
          <div class="ivyforms-flex ivyforms-align-items-center ivyforms-gap-4 ivyforms-width-100">
            <IvyButtonOption
              v-if="option.children"
              type="ghost"
              icon-start-type="outline"
              icon-start="arrow-down"
              icon-start-category="arrows"
              size="xs"
              class="ivyforms-filter-item__collapse-button"
              :class="{ collapsed: collapsedCategories.includes(option.value) }"
              @click="toggleCategory(option.value)"
            />
            <!--            <IvyAvatar-->
            <!--                v-if="option.thumbPath !== undefined"-->
            <!--                :src="option.thumbPath"-->
            <!--                class="ivyforms-mr-16"-->
            <!--            />-->
            <IvyIcon
              v-if="option.icon"
              :name="option.icon"
              :category="option.iconCategory || 'global'"
              :color="option.iconColor"
              :type="option.iconType"
              outer-size="20px"
            />
            <div class="ivyforms-width-100">
              <div
                class="ivyforms-filter-item-label ivyforms-width-100 ivyforms-flex ivyforms-align-items-start ivyforms-justify-content-between ivyforms-gap-4"
              >
                {{ option.label }}
                <div class="ivyforms-filter-item-value regular-14">
                  {{ option.labelId }}
                </div>
              </div>
              <div v-if="option.subtitle" class="ivyforms-filter-item-subtitle">
                {{ option.subtitle }}
              </div>
            </div>
          </div>
          <IvyCheckbox
            v-if="Array.isArray(props.selectedItems)"
            :indeterminate="isOptionIndeterminate(option)"
            :model-value="isOptionSelected(option)"
            @change="option.children ? onSelect(option) : undefined"
          />
        </div>
        <!-- /Item Or Parent -->

        <!-- Children -->
        <div
          v-if="option.children"
          class="ivyforms-filter-item__children__wrapper"
          :class="{ collapsed: collapsedCategories.includes(option.value) }"
        >
          <div class="ivyforms-filter-item__children__items">
            <div
              v-for="(child, indexVal) in option.children"
              :key="indexVal"
              class="ivyforms-filter-item"
              @click="onSelect(child)"
            >
              {{ child.label }}
              <IvyCheckbox :model-value="isOptionSelected(child)" />
            </div>
          </div>
        </div>
        <!-- Children -->
      </div>
    </template>
    <!-- /Options -->

    <!-- Skeleton -->
    <IvySkeleton v-if="loading">
      <template #template>
        <div v-for="n in 10" :key="n" class="ivyforms-filter-options--parent ivyforms-py-8">
          <IvySkeletonItem style="height: 20px" />
        </div>
      </template>
    </IvySkeleton>
    <!-- /Skeleton -->

    <!-- Empty State -->
    <div
      v-if="!filteredOptions.length && !loading"
      class="ivyforms-filter-options--empty ivyforms-p-16"
    >
      <div class="ivyforms-filter-options--empty__text medium-14 ivyforms-mb-8 text-align-center">
        {{ formatNoResults(filterText) }}
      </div>
      <div class="ivyforms-filter-options--empty__text regular-12 text-align-center">
        {{ getLabel('try_adjusting_search') }}
      </div>
    </div>
    <!-- /Empty State -->
  </div>
</template>

<script setup lang="ts">
import type { IconCategory } from '@/types/icons/icon-category'
import { computed, ref } from 'vue'
import { useLabels } from '@/composables/useLabels'

interface Option {
  value: string | number
  label: string
  labelId?: string
  icon?: string
  iconColor?: string
  iconType?: string
  iconCategory?: IconCategory
  thumbPath?: string
  children?: Option[]
  subtitle?: string
}

interface Props {
  options?: Array<Option>
  selectedItems: (number | string)[] | number | string | null
  filterText: string
  loading: boolean
}
const { getLabel } = useLabels()
const formatNoResults = (text: string) => `${getLabel('no_results_for')} ${text}`

const props = defineProps<Props>()

const emit = defineEmits(['select'])

const collapsedCategories = ref([])

const filteredOptions = computed(() => {
  const optionsToFilter = JSON.parse(JSON.stringify(props.options))
  if (!props.filterText) return optionsToFilter

  return optionsToFilter.filter((option: Option) => {
    if (option.children) {
      const filteredChildren = option.children.filter((child: Option) =>
        child.label.toLowerCase().includes(props.filterText.toLowerCase()),
      )
      option.children = filteredChildren

      return filteredChildren.length > 0
    }

    return option.label.toLowerCase().includes(props.filterText.toLowerCase())
  })
})

const onSelect = (option: Option) => {
  if (option.children) {
    const indeterminateOption = isOptionIndeterminate(option)

    for (const child of option.children) {
      if (indeterminateOption && isOptionSelected(child)) {
        continue
      }

      emit('select', child.value)
    }

    return
  }

  emit('select', option.value)
}

const isOptionSelected = (option: Option) => {
  if (Array.isArray(props.selectedItems)) {
    if (option.children) {
      return option.children.every((child) =>
        (props.selectedItems as (string | number)[]).includes(child.value),
      )
    }

    return props.selectedItems.includes(option.value)
  }

  return props.selectedItems === option.value
}

const isOptionIndeterminate = (option: Option) => {
  if (!Array.isArray(props.selectedItems)) {
    return false
  }

  if (option.children) {
    return (
      !option.children.every((child) =>
        (props.selectedItems as (string | number)[]).includes(child.value),
      ) &&
      option.children.some((child) =>
        (props.selectedItems as (string | number)[]).includes(child.value),
      )
    )
  }

  return false
}

const toggleCategory = (categoryValue: number | string) => {
  const valueIndex = collapsedCategories.value.findIndex(
    (collapsedCategory) => collapsedCategory === categoryValue,
  )

  if (valueIndex === -1) {
    collapsedCategories.value.push(categoryValue)
  } else {
    collapsedCategories.value.splice(valueIndex, 1)
  }
}
</script>

<style lang="scss">
// Options
.ivyforms-filter-options {
  // Parent
  &--parent {
    padding: 0 8px;
  }
  // Empty
  &--empty {
    &__text {
      color: var(--map-base-text--2);
      font-size: 14px;
      line-height: 20px;
      font-weight: 400;
    }
  }
}

// Filter Item
.ivyforms-filter-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  margin: 0px -16px;
  color: var(--map-base-text-0);
  font-weight: 400;
  gap: 8px;
  position: relative;
  margin: 0;
  font-size: 14px;
  line-height: 20px;
  padding: 8px 16px;

  // Parent
  &--parent {
    margin: 0;
    padding: 8px 8px 8px 0;
  }

  // Collapse Button
  &__collapse-button {
    transition: 200ms ease;

    // Collapsed
    &.collapsed {
      rotate: -90deg;
    }
  }

  // Children
  &__children {
    // Wrapper
    &__wrapper {
      border-left: 1px solid var(--map-divider);
      padding-left: 8px;
      margin-left: 8px;
      display: grid;
      grid-template-rows: 1fr;
      transition: grid-template-rows 200ms;

      // Collapsed
      &.collapsed {
        grid-template-rows: 0fr;
      }

      // Item
      .ivyforms-filter-item {
        margin: 0;
        padding: 9px 8px;
        border-radius: 4px;
        line-height: 22px;

        // Tablet & Up
        @include tablet-and-up {
          padding: 7px 8px;
        }
      }
    }

    // Items
    &__items {
      overflow: hidden;
    }
  }

  // Hover
  &:not(.ivyforms-filter-item--parent):hover {
    background: var(--map-hover);
  }

  // Selected
  &.is-selected {
    background: var(--map-base-brand-o10);
    color: var(--map-base-text-0);
  }

  // Checkbox
  .el-checkbox {
    height: 20px;
    min-height: 20px;
  }

  &-label {
    word-break: break-word;
  }

  &-subtitle {
    color: var(--map-base-dusk-text--1);
    font-size: 14px;
    @include tablet-and-up {
      font-size: 12px;
    }
  }

  &-value {
    color: var(--map-base-text--2);
    white-space: nowrap;
  }
}
</style>
