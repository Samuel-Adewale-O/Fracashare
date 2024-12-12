<template>
  <div
    v-if="show"
    :class="[
      'mt-4 rounded-md p-4',
      type === 'success' ? 'bg-green-50' : 'bg-red-50'
    ]"
  >
    <div class="flex">
      <div class="flex-shrink-0">
        <CheckCircleIcon
          v-if="type === 'success'"
          class="h-5 w-5 text-green-400"
          aria-hidden="true"
        />
        <XCircleIcon
          v-else
          class="h-5 w-5 text-red-400"
          aria-hidden="true"
        />
      </div>
      <div class="ml-3">
        <p
          :class="[
            'text-sm font-medium',
            type === 'success' ? 'text-green-800' : 'text-red-800'
          ]"
        >
          {{ message }}
        </p>
      </div>
      <div class="ml-auto pl-3">
        <div class="-mx-1.5 -my-1.5">
          <button
            type="button"
            :class="[
              'inline-flex rounded-md p-1.5',
              type === 'success'
                ? 'bg-green-50 text-green-500 hover:bg-green-100'
                : 'bg-red-50 text-red-500 hover:bg-red-100'
            ]"
            @click="$emit('close')"
          >
            <span class="sr-only">Dismiss</span>
            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { CheckCircleIcon, XCircleIcon, XMarkIcon } from '@heroicons/vue/20/solid'

export default {
  name: 'KycVerificationAlert',

  components: {
    CheckCircleIcon,
    XCircleIcon,
    XMarkIcon
  },

  props: {
    show: {
      type: Boolean,
      required: true
    },
    type: {
      type: String,
      required: true,
      validator: value => ['success', 'error'].includes(value)
    },
    message: {
      type: String,
      required: true
    }
  },

  emits: ['close']
}
</script>