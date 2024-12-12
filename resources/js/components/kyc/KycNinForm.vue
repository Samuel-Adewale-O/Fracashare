<template>
  <div>
    <label for="nin" class="form-label">National Identification Number (NIN)</label>
    <input
      id="nin"
      type="text"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      pattern="\d{11}"
      maxlength="11"
      class="form-input"
      :class="{ 'border-red-300': error }"
      :disabled="disabled"
      placeholder="Enter your 11-digit NIN"
    />
    <p class="mt-2 text-sm text-gray-500">
      Your 11-digit NIN can be found on your National ID card or by dialing *346#
    </p>
    <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script>
export default {
  name: 'KycNinForm',

  props: {
    modelValue: {
      type: String,
      required: true
    },
    error: {
      type: String,
      default: ''
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },

  emits: ['update:modelValue']
}
</script>