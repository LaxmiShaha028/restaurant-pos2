<script setup>

import {
  computed,
  onMounted,
  ref,
} from 'vue'

import {
  getCentralDashboard,
  getCentralRevenue,
  getRestaurants,
} from '@/services/superAdminApi'


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const loading =
  ref(false)

const error =
  ref('')

const dashboard =
  ref(null)

const restaurants =
  ref([])

const revenue =
  ref({
    currencies: [],
    monthly_trend: [],
    payment_types: [],
    payment_methods: [],
    total_paid_transactions: 0,
  })

const selectedCurrency =
  ref('')

const lastUpdatedAt =
  ref(null)


/*
|--------------------------------------------------------------------------
| Load Dashboard Data
|--------------------------------------------------------------------------
|
| No backend change is required here if Step 9 payment APIs
| are already installed. We combine:
|
| - /central/dashboard
| - /central/restaurants
| - /central/payments/revenue
|
*/

async function loadDashboard() {

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

    const results =
      await Promise.allSettled([
        getCentralDashboard(),
        getRestaurants(),
        getCentralRevenue(),
      ])


    const [
      dashboardResult,
      restaurantsResult,
      revenueResult,
    ] = results


    /*
    |--------------------------------------------------------------------------
    | Dashboard Overview
    |--------------------------------------------------------------------------
    */

    if (
      dashboardResult.status === 'fulfilled' &&
      dashboardResult.value?.success
    ) {

      dashboard.value =
        dashboardResult.value.data
    }


    /*
    |--------------------------------------------------------------------------
    | Restaurants
    |--------------------------------------------------------------------------
    */

    if (
      restaurantsResult.status === 'fulfilled' &&
      restaurantsResult.value?.success
    ) {

      restaurants.value =
        Array.isArray(
          restaurantsResult.value.data
        )
          ? restaurantsResult.value.data
          : []
    }


    /*
    |--------------------------------------------------------------------------
    | Revenue
    |--------------------------------------------------------------------------
    */

    if (
      revenueResult.status === 'fulfilled' &&
      revenueResult.value?.success
    ) {

      revenue.value = {
        currencies:
          Array.isArray(
            revenueResult.value.data?.currencies
          )
            ? revenueResult.value.data.currencies
            : [],

        monthly_trend:
          Array.isArray(
            revenueResult.value.data?.monthly_trend
          )
            ? revenueResult.value.data.monthly_trend
            : [],

        payment_types:
          Array.isArray(
            revenueResult.value.data?.payment_types
          )
            ? revenueResult.value.data.payment_types
            : [],

        payment_methods:
          Array.isArray(
            revenueResult.value.data?.payment_methods
          )
            ? revenueResult.value.data.payment_methods
            : [],

        total_paid_transactions:
          Number(
            revenueResult.value.data?.total_paid_transactions || 0
          ),
      }
    }


    /*
    |--------------------------------------------------------------------------
    | Currency Default
    |--------------------------------------------------------------------------
    */

    if (
      !selectedCurrency.value &&
      revenue.value.currencies.length
    ) {

      selectedCurrency.value =
        revenue.value.currencies[0].currency
    }


    /*
    |--------------------------------------------------------------------------
    | Fallback Error
    |--------------------------------------------------------------------------
    */

    const everyCallFailed =
      results.every(
        (result) =>
          result.status === 'rejected'
      )

    const noUsefulData =
      !dashboard.value &&
      !restaurants.value.length &&
      !revenue.value.currencies.length


    if (
      everyCallFailed ||
      noUsefulData
    ) {

      throw new Error(
        'Failed to load dashboard data.'
      )
    }


    lastUpdatedAt.value =
      new Date()

  } catch (
    exception
  ) {

    error.value =
      exception?.message ||
      'Failed to load dashboard data.'

  } finally {

    loading.value =
      false
  }
}


/*
|--------------------------------------------------------------------------
| Revenue Summary
|--------------------------------------------------------------------------
*/

const currencyOptions =
  computed(() => {

    return Array.isArray(
      revenue.value.currencies
    )
      ? revenue.value.currencies
      : []

  })


const selectedRevenueSummary =
  computed(() => {

    return (
      currencyOptions.value.find(
        (item) =>
          item.currency ===
          selectedCurrency.value
      )
      ||
      currencyOptions.value[0]
      ||
      null
    )

  })


const selectedRevenueTrend =
  computed(() => {

    return (
      Array.isArray(
        revenue.value.monthly_trend
      )
        ? revenue.value.monthly_trend
        : []
    )
      .filter(
        (item) =>
          item.currency ===
          selectedCurrency.value
      )
      .slice(-6)

  })


const maxRevenueAmount =
  computed(() => {

    return Math.max(
      1,
      ...selectedRevenueTrend.value.map(
        (item) =>
          Number(
            item.amount || 0
          )
      )
    )

  })


/*
|--------------------------------------------------------------------------
| Restaurant Analytics
|--------------------------------------------------------------------------
*/

const totalRestaurants =
  computed(() => {

    return Number(
      dashboard.value?.restaurants?.total ||
      restaurants.value.length ||
      0
    )

  })


const activeRestaurants =
  computed(() => {

    return Number(
      dashboard.value?.restaurants?.active ||
      restaurants.value.filter(
        (restaurant) =>
          String(
            restaurant?.status || ''
          ).toLowerCase() ===
          'active'
      ).length ||
      0
    )

  })


const pendingRestaurants =
  computed(() => {

    return Number(
      dashboard.value?.restaurants?.pending ||
      restaurants.value.filter(
        (restaurant) =>
          String(
            restaurant?.status || ''
          ).toLowerCase() ===
          'pending'
      ).length ||
      0
    )

  })


const suspendedRestaurants =
  computed(() => {

    return restaurants.value.filter(
      (restaurant) =>
        String(
          restaurant?.status || ''
        ).toLowerCase() ===
        'suspended'
    ).length

  })


const inactiveRestaurants =
  computed(() => {

    return restaurants.value.filter(
      (restaurant) =>
        String(
          restaurant?.status || ''
        ).toLowerCase() ===
        'inactive'
    ).length

  })


const failedRestaurants =
  computed(() => {

    return restaurants.value.filter(
      (restaurant) =>
        String(
          restaurant?.status || ''
        ).toLowerCase() ===
        'failed'
    ).length

  })


const statusBreakdown =
  computed(() => {

    const total =
      Math.max(
        totalRestaurants.value,
        1
      )

    return [
      {
        key: 'active',
        label: 'Active',
        value: activeRestaurants.value,
        percentage:
          (
            activeRestaurants.value /
            total
          ) * 100,
      },
      {
        key: 'pending',
        label: 'Pending',
        value: pendingRestaurants.value,
        percentage:
          (
            pendingRestaurants.value /
            total
          ) * 100,
      },
      {
        key: 'suspended',
        label: 'Suspended',
        value: suspendedRestaurants.value,
        percentage:
          (
            suspendedRestaurants.value /
            total
          ) * 100,
      },
      {
        key: 'inactive',
        label: 'Inactive / Failed',
        value:
          inactiveRestaurants.value +
          failedRestaurants.value,
        percentage:
          (
            (
              inactiveRestaurants.value +
              failedRestaurants.value
            ) /
            total
          ) * 100,
      },
    ]

  })


const recentRestaurantTrend =
  computed(() => {

    const months =
      getMonthBuckets(6)

    const mapped =
      months.map(
        (month) => ({
          ...month,
          value: 0,
        })
      )


    restaurants.value.forEach(
      (restaurant) => {

        const date =
          safeDate(
            restaurant?.created_at
          )

        if (
          !date
        ) {
          return
        }


        const key =
          monthKey(date)

        const bucket =
          mapped.find(
            (item) =>
              item.key === key
          )

        if (
          bucket
        ) {
          bucket.value += 1
        }
      }
    )


    return mapped

  })


const maxRestaurantTrend =
  computed(() => {

    return Math.max(
      1,
      ...recentRestaurantTrend.value.map(
        (item) =>
          item.value
      )
    )

  })


/*
|--------------------------------------------------------------------------
| Dashboard Metrics
|--------------------------------------------------------------------------
*/

const activeSubscriptions =
  computed(() => {

    return Number(
      dashboard.value?.subscriptions?.active ||
      0
    )

  })


const pendingSubscriptions =
  computed(() => {

    return Number(
      dashboard.value?.subscriptions?.pending ||
      0
    )

  })


const activePlans =
  computed(() => {

    return Number(
      dashboard.value?.subscription_plans?.active ||
      0
    )

  })


const activeTenantDatabases =
  computed(() => {

    return Number(
      dashboard.value?.tenant_databases?.active ||
      0
    )

  })


const selectedRevenueTotal =
  computed(() => {

    return Number(
      selectedRevenueSummary.value?.total_revenue ||
      0
    )

  })


const selectedRevenueMonth =
  computed(() => {

    return Number(
      selectedRevenueSummary.value?.month_revenue ||
      0
    )

  })


const selectedRevenueToday =
  computed(() => {

    return Number(
      selectedRevenueSummary.value?.today_revenue ||
      0
    )

  })


const selectedRevenueTransactions =
  computed(() => {

    return Number(
      selectedRevenueSummary.value?.total_transactions ||
      0
    )

  })


const overviewCards =
  computed(() => {

    return [
      {
        key: 'total-restaurants',
        title: 'Total Restaurants',
        value:
          totalRestaurants.value,
        helper:
          'Registered on the platform',
        icon:
          'bi bi-shop',
        tone:
          'primary',
      },
      {
        key: 'active-restaurants',
        title: 'Active Restaurants',
        value:
          activeRestaurants.value,
        helper:
          'Currently running',
        icon:
          'bi bi-check-circle',
        tone:
          'success',
      },
      {
        key: 'active-subscriptions',
        title: 'Active Subscriptions',
        value:
          activeSubscriptions.value,
        helper:
          'Paid and currently live',
        icon:
          'bi bi-credit-card',
        tone:
          'violet',
      },
      {
        key: 'active-tenant-dbs',
        title: 'Active Tenant Databases',
        value:
          activeTenantDatabases.value,
        helper:
          'Provisioned and available',
        icon:
          'bi bi-hdd-network',
        tone:
          'slate',
      },
      {
        key: 'total-revenue',
        title: 'Total Revenue',
        value:
          selectedRevenueSummary.value
            ? formatAmount(
                selectedRevenueTotal.value,
                selectedCurrency.value
              )
            : '—',
        helper:
          'Successful paid collections',
        icon:
          'bi bi-cash-stack',
        tone:
          'emerald',
      },
      {
        key: 'month-revenue',
        title: 'This Month Revenue',
        value:
          selectedRevenueSummary.value
            ? formatAmount(
                selectedRevenueMonth.value,
                selectedCurrency.value
              )
            : '—',
        helper:
          'Current month paid revenue',
        icon:
          'bi bi-graph-up-arrow',
        tone:
          'amber',
      },
    ]

  })


const keyHighlights =
  computed(() => {

    const activeRate =
      totalRestaurants.value
        ? Math.round(
            (
              activeRestaurants.value /
              totalRestaurants.value
            ) *
            100
          )
        : 0

    return [
      {
        label:
          'Platform Health',
        value:
          `${activeRate}% restaurants active`,
      },
      {
        label:
          'Pending Attention',
        value:
          `${pendingRestaurants.value} restaurants pending review`,
      },
      {
        label:
          'Revenue Today',
        value:
          selectedRevenueSummary.value
            ? formatAmount(
                selectedRevenueToday.value,
                selectedCurrency.value
              )
            : 'No revenue data',
      },
      {
        label:
          'Paid Transactions',
        value:
          `${selectedRevenueTransactions.value} successful payments`,
      },
    ]

  })


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function safeDate(
  value
) {

  if (
    !value
  ) {
    return null
  }

  const date =
    new Date(value)

  return Number.isNaN(
    date.getTime()
  )
    ? null
    : date
}


function monthKey(
  value
) {

  return `${value.getFullYear()}-${String(
    value.getMonth() + 1
  ).padStart(2, '0')}`
}


function monthLabel(
  value
) {

  return value.toLocaleDateString(
    'en-GB',
    {
      month: 'short',
      year: '2-digit',
    }
  )
}


function getMonthBuckets(
  count = 6
) {

  const items = []

  const today =
    new Date()

  for (
    let index = count - 1;
    index >= 0;
    index--
  ) {

    const date =
      new Date(
        today.getFullYear(),
        today.getMonth() - index,
        1
      )

    items.push({
      key:
        monthKey(date),
      label:
        monthLabel(date),
    })
  }

  return items
}


function barHeight(
  value,
  max
) {

  const percentage =
    (
      Number(value || 0) /
      Math.max(max, 1)
    ) * 100

  return `${Math.max(
    6,
    Math.min(
      100,
      percentage
    )
  )}%`
}


function formatNumber(
  value
) {

  return Number(
    value || 0
  ).toLocaleString(
    'en-BD'
  )
}


function formatAmount(
  value,
  currency = 'BDT'
) {

  return `${currency} ${Number(
    value || 0
  ).toLocaleString(
    'en-BD',
    {
      maximumFractionDigits: 2,
    }
  )}`
}


function formatTime(
  value
) {

  if (
    !value
  ) {
    return 'Not refreshed yet'
  }

  return value.toLocaleTimeString(
    'en-GB',
    {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
    }
  )
}


/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(
  loadDashboard
)

</script>


<template>

  <section class="super-admin-dashboard-page">


    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <header class="dashboard-hero">

      <div class="dashboard-hero-copy">

        <span class="dashboard-kicker">
          Central Administration
        </span>

        <h1>
          Super Admin Dashboard
        </h1>

        <p>
          Monitor restaurants, subscriptions, tenant infrastructure
          and revenue from one organized overview.
        </p>

      </div>


      <div class="dashboard-hero-actions">

        <div class="dashboard-last-updated">
          <span>
            Last updated
          </span>

          <strong>
            {{ formatTime(lastUpdatedAt) }}
          </strong>
        </div>


        <div
          v-if="currencyOptions.length > 1"
          class="dashboard-currency-control"
        >
          <label for="dashboard-currency">
            Revenue Currency
          </label>

          <select
            id="dashboard-currency"
            v-model="selectedCurrency"
            class="form-select"
          >
            <option
              v-for="option in currencyOptions"
              :key="option.currency"
              :value="option.currency"
            >
              {{ option.currency }}
            </option>
          </select>
        </div>


        <button
          type="button"
          class="btn btn-primary dashboard-refresh-button"
          :disabled="loading"
          @click="loadDashboard"
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
         ERROR
    ========================================================== -->

    <div
      v-if="error"
      class="dashboard-alert"
    >

      <div class="dashboard-alert-icon">
        <i class="bi bi-exclamation-triangle-fill"></i>
      </div>

      <div class="dashboard-alert-content">
        <strong>
          Dashboard could not be loaded
        </strong>

        <p>
          {{ error }}
        </p>
      </div>

      <button
        type="button"
        class="btn btn-sm btn-outline-danger"
        :disabled="loading"
        @click="loadDashboard"
      >
        Try Again
      </button>

    </div>


    <!-- =========================================================
         LOADING
    ========================================================== -->

    <section
      v-if="
        loading &&
        !dashboard &&
        !restaurants.length
      "
      class="dashboard-loading-card"
    >

      <span class="spinner-border text-primary"></span>

      <div>
        <strong>
          Loading dashboard overview
        </strong>

        <p>
          Preparing platform statistics and revenue insights.
        </p>
      </div>

    </section>


    <template
      v-if="
        dashboard ||
        restaurants.length ||
        selectedRevenueSummary
      "
    >


      <!-- =======================================================
           HERO SUMMARY
      ======================================================== -->

      <section class="dashboard-highlight-grid">

        <article class="dashboard-spotlight-card dashboard-spotlight-main">

          <div class="dashboard-spotlight-head">
            <span class="dashboard-mini-kicker">
              Platform Overview
            </span>

            <strong>
              All systems in one place
            </strong>
          </div>


          <div class="dashboard-spotlight-metrics">

            <div>
              <span>
                Restaurants
              </span>

              <strong>
                {{ formatNumber(totalRestaurants) }}
              </strong>
            </div>

            <div>
              <span>
                Active
              </span>

              <strong>
                {{ formatNumber(activeRestaurants) }}
              </strong>
            </div>

            <div>
              <span>
                Subscriptions
              </span>

              <strong>
                {{ formatNumber(activeSubscriptions) }}
              </strong>
            </div>

          </div>


          <div class="dashboard-spotlight-footer">
            Active platform operations, subscription monitoring
            and revenue tracking are available from this panel.
          </div>

        </article>


        <article class="dashboard-spotlight-card dashboard-spotlight-side">

          <span class="dashboard-mini-kicker">
            Quick Highlights
          </span>

          <ul class="dashboard-highlight-list">
            <li
              v-for="item in keyHighlights"
              :key="item.label"
            >
              <span>
                {{ item.label }}
              </span>

              <strong>
                {{ item.value }}
              </strong>
            </li>
          </ul>

        </article>

      </section>


      <!-- =======================================================
           OVERVIEW CARDS
      ======================================================== -->

      <section class="dashboard-overview-grid">

        <article
          v-for="card in overviewCards"
          :key="card.key"
          class="dashboard-stat-card"
          :class="`tone-${card.tone}`"
        >

          <div class="dashboard-stat-icon">
            <i :class="card.icon"></i>
          </div>

          <div class="dashboard-stat-copy">
            <span>
              {{ card.title }}
            </span>

            <strong>
              {{ card.value }}
            </strong>

            <small>
              {{ card.helper }}
            </small>
          </div>

        </article>

      </section>


      <!-- =======================================================
           ANALYTICS GRID
      ======================================================== -->

      <section class="dashboard-analytics-grid">


        <!-- Restaurants Joined Chart -->

        <article class="dashboard-panel-card">

          <div class="dashboard-panel-head">

            <div>
              <span class="dashboard-mini-kicker">
                Growth
              </span>

              <h2>
                New Restaurants by Month
              </h2>
            </div>

            <span class="dashboard-panel-tag">
              Last 6 months
            </span>

          </div>


          <div class="dashboard-chart-bars">

            <div
              v-for="item in recentRestaurantTrend"
              :key="item.key"
              class="dashboard-chart-column"
            >

              <div class="dashboard-chart-value">
                {{ item.value }}
              </div>

              <div class="dashboard-chart-track">

                <div
                  class="dashboard-chart-bar bar-restaurants"
                  :style="{
                    height: barHeight(
                      item.value,
                      maxRestaurantTrend
                    ),
                  }"
                ></div>

              </div>

              <div class="dashboard-chart-label">
                {{ item.label }}
              </div>

            </div>

          </div>

        </article>


        <!-- Revenue Chart -->

        <article class="dashboard-panel-card">

          <div class="dashboard-panel-head">

            <div>
              <span class="dashboard-mini-kicker">
                Revenue
              </span>

              <h2>
                Monthly Revenue
              </h2>
            </div>

            <span class="dashboard-panel-tag">
              {{
                selectedCurrency || 'BDT'
              }}
            </span>

          </div>


          <div
            v-if="selectedRevenueTrend.length"
            class="dashboard-chart-bars"
          >

            <div
              v-for="item in selectedRevenueTrend"
              :key="item.month_key"
              class="dashboard-chart-column"
            >

              <div class="dashboard-chart-value">
                {{ formatNumber(item.amount) }}
              </div>

              <div class="dashboard-chart-track">

                <div
                  class="dashboard-chart-bar bar-revenue"
                  :style="{
                    height: barHeight(
                      item.amount,
                      maxRevenueAmount
                    ),
                  }"
                ></div>

              </div>

              <div class="dashboard-chart-label">
                {{
                  item.month_label
                    .split(' ')[0]
                }}
              </div>

            </div>

          </div>


          <div
            v-else
            class="dashboard-empty-mini"
          >
            No revenue chart data available yet.
          </div>

        </article>


        <!-- Status Breakdown -->

        <article class="dashboard-panel-card">

          <div class="dashboard-panel-head">

            <div>
              <span class="dashboard-mini-kicker">
                Status Overview
              </span>

              <h2>
                Restaurant Distribution
              </h2>
            </div>

            <span class="dashboard-panel-tag">
              Live status mix
            </span>

          </div>


          <div class="dashboard-status-list">

            <div
              v-for="item in statusBreakdown"
              :key="item.key"
              class="dashboard-status-item"
            >

              <div class="dashboard-status-row">

                <div class="dashboard-status-title">
                  {{ item.label }}
                </div>

                <div class="dashboard-status-value">
                  {{ item.value }}
                  <span>
                    {{
                      Math.round(
                        item.percentage
                      )
                    }}%
                  </span>
                </div>

              </div>

              <div class="dashboard-status-progress">
                <div
                  class="dashboard-status-fill"
                  :class="`status-${item.key}`"
                  :style="{
                    width: `${item.percentage}%`,
                  }"
                ></div>
              </div>

            </div>

          </div>

        </article>


        <!-- Revenue Snapshot -->

        <article class="dashboard-panel-card">

          <div class="dashboard-panel-head">

            <div>
              <span class="dashboard-mini-kicker">
                Revenue Snapshot
              </span>

              <h2>
                Collection Summary
              </h2>
            </div>

            <span class="dashboard-panel-tag">
              Successful payments
            </span>

          </div>


          <div
            v-if="selectedRevenueSummary"
            class="dashboard-snapshot-grid"
          >

            <div class="dashboard-snapshot-item">
              <span>
                Today
              </span>

              <strong>
                {{
                  formatAmount(
                    selectedRevenueToday,
                    selectedCurrency
                  )
                }}
              </strong>
            </div>

            <div class="dashboard-snapshot-item">
              <span>
                This Month
              </span>

              <strong>
                {{
                  formatAmount(
                    selectedRevenueMonth,
                    selectedCurrency
                  )
                }}
              </strong>
            </div>

            <div class="dashboard-snapshot-item">
              <span>
                Total Revenue
              </span>

              <strong>
                {{
                  formatAmount(
                    selectedRevenueTotal,
                    selectedCurrency
                  )
                }}
              </strong>
            </div>

            <div class="dashboard-snapshot-item">
              <span>
                Paid Transactions
              </span>

              <strong>
                {{
                  formatNumber(
                    selectedRevenueTransactions
                  )
                }}
              </strong>
            </div>

          </div>


          <div
            v-else
            class="dashboard-empty-mini"
          >
            Revenue data is not available yet.
          </div>

        </article>

      </section>

    </template>

  </section>

</template>


<style
  src="@/styles/super-admin/dashboard.css"
></style>
