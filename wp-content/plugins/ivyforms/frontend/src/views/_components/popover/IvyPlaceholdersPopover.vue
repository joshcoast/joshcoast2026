<template>
  <IvyPopover ref="popoverRef" trigger="click" :width="340">
    <div
      class="ivyforms-placeholders-popover ivyforms-flex ivyforms-flex-direction-column ivyforms-align-items-start ivyforms-gap-8"
    >
      <!-- Close Icon Top Right -->
      <div class="ivyforms-placeholders-popover__close ivyforms-p-2" @click="popoverRef.hide()">
        <IvyIcon name="close" category="global" type="outline" size="s" />
      </div>
      <IvyTabs
        v-model="activeTab"
        :tabs="tabs"
        priority="secondary"
        class="ivyforms-placeholders-popover__tabs ivyforms-flex ivyforms-pl-8 ivyforms-pr-8 ivyforms-flex-direction-column"
      />
      <div
        class="ivyforms-placeholders-popover__search ivyforms-flex ivyforms-pl-8 ivyforms-pr-8 ivyforms-flex-direction-column"
      >
        <IvySearch v-model="searchText" :placeholder="getLabel('search_placeholder')" />
      </div>
      <IvyScrollbar
        class="ivyforms-placeholders-popover__list"
        style="max-height: 220px; height: 220px"
      >
        <template v-if="activeTab === 'fields'">
          <template v-if="filteredFields.length === 0">
            <div
              class="ivyforms-placeholders-popover__empty ivyforms-flex ivyforms-flex-direction-column ivyforms-align-items-center ivyforms-gap-10 medium-14"
            >
              {{ getLabel('no_fields_available') }}
            </div>
          </template>
          <template v-else>
            <div
              v-for="field in filteredFields"
              :key="field.fieldIndex"
              class="ivyforms-placeholders-popover__item ivyforms-gap-6 ivyforms-flex ivyforms-justify-content-between ivyforms-align-center"
              :class="{ 'is-highlighted': highlightedField === field.fieldIndex }"
              @click="insertPlaceholder(getFieldPlaceholder(field))"
              @mouseenter="highlightedField = field.fieldIndex"
              @mouseleave="highlightedField = null"
            >
              <span class="ivyforms-placeholders-popover__item-label regular-14">{{
                field.label
              }}</span>
              <span class="ivyforms-placeholders-popover__item-value regular-14">{{
                getFieldPlaceholder(field)
              }}</span>
            </div>
          </template>
        </template>
        <template v-else>
          <template v-if="filteredGeneralPlaceholders.length === 0">
            <div
              class="ivyforms-placeholders-popover__empty ivyforms-flex ivyforms-flex-direction-column ivyforms-align-items-center ivyforms-gap-10 medium-14"
            >
              {{ getLabel('no_placeholders_available') }}
            </div>
          </template>
          <template v-else>
            <div
              v-for="item in filteredGeneralPlaceholders"
              :key="item.key"
              class="ivyforms-placeholders-popover__item ivyforms-gap-6 ivyforms-flex ivyforms-justify-content-between ivyforms-align-center"
              :class="{ 'is-highlighted': highlightedGeneral === item.key }"
              @click="insertPlaceholder(item.value)"
              @mouseenter="highlightedGeneral = item.key"
              @mouseleave="highlightedGeneral = null"
            >
              <span class="ivyforms-placeholders-popover__item-label regular-14">{{
                item.label
              }}</span>
              <span class="ivyforms-placeholders-popover__item-value regular-14">{{
                item.value
              }}</span>
            </div>
          </template>
        </template>
      </IvyScrollbar>
    </div>
    <template #reference>
      <slot name="reference" />
    </template>
  </IvyPopover>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import IvyTabs from '@/views/_components/tabs/IvyTabs.vue'
import IvySearch from '@/views/_components/search/IvySearch.vue'
import { useFormBuilder } from '@/stores/useFormBuilder'
import { useLabels } from '@/composables/useLabels'
import IvyScrollbar from '@/views/_components/scrollbar/IvyScrollbar.vue'
import { getGeneralPlaceholders } from '@/constants/generalPlaceholders.ts'
import type { Field } from '@/types/field'
import IvyIcon from '@/views/_components/icon/IvyIcon.vue'

const props = defineProps({
  fields: {
    type: Array,
    default: undefined,
  },
  general: {
    type: Array,
    default: undefined,
  },
})

const { getLabel } = useLabels()
const formBuilderStore = useFormBuilder()

const activeTab = ref('fields')
const searchText = ref('')
const highlightedField = ref<number | null>(null)
const highlightedGeneral = ref<string | null>(null)
const popoverRef = ref(null)

const tabs = [
  { name: 'fields', label: getLabel('fields') },
  { name: 'general', label: getLabel('general') },
]

const fieldsSource = computed(() =>
  props.fields !== undefined ? props.fields : formBuilderStore.fields,
)

// Type for general placeholders
interface GeneralPlaceholderItem {
  key: string
  label: string
  value: string
  [key: string]: unknown
}

const generalSource = computed<GeneralPlaceholderItem[]>(() =>
  props.general !== undefined
    ? (props.general as GeneralPlaceholderItem[])
    : (getGeneralPlaceholders() as GeneralPlaceholderItem[]),
)

const filteredFields = computed<Field[]>(() => {
  if (!searchText.value) return fieldsSource.value as Field[]
  const search = searchText.value.toLowerCase()
  return (fieldsSource.value as Field[]).filter((f) => {
    const label = (f.label || '').toLowerCase()
    const placeholder = getFieldPlaceholder(f).toLowerCase()
    return label.includes(search) || placeholder.includes(search)
  })
})

const filteredGeneralPlaceholders = computed<GeneralPlaceholderItem[]>(() => {
  if (!searchText.value) return generalSource.value
  const search = searchText.value.toLowerCase()
  return generalSource.value.filter(
    (item) =>
      (item.label || '').toLowerCase().includes(search) ||
      (item.value || '').toLowerCase().includes(search),
  )
})

const emit = defineEmits(['insertPlaceholder'])

function insertPlaceholder(value: string) {
  emit('insertPlaceholder', value)
}
function getFieldPlaceholder(field: Field): string {
  return `{{${field.type}_${field.fieldIndex}}}`
}
</script>

<style lang="scss">
.el-popper {
  &.ivyforms-popover-popper {
    &.level-2-foreground {
      padding: 0 !important;
      transform: translate(-18px) !important;
    }
  }
}

.ivyforms-placeholders-popover {
  min-width: 340px;
  max-width: 340px;
  display: flex;
  padding: 8px 0;
  flex-direction: column;
  align-items: flex-start;
  gap: 8px;
  align-self: stretch;

  &__tabs {
    align-items: flex-start;
    gap: 10px;
    align-self: stretch;

    .el-tabs__nav {
      height: 32px;
      align-items: center;
    }
  }
  &__search {
    align-items: flex-start;
    gap: 10px;
    align-self: stretch;

    .ivyforms-search {
      width: 100%;
    }
  }
  &__list {
    padding: 0;
    width: 100%;

    .el-scrollbar__wrap {
      width: 100%;
      max-height: 100% !important;
      height: 100% !important;

      .el-scrollbar__view {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        align-self: stretch;
      }
    }
  }

  &__empty {
    align-self: stretch;
    color: var(--map-base-text-0);
  }
  &__item {
    align-items: center;
    height: 36px;
    padding: 0 12px;
    align-self: stretch;
    cursor: pointer;

    &-label {
      color: var(--map-base-text-0);
    }
    &-value {
      color: var(--map-base-text--2);
    }
    &.is-highlighted,
    &:active,
    &:hover {
      color: var(--map-base-purple-symbol-0);
      background: var(--map-base-purple-o05);

      .ivyforms-placeholders-popover__item-label,
      .ivyforms-placeholders-popover__item-value {
        color: var(--map-base-purple-symbol-0);
      }
    }
  }
  &__close {
    position: absolute;
    top: -15px;
    right: -15px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    z-index: 2;
    cursor: pointer;
    box-shadow:
      0 1px 2px 0 rgba(18, 26, 38, 0.3),
      0 1px 3px 1px rgba(18, 26, 38, 0.15);
    background: var(--map-ground-level-2-foreground);
  }
}
</style>
