<script setup>

import {
  computed,
  onMounted,
  ref,
  watch,
} from 'vue'

import {
  getCentralRevenue,
} from '@/services/superAdminApi'


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const revenue =
  ref({
    total_paid_transactions:
      0,

    currencies:
      [],

    monthly_trend:
      [],

    payment_types:
      [],

    payment_methods:
      [],
  })

const selectedCurrency =
  ref('')

const loading =
  ref(false)

const error =
  ref('')

const lastUpdatedAt =
  ref(null)


/*
|--------------------------------------------------------------------------
| Currency Options
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


const selectedSummary =
  computed(() => {

    return (
      currencyOptions.value.find(
        (item) =>
          item.currency ===
          selectedCurrency.value
      )
      ||
      null
    )

  })


/*
|--------------------------------------------------------------------------
| Monthly Trend
|--------------------------------------------------------------------------
*/

const selectedMonthlyTrend =
  computed(() => {

    return (
      Array.isArray(
        revenue.value.monthly_trend
      )
        ? revenue.value.monthly_trend
        : []
    ).filter(
      (item) =>
        item.currency ===
        selectedCurrency.value
    )

  })


const maxMonthlyRevenue =
  computed(() => {

    const values =
      selectedMonthlyTrend.value.map(
        (item) =>
          Number(
            item.amount || 0
          )
      )


    return Math.max(
      1,
      ...values
    )

  })


/*
|--------------------------------------------------------------------------
| Breakdowns
|--------------------------------------------------------------------------
*/

const selectedPaymentTypes =
  computed(() => {

    return (
      Array.isArray(
        revenue.value.payment_types
      )
        ? revenue.value.payment_types
        : []
    ).filter(
      (item) =>
        item.currency ===
        selectedCurrency.value
    )

  })


const selectedPaymentMethods =
  computed(() => {

    return (
      Array.isArray(
        revenue.value.payment_methods
      )
        ? revenue.value.payment_methods
        : []
    ).filter(
      (item) =>
        item.currency ===
        selectedCurrency.value
    )

  })


/*
|--------------------------------------------------------------------------
| Load
|--------------------------------------------------------------------------
*/

async function loadRevenue() {

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
      await getCentralRevenue()


    if (
      !response?.success
    ) {

      throw new Error(
        response?.message ||
        'Failed to load revenue.'
      )
    }


    revenue.value = {
      total_paid_transactions:
        Number(
          response.data
            ?.total_paid_transactions ||
          0
        ),

      currencies:
        Array.isArray(
          response.data?.currencies
        )
          ? response.data.currencies
          : [],

      monthly_trend:
        Array.isArray(
          response.data?.monthly_trend
        )
          ? response.data.monthly_trend
          : [],

      payment_types:
        Array.isArray(
          response.data?.payment_types
        )
          ? response.data.payment_types
          : [],

      payment_methods:
        Array.isArray(
          response.data?.payment_methods
        )
          ? response.data.payment_methods
          : [],
    }


    if (
      !selectedCurrency.value ||
      !revenue.value.currencies.some(
        (item) =>
          item.currency ===
          selectedCurrency.value
      )
    ) {

      selectedCurrency.value =
        revenue.value.currencies[0]
          ?.currency ||
        ''
    }


    lastUpdatedAt.value =
      new Date()

  } catch (
    exception
  ) {

    error.value =
      exception?.message ||
      'Failed to load revenue.'

  } finally {

    loading.value =
      false
  }
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function formatAmount(
  value
) {

  return `${selectedCurrency.value || ''} ${Number(
    value || 0
  ).toLocaleString(
    'en-BD',
    {
      maximumFractionDigits:
        2,
    }
  )}`.trim()
}


function formatCompactAmount(
  value
) {

  const amount =
    Number(
      value || 0
    )


  if (
    amount >= 1000000
  ) {
    return `${(
      amount /
      1000000
    ).toFixed(1)}M`
  }


  if (
    amount >= 1000
  ) {
    return `${(
      amount /
      1000
    ).toFixed(1)}K`
  }


  return amount.toLocaleString(
    'en-BD',
    {
      maximumFractionDigits:
        0,
    }
  )
}


function paymentTypeLabel(
  type
) {

  const labels = {
    initial:
      'Initial Payments',

    upgrade:
      'Trial Upgrades',

    plan_change:
      'Plan Changes',

    renewal:
      'Renewals',

    other:
      'Other',
  }


  return (
    labels[type] ||
    String(type || 'Other')
  )
}


function barHeight(
  amount
) {

  const percentage =
    (
      Number(
        amount || 0
      ) /
      maxMonthlyRevenue.value
    ) *
    100


  return `${Math.max(
    3,
    Math.min(
      100,
      percentage
    )
  )}%`
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


/*
|--------------------------------------------------------------------------
| Initial Load
|--------------------------------------------------------------------------
*/

onMounted(
  loadRevenue
)

</script>


<template>

  <section class="payments-page revenue-page">


    <!-- =========================================================
         HEADER
    ========================================================== -->

    <header class="payments-page-header">

      <div class="payments-heading">

        <div class="payments-heading-icon revenue-heading-icon">

          <i class="bi bi-graph-up-arrow"></i>

        </div>


        <div>

          <span class="payments-eyebrow">
            Payment Analytics
          </span>

          <h1>
            Revenue
          </h1>

          <p>
            Analyze paid subscription revenue without mixing different currencies.
          </p>

        </div>

      </div>


      <div class="payments-header-actions">

        <div
          v-if="
            currencyOptions.length > 1
          "
          class="revenue-currency-control"
        >

          <label for="revenue-currency">
            Currency
          </label>

          <select
            id="revenue-currency"
            v-model="selectedCurrency"
            class="form-select"
          >

            <option
              v-for="item in currencyOptions"
              :key="item.currency"
              :value="item.currency"
            >
              {{ item.currency }}
            </option>

          </select>

        </div>


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
          @click="loadRevenue"
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
      class="payments-alert"
    >

      <div class="payments-alert-icon">
        <i class="bi bi-exclamation-triangle-fill"></i>
      </div>


      <div class="payments-alert-content">

        <strong>
          Revenue could not be loaded
        </strong>

        <p>
          {{ error }}
        </p>

      </div>


      <button
        type="button"
        class="btn btn-sm btn-outline-danger"
        :disabled="loading"
        @click="loadRevenue"
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
        !currencyOptions.length
      "
      class="payments-loading-card"
    >

      <span class="spinner-border text-primary"></span>

      <div>
        <strong>Loading revenue analytics</strong>
        <p>Aggregating paid central payment records.</p>
      </div>

    </div>


    <template
      v-if="
        !loading &&
        !error &&
        selectedSummary
      "
    >


      <!-- =======================================================
           SUMMARY
      ======================================================== -->

      <section class="payments-stat-grid">

        <article class="payments-stat-card">

          <span class="payments-stat-icon revenue-stat-total">
            <i class="bi bi-cash-stack"></i>
          </span>

          <div>
            <span>Total Revenue</span>
            <strong class="revenue-stat-value">
              {{
                formatAmount(
                  selectedSummary.total_revenue
                )
              }}
            </strong>
          </div>

        </article>


        <article class="payments-stat-card">

          <span class="payments-stat-icon revenue-stat-month">
            <i class="bi bi-calendar3"></i>
          </span>

          <div>
            <span>This Month</span>
            <strong class="revenue-stat-value">
              {{
                formatAmount(
                  selectedSummary.month_revenue
                )
              }}
            </strong>
          </div>

        </article>


        <article class="payments-stat-card">

          <span class="payments-stat-icon revenue-stat-today">
            <i class="bi bi-calendar-check"></i>
          </span>

          <div>
            <span>Today</span>
            <strong class="revenue-stat-value">
              {{
                formatAmount(
                  selectedSummary.today_revenue
                )
              }}
            </strong>
          </div>

        </article>


        <article class="payments-stat-card">

          <span class="payments-stat-icon revenue-stat-count">
            <i class="bi bi-receipt"></i>
          </span>

          <div>
            <span>Paid Transactions</span>
            <strong>
              {{ selectedSummary.total_transactions }}
            </strong>
          </div>

        </article>

      </section>


      <!-- =======================================================
           12 MONTH TREND
      ======================================================== -->

      <section class="revenue-chart-card">

        <div class="revenue-card-header">

          <div>

            <span class="payments-section-kicker">
              12 Month Trend
            </span>

            <h2>
              Monthly Revenue
            </h2>

            <p>
              Paid revenue in {{ selectedCurrency }}.
            </p>

          </div>


          <span class="revenue-card-total">

            {{
              selectedCurrency
            }}

          </span>

        </div>


        <div class="revenue-chart">

          <div
            v-for="month in selectedMonthlyTrend"
            :key="month.month_key"
            class="revenue-chart-column"
          >

            <div class="revenue-chart-value">

              {{
                formatCompactAmount(
                  month.amount
                )
              }}

            </div>


            <div class="revenue-chart-bar-track">

              <div
                class="revenue-chart-bar"
                :style="{
                  height:
                    barHeight(
                      month.amount
                    )
                }"
                :title="
                  `${month.month_label}: ${formatAmount(month.amount)}`
                "
              ></div>

            </div>


            <div class="revenue-chart-label">

              {{
                month.month_label
                  .split(' ')[0]
              }}

            </div>

          </div>

        </div>

      </section>


      <!-- =======================================================
           BREAKDOWNS
      ======================================================== -->

      <div class="revenue-breakdown-grid">


        <!-- Payment Type -->

        <section class="revenue-breakdown-card">

          <div class="revenue-card-header">

            <div>

              <span class="payments-section-kicker">
                Revenue Source
              </span>

              <h2>
                Payment Types
              </h2>

            </div>

          </div>


          <div
            v-if="selectedPaymentTypes.length"
            class="revenue-breakdown-list"
          >

            <article
              v-for="item in selectedPaymentTypes"
              :key="item.payment_type"
              class="revenue-breakdown-item"
            >

              <div class="revenue-breakdown-icon">

                <i
                  :class="
                    item.payment_type === 'renewal'
                      ? 'bi bi-arrow-repeat'
                      : item.payment_type === 'upgrade'
                        ? 'bi bi-arrow-up-circle'
                        : item.payment_type === 'plan_change'
                          ? 'bi bi-arrow-left-right'
                          : 'bi bi-credit-card'
                  "
                ></i>

              </div>


              <div class="revenue-breakdown-copy">

                <strong>
                  {{
                    paymentTypeLabel(
                      item.payment_type
                    )
                  }}
                </strong>

                <span>
                  {{ item.transactions }}
                  transaction{{
                    Number(item.transactions) === 1
                      ? ''
                      : 's'
                  }}
                </span>

              </div>


              <strong class="revenue-breakdown-amount">

                {{
                  formatAmount(
                    item.amount
                  )
                }}

              </strong>

            </article>

          </div>


          <div
            v-else
            class="revenue-small-empty"
          >
            No payment type data.
          </div>

        </section>


        <!-- Payment Method -->

        <section class="revenue-breakdown-card">

          <div class="revenue-card-header">

            <div>

              <span class="payments-section-kicker">
                Collection Channels
              </span>

              <h2>
                Payment Methods
              </h2>

            </div>

          </div>


          <div
            v-if="selectedPaymentMethods.length"
            class="revenue-method-table"
          >

            <div class="revenue-method-head">

              <span>Method</span>
              <span>Transactions</span>
              <span>Revenue</span>

            </div>


            <div
              v-for="item in selectedPaymentMethods"
              :key="item.payment_method"
              class="revenue-method-row"
            >

              <strong>
                {{ item.payment_method }}
              </strong>

              <span>
                {{ item.transactions }}
              </span>

              <strong>
                {{
                  formatAmount(
                    item.amount
                  )
                }}
              </strong>

            </div>

          </div>


          <div
            v-else
            class="revenue-small-empty"
          >
            No payment method data.
          </div>

        </section>

      </div>

    </template>


    <!-- =========================================================
         EMPTY
    ========================================================== -->

    <section
      v-if="
        !loading &&
        !error &&
        !selectedSummary
      "
      class="payments-empty-state"
    >

      <div class="payments-empty-icon">
        <i class="bi bi-graph-up"></i>
      </div>

      <h2>
        No paid revenue yet
      </h2>

      <p>
        Revenue analytics will appear after successful paid transactions.
      </p>

    </section>

  </section>

</template>


<style
  src="@/styles/super-admin/payments.css"
></style>
