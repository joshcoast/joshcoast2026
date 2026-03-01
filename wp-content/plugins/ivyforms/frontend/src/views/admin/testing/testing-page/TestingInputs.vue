<template>
  <h3>Inputs in Form Component Preview</h3>
  <div class="inputs-preview">
    <IvyForm :model="formData" :rules="rules">
      <IvyFormItem label="First name" prop="firstName" right-label="Options" secondary>
        <IvyTextInput
          v-model="formData.firstName"
          :placeholder="placeholderName"
          icon-start="user"
          clearable
        ></IvyTextInput>
      </IvyFormItem>

      <IvyFormItem label="Last name" prop="lastName">
        <IvyTextInput v-model="formData.lastName" icon-end="user"></IvyTextInput>
      </IvyFormItem>

      <IvyFormItem label="Email" prop="email">
        <IvyTextInput
          v-model="formData.email"
          placeholder="email"
          icon-start="email"
        ></IvyTextInput>
      </IvyFormItem>

      <IvyFormItem label="Job" prop="job">
        <IvySelectInput v-model="formData.job" class="am-time-select">
          <IvySelectOption
            v-for="item in items"
            :key="item.value"
            :label="item.label"
            :value="item.value"
            :disabled="item.disabled"
          >
            {{ item.label }}
          </IvySelectOption>
        </IvySelectInput>
      </IvyFormItem>

      <IvyFormItem label="Hobbies" prop="hobbies" secondary>
        <IvySelectInput v-model="formData.hobbies" multiple>
          <IvySelectOption
            v-for="item in hobbies"
            :key="item.value"
            :label="item.label"
            :value="item.value"
            :disabled="item.disabled"
          >
            {{ item.label }}
          </IvySelectOption>
        </IvySelectInput>
      </IvyFormItem>

      <IvyFormItem secondary>
        <IvyPhoneInput placeholder="Phone number" />
      </IvyFormItem>
    </IvyForm>

    <h3>Inputs Outside of a Form</h3>
    <span class="ivyforms-mt-10">Primary:</span>
    <IvyTextInput v-model="modelValuePrimary" :placeholder="placeholderName" icon-start="user" />

    <span class="ivyforms-mt-10">Secondary:</span>
    <IvyTextInput
      v-model="modelValueSecondary"
      secondary
      :placeholder="placeholderName"
      icon-start="user"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import IvyPhoneInput from '@/views/_components/input/IvyPhoneInput.vue'

const formData = ref({
  firstName: '',
  lastName: '',
  email: '',
  job: '',
  hobbies: '',
})

const items = ref([
  { value: 'Engineer', label: 'Engineer', disabled: false },
  { value: 'Manager', label: 'Manager', disabled: false },
  { value: 'Designer', label: 'Designer', disabled: false },
  { value: 'Developer', label: 'Developer', disabled: false },
  { value: 'Tester', label: 'Tester', disabled: false },
  { value: 'Support', label: 'Support', disabled: false },
  { value: 'Sales', label: 'Sales', disabled: false },
  { value: 'Marketing', label: 'Marketing', disabled: false },
  { value: 'HR', label: 'HR', disabled: false },
  { value: 'Finance', label: 'Finance', disabled: false },
  { value: 'Admin', label: 'Admin', disabled: false },
  { value: 'Other', label: 'Other', disabled: true },
])

const hobbies = ref([
  { value: 'Reading', label: 'Reading', disabled: false },
  { value: 'Writing', label: 'Writing', disabled: false },
  { value: 'Drawing', label: 'Drawing', disabled: false },
  { value: 'Singing', label: 'Singing', disabled: false },
  { value: 'Dancing', label: 'Dancing', disabled: false },
  { value: 'Cooking', label: 'Cooking', disabled: false },
  { value: 'Gardening', label: 'Gardening', disabled: false },
  { value: 'Traveling', label: 'Traveling', disabled: false },
  { value: 'Photography', label: 'Photography', disabled: false },
  { value: 'Gaming', label: 'Gaming', disabled: false },
  { value: 'Exercising', label: 'Exercising', disabled: false },
  { value: 'Other', label: 'Other', disabled: true },
])

const rules = ref({
  firstName: [{ required: true, message: 'Please enter first name.', trigger: 'blur' }],
  lastName: [{ required: true, message: 'Please enter last name.', trigger: 'blur' }],
  email: [
    { type: 'email', message: 'Please input correct email address', trigger: ['blur', 'change'] },
  ],
  job: [{ required: true, message: 'Please select a job.', trigger: 'change' }],
})

const placeholderName = 'Your name'
const modelValuePrimary = ref('')
const modelValueSecondary = ref('')
</script>

<style lang="scss">
.inputs-preview {
  width: 300px;
  align-items: center;

  .ivyforms-mt-10 {
    display: flex;
    margin-top: 10px;
  }
}
</style>
