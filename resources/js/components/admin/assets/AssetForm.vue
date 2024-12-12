<template>
  <div class="bg-white shadow-sm rounded-lg p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-6">
      {{ isEditing ? 'Edit Asset' : 'Create New Asset' }}
    </h3>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label for="name" class="form-label">Asset Name</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="form-input"
          />
        </div>

        <div>
          <label for="type" class="form-label">Asset Type</label>
          <select
            id="type"
            v-model="form.type"
            required
            class="form-input"
          >
            <option value="real_estate">Real Estate</option>
            <option value="stocks">Stocks</option>
          </select>
        </div>

        <div>
          <label for="total_value" class="form-label">Total Value (NGN)</label>
          <input
            id="total_value"
            v-model.number="form.total_value"
            type="number"
            min="0"
            step="0.01"
            required
            class="form-input"
          />
        </div>

        <div>
          <label for="minimum_investment" class="form-label">Minimum Investment (NGN)</label>
          <input
            id="minimum_investment"
            v-model.number="form.minimum_investment"
            type="number"
            min="0"
            step="0.01"
            required
            class="form-input"
          />
        </div>

        <div>
          <label for="total_shares" class="form-label">Total Shares</label>
          <input
            id="total_shares"
            v-model.number="form.total_shares"
            type="number"
            min="1"
            required
            class="form-input"
          />
        </div>

        <div>
          <label for="share_price" class="form-label">Share Price (NGN)</label>
          <input
            id="share_price"
            v-model.number="form.share_price"
            type="number"
            min="0"
            step="0.01"
            required
            class="form-input"
          />
        </div>

        <div>
          <label for="expected_roi" class="form-label">Expected ROI (%)</label>
          <input
            id="expected_roi"
            v-model.number="form.expected_roi"
            type="number"
            min="0"
            step="0.01"
            required
            class="form-input"
          />
        </div>

        <div>
          <label for="risk_level" class="form-label">Risk Level</label>
          <select
            id="risk_level"
            v-model="form.risk_level"
            required
            class="form-input"
          >
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
          </select>
        </div>
      </div>

      <div>
        <label for="description" class="form-label">Description</label>
        <textarea
          id="description"
          v-model="form.description"
          rows="4"
          required
          class="form-input"
        ></textarea>
      </div>

      <div class="flex justify-end space-x-4">
        <button
          type="button"
          class="btn-secondary"
          @click="$emit('cancel')"
        >
          Cancel
        </button>
        <button
          type="submit"
          class="btn-primary"
          :disabled="loading"
        >
          {{ loading ? 'Saving...' : (isEditing ? 'Update Asset' : 'Create Asset') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'AssetForm',

  props: {
    asset: {
      type: Object,
      default: null
    }
  },

  setup(props, { emit }) {
    const store = useStore()
    const loading = ref(false)

    const isEditing = computed(() => !!props.asset)

    const form = ref({
      name: props.asset?.name ?? '',
      type: props.asset?.type ?? 'real_estate',
      description: props.asset?.description ?? '',
      total_value: props.asset?.total_value ?? 0,
      minimum_investment: props.asset?.minimum_investment ?? 0,
      total_shares: props.asset?.total_shares ?? 1,
      share_price: props.asset?.share_price ?? 0,
      expected_roi: props.asset?.expected_roi ?? 0,
      risk_level: props.asset?.risk_level ?? 'medium'
    })

    const handleSubmit = async () => {
      loading.value = true
      try {
        if (isEditing.value) {
          await store.dispatch('assets/updateAsset', {
            id: props.asset.id,
            ...form.value
          })
          emit('asset-updated')
        } else {
          await store.dispatch('assets/createAsset', form.value)
          emit('asset-created')
        }
      } catch (error) {
        console.error('Failed to save asset:', error)
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      isEditing,
      handleSubmit
    }
  }
}
</script>