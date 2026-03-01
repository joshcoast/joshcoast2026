<template>
  <IvyFormItem
    :error="props.comingSoonPosition === 'bottom'"
    :coming-soon-position="props.comingSoonPosition"
    :class="[
      'ivyforms-coming-soon__label-' + props.comingSoonPosition,
      'ivyforms-coming-soon-input',
    ]"
  >
    <template #label>
      <div v-if="props.comingSoonPosition === 'left'">
        <span>Label</span>
        <ComingSoonBadge
          v-if="props.comingSoonPosition === 'left'"
          :image="'coming-soon-arrow'"
          :size="'s'"
          :coming-soon-position="props.comingSoonPosition"
        />
      </div>
      <div v-else-if="props.comingSoonPosition === 'right'">
        <ComingSoonBadge
          v-if="props.comingSoonPosition === 'right'"
          :coming-soon-position="props.comingSoonPosition"
        />
      </div>
    </template>
    <template #default>
      <IvyTextInput v-model="inputValue" disabled />
    </template>
    <template #error>
      <div v-if="props.comingSoonPosition === 'bottom'" class="ivyforms-coming-soon__input-badge">
        <ComingSoonBadge
          v-if="props.comingSoonPosition === 'bottom'"
          image="coming-soon-arrow"
          size="s"
        />
        <p>Coming Soon</p>
      </div>
    </template>
  </IvyFormItem>
</template>

<script setup lang="ts">
import { ref } from 'vue'
interface Props {
  comingSoonPosition?: 'left' | 'right' | 'bottom'
}

const props = withDefaults(defineProps<Props>(), {
  comingSoonPosition: 'right',
})

const inputValue = ref('')
</script>

<style lang="scss">
.ivyforms-form-item {
  &.ivyforms-coming-soon-input {
    .ivyforms-coming-soon__input-badge {
      display: flex;
      padding: var(--Spacing-2xs, 4px) var(--Spacing-sm, 8px);
      align-items: center;
      gap: var(--Spacing-2xs, 4px);
      align-self: stretch;
      border-radius: var(--Radius-radius-md, 8px);
      background: var(--map-accent-maroon-fill--4);
      max-height: 28px;
      // TODO Define the color to use here
      color: var(--map-accent-maroon-symbol-0);
      //color: var(--map-accent-maroon-symbol-1);

      p {
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 20px;
        margin: 0;
      }
    }

    &.ivyforms-coming-soon__label-right {
      label {
        flex-direction: row-reverse;
      }
    }

    .el-form-item__label {
      div {
        display: flex;
        align-items: center;
      }
    }

    // Hide the error message when the coming soon badge is displayed
    .el-form-item__error {
      display: none !important;
    }

    // Remove border for error coming soon input
    .el-input {
      .el-input__wrapper {
        border: none !important;
      }
    }
  }
}
</style>
