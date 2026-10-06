<template>
  <header class="customer-page-header">
    <div class="customer-page-header-left">
      <div class="customer-page-header-icon">
        <i class="bi bi-people"></i>
      </div>

      <div class="customer-page-heading">
        <p class="customer-page-eyebrow">
          Guest Management
        </p>

        <h1>
          Customer Management
        </h1>

        <p>
          Manage customer profiles, visits, spending and account activity.
        </p>
      </div>
    </div>

    <div class="customer-header-actions">
      <button
        type="button"
        class="customer-header-button customer-refresh-button"
        :disabled="loading"
        :aria-busy="loading"
        @click="handleRefresh"
      >
        <span
          v-if="loading"
          class="spinner-border spinner-border-sm"
          aria-hidden="true"
        ></span>

        <i
          v-else
          class="bi bi-arrow-clockwise"
          aria-hidden="true"
        ></i>

        <span>
          {{ loading ? 'Refreshing...' : 'Refresh' }}
        </span>
      </button>
    </div>
  </header>
</template>

<script setup>
/*
|--------------------------------------------------------------------------
| Component Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
})

/*
|--------------------------------------------------------------------------
| Component Events
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
  'refresh',
  'add',
])

/*
|--------------------------------------------------------------------------
| Handle Refresh
|--------------------------------------------------------------------------
*/

function handleRefresh() {
  if (props.loading) {
    return
  }

  emit('refresh')
}

/*
|--------------------------------------------------------------------------
| Handle Add Customer
|--------------------------------------------------------------------------
*/

// function handleAddCustomer() {
//   if (props.loading) {
//     return
//   }

//   emit('add')
// }
</script>

<style scoped>
/* ==========================================================================
   CUSTOMER PAGE HEADER
   Same size & spacing as Kitchen / Order / Table / Billing headers
   ========================================================================== */

.customer-page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 8px;
  padding: 7px 8px;
  border: 1px solid #e5e7eb;
  border-radius: 5px;
  background: #ffffff;
  box-shadow: none;
}

.customer-page-header-left {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 7px;
  flex: 1;
}

.customer-page-header-icon {
  display: inline-flex;
  width: 28px;
  height: 28px;
  flex: 0 0 28px;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  background: #eff6ff;
  color: #2563eb;
  font-size: 15px;
}

.customer-page-heading {
  min-width: 0;
}

.customer-page-eyebrow {
  margin: 0 0 1px;
  color: #2563eb;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.1;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}

.customer-page-heading h1 {
  margin: 0;
  color: #172033;
  font-size: 16px;
  font-weight: 700;
  line-height: 1.2;
  letter-spacing: 0;
}

.customer-page-heading p {
  max-width: 620px;
  margin: 2px 0 0;
  overflow: hidden;
  color: #667085;
  font-size: 10px;
  line-height: 1.25;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.customer-header-actions {
  display: flex;
  align-items: center;
  gap: 5px;
  flex-shrink: 0;
}

.customer-header-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  min-height: 28px;
  padding: 0 8px;
  border: none;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.customer-header-button:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.customer-refresh-button {
  background: #172033;
  color: #ffffff;
}

.customer-refresh-button:hover:not(:disabled) {
  background: #29354b;
}

.customer-header-button .spinner-border {
  width: 10px;
  height: 10px;
}
</style>