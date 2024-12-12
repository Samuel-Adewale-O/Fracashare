<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Verify your email
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          {{ message }}
        </p>
      </div>

      <div v-if="loading" class="flex justify-center">
        <svg class="animate-spin h-10 w-10 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <div v-if="error" class="text-center text-sm text-red-600">
        {{ error }}
      </div>

      <div v-if="!loading && !verified" class="text-center">
        <button
          @click="resendVerification"
          :disabled="resending"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          {{ resending ? 'Sending...' : 'Resend verification email' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useStore } from 'vuex'
import { useRouter, useRoute } from 'vue-router'

export default {
  name: 'VerifyEmail',

  setup() {
    const store = useStore()
    const router = useRouter()
    const route = useRoute()
    const loading = ref(false)
    const resending = ref(false)
    const error = ref('')
    const verified = ref(false)
    const message = ref('Please verify your email address to continue.')

    const verifyEmail = async () => {
      if (!route.params.token) return

      loading.value = true
      error.value = ''

      try {
        await store.dispatch('auth/verifyEmail', route.params.token)
        verified.value = true
        message.value = 'Email verified successfully! Redirecting...'
        setTimeout(() => router.push('/dashboard'), 2000)
      } catch (err) {
        error.value = err.response?.data?.message || 'Verification failed'
      } finally {
        loading.value = false
      }
    }

    const resendVerification = async () => {
      resending.value = true
      error.value = ''

      try {
        await store.dispatch('auth/resendVerification')
        message.value = 'A new verification link has been sent to your email address.'
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to resend verification email'
      } finally {
        resending.value = false
      }
    }

    onMounted(() => {
      if (route.params.token) {
        verifyEmail()
      }
    })

    return {
      loading,
      resending,
      error,
      verified,
      message,
      resendVerification
    }
  }
}
</script>