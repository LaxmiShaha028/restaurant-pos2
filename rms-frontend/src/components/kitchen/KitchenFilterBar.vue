<template>
  <section class="kitchen-filter-bar">
    <!-- ==========================================
         Search
    =========================================== -->
    <div class="kitchen-search-field">
      <i class="bi bi-search"></i>

      <input
        :value="search"
        type="text"
        placeholder="Search order, table, item or chef..."
        @input="
          emit(
            'update:search',
            $event.target.value
          )
        "
        @keyup.enter="
          emit('search')
        "
      />

      <button
        v-if="search"
        type="button"
        class="kitchen-search-clear"
        aria-label="Clear search"
        @click="clearSearch"
      >
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <!-- ==========================================
         Status Filter
    =========================================== -->
    <div class="kitchen-filter-field">
      <label for="kitchen-status-filter">
        Status
      </label>

      <OrderFilterSelect
        :model-value="status"
        :options="statusOptions"
        placeholder="All Statuses"
        label="Kitchen order status"
        @update:model-value="updateStatus"
      />
    </div>

    <!-- ==========================================
         Assignment Filter
    =========================================== -->
    <div class="kitchen-filter-field">
      <label for="kitchen-assignment-filter">
        Assignment
      </label>

      <OrderFilterSelect
        :model-value="assignment"
        :options="assignmentOptions"
        placeholder="All Orders"
        label="Kitchen order assignment"
        @update:model-value="updateAssignment"
      />
    </div>

    <!-- ==========================================
         Actions
    =========================================== -->
    <div class="kitchen-filter-actions">
      <button
        type="button"
        class="kitchen-filter-button search-button"
        @click="emit('search')"
      >
        <i class="bi bi-search"></i>

        Apply
      </button>

      <button
        type="button"
        class="kitchen-filter-button clear-button"
        :disabled="!hasActiveFilters"
        @click="clearFilters"
      >
        <i class="bi bi-eraser"></i>

        Clear
      </button>

      <button
        type="button"
        class="kitchen-filter-button refresh-button"
        @click="emit('refresh')"
      >
        <i class="bi bi-arrow-clockwise"></i>

        Refresh
      </button>
    </div>
  </section>
</template>

<script setup>
import {
  computed,
} from 'vue'
import OrderFilterSelect from '@/components/orders/OrderFilterSelect.vue'

const statusOptions = [
  { value: 'pending', label: 'Pending' },
  { value: 'preparing', label: 'Preparing' },
  { value: 'ready', label: 'Ready' },
]

const assignmentOptions = [
  { value: 'unassigned', label: 'Unassigned' },
  { value: 'assigned', label: 'Assigned' },
]

const props = defineProps({
  search: {
    type: String,
    default: '',
  },

  status: {
    type: String,
    default: '',
  },

  assignment: {
    type: String,
    default: '',
  },
})

const emit = defineEmits([
  'update:search',
  'update:status',
  'update:assignment',
  'search',
  'refresh',
])

/*
|--------------------------------------------------------------------------
| Active Filter State
|--------------------------------------------------------------------------
*/

const hasActiveFilters =
  computed(() => {
    return Boolean(
      props.search ||
      props.status ||
      props.assignment
    )
  })

/*
|--------------------------------------------------------------------------
| Update Status
|--------------------------------------------------------------------------
*/

function updateStatus(value) {
  emit(
    'update:status',
    value
  )

  emit('search')
}

/*
|--------------------------------------------------------------------------
| Update Assignment
|--------------------------------------------------------------------------
*/

function updateAssignment(value) {
  emit(
    'update:assignment',
    value
  )

  emit('search')
}

/*
|--------------------------------------------------------------------------
| Clear Search
|--------------------------------------------------------------------------
*/

function clearSearch() {
  emit(
    'update:search',
    ''
  )

  emit('search')
}

/*
|--------------------------------------------------------------------------
| Clear All Filters
|--------------------------------------------------------------------------
*/

function clearFilters() {
  emit(
    'update:search',
    ''
  )

  emit(
    'update:status',
    ''
  )

  emit(
    'update:assignment',
    ''
  )

  emit('search')
}
</script>
