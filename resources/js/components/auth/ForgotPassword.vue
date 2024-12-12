<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Reset your password
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Enter your email address and we'll send you a link to reset your password.
        </p>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div>
          <label for="email" class="sr-only">Email address</label>
          <input
            id="email"
            v-model="email"
            type="email"
            required
            class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
            placeholder="Email address"
          />
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <MailIcon class="h-5 w-5 text-primary-500 group-hover:text-primary-400" aria-hidden="true" />
            </span>
            {{ loading ? 'Sending...' : 'Send Reset Link' }}
          </button>
        </div>

        <div v-if="error" class="mt-2 text-sm text-red-600">
          {{ error }}
        </div>

        <div v-if="success" class="mt-2 text-sm text-green-600">
          {{ success }}
        </div>
      </form>

      <div class="text-sm text-center">
        <router-link to="/login" class="font-medium text-primary-600 hover:text-primary-500">
          Back to login
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useStore } from 'vuex'
import { MailIcon } from '@heroicons/vue/solid'

export default {
  name: 'ForgotPassword',
  
  components: {
    MailIcon
  },

  setup() {
    const store = useStore()
    const email = ref('')
    const loading = ref(false)
    const error = ref('')
    const success = ref('')

    const handleSubmit = async () => {
      loading.value = true
      error.value = ''
      success.value = ''

      try {
        await store.dispatch('auth/sendPasswordResetLink', email.value)
        success.value = 'Password reset link has been sent to your email'
        email.value = ''
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to send reset link'
      } finally {
        loading.value = false
      }
    }

    return {
      email,
      loading,
      error,
      success,
      handleSubmit
    }
  }
}
</script>