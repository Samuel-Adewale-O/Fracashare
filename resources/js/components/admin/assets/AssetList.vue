<template>
  <div class="bg-white shadow-sm rounded-lg">
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Asset Management</h2>
        <button
          @click="$emit('create-asset')"
          class="btn-primary flex items-center"
        >
          <PlusIcon class="h-5 w-5 mr-2" />
          Add New Asset
        </button>
      </div>

      <!-- Filters -->
      <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <select v-model="filters.type" class="form-input">
          <option value="">All Types</option>
          <option value="real_estate">Real Estate</option>
          <option value="stocks">Stocks</option>
        </select>

        <select v-model="filters.status" class="form-input">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="draft">Draft</option>
          <option value="closed">Closed</option>
        </select>

        <select v-model="filters.risk_level" class="form-input">
          <option value="">All Risk Levels</option>
          <option value="low">Low Risk</option>
          <option value="medium">Medium Risk</option>
          <option value="high">High Risk</option>
        </select>
      </div>

      <!-- Asset Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Asset Name
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Type
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Value
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="asset in filteredAssets" :key="asset.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ asset.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <AssetTypeBadge :type="asset.type" />
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">₦{{ formatMoney(asset.total_value) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <StatusBadge :status="asset.status" />
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex space-x-2">
                  <button
                    @click="$emit('edit-asset', asset)"
                    class="text-primary-600 hover:text-primary-900"
                  >
                    Edit
                  </button>
                  <button
                    @click="$emit('view-analytics', asset)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Analytics
                  </button>
                  <button
                    v-if="asset.status !== 'closed'"
                    @click="$emit('toggle-status', asset)"
                    class="text-red-600 hover:text-red-900"
                  >
                    {{ asset.status === 'active' ? 'Deactivate' : 'Activate' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-4">
        <Pagination
          :total="totalAssets"
          :per-page="perPage"
          :current-page="currentPage"
          @page-changed="handlePageChange"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useStore } from 'vuex'
import { PlusIcon } from '@heroicons/vue/24/outline'
import AssetTypeBadge from './AssetTypeBadge.vue'
import StatusBadge from '../common/StatusBadge.vue'
import Pagination from '../common/Pagination.vue'
import { formatMoney } from '@/utils/currency'

export default {
  name: 'AssetList',
  
  components: {
    PlusIcon,
    AssetTypeBadge,
    StatusBadge,
    Pagination
  },

  setup() {
    const store = useStore()
    const filters = ref({
      type: '',
      status: '',
      risk_level: ''
    })
    const currentPage = ref(1)
    const perPage = ref(10)

    const filteredAssets = computed(() => {
      return store.getters['assets/filteredAssets'](filters.value)
    })

    const totalAssets = computed(() => filteredAssets.value.length)

    const handlePageChange = (page) => {
      currentPage.value = page
    }

    return {
      filters,
      currentPage,
      perPage,
      filteredAssets,
      totalAssets,
      handlePageChange,
      formatMoney
    }
  }
}
</script>