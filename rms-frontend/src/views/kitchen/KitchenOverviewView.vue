<template>
  <section class="kitchen-page">
    <!-- ==================================================
         Kitchen Page Header
    =================================================== -->

    <header class="kitchen-header">
      <div class="kitchen-header-left">
        <div class="kitchen-header-icon">
          <i class="bi bi-fire"></i>
        </div>

        <div>
          <p class="kitchen-page-tag">
            Restaurant Kitchen
          </p>

          <h1>
            Kitchen Display System
          </h1>

          <p class="kitchen-page-description">
            Review incoming orders, assign chefs and
            manage kitchen preparation progress.
          </p>
        </div>
      </div>

      <div class="kitchen-header-right">
        <div class="kitchen-live-status">
        </div>

        <button
          v-if="isChef"
          type="button"
          class="btn kitchen-alert-toggle"
          :class="{
            'kitchen-alert-toggle-active':
              alertsEnabled,
          }"
          @click="toggleKitchenAlerts"
        >
          <i
            :class="
              alertsEnabled
                ? 'bi bi-bell-fill'
                : 'bi bi-bell'
            "
          ></i>

          <span>
            {{
              alertsEnabled
                ? 'Alerts On'
                : 'Enable Alerts'
            }}
          </span>
        </button>

        <button
          type="button"
          class="btn btn-outline-primary"
          :disabled="loading"
          @click="refreshOrders"
        >
          <span
            v-if="loading"
            class="spinner-border spinner-border-sm"
            role="status"
            aria-hidden="true"
          ></span>

          <i
            v-else
            class="bi bi-arrow-clockwise"
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

    <!-- ==================================================
         Chef Incoming Order Alert
    =================================================== -->

    <section
      v-if="
        isChef &&
        pendingUnassignedCount > 0
      "
      class="kitchen-incoming-alert"
      role="status"
      aria-live="polite"
    >
      <div class="kitchen-incoming-alert-icon">
        <i class="bi bi-bell-fill"></i>
      </div>

      <div class="kitchen-incoming-alert-content">
        <strong>
          {{
            pendingUnassignedCount === 1
              ? '1 kitchen order is waiting for a chef'
              : `${pendingUnassignedCount} kitchen orders are waiting for a chef`
          }}
        </strong>

        <span>
          {{
            alertsEnabled
              ? 'Alert sound will continue until every waiting order is accepted.'
              : 'Enable kitchen alerts to hear a repeating notification sound.'
          }}
        </span>
      </div>

      <button
        v-if="!alertsEnabled"
        type="button"
        class="kitchen-incoming-enable-button"
        @click="enableKitchenAlerts"
      >
        <i class="bi bi-volume-up"></i>

        Enable Sound
      </button>
    </section>

    <div
      v-if="alertError && isChef"
      class="kitchen-alert-error"
      role="alert"
    >
      <i class="bi bi-exclamation-triangle"></i>

      <span>{{ alertError }}</span>
    </div>

    <!-- ==================================================
         Kitchen Summary Cards
    =================================================== -->

    <KitchenSummaryCards
      :orders="orders"
    />

    <!-- ==================================================
         Search and Filter Bar
    =================================================== -->

    <KitchenFilterBar
      v-model:search="filters.search"
      v-model:status="filters.status"
      v-model:assignment="filters.assignment"
      @search="handleFilterSearch"
      @refresh="refreshOrders"
    />

    <!-- ==================================================
         Global Error State
    =================================================== -->

    <div
      v-if="errorMessage && !loading"
      class="kitchen-page-error"
      role="alert"
    >
      <span class="kitchen-page-error-icon">
        <i class="bi bi-exclamation-circle"></i>
      </span>

      <div class="kitchen-page-error-content">
        <strong>
          Unable to load kitchen orders
        </strong>

        <p>
          {{ errorMessage }}
        </p>
      </div>

      <button
        type="button"
        class="kitchen-page-error-retry"
        @click="refreshOrders"
      >
        <i class="bi bi-arrow-clockwise"></i>

        <span>Try Again</span>
      </button>
    </div>

    <!-- ==================================================
         Loading Skeleton
    =================================================== -->

    <KitchenLoading
      v-if="loading"
    />

    <!-- ==================================================
         Empty State
    =================================================== -->

    <KitchenEmptyState
      v-else-if="
        !errorMessage &&
        !orders.length
      "
      @refresh="refreshOrders"
    />

    <!-- ==================================================
         Compact Kitchen Order Grid
    =================================================== -->

    <section
      v-else-if="orders.length"
      class="kitchen-order-grid"
      aria-label="Kitchen orders"
    >
      <KitchenCompactOrderCard
        v-for="order in orders"
        :key="`${order.id}-${order.kitchen_batch_no || order.batch_no || 1}`"
        :order="order"
        @accepted="
          handleOrderAccepted(
            order,
            $event
          )
        "
      />
    </section>

    <!-- ==================================================
         Pagination
    =================================================== -->

    <nav
      v-if="
        !loading &&
        !errorMessage &&
        meta.last_page > 1
      "
      class="kitchen-pagination"
      aria-label="Kitchen order pagination"
    >
      <button
        type="button"
        class="btn btn-light"
        :disabled="
          loading ||
          meta.current_page <= 1
        "
        @click="previousPage"
      >
        <i class="bi bi-chevron-left"></i>

        <span>Previous</span>
      </button>

      <div class="kitchen-pagination-info">
        <span>
          Page

          <strong>
            {{ meta.current_page }}
          </strong>

          of

          <strong>
            {{ meta.last_page }}
          </strong>
        </span>

        <small>
          {{ meta.total }}

          {{
            meta.total === 1
              ? 'order'
              : 'orders'
          }}
        </small>
      </div>

      <button
        type="button"
        class="btn btn-light"
        :disabled="
          loading ||
          meta.current_page >=
            meta.last_page
        "
        @click="nextPage"
      >
        <span>Next</span>

        <i class="bi bi-chevron-right"></i>
      </button>
    </nav>
  </section>
</template>

<script setup>
import {
  computed,
  onBeforeUnmount,
  onMounted,
  reactive,
  ref,
  watch,
} from 'vue'

import kitchenOrderService from '@/services/kitchenOrderService'
import { useAuthStore } from '@/stores/auth'

import KitchenSummaryCards from '@/components/kitchen/KitchenSummaryCards.vue'
import KitchenFilterBar from '@/components/kitchen/KitchenFilterBar.vue'
import KitchenCompactOrderCard from '@/components/kitchen/KitchenCompactOrderCard.vue'
import KitchenLoading from '@/components/kitchen/KitchenLoading.vue'
import KitchenEmptyState from '@/components/kitchen/KitchenEmptyState.vue'

/*
|--------------------------------------------------------------------------
| Reactive State
|--------------------------------------------------------------------------
*/

const loading = ref(false)

const errorMessage = ref('')

const orders = ref([])

const backgroundRefreshing =
  ref(false)

const pendingUnassignedCount =
  ref(0)

const alertsEnabled =
  ref(false)

const alertError =
  ref('')

const authStore =
  useAuthStore()

const AUTO_REFRESH_SECONDS = 4

const AUTO_REFRESH_INTERVAL_MS =
  AUTO_REFRESH_SECONDS * 1000

const ALERT_REPEAT_INTERVAL_MS =
  3000

let autoRefreshTimer = null

let alertRepeatTimer = null

let audioContext = null

/*
|--------------------------------------------------------------------------
| Chef Detection
|--------------------------------------------------------------------------
*/

const currentRoleName =
  computed(() => {
    return String(
      authStore.user?.role?.name
      ??
      authStore.user?.role_name
      ??
      ''
    )
      .trim()
      .toLowerCase()
  })

const isChef =
  computed(() => {
    return (
      currentRoleName.value ===
      'chef'
    )
  })

/*
|--------------------------------------------------------------------------
| Pagination Meta
|--------------------------------------------------------------------------
*/

const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: null,
  to: null,
})

/*
|--------------------------------------------------------------------------
| Kitchen Filters
|--------------------------------------------------------------------------
*/

const filters = reactive({
  page: 1,
  per_page: 10,
  search: '',
  status: '',
  assignment: '',
})

/*
|--------------------------------------------------------------------------
| Load Kitchen Orders
|--------------------------------------------------------------------------
*/

async function loadOrders(
  {
    silent = false,
  } = {},
) {
  if (
    loading.value ||
    backgroundRefreshing.value
  ) {
    return
  }

  if (silent) {
    backgroundRefreshing.value =
      true
  } else {
    loading.value = true

    errorMessage.value = ''
  }

  try {
    const response =
      await kitchenOrderService
        .getKitchenOrders({
          page:
            filters.page,

          per_page:
            filters.per_page,

          search:
            filters.search.trim(),

          status:
            filters.status,

          assignment:
            filters.assignment,
        })

    orders.value =
      Array.isArray(
        response?.data,
      )
        ? response.data
        : []

    updatePaginationMeta(
      response?.meta,
    )

    updateKitchenAlertState(
      response?.alert,
    )
  } catch (error) {
    console.error(
      silent
        ? 'Kitchen auto refresh failed:'
        : 'Unable to load kitchen orders:',
      error,
    )

    /*
    |--------------------------------------------------------------------------
    | Silent Refresh Failure
    |--------------------------------------------------------------------------
    |
    | Do not wipe the kitchen screen because one background poll failed.
    |
    */

    if (silent) {
      return
    }

    orders.value = []

    resetPaginationMeta()

    errorMessage.value =
      resolveErrorMessage(
        error,
        'Unable to load kitchen orders. Please try again.',
      )
  } finally {
    if (silent) {
      backgroundRefreshing.value =
        false
    } else {
      loading.value = false
    }
  }
}

/*
|--------------------------------------------------------------------------
| Handle Filter Search
|--------------------------------------------------------------------------
|
| নতুন search বা filter apply করলে প্রথম page থেকে result load হবে।
|
*/

function handleFilterSearch() {
  filters.page = 1

  loadOrders()
}

/*
|--------------------------------------------------------------------------
| Refresh Kitchen Orders
|--------------------------------------------------------------------------
*/

function refreshOrders() {
  filters.page = 1

  loadOrders()
}

/*
|--------------------------------------------------------------------------
| Handle Accepted Order
|--------------------------------------------------------------------------
|
| Chef Accept করার পরে backend থেকে পাওয়া updated order সরাসরি current
| card-এ replace হবে। ফলে page reload ছাড়াই assigned chef username দেখাবে।
|
*/

function handleOrderAccepted(
  currentOrder,
  updatedOrder,
) {
  /*
  |--------------------------------------------------------------------------
  | Stop This Order From Contributing To The Alert Immediately
  |--------------------------------------------------------------------------
  */

  if (
    currentOrder?.status ===
      'pending' &&
    !currentOrder?.chef
  ) {
    pendingUnassignedCount.value =
      Math.max(
        0,
        pendingUnassignedCount.value -
          1,
      )
  }

  if (
    !updatedOrder ||
    typeof updatedOrder !==
      'object'
  ) {
    loadOrders({
      silent: true,
    })

    return
  }

  const orderIndex =
    orders.value.findIndex(
      (order) =>
        Number(order.id) ===
        Number(currentOrder.id),
    )

  if (orderIndex === -1) {
    loadOrders({
      silent: true,
    })

    return
  }

  orders.value.splice(
    orderIndex,
    1,
    {
      ...orders.value[
        orderIndex
      ],

      ...updatedOrder,
    },
  )

  /*
  |--------------------------------------------------------------------------
  | Reconcile With Server
  |--------------------------------------------------------------------------
  |
  | Another chef may have accepted another order at the same time.
  |
  */

  loadOrders({
    silent: true,
  })
}

/*
|--------------------------------------------------------------------------
| Previous Page
|--------------------------------------------------------------------------
*/

function previousPage() {
  if (
    loading.value ||
    filters.page <= 1
  ) {
    return
  }

  filters.page -= 1

  loadOrders()
}

/*
|--------------------------------------------------------------------------
| Next Page
|--------------------------------------------------------------------------
*/

function nextPage() {
  if (
    loading.value ||
    filters.page >=
      meta.last_page
  ) {
    return
  }

  filters.page += 1

  loadOrders()
}

/*
|--------------------------------------------------------------------------
| Update Pagination Meta
|--------------------------------------------------------------------------
*/

function updatePaginationMeta(
  paginationMeta,
) {
  const resolvedMeta =
    paginationMeta &&
    typeof paginationMeta ===
      'object'
      ? paginationMeta
      : {}

  meta.current_page =
    toPositiveNumber(
      resolvedMeta.current_page,
      1,
    )

  meta.last_page =
    toPositiveNumber(
      resolvedMeta.last_page,
      1,
    )

  meta.per_page =
    toPositiveNumber(
      resolvedMeta.per_page,
      filters.per_page,
    )

  meta.total =
    toNonNegativeNumber(
      resolvedMeta.total,
      0,
    )

  meta.from =
    resolvedMeta.from ??
    null

  meta.to =
    resolvedMeta.to ??
    null

  filters.page =
    meta.current_page
}

/*
|--------------------------------------------------------------------------
| Reset Pagination Meta
|--------------------------------------------------------------------------
*/

function resetPaginationMeta() {
  Object.assign(
    meta,
    {
      current_page: 1,
      last_page: 1,
      per_page:
        filters.per_page,
      total: 0,
      from: null,
      to: null,
    },
  )

  filters.page = 1
}

/*
|--------------------------------------------------------------------------
| Positive Number Helper
|--------------------------------------------------------------------------
*/

function toPositiveNumber(
  value,
  fallback,
) {
  const resolvedValue =
    Number(value)

  if (
    Number.isFinite(
      resolvedValue,
    ) &&
    resolvedValue > 0
  ) {
    return resolvedValue
  }

  return fallback
}

/*
|--------------------------------------------------------------------------
| Non-negative Number Helper
|--------------------------------------------------------------------------
*/

function toNonNegativeNumber(
  value,
  fallback,
) {
  const resolvedValue =
    Number(value)

  if (
    Number.isFinite(
      resolvedValue,
    ) &&
    resolvedValue >= 0
  ) {
    return resolvedValue
  }

  return fallback
}

/*
|--------------------------------------------------------------------------
| Resolve API Error
|--------------------------------------------------------------------------
*/

function resolveErrorMessage(
  error,
  fallbackMessage,
) {
  if (
    typeof kitchenOrderService
      .getKitchenErrorMessage ===
    'function'
  ) {
    return kitchenOrderService
      .getKitchenErrorMessage(
        error,
        fallbackMessage,
      )
  }

  const validationErrors =
    error?.response?.data
      ?.errors

  if (
    validationErrors &&
    typeof validationErrors ===
      'object'
  ) {
    const firstError =
      Object.values(
        validationErrors,
      )
        .flat()
        .find(Boolean)

    if (firstError) {
      return String(
        firstError,
      )
    }
  }

  return (
    error?.response?.data
      ?.message ||
    error?.message ||
    fallbackMessage
  )
}

/*
|--------------------------------------------------------------------------
| Global Kitchen Alert State
|--------------------------------------------------------------------------
*/

function updateKitchenAlertState(
  alert,
) {
  pendingUnassignedCount.value =
    toNonNegativeNumber(
      alert
        ?.pending_unassigned_count,
      0,
    )
}

/*
|--------------------------------------------------------------------------
| Auto Refresh
|--------------------------------------------------------------------------
*/

function startAutoRefresh() {
  stopAutoRefresh()

  autoRefreshTimer =
    window.setInterval(
      () => {
        loadOrders({
          silent: true,
        })
      },
      AUTO_REFRESH_INTERVAL_MS,
    )
}

function stopAutoRefresh() {
  if (
    autoRefreshTimer !== null
  ) {
    window.clearInterval(
      autoRefreshTimer,
    )

    autoRefreshTimer = null
  }
}

/*
|--------------------------------------------------------------------------
| Kitchen Sound Alerts
|--------------------------------------------------------------------------
|
| Browsers block unsolicited audio. The chef therefore enables sound once
| by clicking the visible button. After that, pending + unassigned kitchen
| orders trigger a repeating alert until the queue no longer has one.
|
*/

async function enableKitchenAlerts() {
  if (!isChef.value) {
    return
  }

  alertError.value = ''

  try {
    const AudioContextClass =
      window.AudioContext
      ||
      window.webkitAudioContext

    if (!AudioContextClass) {
      throw new Error(
        'This browser does not support kitchen sound alerts.',
      )
    }

    if (!audioContext) {
      audioContext =
        new AudioContextClass()
    }

    if (
      audioContext.state ===
      'suspended'
    ) {
      await audioContext.resume()
    }

    alertsEnabled.value =
      true
  } catch (error) {
    console.error(
      'Unable to enable kitchen alerts:',
      error,
    )

    alertsEnabled.value =
      false

    alertError.value =
      error?.message
      ||
      'Unable to enable kitchen sound alerts.'
  }
}

function disableKitchenAlerts() {
  alertsEnabled.value =
    false

  stopKitchenAlertSound()
}

async function toggleKitchenAlerts() {
  if (alertsEnabled.value) {
    disableKitchenAlerts()

    return
  }

  await enableKitchenAlerts()
}

function syncKitchenAlertSound() {
  const shouldPlay =
    isChef.value
    &&
    alertsEnabled.value
    &&
    pendingUnassignedCount.value >
      0

  if (shouldPlay) {
    startKitchenAlertSound()

    return
  }

  stopKitchenAlertSound()
}

function startKitchenAlertSound() {
  if (
    alertRepeatTimer !== null
  ) {
    return
  }

  playKitchenAlertPattern()

  alertRepeatTimer =
    window.setInterval(
      playKitchenAlertPattern,
      ALERT_REPEAT_INTERVAL_MS,
    )
}

function stopKitchenAlertSound() {
  if (
    alertRepeatTimer !== null
  ) {
    window.clearInterval(
      alertRepeatTimer,
    )

    alertRepeatTimer = null
  }
}

function playKitchenAlertPattern() {
  if (
    !audioContext
    ||
    audioContext.state !==
      'running'
    ||
    !isChef.value
    ||
    !alertsEnabled.value
    ||
    pendingUnassignedCount.value <=
      0
  ) {
    return
  }

  playTone(
    740,
    0,
  )

  playTone(
    920,
    0.24,
  )

  playTone(
    740,
    0.48,
  )
}

function playTone(
  frequency,
  delaySeconds,
) {
  if (!audioContext) {
    return
  }

  const oscillator =
    audioContext
      .createOscillator()

  const gain =
    audioContext
      .createGain()

  const startsAt =
    audioContext.currentTime +
    delaySeconds

  const endsAt =
    startsAt + 0.16

  oscillator.type =
    'sine'

  oscillator.frequency
    .setValueAtTime(
      frequency,
      startsAt,
    )

  gain.gain
    .setValueAtTime(
      0.0001,
      startsAt,
    )

  gain.gain
    .exponentialRampToValueAtTime(
      0.12,
      startsAt + 0.02,
    )

  gain.gain
    .exponentialRampToValueAtTime(
      0.0001,
      endsAt,
    )

  oscillator.connect(
    gain,
  )

  gain.connect(
    audioContext.destination,
  )

  oscillator.start(
    startsAt,
  )

  oscillator.stop(
    endsAt + 0.02,
  )
}

/*
|--------------------------------------------------------------------------
| Alert State Watch
|--------------------------------------------------------------------------
*/

watch(
  [
    isChef,
    alertsEnabled,
    pendingUnassignedCount,
  ],
  () => {
    syncKitchenAlertSound()
  },
)

/*
|--------------------------------------------------------------------------
| Initial Page Load
|--------------------------------------------------------------------------
*/

onMounted(async () => {
  await loadOrders()

  startAutoRefresh()
})

/*
|--------------------------------------------------------------------------
| Cleanup
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {
  stopAutoRefresh()

  stopKitchenAlertSound()

  if (audioContext) {
    audioContext
      .close()
      .catch(() => {})

    audioContext = null
  }
})
</script>

<style
  src="@/assets/css/kitchen/kitchen-overview.css"
></style>

<style
  src="@/assets/css/kitchen/kitchen-card.css"
></style>

<style
  src="@/assets/css/kitchen/kitchen-responsive.css"
></style>

<style scoped>
.kitchen-live-status {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  color: #64748b;
  font-size: 0.82rem;
  font-weight: 600;
}

.kitchen-live-dot {
  width: 0.55rem;
  height: 0.55rem;
  border-radius: 999px;
  background: #22c55e;
  box-shadow: 0 0 0 0.22rem rgba(34, 197, 94, 0.14);
}

.kitchen-alert-toggle {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  border: 1px solid #f59e0b;
  background: #fff;
  color: #b45309;
  font-weight: 700;
}

.kitchen-alert-toggle:hover {
  border-color: #d97706;
  color: #92400e;
}

.kitchen-alert-toggle-active {
  border-color: #16a34a;
  background: #f0fdf4;
  color: #15803d;
}

.kitchen-incoming-alert {
  display: flex;
  align-items: center;
  gap: 0.9rem;
  margin-bottom: 1rem;
  padding: 0.9rem 1rem;
  border: 1px solid rgba(245, 158, 11, 0.35);
  border-radius: 0.85rem;
  background: rgba(255, 251, 235, 0.96);
}

.kitchen-incoming-alert-icon {
  display: grid;
  width: 2.5rem;
  height: 2.5rem;
  flex: 0 0 auto;
  place-items: center;
  border-radius: 999px;
  background: #f59e0b;
  color: #fff;
  font-size: 1.05rem;
}

.kitchen-incoming-alert-content {
  display: flex;
  min-width: 0;
  flex: 1;
  flex-direction: column;
  gap: 0.12rem;
}

.kitchen-incoming-alert-content strong {
  color: #78350f;
}

.kitchen-incoming-alert-content span {
  color: #92400e;
  font-size: 0.86rem;
}

.kitchen-incoming-enable-button {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  flex: 0 0 auto;
  border: 0;
  border-radius: 0.55rem;
  padding: 0.55rem 0.75rem;
  background: #b45309;
  color: #fff;
  font-size: 0.82rem;
  font-weight: 700;
}

.kitchen-alert-error {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin: -0.4rem 0 1rem;
  color: #b91c1c;
  font-size: 0.82rem;
  font-weight: 600;
}

@media (max-width: 767.98px) {
  .kitchen-header-right {
    align-items: stretch;
  }

  .kitchen-live-status {
    width: 100%;
  }

  .kitchen-incoming-alert {
    align-items: flex-start;
    flex-wrap: wrap;
  }

  .kitchen-incoming-enable-button {
    width: 100%;
    justify-content: center;
  }
}
</style>
