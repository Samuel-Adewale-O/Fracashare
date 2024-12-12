<template>
  <div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow sm:rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
          Complete KYC Verification
        </h3>
        <div class="mt-2 max-w-xl text-sm text-gray-500">
          <p>Please provide either your BVN or NIN to complete the verification process.</p>
        </div>
        
        <KycVerificationTabs
          v-model="activeTab"
          :disabled="loading"
        />

        <form class="mt-5 space-y-6" @submit.prevent="handleSubmit">
          <KycBvnForm
            v-if="activeTab === 'bvn'"
            v-model="form.bvn"
            :error="errors.bvn"
            :disabled="loading"
          />

          <KycNinForm
            v-else
            v-model="form.nin"
            :error="errors.nin"
            :disabled="loading"
          />

          <div class="flex justify-end">
            <button
              type="submit"
              class="btn-primary"
              :disabled="loading || !isValid"
            >
              {{ loading ? 'Verifying...' : 'Verify Identity' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <KycVerificationAlert
      v-if="alert.show"
      :type="alert.type"
      :message="alert.message"
      @close="alert.show = false"
    />
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import KycVerificationTabs from './KycVerificationTabs.vue'
import KycBvnForm from './KycBvnForm.vue'
import KycNinForm from './KycNinForm.vue'
import KycVerificationAlert from './KycVerificationAlert.vue'
import { validateBVN, validateNIN } from '@/utils/validators/KycValidator'

export default {
  name: 'KycVerificationForm',
  
  components: {
    KycVerificationTabs,
    KycBvnForm,
    KycNinForm,
    KycVerificationAlert
  },

  setup() {
    const store = useStore()
    const router = useRouter()
    const loading = ref(false)
    const activeTab = ref('bvn')
    const errors = ref({})
    const alert = ref({
      show: false,
      type: 'error',
      message: ''
    })

    const form = ref({
      bvn: '',
      nin: ''
    })

    const isValid = computed(() => {
      if (activeTab.value === 'bvn') {
        return validateBVN(form.value.bvn).isValid
      }
      return validateNIN(form.value.nin).isValid
    })

    const showAlert = (type, message) => {
      alert.value = {
        show: true,
        type,
        message
      }
    }

    const handleSubmit = async () => {
      loading.value = true
      errors.value = {}
      
      try {
        const data = {
          [activeTab.value]: form.value[activeTab.value]
        }
        
        const response = await store.dispatch('auth/verifyKyc', data)
        
        if (response.data.status === 'success') {
          showAlert('success', 'Verification successful')
          setTimeout(() => router.push('/dashboard'), 2000)
        }
      } catch (err) {
        const message = err.response?.data?.message || 'Verification failed. Please try again.'
        showAlert('error', message)
        
        if (err.response?.data?.errors) {
          errors.value = err.response.data.errors
        }
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      errors,
      alert,
      activeTab,
      isValid,
      handleSubmit
    }
  }
}
</script>