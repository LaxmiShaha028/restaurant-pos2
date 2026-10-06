<template>
  <header class="purchase-order-header-card">
    <div class="purchase-order-header-content">
      <!-- Left: Icon + Text -->
      <div class="purchase-order-header-left">
        

        <div class="purchase-order-header-text">

          <h1>
            Purchase Orders Information
          </h1>

          <p>
            Manage supplier purchase orders,
            items and payments.
          </p>
        </div>
      </div>

      <!-- Right: Actions -->
      <div class="purchase-order-header-actions">
        <button
          type="button"
          class="po-refresh-btn"
          title="Refresh Purchase Orders"
          :disabled="loading"
          :aria-busy="loading"
          :aria-label="
            loading
              ? 'Refreshing purchase orders'
              : 'Refresh purchase orders'
          "
          @click="refresh"
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

        <button
          v-if="canManagePurchase"
          type="button"
          class="po-add-btn"
          title="Create New Purchase Order"
          :disabled="loading"
          aria-label="Create new purchase order"
          @click="addPurchaseOrder"
        >
          <i
            class="bi bi-plus-lg"
            aria-hidden="true"
          ></i>

          <span>
            Add Purchase Order
          </span>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import {
  computed,
} from 'vue'

import {
  useAuthStore,
} from '@/stores/auth'

/*
|--------------------------------------------------------------------------
| Props / Emits
|--------------------------------------------------------------------------
*/

const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits([
  'refresh',
  'add',
])

/*
|--------------------------------------------------------------------------
| Permission
|--------------------------------------------------------------------------
*/

const authStore =
  useAuthStore()

const canManagePurchase =
  computed(() => {
    if (
      typeof authStore.hasPermission
      !==
      'function'
    ) {
      return false
    }

    return Boolean(
      authStore.hasPermission(
        'inventory.manage',
      )
      ||
      authStore.hasPermission(
        'suppliers.manage',
      ),
    )
  })

/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

function refresh() {
  if (props.loading) {
    return
  }

  emit('refresh')
}

function addPurchaseOrder() {
  if (
    props.loading
    ||
    !canManagePurchase.value
  ) {
    return
  }

  emit('add')
}
</script>
