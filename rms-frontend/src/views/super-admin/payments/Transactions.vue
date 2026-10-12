<script setup>

import {
  computed,
  onMounted,
  ref,
  watch,
} from 'vue'

import PaginationControls from '@/components/shared/PaginationControls.vue'

import {
  useRouter,
} from 'vue-router'

import {
  getCentralTransactions,
} from '@/services/superAdminApi'


/*
|--------------------------------------------------------------------------
| Router
|--------------------------------------------------------------------------
*/

const router =
  useRouter()


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const transactions =
  ref([])

const currentPage = ref(1)
const pageSize = 10

const loading =
  ref(false)

const error =
  ref('')

const search =
  ref('')

const statusFilter =
  ref('all')

const typeFilter =
  ref('all')

const methodFilter =
  ref('all')

const lastUpdatedAt =
  ref(null)


/*
|--------------------------------------------------------------------------
| Filter Options
|--------------------------------------------------------------------------
*/

const paymentTypes =
  computed(() => {

    return [
      ...new Set(
        transactions.value
          .map(
            (transaction) =>
              transaction.payment_type
          )
          .filter(Boolean)
      ),
    ]
      .sort()

  })


const paymentMethods =
  computed(() => {

    return [
      ...new Set(
        transactions.value
          .map(
            (transaction) =>
              transaction.payment_method_name ||
              transaction.payment_method
          )
          .filter(Boolean)
      ),
    ]
      .sort()

  })


/*
|--------------------------------------------------------------------------
| Filtered Transactions
|--------------------------------------------------------------------------
*/

const filteredTransactions =
  computed(() => {

    const keyword =
      search.value
        .trim()
        .toLowerCase()


    return transactions.value.filter(
      (transaction) => {

        if (
          statusFilter.value !== 'all' &&
          transaction.status !==
            statusFilter.value
        ) {
          return false
        }


        if (
          typeFilter.value !== 'all' &&
          transaction.payment_type !==
            typeFilter.value
        ) {
          return false
        }


        const methodName =
          transaction.payment_method_name ||
          transaction.payment_method ||
          ''


        if (
          methodFilter.value !== 'all' &&
          methodName !==
            methodFilter.value
        ) {
          return false
        }


        if (
          !keyword
        ) {
          return true
        }


        const restaurant =
          transaction.restaurant ||
          {}


        const haystack = [
          restaurant.name,
          restaurant.owner_name,
          restaurant.owner_email,
          transaction.transaction_id,
          transaction.reference,
          transaction.payment_method,
          transaction.payment_method_name,
          transaction.payment_type,
          transaction.status,
          transaction.currency,
        ]
          .filter(Boolean)
          .join(' ')
          .toLowerCase()


        return haystack.includes(
          keyword
        )
      }
    )

  })

const paginatedTransactions = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return filteredTransactions.value.slice(start, start + pageSize)
})

watch(
  [search, statusFilter, typeFilter, methodFilter],
  () => {
    currentPage.value = 1
  },
)


/*
|--------------------------------------------------------------------------
| Counts
|--------------------------------------------------------------------------
*/

const totalCount =
  computed(() =>
    transactions.value.length
  )


const paidCount =
  computed(() =>
    transactions.value.filter(
      (transaction) =>
        transaction.status ===
        'paid'
    ).length
  )


const pendingCount =
  computed(() =>
    transactions.value.filter(
      (transaction) =>
        transaction.status ===
        'pending'
    ).length
  )


const failedCount =
  computed(() =>
    transactions.value.filter(
      (transaction) =>
        transaction.status ===
        'failed'
    ).length
  )


/*
|--------------------------------------------------------------------------
| Load
|--------------------------------------------------------------------------
*/

async function loadTransactions() {

  if (
    loading.value
  ) {
    return
  }


  loading.value =
    true

  error.value =
    ''


  try {

    const response =
      await getCentralTransactions()


    if (
      !response?.success
    ) {

      throw new Error(
        response?.message ||
        'Failed to load transactions.'
      )
    }


    transactions.value =
      Array.isArray(
        response.data
      )
        ? response.data
        : []


    lastUpdatedAt.value =
      new Date()

  } catch (
    exception
  ) {

    error.value =
      exception?.message ||
      'Failed to load transactions.'

  } finally {

    loading.value =
      false
  }
}


/*
|--------------------------------------------------------------------------
| View Restaurant
|--------------------------------------------------------------------------
*/

function viewRestaurant(
  transaction
) {

  const restaurantId =
    transaction
      ?.restaurant
      ?.id ||
    transaction
      ?.restaurant_id


  if (
    !restaurantId
  ) {
    return
  }


  router.push({
    name:
      'super-admin-restaurant-details',

    params: {
      restaurant:
        restaurantId,
    },
  })
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function formatDateTime(
  value
) {

  if (
    !value
  ) {
    return '—'
  }


  const date =
    new Date(value)


  if (
    Number.isNaN(
      date.getTime()
    )
  ) {
    return value
  }


  return date.toLocaleString(
    'en-GB',
    {
      day:
        '2-digit',

      month:
        'short',

      year:
        'numeric',

      hour:
        '2-digit',

      minute:
        '2-digit',
    }
  )
}


function formatAmount(
  amount,
  currency
) {

  return `${currency || 'BDT'} ${Number(
    amount || 0
  ).toLocaleString(
    'en-BD',
    {
      maximumFractionDigits:
        2,
    }
  )}`
}


function paymentTypeLabel(
  type
) {

  const labels = {
    initial:
      'Initial',

    upgrade:
      'Trial Upgrade',

    plan_change:
      'Plan Change',

    renewal:
      'Renewal',

    other:
      'Other',
  }


  return (
    labels[type] ||
    String(type || 'Other')
  )
}


function statusClass(
  status
) {

  if (
    status === 'paid'
  ) {
    return 'payment-status-paid'
  }


  if (
    status === 'pending'
  ) {
    return 'payment-status-pending'
  }


  if (
    status === 'failed'
  ) {
    return 'payment-status-failed'
  }


  return 'payment-status-other'
}


function formatLastUpdated() {

  if (
    !lastUpdatedAt.value
  ) {
    return 'Not refreshed yet'
  }


  return lastUpdatedAt.value
    .toLocaleTimeString(
      'en-GB',
      {
        hour:
          '2-digit',

        minute:
          '2-digit',

        second:
          '2-digit',
      }
    )
}


function clearFilters() {

  search.value =
    ''

  statusFilter.value =
    'all'

  typeFilter.value =
    'all'

  methodFilter.value =
    'all'
}


/*
|--------------------------------------------------------------------------
| Initial Load
|--------------------------------------------------------------------------
*/

onMounted(
  loadTransactions
)

</script>


<template>

  <section class="payments-page">


    <!-- =========================================================
         HEADER
    ========================================================== -->

    <header class="payments-page-header">

      <div class="payments-heading">

        <div class="payments-heading-icon">

          <i class="bi bi-receipt"></i>

        </div>


        <div>

          <span class="payments-eyebrow">
            Payment Management
          </span>

          <h1>
            Transactions
          </h1>

          <p>
            Review subscription payment transactions recorded
            across all restaurants.
          </p>

        </div>

      </div>


      <div class="payments-header-actions">

        <div class="payments-last-updated">

          <span>
            Last updated
          </span>

          <strong>
            {{ formatLastUpdated() }}
          </strong>

        </div>


        <button
          type="button"
          class="btn btn-primary payments-refresh-button"
          :disabled="loading"
          @click="loadTransactions"
        >

          <span
            v-if="loading"
            class="spinner-border spinner-border-sm"
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


    <!-- =========================================================
         SUMMARY CARDS
    ========================================================== -->

    <section class="payments-stat-grid">

      <article class="payments-stat-card">

        <span class="payments-stat-icon payment-stat-total">
          <i class="bi bi-receipt-cutoff"></i>
        </span>

        <div>
          <span>Total Transactions</span>
          <strong>{{ totalCount }}</strong>
        </div>

      </article>


      <article class="payments-stat-card">

        <span class="payments-stat-icon payment-stat-paid">
          <i class="bi bi-check-circle"></i>
        </span>

        <div>
          <span>Paid</span>
          <strong>{{ paidCount }}</strong>
        </div>

      </article>


      <article class="payments-stat-card">

        <span class="payments-stat-icon payment-stat-pending">
          <i class="bi bi-hourglass-split"></i>
        </span>

        <div>
          <span>Pending</span>
          <strong>{{ pendingCount }}</strong>
        </div>

      </article>


      <article class="payments-stat-card">

        <span class="payments-stat-icon payment-stat-failed">
          <i class="bi bi-x-circle"></i>
        </span>

        <div>
          <span>Failed</span>
          <strong>{{ failedCount }}</strong>
        </div>

      </article>

    </section>


    <!-- =========================================================
         FILTERS
    ========================================================== -->

    <section class="payments-filter-card">

      <div class="payments-search">

        <i class="bi bi-search"></i>

        <input
          v-model="search"
          type="search"
          placeholder="Search restaurant, transaction ID, reference..."
          aria-label="Search transactions"
        />

      </div>


      <select
        v-model="statusFilter"
        class="form-select payments-filter-select"
      >
        <option value="all">
          All Statuses
        </option>

        <option value="paid">
          Paid
        </option>

        <option value="pending">
          Pending
        </option>

        <option value="failed">
          Failed
        </option>
      </select>


      <select
        v-model="typeFilter"
        class="form-select payments-filter-select"
      >
        <option value="all">
          All Payment Types
        </option>

        <option
          v-for="type in paymentTypes"
          :key="type"
          :value="type"
        >
          {{ paymentTypeLabel(type) }}
        </option>
      </select>


      <select
        v-model="methodFilter"
        class="form-select payments-filter-select"
      >
        <option value="all">
          All Methods
        </option>

        <option
          v-for="method in paymentMethods"
          :key="method"
          :value="method"
        >
          {{ method }}
        </option>
      </select>


      <button
        type="button"
        class="btn btn-outline-secondary payments-clear-button"
        @click="clearFilters"
      >
        <i class="bi bi-x-lg"></i>
        Clear
      </button>

    </section>


    <!-- =========================================================
         ERROR
    ========================================================== -->

    <div
      v-if="error"
      class="payments-alert"
    >

      <div class="payments-alert-icon">
        <i class="bi bi-exclamation-triangle-fill"></i>
      </div>


      <div class="payments-alert-content">

        <strong>
          Transactions could not be loaded
        </strong>

        <p>
          {{ error }}
        </p>

      </div>


      <button
        type="button"
        class="btn btn-sm btn-outline-danger"
        :disabled="loading"
        @click="loadTransactions"
      >
        Try Again
      </button>

    </div>


    <!-- =========================================================
         LOADING
    ========================================================== -->

    <div
      v-if="
        loading &&
        !transactions.length
      "
      class="payments-loading-card"
    >

      <span class="spinner-border text-primary"></span>

      <div>
        <strong>Loading transactions</strong>
        <p>Fetching central payment records.</p>
      </div>

    </div>


    <!-- =========================================================
         TABLE
    ========================================================== -->

    <section
      v-if="
        !loading &&
        !error &&
        filteredTransactions.length
      "
      class="payments-table-card"
    >

      <div class="payments-table-header">

        <div>

          <span class="payments-section-kicker">
            Payment Directory
          </span>

          <h2>
            All Transactions
          </h2>

        </div>


        <span class="payments-count-badge">

          {{ filteredTransactions.length }}
          record{{
            filteredTransactions.length === 1
              ? ''
              : 's'
          }}

        </span>

      </div>


      <div class="payments-table-wrapper">

        <table class="payments-table">

          <thead>

            <tr>

              <th>Restaurant</th>
              <th>Transaction</th>
              <th>Type</th>
              <th>Method</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Paid At</th>
              <th class="payments-actions-heading">
                Action
              </th>

            </tr>

          </thead>


          <tbody>

            <tr
              v-for="transaction in paginatedTransactions"
              :key="transaction.id"
            >


              <!-- Restaurant -->

              <td data-label="Restaurant">

                <div class="payment-restaurant-cell">

                  <span class="payment-restaurant-avatar">

                    {{
                      String(
                        transaction.restaurant?.name ||
                        'R'
                      )
                        .charAt(0)
                        .toUpperCase()
                    }}

                  </span>


                  <div class="payment-primary-copy">

                    <strong>
                      {{
                        transaction.restaurant?.name ||
                        'Unknown Restaurant'
                      }}
                    </strong>

                    <small>
                      {{
                        transaction.restaurant?.owner_email ||
                        '—'
                      }}
                    </small>

                  </div>

                </div>

              </td>


              <!-- Transaction -->

              <td data-label="Transaction">

                <div class="payment-info-cell">

                  <strong>
                    {{
                      transaction.transaction_id ||
                      `Payment #${transaction.id}`
                    }}
                  </strong>

                  <small>
                    {{
                      transaction.reference ||
                      `Subscription #${transaction.subscription_id || '—'}`
                    }}
                  </small>

                </div>

              </td>


              <!-- Type -->

              <td data-label="Type">

                <span class="payment-type-badge">

                  {{
                    paymentTypeLabel(
                      transaction.payment_type
                    )
                  }}

                </span>

              </td>


              <!-- Method -->

              <td data-label="Method">

                <div class="payment-info-cell">

                  <strong>
                    {{
                      transaction.payment_method_name ||
                      transaction.payment_method ||
                      '—'
                    }}
                  </strong>

                  <small>
                    {{
                      transaction.gateway ||
                      'manual'
                    }}
                  </small>

                </div>

              </td>


              <!-- Amount -->

              <td data-label="Amount">

                <strong class="payment-amount">

                  {{
                    formatAmount(
                      transaction.amount,
                      transaction.currency
                    )
                  }}

                </strong>

              </td>


              <!-- Status -->

              <td data-label="Status">

                <span
                  class="payment-status-badge"
                  :class="
                    statusClass(
                      transaction.status
                    )
                  "
                >

                  <span class="payment-status-dot"></span>

                  {{
                    transaction.status ||
                    'unknown'
                  }}

                </span>

              </td>


              <!-- Paid At -->

              <td data-label="Paid At">

                <span class="payment-date">
                  {{
                    formatDateTime(
                      transaction.paid_at ||
                      transaction.created_at
                    )
                  }}
                </span>

              </td>


              <!-- Action -->

              <td
                data-label="Action"
                class="payments-actions-cell"
              >

                <button
                  type="button"
                  class="payment-view-button"
                  :disabled="
                    !transaction.restaurant_id
                  "
                  @click="
                    viewRestaurant(
                      transaction
                    )
                  "
                >

                  <i class="bi bi-eye"></i>

                  <span>
                    View
                  </span>

                </button>

              </td>

            </tr>

          </tbody>

        </table>

      </div>

      <PaginationControls
        v-model:page="currentPage"
        :total="filteredTransactions.length"
        :per-page="pageSize"
      />

    </section>


    <!-- =========================================================
         FILTER EMPTY
    ========================================================== -->

    <section
      v-if="
        !loading &&
        !error &&
        transactions.length &&
        !filteredTransactions.length
      "
      class="payments-empty-state"
    >

      <div class="payments-empty-icon">
        <i class="bi bi-search"></i>
      </div>

      <h2>
        No matching transactions
      </h2>

      <p>
        Change or clear the current transaction filters.
      </p>

      <button
        type="button"
        class="btn btn-outline-primary"
        @click="clearFilters"
      >
        Clear Filters
      </button>

    </section>


    <!-- =========================================================
         EMPTY
    ========================================================== -->

    <section
      v-if="
        !loading &&
        !error &&
        !transactions.length
      "
      class="payments-empty-state"
    >

      <div class="payments-empty-icon">
        <i class="bi bi-receipt-cutoff"></i>
      </div>

      <h2>
        No payment transactions
      </h2>

      <p>
        Completed or attempted subscription payments will appear here.
      </p>

    </section>

  </section>

</template>


<style
  src="@/styles/super-admin/payments.css"
></style>
