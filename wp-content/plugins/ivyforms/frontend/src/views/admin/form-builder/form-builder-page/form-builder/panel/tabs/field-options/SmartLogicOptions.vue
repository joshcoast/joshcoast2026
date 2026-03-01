<template>
  <div
    class="ivyforms-smart-logic-placeholder ivyforms-flex ivyforms-align-items-center ivyforms-justify-content-center"
  >
    <PageEmptyState
      :title="getLabel('smart_logic_pro_feature')"
      :subtitle="getLabel('smart_logic_pro_description')"
      image="fields-options"
    >
      <template #actionButton>
        <IvyButtonAction
          priority="primary"
          size="d"
          icon-start="arrow-up-right"
          icon-start-category="global"
          icon-start-type="outline"
          @click="handleUpgrade"
        >
          {{ getLabel('upgrade_to_pro') }}
        </IvyButtonAction>
      </template>
    </PageEmptyState>
  </div>
</template>

<script setup lang="ts">
import type { Field } from '@/types/field'
import { useLabels } from '@/composables/useLabels'
import { openPricingPage } from '@/composables/useProFeatureUpgrade'
import PageEmptyState from '@/views/admin/parts/PageEmptyState.vue'

defineProps<{
  field: Field
}>()

const { getLabel } = useLabels()

const handleUpgrade = () => {
  // Open pricing page in new tab
  openPricingPage()
}
</script>

<style scoped lang="scss">
.ivyforms-smart-logic-placeholder {
  min-height: 300px;
}
</style>
