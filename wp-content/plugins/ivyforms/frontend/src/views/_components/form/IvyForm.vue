<template>
  <ElForm ref="elForm" class="ivyforms-form" v-bind="{ ...$props, ...$attrs }" label-position="top">
    <slot />
  </ElForm>
</template>

<script setup lang="ts">
import { ElForm } from 'element-plus'
import type { FormInstance, FormValidateCallback } from 'element-plus'
import { ref } from 'vue'

const elForm = ref<FormInstance>()

const resetFields = () => {
  elForm.value?.resetFields()
}

const clearValidate = (props = undefined) => {
  elForm.value?.clearValidate(props)
}

const validateField = async (field: string, callback?: FormValidateCallback | undefined) => {
  return await elForm.value?.validateField(field, callback)
}

const validate = async (callback?: FormValidateCallback | undefined) => {
  return await elForm.value?.validate(callback)
}

const scrollToField = (field: string) => {
  elForm.value?.scrollToField(field)
}

defineExpose({
  resetFields,
  clearValidate,
  validateField,
  validate,
  scrollToField,
})
</script>
