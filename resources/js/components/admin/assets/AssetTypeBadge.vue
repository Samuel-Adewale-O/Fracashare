<template>
  <span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    :class="typeClasses"
  >
    {{ typeLabel }}
  </span>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'AssetTypeBadge',

  props: {
    type: {
      type: String,
      required: true
    }
  },

  setup(props) {
    const typeClasses = computed(() => ({
      'bg-blue-100 text-blue-800': props.type === 'real_estate',
      'bg-green-100 text-green-800': props.type === 'stocks'
    }))

    const typeLabel = computed(() => ({
      real_estate: 'Real Estate',
      stocks: 'Stocks'
    }[props.type] || props.type))

    return {
      typeClasses,
      typeLabel
    }
  }
}
</script>