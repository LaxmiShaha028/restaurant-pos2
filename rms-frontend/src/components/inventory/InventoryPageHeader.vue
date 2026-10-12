<template>
  <header class="inventory-page-header">
    <!-- Left: Icon + Text -->
    <div class="inventory-header-content">
      <div class="inventory-header-icon">
        <i
          class="bi bi-box-seam"
          aria-hidden="true"
        ></i>
      </div>

      <div class="inventory-header-text">
        <p class="inventory-page-eyebrow">
          Stock Control
        </p>

        <h1>
          Inventory Management
        </h1>

        <p>
          Manage recipes, warehouse stock
          and restaurant stock
        </p>
      </div>
    </div>

    <!-- Right: Low Stock + Refresh -->
    <div class="inventory-header-actions">
      <!-- Low Stock Status -->
      <div
        class="inventory-low-stock-alert"
        :class="{
          'inventory-alert-active': hasLowStock,
          'inventory-alert-clear': !hasLowStock,
        }"
        role="status"
        aria-live="polite"
      >
        <i
          class="bi"
          :class="
            hasLowStock
              ? 'bi-exclamation-triangle-fill'
              : 'bi-check-circle-fill'
          "
          aria-hidden="true"
        ></i>

        <span>
          {{ lowStockMessage }}
        </span>
      </div>

      <!-- Refresh -->
      <button
        type="button"
        class="inventory-refresh-button"
        :disabled="loading"
        :aria-busy="loading"
        :aria-label="
          loading
            ? 'Refreshing inventory'
            : 'Refresh inventory'
        "
        @click="handleRefresh"
      >
        <i
          class="bi bi-arrow-clockwise"
          :class="{
            'inventory-refresh-spin': loading,
          }"
          aria-hidden="true"
        ></i>

        <span>
          {{
            loading
              ? 'Refreshing...'
              : 'Refresh'
          }}
        </span>
      </button>
    </div>
  </header>
</template>

<script setup>
import {
  computed,
} from 'vue'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
  summary: {
    type: Object,
    default: () => ({
      low_stock_alert_count: 0,
      has_low_stock_alert: false,
    }),
  },

  loading: {
    type: Boolean,
    default: false,
  },
})

/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
  'refresh',
])

/*
|--------------------------------------------------------------------------
| Low Stock Count
|--------------------------------------------------------------------------
*/

const lowStockCount = computed(() => {
  const count =
    Number(
      props.summary
        ?.low_stock_alert_count,
    )

  if (
    !Number.isFinite(count)
    ||
    count <= 0
  ) {
    return 0
  }

  return Math.floor(
    count,
  )
})

/*
|--------------------------------------------------------------------------
| Low Stock Status
|--------------------------------------------------------------------------
*/

const hasLowStock = computed(() => {
  return (
    props.summary
      ?.has_low_stock_alert === true
    ||
    lowStockCount.value > 0
  )
})

/*
|--------------------------------------------------------------------------
| Low Stock Message
|--------------------------------------------------------------------------
*/

const lowStockMessage = computed(() => {
  if (!hasLowStock.value) {
    return 'Stock Levels Normal'
  }

  if (lowStockCount.value <= 0) {
    return 'Low Stock Alert'
  }

  return (
    `${lowStockCount.value} Low Stock Alert`
    +
    (
      lowStockCount.value === 1
        ? ''
        : 's'
    )
  )
})

/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

function handleRefresh() {
  if (props.loading) {
    return
  }

  emit(
    'refresh',
  )
}
</script>

<style scoped>
/* ==========================================================================
   INVENTORY PAGE HEADER
   Same size & spacing as Kitchen / Order / Table / Billing / etc.
   ========================================================================== */

.inventory-page-header {
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

/* Left side */
.inventory-header-content {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 7px;
}

.inventory-header-icon {
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

.inventory-header-text {
  min-width: 0;
}

.inventory-page-eyebrow {
  margin: 0 0 1px;
  color: #2563eb;
  font-size: 7px;
  font-weight: 700;
  line-height: 1.1;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}

.inventory-header-text h1 {
  margin: 0;
  color: #172033;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.2;
  letter-spacing: 0;
}

.inventory-header-text p {
  max-width: 520px;
  margin: 2px 0 0;
  overflow: hidden;
  color: #667085;
  font-size: 10px;
  line-height: 1.25;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Right side actions */
.inventory-header-actions {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
}

/* Low Stock Alert */
.inventory-low-stock-alert {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  min-height: 28px;
  padding: 0 8px;
  border: 1px solid transparent;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}

.inventory-alert-active {
  border-color: #fde68a;
  color: #b45309;
  background: #fffbeb;
}

.inventory-alert-clear {
  border-color: #bbf7d0;
  color: #15803d;
  background: #f0fdf4;
}

/* Refresh Button */
.inventory-refresh-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  min-height: 28px;
  padding: 0 8px;
  border: 1px solid #2563eb;
  border-radius: 4px;
  background: #2563eb;
  color: #ffffff;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
  cursor: pointer;
  transition: background 0.15s ease, border-color 0.15s ease;
}

.inventory-refresh-button:hover:not(:disabled) {
  border-color: #1d4ed8;
  background: #1d4ed8;
}

.inventory-refresh-button:disabled {
  cursor: not-allowed;
  opacity: 0.55;
}

.inventory-refresh-spin {
  animation: inventory-spin 0.8s linear infinite;
}

@keyframes inventory-spin {
  to {
    transform: rotate(360deg);
  }
}

/* Responsive */
@media (max-width: 767.98px) {
  .inventory-page-header {
    align-items: flex-start;
    flex-direction: column;
    gap: 8px;
  }

  .inventory-header-actions {
    width: 100%;
    justify-content: space-between;
  }

  .inventory-header-text p {
    white-space: normal;
  }
}
</style>