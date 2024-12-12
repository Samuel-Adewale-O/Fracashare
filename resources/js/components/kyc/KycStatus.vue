<template>
  <div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <div class="sm:flex sm:items-start sm:justify-between">
        <div>
          <h3 class="text-lg leading-6 font-medium text-gray-900">
            KYC Verification Status
          </h3>
          <div class="mt-2 max-w-xl text-sm text-gray-500">
            <p>{{ getStatusMessage }}</p>
          </div>
        </div>
        <div class="mt-5 sm:mt-0 sm:ml-6 sm:flex-shrink-0">
          <StatusBadge :status="kycStatus" />
        </div>
      </div>

      <div v-if="kycStatus !== 'verified'" class="mt-5">
        <div class="rounded-md bg-yellow-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <ExclamationIcon class="h-5 w-5 text-yellow-400" aria-hidden="true" />
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-yellow-800">
                Verification Required
              </h3>
              <div class="mt-2 text-sm text-yellow-700">
                <p>
                  You need to complete KYC verification to access all platform features.
                  {{ attemptsRemaining }} verification attempts remaining.
                </p>
              </div>
              <div class="mt-4">
                <div class="-mx-2 -my-1.5 flex">
                  <router-link
                    to="/kyc-verification"
                    class="bg-yellow-50 px-2 py-1.5 rounded-md text-sm font-medium text-yellow-800 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-yellow-50 focus:ring-yellow-600"
                  >
                    Start Verification
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="kycStatus === 'verified'" class="mt-5">
        <div class="rounded-md bg-green-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <CheckCircleIcon class="h-5 w-5 text-green-400" aria-hidden="true" />
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-green-800">
                Your account is fully verified
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useStore } from 'vuex'
import { ExclamationIcon, CheckCircleIcon } from '@heroicons/vue/solid'
import StatusBadge from '../common/StatusBadge.vue'

export default {
  name: 'KycStatus',

  components: {
    ExclamationIcon,
    CheckCircleIcon,
    StatusBadge
  },

  setup() {
    const store = useStore()
    const kycStatus = computed(() => store.getters['auth/kycStatus'])
    const attemptsRemaining = computed(() => 3 - store.getters['auth/kycAttempts'])

    const getStatusMessage = computed(() => {
      switch (kycStatus.value) {
        case 'verified':
          return 'Your account is fully verified and you have access to all platform features.'
        case 'pending':
          return 'Your account verification is pending. Please complete the KYC process.'
        case 'failed':
          return 'Your last verification attempt failed. Please try again with correct information.'
        default:
          return 'Please verify your identity to access all platform features.'
      }
    })

    return {
      kycStatus,
      attemptsRemaining,
      getStatusMessage
    }
  }
}
</script>