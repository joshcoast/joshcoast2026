<template>
  <ElPagination
    v-model:page-size="pageSize"
    v-model:current-page="currentPage"
    :class="[
      isMobile ? 'ivyforms-pagination-mobile' : 'ivyforms-pagination',
      { 'ivyforms-pagination-single': isSinglePage },
      { 'is-secondary': props.secondary },
    ]"
    :popper-class="props.secondary ? 'is-secondary' : ''"
    :pager-count="5"
    :page-sizes="[10, 20, 30, 40]"
    :layout="getLayout"
    :aria-label="ariaLabel"
    v-bind="{ ...$props, ...$attrs }"
  >
  </ElPagination>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { useLabels } from '@/composables/useLabels'
import utilResponsive from '@/utils/utilResponsive.ts'
import { isSinglePage as isSinglePageUtil } from '@/utils/utilPagination.ts'

const { isMobile } = utilResponsive()
const { getLabel } = useLabels()

const props = defineProps({
  total: {
    type: Number,
    default: 0,
  },
  pageSize: {
    type: Number,
    default: 10,
  },
  currentPage: {
    type: Number,
    default: 1,
  },
  background: {
    type: Boolean,
    default: false,
  },
  more: {
    type: Boolean,
    default: false,
  },
  count: {
    type: Boolean,
    default: true,
  },
  ariaLabel: {
    type: String,
    default: '',
  },
  secondary: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:pageSize', 'update:currentPage'])

const pageSize = ref(props.pageSize)
const currentPage = ref((props.currentPage as number | undefined) || 1)

watch(
  () => props.pageSize,
  (val) => {
    pageSize.value = val
  },
)
watch(pageSize, (val) => {
  emit('update:pageSize', val)
})

watch(
  () => props.currentPage as number | undefined,
  (val) => {
    if (val !== undefined) currentPage.value = val
  },
)
watch(currentPage, (val) => {
  emit('update:currentPage', val)
})

const isSinglePage = computed(() => {
  return isSinglePageUtil(props.total, pageSize.value)
})

const getLayout = computed(() => {
  if (isSinglePage.value) {
    return 'total, sizes'
  }
  return 'total, sizes, prev, pager, next, jumper'
})

// Add onMounted lifecycle hook to programmatically add aria-label to the page size selector input for accessibility
onMounted(() => {
  nextTick(() => {
    // Add aria-label to the page size selector input
    const pageSizeInput = document.querySelector('.el-pagination__sizes .el-select__input')
    if (pageSizeInput) {
      pageSizeInput.setAttribute('aria-label', getLabel('items_per_page'))
    }
  })
})
</script>

<style lang="scss">
// Pagination
.ivyforms-pagination,
.ivyforms-pagination-mobile {
  flex-wrap: wrap;
  justify-content: center;

  // Secondary variant
  &.is-secondary {
    &.el-pagination {
      // Sizes
      .el-pagination__sizes {
        .el-select {
          .el-select__wrapper {
            &.is-focused {
              border: 1px solid var(--map-base-purple-stroke-0);
            }
          }
        }
      }

      // Editor (Go To input)
      .el-pagination__editor {
        .el-input__wrapper {
          &.is-focused {
            border: 1px solid var(--map-base-purple-stroke-0);
          }
        }
      }
    }
  }

  // Element Pagination
  &.el-pagination {
    .el-icon {
      color: var(--map-base-dusk-symbol-2);
    }

    // Total & Go To
    .el-pagination__total,
    .el-pagination__goto {
      font-size: 16px;
      font-style: normal;
      font-weight: 400;
      line-height: 22px;
      color: var(--map-base-text-0);
    }

    // Single page
    &.ivyforms-pagination-single {
      justify-content: flex-start;
    }

    // Total
    .el-pagination__total {
      // Disabled
      &[disabled='true'] {
        opacity: 0.5;
      }
    }

    // Sizes
    .el-pagination__sizes {
      // Select
      .el-select {
        // Select Wrapper
        .el-select__wrapper {
          border-radius: var(--Radius-radius-md, 8px);
          border: 1px solid var(--map-base-dusk-stroke--2);
          background: var(--map-base-dusk-o05);
          box-shadow: none;

          // Hover
          &:hover {
            border: 1px solid var(--map-base-dusk-stroke-0);
            box-shadow: none;
          }

          // Focus
          &.is-focused {
            border: 1px solid var(--map-base-brand-stroke-0);
            box-shadow: none;
          }

          // Disabled
          &.is-disabled {
            cursor: not-allowed;
            opacity: 0.5;
            box-shadow: none;
          }

          // Selection
          .el-select__selection {
            // Selected Item
            .el-select__selected-item {
              color: var(--map-base-text-0);
              font-size: 16px;
              font-style: normal;
              font-weight: 400;
              line-height: 22px;
            }
          }

          // Suffix
          .el-select__suffix {
            // Arrow Icon
            i.el-select__caret {
              // Before
              &:before {
              }

              // Element Icon
              svg {
                //display: none;
              }
            }
          }

          // Input accessibility fix
          .el-select__input {
            &[aria-label=''],
            &:not([aria-label]) {
              &::before {
                content: attr(data-aria-label);
                position: absolute;
                left: -10000px;
              }
            }
          }
        }
      }
    }

    // Editor
    .el-pagination__editor {
      // Input Wrapper
      .el-input__wrapper {
        border-radius: var(--Radius-radius-md, 8px);
        border: 1px solid var(--map-base-dusk-stroke--2);
        background: var(--map-base-dusk-o05);
        box-shadow: none;

        // Hover
        &:hover {
          border: 1px solid var(--map-base-dusk-stroke-0);
          box-shadow: none;
        }

        // Focus
        &.is-focused {
          border: 1px solid var(--map-base-brand-stroke-0);
          box-shadow: none;
        }

        // Disabled
        &.is-disabled {
          cursor: not-allowed;
          opacity: 0.5;
          box-shadow: none;
        }

        // Input
        input {
          color: var(--map-base-text-0);
          -webkit-text-fill-color: unset;
          padding: 0;
          line-height: inherit;
          min-height: inherit;
          box-shadow: none;
          border-radius: 0;
          border: 0;
          background-color: unset;
        }
      }

      // Disabled
      &.is-disabled {
        // Input Wrapper
        .el-input__wrapper {
          cursor: not-allowed;
          opacity: 0.5;
        }
      }
    }

    // Jump
    .el-pagination__jump {
      flex-basis: calc(50% - 8px);
      justify-content: flex-end;
      margin-left: 0;
      margin-right: 8px !important;

      // Tablet & Up
      @include tablet-and-up {
        flex-basis: auto;
        //order: 6;
        margin-right: 0 !important;
        margin-left: 16px;
      }

      // Disabled
      &[disabled='true'] {
        // Go To
        .el-pagination__goto {
          color: var(--map-base-text--2);
        }
      }
    }

    // Previous Button, Next Button
    .btn-prev,
    .btn-next {
      height: 32px;
      width: 32px;
      padding: 0;
      border-radius: 4px;
      background: none;

      // Element Icon
      //i {
      //  display: none;
      //}

      // Before
      &:before {
      }

      // Disabled
      &:disabled {
        opacity: 0.5;

        // Before
        &:before {
          opacity: 0.5;
        }
      }

      // Hover
      &:hover:not(:disabled) {
        // Before
        &:before {
          color: var(--map-base-brand-symbol-0);
        }
      }

      // Focus Visible
      &:focus-visible {
        box-shadow: 0 0 0 4px var(--map-base-brand-o40);
        outline: none;
      }
    }

    // Previous Button
    .btn-prev {
      //order: 3;
      margin-left: 0;

      // Tablet & Up
      @include tablet-and-up {
        margin-left: auto;
      }

      // Before
      &:before {
        content: '';
        background-image: url('@/assets/icons/arrows/line/chevron-left.svg');
        background-size: contain;
      }
    }

    // Next Button
    .btn-next {
      // Before
      &:before {
        content: '';
        background-image: url('@/assets/icons/arrows/line/chevron-right.svg');
        background-size: contain;
      }
    }

    // Element Pager
    .el-pager {
      display: flex;
      gap: 2px;

      // List Item (page numbers)
      li {
        border-radius: var(--radius-l, 8px);
        display: flex;
        width: 32px;
        height: 32px;
        padding: 6px;
        justify-content: center;
        align-items: center;
        gap: var(--spacing-02, 8px);
        color: var(--map-base-text-0);
        text-align: center;
        font-size: 14px;
        font-style: normal;
        font-weight: 500;
        line-height: 20px;
        position: relative;
        background: none;
        margin-bottom: 0;

        // Active
        &.is-active:not(.is-disabled) {
          background: var(--map-base-purple-o05);
          display: flex;
          width: 32px;
          height: 32px;
          padding: 6px;
          justify-content: center;
          align-items: center;
          color: var(--map-base-purple-symbol-0);
          gap: var(--spacing-02, 8px);
        }

        // Disabled
        &.is-disabled {
          opacity: 0.5;

          // More
          &.more {
            // Before
            &:before {
              opacity: 0.5;
            }
          }
        }

        // Hover
        &:hover:not(:disabled):not(.is-active):not(.is-disabled) {
          background: var(--map-base-dusk-o10);
        }

        // More
        &.more {
          // SVG
          svg {
            //display: none;
          }

          // Before
          &:before {
            display: flex;
            justify-content: center;
            font-style: normal;
            font-size: 22px;
          }

          // Hover
          &:hover:not(.is-disabled) {
            // Previous
            &.btn-quickprev {
              // Before
              &:before {
                font-size: 18px;
              }
            }

            // Next
            &.btn-quicknext {
              // Before
              &:before {
                color: var(--map-base-brand-symbol-0);
                font-size: 18px;
              }
            }
          }
        }
      }
    }
  }
}

.el-select__popper {
  border: 1px solid var(--map-base-dusk-stroke--2) !important;
  border-radius: 0;

  .el-scrollbar {
    background-color: var(--map-ground-level-1-foreground);

    .el-select-dropdown__list {
      padding: 0;
    }
    .el-select-dropdown__item {
      color: var(--map-base-dusk-symbol-2);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin: 0;
      font-size: 14px;
      font-style: normal;
      font-weight: 400;
      line-height: 20px;

      &:first-child {
        margin-top: 8px !important;
      }

      &:last-child {
        margin-bottom: 8px !important;
      }

      &.selected,
      &.is-selected {
        background: var(--map-base-brand-o10);
        color: var(--map-base-dusk-symbol-2);
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
      }

      &:hover,
      &.is-hovering {
        color: var(--map-base-dusk-symbol-2);
        background: var(--map-hover);
      }
    }
  }

  // Secondary variant
  &.is-secondary {
    .el-scrollbar {
      .el-select-dropdown__item {
        &.selected,
        &.is-selected {
          background: var(--map-base-purple-o10) !important;
        }
      }
    }
  }
}
</style>
