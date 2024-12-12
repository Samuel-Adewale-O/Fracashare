<template>
  <div>
    <label for="bvn" class="form-label">Bank Verification Number (BVN)</label>
    <input
      id="bvn"
      type="text"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      pattern="\d{11}"
      maxlength="11"
      class="form-input"
      :class="{ 'border-red-300': error }"
      :disabled="disabled"
      placeholder="Enter your 11-digit BVN"
    />
    <p class="mt-2 text-sm text-gray-500">
      Your 11-digit BVN number can be found by dialing *565*0#
    </p>
    <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script>
export default {
  name: 'KycBvnForm',

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