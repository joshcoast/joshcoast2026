<template>
  <div class="ivyforms-field__require-pro">
    <IvyFormItem>
      <div class="ivyforms-field__require-pro-warning">
        <IvyNotification
          state="warning"
          :description="getLabel('require_pro_plan')"
          :show-link="false"
          :show-button="true"
          :button-label="getLabel('upgrade')"
          @button-click="Upgrade()"
        />
      </div>
    </IvyFormItem>
  </div>
</template>
<script setup lang="ts">
import { useLabels } from '@/composables/useLabels'
import { openPricingPage } from '@/composables/useProFeatureUpgrade'

const { getLabel } = useLabels()

const Upgrade = () => {
  return openPricingPage()
}
</script>
