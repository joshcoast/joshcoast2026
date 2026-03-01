<template>
  <div class="ivyforms-all-forms__header">
    <IvyCheckbox
      type="checkmark"
      indeterminate
      size="s"
      :model-value="rowSelected"
      class="ivyforms-ml-12"
      @update:model-value="toggleSelectRows"
    >
      <slot></slot>
    </IvyCheckbox>
    <IvyButtonAction priority="danger" size="xs" @click="showDeleteDialog">
      {{ getLabel('delete') }}
    </IvyButtonAction>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import IvyButtonAction from '@/views/_components/button/IvyButtonAction.vue'
import IvyCheckbox from '@/views/_components/checkbox/IvyCheckbox.vue'
import { useActionEntityStore } from '@/stores/actionEntityStore'
import { useLabels } from '@/composables/useLabels.ts'

const actionEntityStore = useActionEntityStore()
const { getLabel } = useLabels()

const props = defineProps<{
  selectedIds: number[]
  loading?: boolean
}>()

const emit = defineEmits(['unselect-all', 'reload', 'update:loading'])

const loadingState = ref(false)

const showDeleteDialog = () => {
  if (props.selectedIds.length > 0) {
    actionEntityStore.handleActionClick(null, props.selectedIds, 'forms', 'delete', {
      setLoading: (isLoading) => {
        loadingState.value = isLoading
        emit('update:loading', isLoading)
      },
    })
  }
}

const rowSelected = computed(() => props.selectedIds.length > 0)

const toggleSelectRows = (value: boolean) => {
  if (!value) emit('unselect-all')
}
</script>

<style lang="scss">
.ivyforms-all-forms__header {
  display: flex;
  height: 48px;
  padding: 0 16px 0 8px;
  align-items: center;
  align-self: stretch;
  background: var(--map-ground-level-1-foreground);
}

.ivyforms-dialog {
  &.el-dialog {
    .el-dialog__body {
      display: none;
    }
  }
}
</style>
