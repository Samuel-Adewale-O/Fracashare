<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Reset your password
        </h2>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <input type="hidden" v-model="token" />
        
        <div>
          <label for="email" class="sr-only">Email address</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="appearance-none rounded-t-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 z-10 sm:text-sm"
            placeholder="Email address"
          />
        </div>

        <div>
          <label for="password" class="sr-only">New password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 z-10 sm:text-sm"
            placeholder="New password"
          />
        </div>

        <div>
          <label for="password_confirmation" class="sr-only">Confirm new password</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            required
            class="appearance-none rounded-b-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 z-10 sm:text-sm"
            placeholder="Confirm new password"
          />
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <LockClosedIcon class="h-5 w-5 text-primary-500 group-hover:text-primary-400" aria-hidden="true" />
            </span>
            {{ loading ? 'Resetting...' : 'Reset Password' }}
          </button>
        </div>

        <div v-if="error" class="mt-2 text-sm text-red-600">
          {{ error }}
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useStore } from 'vuex'
import { useRouter, useRoute } from 'vue-router'
import { LockClosedIcon } from '@heroicons/vue/solid'

export default {
  name: 'ResetPassword',
  
  components: {
    LockClosedIcon
  },

  setup() {
    const store = useStore()
    const router = useRouter()
    const route = useRoute()
    const loading = ref(false)
    const error = ref('')
    const token = ref(route.params.token)
    const form = ref({
      email: '',
      password: '',
      password_confirmation: '',
      token: token.value
    })

    const handleSubmit = async () => {
      loading.value = true
      error.value = ''

      try {
        await store.dispatch('auth/resetPassword', form.value)
        router.push('/login')
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to reset password'
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      token,
      loading,
      error,
      handleSubmit
    }
  }
}
</script>