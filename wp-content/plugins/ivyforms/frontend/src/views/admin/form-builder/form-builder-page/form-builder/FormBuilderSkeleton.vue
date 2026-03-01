<template>
  <div
    class="ivyforms-form-builder-skeleton ivyforms-flex ivyforms-flex-direction-column ivyforms-p-20"
  >
    <!-- Row 1: 1 field -->
    <div class="ivyforms-form-builder-skeleton__row ivyforms-flex ivyforms-gap-16">
      <div
        class="ivyforms-form-builder-skeleton__field ivyforms-flex-1 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-6"
      >
        <div class="ivyforms-form-builder-skeleton__label"></div>
        <div class="ivyforms-form-builder-skeleton__input"></div>
      </div>
    </div>

    <!-- Row 2: 2 fields -->
    <div class="ivyforms-form-builder-skeleton__row ivyforms-flex ivyforms-gap-16">
      <div
        class="ivyforms-form-builder-skeleton__field ivyforms-flex-1 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-6"
      >
        <div class="ivyforms-form-builder-skeleton__label"></div>
        <div class="ivyforms-form-builder-skeleton__input"></div>
      </div>
      <div
        class="ivyforms-form-builder-skeleton__field ivyforms-flex-1 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-6"
      >
        <div class="ivyforms-form-builder-skeleton__label"></div>
        <div class="ivyforms-form-builder-skeleton__input"></div>
      </div>
    </div>

    <!-- Row 3: 3 fields -->
    <div class="ivyforms-form-builder-skeleton__row ivyforms-flex ivyforms-gap-16">
      <div
        class="ivyforms-form-builder-skeleton__field ivyforms-flex-1 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-6"
      >
        <div class="ivyforms-form-builder-skeleton__label"></div>
        <div class="ivyforms-form-builder-skeleton__input"></div>
      </div>
      <div
        class="ivyforms-form-builder-skeleton__field ivyforms-flex-1 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-6"
      >
        <div class="ivyforms-form-builder-skeleton__label"></div>
        <div class="ivyforms-form-builder-skeleton__input"></div>
      </div>
      <div
        class="ivyforms-form-builder-skeleton__field ivyforms-flex-1 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-6"
      >
        <div class="ivyforms-form-builder-skeleton__label"></div>
        <div class="ivyforms-form-builder-skeleton__input"></div>
      </div>
    </div>

    <!-- Row 4: 4 fields -->
    <div class="ivyforms-form-builder-skeleton__row ivyforms-flex ivyforms-gap-16">
      <div
        class="ivyforms-form-builder-skeleton__field ivyforms-flex-1 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-6"
      >
        <div class="ivyforms-form-builder-skeleton__label"></div>
        <div class="ivyforms-form-builder-skeleton__input"></div>
      </div>
      <div
        class="ivyforms-form-builder-skeleton__field ivyforms-flex-1 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-6"
      >
        <div class="ivyforms-form-builder-skeleton__label"></div>
        <div class="ivyforms-form-builder-skeleton__input"></div>
      </div>
      <div
        class="ivyforms-form-builder-skeleton__field ivyforms-flex-1 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-6"
      >
        <div class="ivyforms-form-builder-skeleton__label"></div>
        <div class="ivyforms-form-builder-skeleton__input"></div>
      </div>
      <div
        class="ivyforms-form-builder-skeleton__field ivyforms-flex-1 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-6"
      >
        <div class="ivyforms-form-builder-skeleton__label"></div>
        <div class="ivyforms-form-builder-skeleton__input"></div>
      </div>
    </div>

    <!-- Row 5: 5 fields -->
    <div class="ivyforms-form-builder-skeleton__row ivyforms-flex ivyforms-gap-16">
      <div
        class="ivyforms-form-builder-skeleton__field ivyforms-flex-1 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-6"
      >
        <div class="ivyforms-form-builder-skeleton__label"></div>
        <div class="ivyforms-form-builder-skeleton__input"></div>
      </div>
      <div
        class="ivyforms-form-builder-skeleton__field ivyforms-flex-1 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-6"
      >
        <div class="ivyforms-form-builder-skeleton__label"></div>
        <div class="ivyforms-form-builder-skeleton__input"></div>
      </div>
      <div
        class="ivyforms-form-builder-skeleton__field ivyforms-flex-1 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-6"
      >
        <div class="ivyforms-form-builder-skeleton__label"></div>
        <div class="ivyforms-form-builder-skeleton__input"></div>
      </div>
      <div
        class="ivyforms-form-builder-skeleton__field ivyforms-flex-1 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-6"
      >
        <div class="ivyforms-form-builder-skeleton__label"></div>
        <div class="ivyforms-form-builder-skeleton__input"></div>
      </div>
      <div
        class="ivyforms-form-builder-skeleton__field ivyforms-flex-1 ivyforms-flex ivyforms-flex-direction-column ivyforms-gap-6"
      >
        <div class="ivyforms-form-builder-skeleton__label"></div>
        <div class="ivyforms-form-builder-skeleton__input"></div>
      </div>
    </div>

    <!-- Submit Button -->
    <div class="ivyforms-form-builder-skeleton__submit-wrapper ivyforms-pl-20">
      <div class="ivyforms-form-builder-skeleton__submit"></div>
    </div>
  </div>
</template>

<script setup lang="ts"></script>

<style scoped lang="scss">
.ivyforms-form-builder-skeleton {
  gap: 52px;
  &__label {
    width: 30%;
    height: 20px;
    background-color: var(--map-skeleton);
    border-radius: 4px;
    animation: pulse 1.5s ease-in-out infinite;
  }

  &__input {
    height: 44px;
    background-color: var(--map-skeleton);
    border-radius: 4px;
    animation: pulse 1.5s ease-in-out infinite;
  }

  &__submit {
    width: 120px;
    height: 40px;
    background-color: var(--map-skeleton);
    border-radius: 4px;
    animation: pulse 1.5s ease-in-out infinite;
  }

  @keyframes pulse {
    0%,
    100% {
      opacity: 1;
    }
    50% {
      opacity: 0.5;
    }
  }
}
</style>
