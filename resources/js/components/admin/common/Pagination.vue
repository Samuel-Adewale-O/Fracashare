<template>
  <div class="flex items-center justify-between">
    <div class="flex-1 flex justify-between sm:hidden">
      <button
        @click="prevPage"
        :disabled="currentPage === 1"
        class="btn-secondary"
      >
        Previous
      </button>
      <button
        @click="nextPage"
        :disabled="currentPage === totalPages"
        class="btn-secondary"
      >
        Next
      </button>
    </div>
    
    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-gray-700">
          Showing
          <span class="font-medium">{{ startIndex }}</span>
          to
          <span class="font-medium">{{ endIndex }}</span>
          of
          <span class="font-medium">{{ total }}</span>
          results
        </p>
      </div>
      
      <div>
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
          <button
            @click="prevPage"
            :disabled="currentPage === 1"
            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
          >
            Previous
          </button>
          
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="goToPage(page)"
            :class="[
              currentPage === page ? 'z-10 bg-primary-50 border-primary-500 text-primary-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
              'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
            ]"
          >
            {{ page }}
          </button>
          
          <button
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
          >
            Next
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'Pagination',

  props: {
    total: {
      type: Number,
      required: true
    },
    perPage: {
      type: Number,
      required: true
    },
    currentPage: {
      type: Number,
      required: true
    }
  },

  setup(props, { emit }) {
    const totalPages = computed(() => Math.ceil(props.total / props.perPage))
    
    const startIndex = computed(() => 
      ((props.currentPage - 1) * props.perPage) + 1
    )
    
    const endIndex = computed(() => 
      Math.min(startIndex.value + props.perPage - 1, props.total)
    )

    const visiblePages = computed(() => {
      const delta = 2
      const range = []
      const rangeWithDots = []
      let l

      for (let i = 1; i <= totalPages.value; i++) {
        if (
          i === 1 ||
          i === totalPages.value ||
          i >= props.currentPage - delta &&
          i <= props.currentPage + delta
        ) {
          range.push(i)
        }
      }

      range.forEach(i => {
        if (l) {
          if (i - l === 2) {
            rangeWithDots.push(l + 1)
          } else if (i - l !== 1) {
            rangeWithDots.push('...')
          }
        }
        rangeWithDots.push(i)
        l = i
      })

      return rangeWithDots
    })

    const prevPage = () => {
      if (props.currentPage > 1) {
        emit('page-changed', props.currentPage - 1)
      }
    }

    const nextPage = () => {
      if (props.currentPage < totalPages.value) {
        emit('page-changed', props.currentPage + 1)
      }
    }

    const goToPage = (page) => {
      if (typeof page === 'number' && page !== props.currentPage) {
        emit('page-changed', page)
      }
    }

    return {
      totalPages,
      startIndex,
      endIndex,
      visiblePages,
      prevPage,
      nextPage,
      goToPage
    }
  }
}
</script>