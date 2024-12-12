<template>
  <span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    :class="statusClasses"
  >
    {{ statusLabel }}
  </span>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'StatusBadge',

  props: {
    status: {
      type: String,
      required: true
    }
  },

  setup(props) {
    const statusClasses = computed(() => ({
      'bg-green-100 text-green-800': props.status === 'active',
      'bg-yellow-100 text-yellow-800': props.status === 'draft',
      'bg-red-100 text-red-800': props.status === 'closed'
    }))

    const statusLabel = computed(() => ({
      active: 'Active',
      draft: 'Draft',
      closed: 'Closed'
    }[props.status] || props.status))

    return {
      statusClasses,
      statusLabel
    }
  }
}
</script>