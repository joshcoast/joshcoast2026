<template>
  <div
    ref="fieldRef"
    :data-field-index="fieldIndex"
    :class="[
      'ivyforms-field-unit ivyforms-p-12 ivyforms-mt-2',
      {
        'ivyforms-field-unit--hovered': !isResizing && (isHovered || isContextMenuOpen),
        'ivyforms-field-unit--selected': isSelected,
      },
    ]"
    @mouseover="!isResizing && (isHovered = true)"
    @mouseleave="!isResizing && (isHovered = false)"
    @click.stop="selectField"
  >
    <component :is="getInputComponent(type)" :field-index="fieldIndex" readonly />
    <div
      v-show="!isResizing && (isHovered || isSelected || isContextMenuOpen)"
      class="ivyforms-field-unit__action-buttons ivyforms-flex ivyforms-gap-4"
    >
      <template v-for="(button, index) in actionButtons" :key="index">
        <div
          v-if="button.isDragHandle"
          v-show="!button.hidden"
          class="ivyforms-field-unit__drag-handle-wrapper ivyforms-flex ivyforms-align-items-center ivyforms-justify-content-center"
          draggable="true"
          @dragstart="onDragHandleStart($event)"
          @dragend="onDragHandleEnd($event)"
        >
          <IvyButtonAction
            size="s"
            class="ivyforms-field-unit__drag-item"
            priority="white"
            type="ghost"
            icon-only
            :icon-start="button.icon"
            :icon-start-category="button.category"
            :icon-start-type="button.type"
            @click.stop
          />
        </div>
        <IvyButtonAction
          v-else
          v-show="!button.hidden"
          size="s"
          priority="white"
          type="ghost"
          icon-only
          :icon-start="button.icon"
          :icon-start-category="button.category"
          :icon-start-type="button.type"
          @click.stop="button.handler($event)"
        />
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, type Component } from 'vue'
import type { IconCategory } from '@/types/icons/icon-category'
import type { IconType } from '@/types/icons/icon-type'
import { useFormBuilder } from '@/stores/useFormBuilder.ts'
import { useFieldResize } from '@/composables/useFieldResize'
import { useContextMenuStore } from '@/stores/contextMenuStore.ts'
import type { ContextMenuAction } from '@/views/_components/context-menu/context-menu-action'
import {
  ContextMenuActionType,
  createContextMenuAction,
} from '@/views/_components/context-menu/kit/ContextMenuActionType'
import TextField from '@/views/admin/form-builder/fields/text/TextField.vue'
import EmailField from '@/views/admin/form-builder/fields/email/EmailField.vue'
import NumericField from '@/views/admin/form-builder/fields/numeric/NumericField.vue'
import ParagraphField from '@/views/admin/form-builder/fields/paragraph/ParagraphField.vue'
import PhoneField from '@/views/admin/form-builder/fields/phone/PhoneField.vue'
import RecaptchaField from '@/views/admin/form-builder/fields/recaptcha/RecaptchaField.vue'
import TurnstileField from '@/views/admin/form-builder/fields/turnstile/TurnstileField.vue'
import HCaptchaField from '@/views/admin/form-builder/fields/hcaptcha/HCaptchaField.vue'
import NameField from '@/views/admin/form-builder/fields/name/NameField.vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import WebsiteField from '@/views/admin/form-builder/fields/website/WebsiteField.vue'
import RequirePro from '@/views/admin/form-builder/fields/require-pro/RequirePro.vue'
import api from '@/composables/IvyFormAPI'
import AddressField from '@/views/admin/form-builder/fields/address/AddressField.vue'
import RadioField from '@/views/admin/form-builder/fields/radio/RadioField.vue'
import CheckboxField from '@/views/admin/form-builder/fields/checkbox/CheckboxField.vue'
import SelectField from '@/views/admin/form-builder/fields/select/SelectField.vue'
import TimeField from '@/views/admin/form-builder/fields/time/TimeField.vue'
import DateField from '@/views/admin/form-builder/fields/date/DateField.vue'
import RatingField from '@/views/admin/form-builder/fields/rating/RatingField.vue'

const formBuilderStore = useFormBuilder()
const { resizingField } = useFieldResize()
const fieldRef = ref(null)

interface Props {
  variant: string
  type: string
  fieldIndex?: number
}

type ActionButton = {
  icon: string
  category?: IconCategory
  type?: IconType
  hidden?: boolean
  draggable?: boolean
  isDragHandle?: boolean
  handler: (event?: Event) => void
}

const props = defineProps<Props>()

const emit = defineEmits<{
  dragstart: [event: DragEvent, fieldIndex: number]
  dragend: [event: DragEvent]
}>()

const selectField = () => {
  formBuilderStore.selectField(props.fieldIndex)
}

const onDragHandleStart = (event: DragEvent) => {
  // Hide the default ghost image
  const img = new Image()
  img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
  event.dataTransfer!.setDragImage(img, 0, 0)
  event.dataTransfer!.effectAllowed = 'move'

  // Find the field data
  const field = formBuilderStore.fields.find((f) => f.fieldIndex === props.fieldIndex)
  if (field) {
    // Set field data in dataTransfer
    event.dataTransfer!.setData('fieldData', JSON.stringify(field))

    // Emit to parent FIRST, then stop propagation
    emit('dragstart', event, props.fieldIndex)

    // Now stop propagation to prevent it from bubbling further (e.g., to row drag)
    event.stopPropagation()
  }
}

const onDragHandleEnd = (event: DragEvent) => {
  emit('dragend', event)
  event.stopPropagation()
}

const isHovered = ref(false)
const isSelected = computed(() => formBuilderStore.isSelected(props.fieldIndex))
const isResizing = computed(() => resizingField.value !== null)

// Context menu
const contextMenuStore = useContextMenuStore()
const isContextMenuOpen = computed(
  () => contextMenuStore.isOpen && contextMenuStore.contextMenuProps.entityId === props.fieldIndex,
)

const openContextMenu = (event: Event) => {
  const field = formBuilderStore.fields.find((f) => f.fieldIndex === props.fieldIndex)
  if (!field) return

  const fieldsInSameRow = formBuilderStore.fields.filter(
    (f) => (f.rowIndex ?? 0) === (field.rowIndex ?? 0),
  ).length

  // Get position in row for left/right arrows
  const fieldsInRow = formBuilderStore.fields
    .filter((f) => (f.rowIndex ?? 0) === (field.rowIndex ?? 0))
    .sort((a, b) => (a.columnIndex ?? 0) - (b.columnIndex ?? 0))

  const currentPosition = fieldsInRow.findIndex((f) => f.fieldIndex === field.fieldIndex)
  const isFirstInRow = currentPosition === 0
  const isLastInRow = currentPosition === fieldsInRow.length - 1

  const actions: ContextMenuAction[] = []

  // Show arrow-left if not first in row
  if (!isFirstInRow) {
    actions.push(
      createContextMenuAction(ContextMenuActionType.MoveLeft, {
        handler: () => {
          formBuilderStore.moveFieldLeft(props.fieldIndex)
          setTimeout(() => formBuilderStore.selectField(props.fieldIndex), 0)
        },
      }),
    )
  }

  // Show arrow-right if not last in row
  if (!isLastInRow) {
    actions.push(
      createContextMenuAction(ContextMenuActionType.MoveRight, {
        handler: () => {
          formBuilderStore.moveFieldRight(props.fieldIndex)
          setTimeout(() => formBuilderStore.selectField(props.fieldIndex), 0)
        },
      }),
    )
  }

  // Show duplicate only if row has less than 5 fields and field is not recaptcha
  if (
    fieldsInSameRow < 5 &&
    field.type !== 'recaptcha' &&
    field.type !== 'turnstile' &&
    field.type !== 'hcaptcha'
  ) {
    actions.push(
      createContextMenuAction(ContextMenuActionType.Duplicate, {
        handler: () => {
          formBuilderStore.duplicateField(props.fieldIndex)
        },
      }),
    )
  }

  // Always show delete
  actions.push(
    createContextMenuAction(ContextMenuActionType.Delete, {
      divided: true,
      handler: () => {
        formBuilderStore.deleteField(props.fieldIndex)
      },
    }),
  )

  // Get the button element from the event
  const buttonElement = event.currentTarget as HTMLElement
  if (buttonElement) {
    contextMenuStore.openContextMenu({
      contextMenuButtonRef: buttonElement,
      actions,
      entityId: props.fieldIndex,
    })
  }
}

// Define action buttons in a computed property for better maintainability
const actionButtons = computed<ActionButton[]>(() => {
  const index = props.fieldIndex
  const field = formBuilderStore.fields.find((f) => f.fieldIndex === index)

  if (!field) return []

  // Check how many fields are in this row
  const fieldsInSameRow = formBuilderStore.fields.filter(
    (f) => (f.rowIndex ?? 0) === (field.rowIndex ?? 0),
  ).length

  // For multi-field rows: show drag, settings, and context menu
  // For single-field rows: show all action buttons as before
  const isMultiFieldRow = fieldsInSameRow > 1

  // Hide up/down arrows if there are multiple fields in the row (they should use row-level arrows instead)
  const hideArrows = fieldsInSameRow > 1

  // Check total number of rows to hide arrows when only 1 row
  const totalRows = Math.max(...formBuilderStore.fields.map((f) => f.rowIndex ?? 0)) + 1
  const hideArrowsForSingleRow = totalRows === 1

  return [
    {
      icon: 'move',
      category: 'builder',
      type: 'outline',
      hidden: false,
      isDragHandle: true, // Special flag for drag handle
      handler: () => {}, // No click handler, only drag
    },
    {
      icon: 'arrow-up',
      category: 'arrows',
      type: 'outline',
      hidden: hideArrows || hideArrowsForSingleRow || (field.rowIndex ?? 0) === 0,
      handler: () => {
        // For single-field rows, swap with row above (like row actions)
        if (fieldsInSameRow === 1 && field.rowIndex && field.rowIndex > 0) {
          const currentRowIndex = field.rowIndex
          const targetRowIndex = currentRowIndex - 1

          // Remember the selected field to maintain selection
          const selectedFieldIndex = formBuilderStore.selectedField?.fieldIndex

          // Swap rowIndex with fields in the row above
          formBuilderStore.fields.forEach((f) => {
            if (f.rowIndex === currentRowIndex) {
              f.rowIndex = targetRowIndex
            } else if (f.rowIndex === targetRowIndex) {
              f.rowIndex = currentRowIndex
            }
          })

          formBuilderStore.updateFieldPositions()

          // Re-select the field to maintain highlight
          if (selectedFieldIndex === field.fieldIndex) {
            formBuilderStore.selectedField = field

            // Scroll the field into view if it moved off-screen
            setTimeout(() => {
              const fieldElement = document.querySelector(
                `[data-field-index="${field.fieldIndex}"]`,
              )
              if (fieldElement) {
                fieldElement.scrollIntoView({ behavior: 'smooth', block: 'center' })
              }
            }, 100)
          }
        } else {
          // For multi-field rows, swap positions within all fields
          formBuilderStore.moveFieldUp(field.fieldIndex)
        }
      },
    },
    {
      icon: 'arrow-down',
      category: 'arrows',
      type: 'outline',
      hidden: hideArrows || hideArrowsForSingleRow || (field.rowIndex ?? 0) >= totalRows - 1,
      handler: () => {
        // For single-field rows, swap with row below (like row actions)
        if (
          fieldsInSameRow === 1 &&
          field.rowIndex !== undefined &&
          field.rowIndex < totalRows - 1
        ) {
          const currentRowIndex = field.rowIndex
          const targetRowIndex = currentRowIndex + 1

          // Remember the selected field to maintain selection
          const selectedFieldIndex = formBuilderStore.selectedField?.fieldIndex

          // Swap rowIndex with fields in the row below
          formBuilderStore.fields.forEach((f) => {
            if (f.rowIndex === currentRowIndex) {
              f.rowIndex = targetRowIndex
            } else if (f.rowIndex === targetRowIndex) {
              f.rowIndex = currentRowIndex
            }
          })

          formBuilderStore.updateFieldPositions()

          // Re-select the field to maintain highlight
          if (selectedFieldIndex === field.fieldIndex) {
            formBuilderStore.selectedField = field

            // Scroll the field into view if it moved off-screen
            setTimeout(() => {
              const fieldElement = document.querySelector(
                `[data-field-index="${field.fieldIndex}"]`,
              )
              if (fieldElement) {
                fieldElement.scrollIntoView({ behavior: 'smooth', block: 'center' })
              }
            }, 100)
          }
        } else {
          // For multi-field rows, swap positions within all fields
          formBuilderStore.moveFieldDown(field.fieldIndex)
        }
      },
    },
    {
      icon: 'settings',
      category: 'global',
      type: 'outline',
      hidden: false,
      handler: () => {
        selectField()
      },
    },
    {
      icon: 'copy',
      category: 'global',
      type: 'outline',
      hidden:
        isMultiFieldRow ||
        field.type === 'recaptcha' ||
        field.type === 'turnstile' ||
        field.type === 'hcaptcha' ||
        fieldsInSameRow >= 5,
      handler: () => {
        formBuilderStore.duplicateField(field.fieldIndex)
      },
    },
    {
      icon: 'trash',
      category: 'global',
      type: 'outline',
      hidden: isMultiFieldRow,
      handler: () => {
        formBuilderStore.deleteField(field.fieldIndex)
      },
    },
    {
      icon: 'context-menu-dot',
      category: 'global',
      type: 'fill',
      hidden: !isMultiFieldRow,
      handler: (event?: Event) => {
        if (event) openContextMenu(event)
      },
    },
  ]
})

// Resolve built-in component by type
const getInputComponent = (type: string): Component => {
  let base: Component
  switch (type) {
    case 'text':
      base = TextField
      break
    case 'email':
      base = EmailField
      break
    case 'number':
      base = NumericField
      break
    case 'textarea':
      base = ParagraphField
      break
    case 'phone':
      base = PhoneField
      break
    case 'website':
      base = WebsiteField
      break
    case 'name':
      base = NameField
      break
    case 'address':
      base = AddressField
      break
    case 'radio':
      base = RadioField
      break
    case 'checkbox':
      base = CheckboxField
      break
    case 'select':
    case 'multi-select':
      base = SelectField
      break
    case 'time':
      base = TimeField
      break
    case 'date':
      base = DateField
      break
    case 'rating':
      base = RatingField
      break
    case 'recaptcha':
      base = RecaptchaField
      break
    case 'turnstile':
      base = TurnstileField
      break
    case 'hcaptcha':
      base = HCaptchaField
      break
    default:
      base = RequirePro
      break
  }
  try {
    const filtered = api.hooks.applyFilters('ivyforms/field/filter/component', base, {
      type,
    }) as Component
    return filtered || base
  } catch {
    return base
  }
}
</script>

<style scoped lang="scss">
.ivyforms-field-unit {
  position: relative;
  border-radius: 8px;
  border: 1px dashed transparent;
  transition: all 0.2s ease;

  &--hovered {
    border-radius: 8px;
    border: 1px dashed var(--map-base-dusk-stroke-0);
    padding-left: 14px;
    padding-right: 14px;
  }

  &--selected {
    border-radius: 8px;
    border: 1px dashed var(--map-base-purple-stroke-0);
    padding-left: 14px;
    padding-right: 14px;

    .ivyforms-field-unit__action-buttons {
      position: absolute;
      right: -1px;
      top: -20px;
      cursor: default !important;
      border-radius: 8px;
      background:
        linear-gradient(0deg, var(--map-base-purple-o10) 0%, var(--map-base-purple-o10) 100%),
        var(--map-ground-level-1-foreground);

      &.hidden {
        display: none;
      }

      button {
        background: none;
        color: var(--map-base-purple-symbol-0);
        border: none;
        cursor: pointer;
        // Add icon styles here
      }
    }
  }

  .ivyforms-form-item {
    margin-bottom: 0;
    :deep(.ivyforms-form-item__label) {
      display: flex;
      align-items: center;
      color: var(--map-base-text-0);
      /* Medium/Medium 14 */
      font-size: 14px;
      font-style: normal;
      font-weight: 500;
      line-height: 20px; /* 142.857% */
    }
    .ivyforms-input.el-input {
      :deep(.el-input__wrapper:hover),
      :deep(.el-input__wrapper.is-focus),
      :deep(.el-input__wrapper) {
        border-radius: var(--Radius-radius-md, 8px);
        border: 1px solid var(--map-base-dusk-stroke--2);
        background: transparent;
        box-shadow: none;
        padding: 0 12px;
        transition: none;
        cursor: default;
      }
      :deep(input) {
        border: none;
        background: transparent;
        cursor: default;
        box-shadow: none;

        &:focus {
          outline: none;
          border: 1px solid transparent; /* Ensure border does not change */
          background: none; /* Ensure background does not change */
          box-shadow: none;
        }
      }
    }
  }
  :deep(.ivyforms-field-unit__action-buttons) {
    position: absolute;
    right: -1px;
    top: -16px;
    display: flex;
    gap: 4px;
    cursor: default !important;
    border-radius: 8px;
    background:
      linear-gradient(0deg, var(--map-base-purple-o10) 0%, var(--map-base-purple-o10) 100%),
      var(--map-ground-level-1-foreground);

    &.hidden {
      display: none;
    }

    button {
      background: none;
      border: none;
      cursor: pointer;

      .ivyforms-icon__svg.name-move {
        svg {
          path {
            fill: var(--map-base-purple-symbol-0) !important;
            stroke: var(--map-base-purple-symbol-0) !important;
          }
        }
      }

      svg {
        fill: var(--map-base-purple-symbol-0) !important;
        stroke: var(--map-base-purple-symbol-0);

        &:hover {
          fill: var(--map-base-purple-symbol-0);
          stroke: var(--map-base-purple-symbol-0);
        }
      }
    }
  }

  &__drag-item {
    cursor: grab !important;
    pointer-events: none;

    &:active {
      cursor: grabbing !important;
    }
  }

  &__drag-handle-wrapper {
    cursor: grab !important;

    &:active {
      cursor: grabbing !important;
    }
  }

  // Form builder specific label truncation
  :deep(.el-form-item--label-top .el-form-item__label) {
    width: 100%;
  }

  :deep(.ivyforms-form-item__left-label) {
    max-width: 100%;
    min-width: 0;
    width: fit-content;

    .ivyforms-form-item__label {
      flex: 1;
      min-width: 0;

      span {
        display: block;
        word-wrap: break-word;
        word-break: break-word;
        white-space: normal;
        overflow-wrap: break-word;
      }
    }

    .ivyforms-form-item__asterisk {
      flex-shrink: 0;
      margin-left: 2px;
    }
  }
}
</style>
