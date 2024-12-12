<template>
  <div class="mt-4 border-b border-gray-200">
    <nav class="-mb-px flex space-x-8" aria-label="Verification Methods">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="$emit('update:modelValue', tab.id)"
        :class="[
          tab.id === modelValue
            ? 'border-primary-500 text-primary-600'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
          'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
        ]"
        :disabled="disabled"
      >
        {{ tab.name }}
      </button>
    </nav>
  </div>
</template>

<script>
export default {
  name: 'KycVerificationTabs',

  props: {
    modelValue: {
      type: String,
      required: true
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },

  emits: ['update:modelValue'],

  setup() {
    const tabs = [
      { id: 'bvn', name: 'BVN Verification' },
      { id: 'nin', name: 'NIN Verification' }
    ]

    return {
      tabs
    }
  }
}
</script>