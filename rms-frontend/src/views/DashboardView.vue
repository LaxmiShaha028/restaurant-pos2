<template>

  <section class="restaurant-dashboard">

    <!-- HEADER -->

    <header class="dashboard-header">

      <div class="dashboard-heading">

        <p class="dashboard-eyebrow">RESTAURANT OVERVIEW</p>

        <h1>

          Good {{ greeting }}, {{ userName }}

        </h1>

        <p class="dashboard-subtitle">

          Here's what's happening in your restaurant today.

        </p>

      </div>

      <div class="dashboard-header-actions">

        <div class="dashboard-date">

          <i class="bi bi-calendar3"></i>

          <div>

            <strong>{{ currentDate }}</strong>

            <span>Have a great day!</span>

          </div>

        </div>

        <button

          type="button"

          class="dashboard-refresh-button"

          :disabled="loading"

          @click="loadDashboard"

        >

          <i

            class="bi bi-arrow-clockwise"

            :class="{ spinning: loading }"

          ></i>

          Refresh

        </button>

      </div>

    </header>



    <!-- ERROR -->

    <div

      v-if="errorMessage"

      class="dashboard-alert"

    >

      <i class="bi bi-exclamation-triangle-fill"></i>

      <span>{{ errorMessage }}</span>

      <button

        type="button"

        @click="loadDashboard"

      >

        Retry

      </button>

    </div>



    <!-- LOADING -->

    <div

      v-if="loading"

      class="dashboard-loading"

    >

      <div class="spinner-border"></div>

      <span>Loading restaurant dashboard...</span>

    </div>



    <template v-else>

      <!-- =====================================================

           KPI CARDS

      ====================================================== -->

      <div class="dashboard-kpi-grid">

        <!-- SALES -->

        <article class="dashboard-kpi-card">

          <div class="kpi-top">

            <span class="kpi-icon green">

              <i class="bi bi-cash-stack"></i>

            </span>

            <span

              class="kpi-trend"

              :class="trendClass"

            >

              <i

                :class="

                  salesChange >= 0

                    ? 'bi bi-arrow-up'

                    : 'bi bi-arrow-down'

                "

              ></i>

              {{ absoluteNumber(salesChange) }}%

            </span>

          </div>

          <span class="kpi-label">

            This Month Sales

          </span>

          <strong class="kpi-value">

            {{ money(summary.total_sales) }}

          </strong>

          <div class="kpi-footer">

            <span>Previous Month</span>

            <strong>

              {{ money(salesComparison.previous_month) }}

            </strong>

          </div>

        </article>



        <!-- ORDERS -->

        <article class="dashboard-kpi-card">

          <div class="kpi-top">

            <span class="kpi-icon blue">

              <i class="bi bi-receipt"></i>

            </span>

            <span class="kpi-mini-badge">

              This Month

            </span>

          </div>

          <span class="kpi-label">

            This Month Orders

          </span>

          <strong class="kpi-value">

            {{ number(summary.total_orders) }}

          </strong>

          <div class="kpi-footer">

            <span>Completed</span>

            <strong>

              {{ number(summary.completed_orders) }}

            </strong>

          </div>

        </article>



        <!-- PAID -->

        <article class="dashboard-kpi-card">

          <div class="kpi-top">

            <span class="kpi-icon purple">

              <i class="bi bi-wallet2"></i>

            </span>

            <span class="kpi-mini-badge success">

              Collected

            </span>

          </div>

          <span class="kpi-label">

            Paid Amount

          </span>

          <strong class="kpi-value">

            {{ money(summary.paid_amount) }}

          </strong>

          <div class="kpi-footer">

            <span>Due</span>

            <strong class="danger-text">

              {{ money(summary.due_amount) }}

            </strong>

          </div>

        </article>



        <!-- ACTIVE ORDERS -->

        <article class="dashboard-kpi-card">

          <div class="kpi-top">

            <span class="kpi-icon orange">

              <i class="bi bi-fire"></i>

            </span>

            <span class="kpi-mini-badge warning">

              Live

            </span>

          </div>

          <span class="kpi-label">

            Active Orders

          </span>

          <strong class="kpi-value">

            {{ number(summary.active_orders) }}

          </strong>

          <div class="kpi-footer">

            <span>Pending Kitchen</span>

            <strong>

              {{ number(summary.pending_kitchen_orders) }}

            </strong>

          </div>

        </article>



        <!-- TABLES -->

        <article class="dashboard-kpi-card">

          <div class="kpi-top">

            <span class="kpi-icon cyan">

              <i class="bi bi-grid-3x3-gap"></i>

            </span>

            <span class="kpi-mini-badge">

              Current

            </span>

          </div>

          <span class="kpi-label">

            Occupied Tables

          </span>

          <strong class="kpi-value">

            {{ number(summary.occupied_tables) }}

          </strong>

          <div class="kpi-footer">

            <span>Available</span>

            <strong class="success-text">

              {{ number(summary.available_tables) }}

            </strong>

          </div>

        </article>

      </div>



      <!-- =====================================================

           MAIN ANALYTICS

      ====================================================== -->

      <div class="dashboard-main-grid">

        <!-- SALES -->

        <section class="dashboard-panel sales-panel">

          <div class="panel-header">

            <div class="panel-title-group">

              <span class="panel-icon blue">

                <i class="bi bi-bar-chart-line"></i>

              </span>

              <div>

                <h2>Sales Overview</h2>

                <p>

                  {{ salesPeriodDescription }}

                </p>

              </div>

            </div>

            <div class="sales-chart-controls">
              <div class="sales-period-picker">
                <button
                  type="button"
                  class="sales-period-select"
                  aria-label="Sales chart period"
                  :aria-expanded="salesPeriodMenuOpen"
                  aria-haspopup="listbox"
                  @click="salesPeriodMenuOpen = !salesPeriodMenuOpen"
                  @keydown.esc="salesPeriodMenuOpen = false"
                >
                  <span>{{ salesPeriodOptions.find(option => option.value === salesPeriod)?.label }}</span>
                  <i class="bi bi-chevron-down" aria-hidden="true"></i>
                </button>
                <Transition name="sales-period-menu">
                  <div
                    v-if="salesPeriodMenuOpen"
                    class="sales-period-menu"
                    role="listbox"
                    aria-label="Sales chart period"
                  >
                    <button
                      v-for="option in salesPeriodOptions"
                      :key="option.value"
                      type="button"
                      role="option"
                      class="sales-period-option"
                      :class="{ 'is-selected': salesPeriod === option.value }"
                      :aria-selected="salesPeriod === option.value"
                      @click="selectSalesPeriod(option.value)"
                    >
                      {{ option.label }}
                      <i v-if="salesPeriod === option.value" class="bi bi-check2" aria-hidden="true"></i>
                    </button>
                  </div>
                </Transition>
              </div>

              <div class="chart-total">
                <span>{{ salesPeriodLabel }}</span>

                <strong>

                  {{ money(salesChartTotal) }}

                </strong>

              </div>
            </div>

          </div>



          <div class="chart-wrapper">
            <div class="chart-body">
              <Bar
                :data="salesChartData"
                :options="salesChartOptions"
                class="sales-bar-chart"
              />
            </div>
          </div>
        </section>



        <!-- ORDER STATUS -->

        <section class="dashboard-panel">

          <div class="panel-header">

            <div class="panel-title-group">

              <span class="panel-icon orange">

                <i class="bi bi-pie-chart-fill"></i>

              </span>

              <div>

                <h2>Order Status</h2>

                <p>

                  Current order distribution

                </p>

              </div>

            </div>

          </div>



          <div class="order-status-content">

            <div class="status-visual">

              <div
  class="status-donut"
  :style="statusDonutStyle"
  @mousemove="handleStatusDonutHover"
  @mouseleave="hoveredStatus = null"
>

  <div class="donut-inner">

    <strong>

      {{ number(summary.total_orders) }}

    </strong>

    <span>

      Orders

    </span>

  </div>

  <div

    v-if="hoveredStatus"

    class="status-chart-tooltip"

  >

    <span class="tooltip-status">

      {{ hoveredStatus.label }}

    </span>

    <strong>

      {{ number(hoveredStatus.total) }} orders

    </strong>

    <small>

      {{ hoveredStatus.percent }}%

    </small>

  </div>

</div>



            </div>



            <div class="status-list">

              <div

                v-for="item in statusDisplay"

                :key="item.status"

                class="status-item"

              >

                <div class="status-item-left">

                  <span

                    class="status-dot"

                    :class="item.class"

                  ></span>

                  <span>

                    {{ item.label }}

                  </span>

                </div>

                <strong>

                  {{ item.total }}

                </strong>

              </div>

            </div>

          </div>

        </section>



        <!-- PAYMENT METHODS -->

        <section class="dashboard-panel">

          <div class="panel-header">

            <div class="panel-title-group">

              <span class="panel-icon orange">

                <i class="bi bi-credit-card"></i>

              </span>

              <div>

                <h2>Payment Methods</h2>

                <p>

                  This month's payments

                </p>

              </div>

            </div>

          </div>



          <div class="payment-list">

            <div

              v-for="item in paymentMethods"

              :key="item.payment_method"

              class="payment-item payment-item-hover"

              :title="`${paymentMethodLabel(item.payment_method)} • ${money(item.total_paid)} • ${paymentPercentage(item.total_paid)}%`"

            >

              <div class="payment-item-left">

                <span

                  class="payment-icon"

                  :class="

                    paymentMethodClass(

                      item.payment_method

                    )

                  "

                >

                  <i

                    :class="

                      paymentMethodIcon(

                        item.payment_method

                      )

                    "

                  ></i>

                </span>

                <div>

                  <strong>

                    {{ paymentMethodLabel(item.payment_method) }}

                  </strong>

                  <span>

                    {{ paymentPercentage(item.total_paid) }}%

                  </span>

                </div>

              </div>

              <strong class="payment-amount">

                {{ money(item.total_paid) }}

              </strong>

            </div>



            <div

              v-if="!paymentMethods.length"

              class="panel-empty"

            >

              No payment data available.

            </div>

          </div>



          <div

            v-if="paymentMethods.length"

            class="payment-progress"

          >

            <span

              v-for="item in paymentMethods"

              :key="`bar-${item.payment_method}`"

              :style="{

                width: `${paymentPercentage(item.total_paid)}%`

              }"

              :class="

                paymentMethodClass(

                  item.payment_method

                )

              "

            ></span>

          </div>

        </section>

      </div>



      <!-- =====================================================

           BOTTOM SECTION

      ====================================================== -->

      <div class="dashboard-bottom-grid">

        <!-- TOP ITEMS -->

        <section class="dashboard-panel">

          <div class="panel-header">

            <div class="panel-title-group">

              <span class="panel-icon orange">

                <i class="bi bi-trophy"></i>

              </span>

              <div>

                <h2>Top Selling Items</h2>

                <p>

                  Most popular menu items this month

                </p>

              </div>

            </div>

            <button

              type="button"

              class="view-all-button"

              @click="goToMenu"

            >

              View All

            </button>

          </div>



          <div class="top-items-table">

            <div class="top-items-head">

              <span>#</span>

              <span>Item Name</span>

              <span>Qty</span>

              <span>Sales</span>

            </div>



            <div

              v-for="(item, index) in topItems.slice(0, 5)"

              :key="item.menu_item_id"

              class="top-item-row top-item-hover"

              :title="`${item.item_name} • ${number(item.quantity_sold)} sold • ${money(item.sales_amount)}`"

            >

              <span class="item-rank">

                {{ index + 1 }}

              </span>

              <div class="item-name">

                <strong>

                  {{ item.item_name }}

                </strong>

                <div class="item-progress">

                  <span

                    :style="{

                      width:

                        `${topItemPercentage(item.quantity_sold)}%`

                    }"

                  ></span>

                </div>

              </div>

              <strong class="item-quantity">

                {{ number(item.quantity_sold) }}

              </strong>

              <strong class="item-sales">

                {{ money(item.sales_amount) }}

              </strong>

            </div>



            <div

              v-if="!topItems.length"

              class="panel-empty"

            >

              No sales data available yet.

            </div>

          </div>

        </section>



        <!-- RECENT ORDERS -->

        <section class="dashboard-panel recent-panel">

          <div class="panel-header">

            <div class="panel-title-group">

              <span class="panel-icon purple">

                <i class="bi bi-clock-history"></i>

              </span>

              <div>

                <h2>Recent Orders</h2>

                <p>

                  Latest orders from your restaurant

                </p>

              </div>

            </div>

            <button

              type="button"

              class="view-all-button"

              @click="goToOrders"

            >

              View All

            </button>

          </div>



          <div class="orders-table-wrapper">

            <table class="dashboard-orders-table">

              <thead>

                <tr>

                  <th>Order</th>

                  <th>Customer</th>

                  <th>Amount</th>

                  <th>Payment</th>

                  <th>Status</th>

                  <th>Time</th>

                </tr>

              </thead>

              <tbody>

                <tr

                  v-for="order in recentOrders.slice(0, 5)"

                  :key="order.id"

                >

                  <td>

                    <strong class="order-number">

                      {{ orderDisplayName(order) }}

                    </strong>

                  </td>

                  <td>

                    <span class="customer-name">

                      {{ order.customer_name || 'Walk-in' }}

                    </span>

                  </td>

                  <td>

                    <strong>

                      {{ money(order.total_amount) }}

                    </strong>

                  </td>

                  <td>

                    <span class="payment-method-text">

                      {{ paymentMethodLabel(order.payment_method) }}

                    </span>

                  </td>

                  <td>

                    <span

                      class="order-status-badge"

                      :class="

                        orderStatusClass(order.status)

                      "

                    >

                      {{ formatLabel(order.status) }}

                    </span>

                  </td>

                  <td>

                    <span class="order-time">

                      {{ formatDate(order.created_at) }}

                    </span>

                  </td>

                </tr>

              </tbody>

            </table>



            <div

              v-if="!recentOrders.length"

              class="orders-empty"

            >

              <span>

                <i class="bi bi-receipt"></i>

              </span>

              <strong>

                No orders yet

              </strong>

              <p>

                New orders will appear here.

              </p>

            </div>

          </div>

        </section>



        <!-- ACCOUNTING -->

        <section class="dashboard-panel accounting-panel">

          <div class="panel-header">

            <div class="panel-title-group">

              <span class="panel-icon blue">

                <i class="bi bi-pie-chart"></i>

              </span>

              <div>

                <h2>Accounting</h2>

                <p>

                  Financial overview

                </p>

              </div>

            </div>

          </div>



          <div class="accounting-content">

            <div class="accounting-info-box">

              <span>

                <i class="bi bi-info-circle"></i>

              </span>

              <div>

                <strong>

                  Profit & Loss

                </strong>

                <p>

                  Detailed expense, COGS and net profit

                  figures will be connected to the

                  accounting dashboard.

                </p>

              </div>

            </div>



            <div class="accounting-row">

              <span>Sales</span>

              <strong>

                {{ money(summary.total_sales) }}

              </strong>

            </div>

            <div class="accounting-row">

              <span>Paid</span>

              <strong class="success-text">

                {{ money(summary.paid_amount) }}

              </strong>

            </div>

            <div class="accounting-row">

              <span>Due</span>

              <strong class="danger-text">

                {{ money(summary.due_amount) }}

              </strong>

            </div>

          </div>

        </section>

      </div>



      <!-- FOOTER SUMMARY -->

      <div class="dashboard-footer-summary">

        <div>

          <span class="footer-summary-icon blue">

            <i class="bi bi-check-circle"></i>

          </span>

          <div>

            <strong>

              {{ number(summary.completed_orders) }}

            </strong>

            <span>

              Completed Orders

            </span>

          </div>

        </div>



        <div>

          <span class="footer-summary-icon green">

            <i class="bi bi-graph-up-arrow"></i>

          </span>

          <div>

            <strong>

              {{ completionRate }}%

            </strong>

            <span>

              Completion Rate

            </span>

          </div>

        </div>



        <div>

          <span class="footer-summary-icon orange">

            <i class="bi bi-fire"></i>

          </span>

          <div>

            <strong>

              {{ number(summary.pending_kitchen_orders) }}

            </strong>

            <span>

              Kitchen Queue

            </span>

          </div>

        </div>



        <div>

          <span class="footer-summary-icon purple">

            <i class="bi bi-calendar-event"></i>

          </span>

          <div>

            <strong>

              {{ number(summary.upcoming_reservations) }}

            </strong>

            <span>

              Upcoming Reservations

            </span>

          </div>

        </div>

      </div>

    </template>

  </section>
</template>

<script setup>

import {

  computed,

  onBeforeUnmount,

  onMounted,

  ref,

} from 'vue'

import { Bar } from 'vue-chartjs'
import 'chart.js/auto'

import {

  useRouter,

} from 'vue-router'

import api from '@/services/api'



const router = useRouter()

const loading = ref(true)

const errorMessage = ref('')

const dashboard = ref({})

const salesPeriod = ref('month')
const salesPeriodMenuOpen = ref(false)
const salesPeriodOptions = [
  { value: 'day', label: 'Day' },
  { value: 'month', label: 'Month' },
  { value: 'year', label: 'Year' },
  { value: 'last_year', label: 'Last Year' },
]
const salesChartRequestId = ref(0)
let reservationRefreshTimer = null

function selectSalesPeriod(period) {
  salesPeriod.value = period
  salesPeriodMenuOpen.value = false
  loadSalesChart()
}


const hoveredStatus = ref(null)



/* =========================================================

   DATA

========================================================= */

const user = computed(

  () => dashboard.value?.user ?? {}

)

const summary = computed(

  () => dashboard.value?.summary ?? {}

)

const salesComparison = computed(

  () => dashboard.value?.sales_comparison ?? {}

)

const salesChart = computed(

  () =>

    Array.isArray(dashboard.value?.sales_chart)

      ? dashboard.value.sales_chart

      : []

)

const salesPeriodLabel = computed(() => ({
  day: 'Today',
  month: 'This Month',
  year: 'This Year',
  last_year: 'Last Year',
}[salesPeriod.value] ?? 'This Month'))

const salesPeriodDescription = computed(() => ({
  day: 'Hourly sales for today',
  month: 'Daily sales for this month',
  year: 'Monthly sales for this year',
  last_year: 'Monthly sales for last year',
}[salesPeriod.value] ?? 'Daily sales for this month'))

const salesChartTotal = computed(() =>
  salesChart.value.reduce((total, item) => total + Number(item.sales ?? 0), 0)
)

const topItems = computed(

  () =>

    Array.isArray(dashboard.value?.top_items)

      ? dashboard.value.top_items

      : []

)

const recentOrders = computed(

  () =>

    Array.isArray(dashboard.value?.recent_orders)

      ? dashboard.value.recent_orders

      : []

)

function orderDisplayName(order) {
  const suffix = String(order?.order_number ?? '').match(/(?:^|-)(\d+)$/)?.[1]
  const label = suffix || order?.id

  return label ? `Order ${label}` : 'Order'
}

const orderStatus = computed(

  () =>

    Array.isArray(dashboard.value?.order_status)

      ? dashboard.value.order_status

      : []

)

const paymentMethods = computed(

  () =>

    Array.isArray(dashboard.value?.payment_methods)

      ? dashboard.value.payment_methods

      : []

)



/* =========================================================

   HEADER

========================================================= */

const userName = computed(

  () => user.value?.name || 'there'

)

const greeting = computed(() => {

  const hour = new Date().getHours()

  if (hour < 12) return 'Morning'

  if (hour < 18) return 'Afternoon'

  return 'Evening'

})

const currentDate = computed(() =>

  new Date().toLocaleDateString(

    'en-US',

    {

      weekday: 'long',

      month: 'short',

      day: 'numeric',

      year: 'numeric',

    }

  )

)



/* =========================================================

   SALES

========================================================= */

const salesChange = computed(

  () =>

    Number(

      salesComparison.value?.change_percent ?? 0

    )

)

const trendClass = computed(

  () =>

    salesChange.value >= 0

      ? 'positive'

      : 'negative'

)



/* =========================================================

   COMPLETION

========================================================= */

const completionRate = computed(() => {

  const total =

    Number(summary.value?.total_orders ?? 0)

  const completed =

    Number(summary.value?.completed_orders ?? 0)

  if (!total) return 0

  return Math.round(

    (completed / total) * 100

  )

})



/* =========================================================

   SALES CHART

========================================================= */

const chartMax = computed(() => {
  const values = salesChart.value.map(item => Number(item.sales ?? 0))
  const max = Math.max(...values, 0)
  if (max <= 0) return 100
  return Math.ceil(max / 1000) * 1000
})

const salesChartData = computed(() => ({
  labels: salesChart.value.map(item => item.label || item.date),
  datasets: [{
    label: 'Sales',
    data: salesChart.value.map(item => Number(item.sales ?? 0)),
    borderColor: '#f97316',
    backgroundColor: '#f97316',
    borderWidth: 0,
    borderRadius: 5,
    maxBarThickness: 28,
    pointRadius: 3.5,
    pointHoverRadius: 6,
    pointBackgroundColor: '#ffffff',
    pointBorderColor: '#f97316',
    pointBorderWidth: 2,
    tension: 0,
    fill: false,
  }],
}))

const salesChartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  animation: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label: context => `Sales: ${money(context.parsed.y)}`,
      },
    },
  },
  scales: {
    x: {
      grid: { display: false },
      border: { display: false },
      ticks: { color: '#94a3b8', maxTicksLimit: 7, font: { size: 9 } },
    },
    y: {
      beginAtZero: true,
      max: chartMax.value,
      grid: { color: '#eef1f5' },
      border: { display: false },
      ticks: {
        count: 5,
        color: '#94a3b8',
        font: { size: 9 },
        callback: value => moneyCompact(value),
      },
    },
  },
}))
/* =========================================================

   ORDER STATUS

========================================================= */

const statusMeta = {

  pending: {

    label: 'Pending',

    class: 'pending',

  },

  preparing: {

    label: 'Preparing',

    class: 'preparing',

  },

  ready: {

    label: 'Ready',

    class: 'ready',

  },

  served: {

    label: 'Served',

    class: 'served',

  },

  completed: {

    label: 'Completed',

    class: 'completed',

  },

  canceled: {

    label: 'Canceled',

    class: 'canceled',

  },

}



/*

 * IMPORTANT:

 * Always show every status.

 * If backend does not return a status,

 * it will still appear with 0.

 */

const statusDisplay = computed(() => {

  const backendStatus = new Map(

    orderStatus.value.map(item => [

      String(item.status ?? '').toLowerCase(),

      Number(item.total ?? 0),

    ])

  )

  return Object.entries(statusMeta).map(

    ([status, meta]) => {

      const total =

        backendStatus.get(status) ?? 0

      const totalOrders =

        Number(

          summary.value?.total_orders ?? 0

        )

      const percent =

        totalOrders

          ? Math.round(

              (total / totalOrders) * 100

            )

          : 0

      return {

        status,

        label: meta.label,

        class: meta.class,

        total,

        percent,

      }

    }

  )

})



/*

 * Dynamic donut chart

 */

const statusDonutStyle = computed(() => {

  const total = statusDisplay.value.reduce(

    (sum, item) => sum + Number(item.total || 0),

    0

  )

  if (!total) {

    return {

      background: '#edf0f4',

    }

  }

  const colors = [

    '#ff6565',

    '#ffad36',

    '#ffd34e',

    '#409ae9',

    '#f97316',

    '#9a3412',

  ]

  let current = 0

  const parts = statusDisplay.value.map((item, index) => {

    const start = current

    current +=

      (Number(item.total || 0) / total) * 360

    return `${colors[index]} ${start}deg ${current}deg`

  })

  return {

    background: `conic-gradient(${parts.join(', ')})`,

  }

})

function handleStatusDonutHover(event) {

  const donut = event.currentTarget

  const rect = donut.getBoundingClientRect()

  const centerX = rect.left + rect.width / 2

  const centerY = rect.top + rect.height / 2

  const x = event.clientX - centerX

  const y = event.clientY - centerY

  let angle = Math.atan2(y, x) * (180 / Math.PI)

  angle += 90

  if (angle < 0) {

    angle += 360

  }

  const total = statusDisplay.value.reduce(

    (sum, item) => sum + Number(item.total || 0),

    0

  )

  if (!total) {

    hoveredStatus.value = null

    return

  }

  let current = 0

  for (const item of statusDisplay.value) {

    const degrees =

      (Number(item.total || 0) / total) * 360

    const start = current

    const end = current + degrees

    if (angle >= start && angle < end) {

      hoveredStatus.value = item

      return

    }

    current = end

  }

  hoveredStatus.value = null

}

/* =========================================================

   TOP ITEMS

========================================================= */

const topItemMax = computed(() => {

  if (!topItems.value.length) {

    return 1

  }

  return Math.max(

    ...topItems.value.map(

      item =>

        Number(

          item.quantity_sold ?? 0

        )

    ),

    1

  )

})



function topItemPercentage(quantity) {

  return Math.max(

    5,

    Math.round(

      (

        Number(quantity || 0) /

        topItemMax.value

      ) * 100

    )

  )

}



/* =========================================================

   PAYMENT

========================================================= */

const totalPayments = computed(

  () =>

    paymentMethods.value.reduce(

      (total, item) =>

        total +

        Number(

          item.total_paid ?? 0

        ),

      0

    )

)



function paymentPercentage(amount) {

  if (!totalPayments.value) {

    return 0

  }

  return Math.round(

    (

      Number(amount || 0) /

      totalPayments.value

    ) * 100

  )

}



/* =========================================================

   FORMATTING

========================================================= */

function money(value) {

  return `৳ ${Number(

    value || 0

  ).toLocaleString(

    'en-BD',

    {

      minimumFractionDigits: 2,

      maximumFractionDigits: 2,

    }

  )}`

}



function moneyCompact(value) {

  const amount =

    Number(value || 0)

  if (amount >= 1000000) {

    return `৳ ${(amount / 1000000).toFixed(1)}M`

  }

  if (amount >= 1000) {

    return `৳ ${(amount / 1000).toFixed(0)}k`

  }

  return `৳ ${Math.round(amount)}`

}



function number(value) {

  const count = Number(value || 0)

  return count < 10

    ? String(count).padStart(2, '0')

    : count.toLocaleString('en-BD')

}



function absoluteNumber(value) {

  return Math.abs(

    Number(value || 0)

  ).toFixed(1)

}



function formatLabel(value) {

  return String(value || '')

    .replaceAll('_', ' ')

    .replace(

      /\b\w/g,

      character =>

        character.toUpperCase()

    )

}



function formatDate(value) {

  if (!value) return '-'

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {

    return '-'

  }

  return date.toLocaleString(

    'en-BD',

    {

      hour: '2-digit',

      minute: '2-digit',

    }

  )

}



/* =========================================================

   PAYMENT UI

========================================================= */

function paymentMethodLabel(method) {

  const labels = {

    cash: 'Cash',

    card: 'Card',

    bkash: 'bKash',

    nagad: 'Nagad',

    bank_transfer: 'Bank Transfer',

  }

  const value =

    String(method || '').toLowerCase()

  return (

    labels[value] ||

    formatLabel(value) ||

    'Other'

  )

}



function paymentMethodIcon(method) {

  const value =

    String(method || '').toLowerCase()

  if (value === 'cash') {

    return 'bi bi-cash-stack'

  }

  if (value === 'card') {

    return 'bi bi-credit-card'

  }

  if (value === 'bkash') {

    return 'bi bi-phone'

  }

  if (value === 'nagad') {

    return 'bi bi-phone-vibrate'

  }

  if (value === 'bank_transfer') {

    return 'bi bi-bank'

  }

  return 'bi bi-wallet2'

}



function paymentMethodClass(method) {

  return String(

    method || 'other'

  )

    .toLowerCase()

    .replaceAll('_', '-')

}



/* =========================================================

   ORDER STATUS UI

========================================================= */

function orderStatusClass(status) {

  return `status-${String(

    status || ''

  ).toLowerCase()}`

}



/* =========================================================

   NAVIGATION

========================================================= */

function goToOrders() {

  router.push({

    name: 'order-management',

  })

}



function goToMenu() {

  router.push({

    name: 'menu-management',

  }).catch(() => {})

}

/* =========================================================

   LOAD

========================================================= */

async function loadDashboard() {

  loading.value = true

  errorMessage.value = ''

  try {

    const response =

      await api.get('/dashboard', {
        params: { sales_period: salesPeriod.value },
      })

    dashboard.value =

      response?.data?.data ?? {}

  } catch (error) {

    errorMessage.value =

      error?.response?.data?.message ||

      error?.message ||

      'Could not load dashboard.'

  } finally {

    loading.value = false

  }

}

async function refreshUpcomingReservations() {
  try {
    const response = await api.get('/dashboard', {
      params: {
        sales_period: salesPeriod.value,
        refresh: true,
      },
    })

    const nextCount = response?.data?.data?.summary?.upcoming_reservations
    if (nextCount !== undefined && dashboard.value.summary) {
      dashboard.value = {
        ...dashboard.value,
        summary: {
          ...dashboard.value.summary,
          upcoming_reservations: Number(nextCount),
        },
      }
    }
  } catch {
    // Keep the current count visible if a background refresh fails.
  }
}

function handleDashboardVisibilityChange() {
  if (document.visibilityState === 'visible') {
    refreshUpcomingReservations()
  }
}

async function loadSalesChart() {
  const requestId = ++salesChartRequestId.value

  try {
    const response = await api.get('/dashboard', {
      params: { sales_period: salesPeriod.value },
    })

    if (requestId !== salesChartRequestId.value) return

    const nextSalesChart = response?.data?.data?.sales_chart
    if (Array.isArray(nextSalesChart)) {
      dashboard.value = {
        ...dashboard.value,
        sales_chart: nextSalesChart,
      }
    }
  } catch (error) {
    if (requestId === salesChartRequestId.value) {
      errorMessage.value =
        error?.response?.data?.message ||
        error?.message ||
        'Could not load sales chart.'
    }
  }
}



onMounted(() => {
  loadDashboard()
  reservationRefreshTimer = window.setInterval(
    refreshUpcomingReservations,
    30000,
  )
  document.addEventListener(
    'visibilitychange',
    handleDashboardVisibilityChange,
  )
})

onBeforeUnmount(() => {
  if (reservationRefreshTimer) {
    window.clearInterval(reservationRefreshTimer)
  }
  document.removeEventListener(
    'visibilitychange',
    handleDashboardVisibilityChange,
  )
})

</script>

<style scoped>
/* =========================================================
   MODERN RESTAURANT DASHBOARD
   Styling only — functionality remains unchanged
========================================================= */

.restaurant-dashboard {
  --dash-bg: #f4f1ed;
  --surface: #ffffff;
  --surface-soft: #fffaf5;
  --surface-muted: #f3ebe2;
  --border: #eee3da;
  --border-strong: #e3d4c7;
  --text: #172033;
  --text-2: #475569;
  --text-3: #7c8a9d;
  --primary: #f97316;
  --primary-2: #fb923c;
  --primary-soft: #fff1e6;
  --success: #ea8a36;
  --success-soft: #fff3e8;
  --warning: #f59e0b;
  --warning-soft: #fffbeb;
  --danger: #ef4444;
  --danger-soft: #fef2f2;
  --purple: #d97706;
  --purple-soft: #fff5eb;
  --cyan: #ea580c;
  --cyan-soft: #fff1e6;
  --shadow-sm: 0 1px 2px rgba(15, 23, 42, .04);
  --shadow-md: 0 8px 24px rgba(15, 23, 42, .06);
  --shadow-lg: 0 18px 45px rgba(15, 23, 42, .09);

  position: relative;
  width: 100%;
  min-height: 100%;
  padding: 18px;
  border-radius: 24px;
  background:
    radial-gradient(circle at 0 0, rgba(249, 115, 22, .055), transparent 28%),
    radial-gradient(circle at 100% 8%, rgba(251, 146, 60, .045), transparent 25%),
    var(--dash-bg);
  color: var(--text);
  font-family:
    Inter,
    ui-sans-serif,
    system-ui,
    -apple-system,
    BlinkMacSystemFont,
    "Segoe UI",
    sans-serif;
}

/* =========================================================
   HEADER
========================================================= */

.dashboard-header {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  margin-bottom: 18px;
  padding: 20px 22px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, .4);
  border-radius: 18px;
  background:
    radial-gradient(ellipse at 34% 10%, rgba(180, 83, 9, .42), transparent 36%),
    radial-gradient(ellipse at 90% 8%, rgba(124, 45, 4, .58), transparent 34%),
    radial-gradient(ellipse at 4% 92%, rgba(69, 21, 2, .82), transparent 42%),
    linear-gradient(112deg, #321407 0%, #542006 38%, #e45a0a 62%, #3b1605 100%);
  box-shadow: 0 12px 30px rgba(40, 17, 4, .32);
}

.dashboard-header::before {
  position: absolute;
  top: -42px;
  right: 120px;
  width: 170px;
  height: 170px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(124, 45, 4, .2), transparent 68%);
  content: "";
  pointer-events: none;
}

.dashboard-header::after {
  position: absolute;
  right: -45px;
  bottom: -85px;
  width: 180px;
  height: 180px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(69, 21, 2, .2), transparent 70%);
  content: "";
  pointer-events: none;
}

.dashboard-heading,
.dashboard-header-actions {
  position: relative;
  z-index: 1;
}

.dashboard-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  margin: 0 0 7px;
  color: #effff5;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 1.35px;
}

.dashboard-eyebrow::before {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #ffedd5;
  box-shadow: 0 0 0 4px rgba(255, 237, 213, .24);
  content: "";
}

.dashboard-heading h1 {
  margin: 0;
  color: #ffffff;
  text-shadow: 0 2px 12px rgba(124, 45, 18, .22);
  font-size: clamp(24px, 2.2vw, 31px);
  font-weight: 800;
  letter-spacing: -.55px;
  line-height: 1.15;
}

.dashboard-subtitle {
  margin: 7px 0 0;
  color: rgba(255, 255, 255, .86);
  font-size: 12px;
  line-height: 1.55;
}

.dashboard-header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.dashboard-date {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 185px;
  padding: 9px 12px;
  border: 1px solid rgba(255, 255, 255, .12);
  border-radius: 13px;
  background: linear-gradient(145deg, #191919 0%, #111111 54%, #090909 100%);
  box-shadow: 0 8px 18px rgba(0, 0, 0, .22);
}

.dashboard-date > i {
  display: grid;
  width: 37px;
  height: 37px;
  flex: 0 0 37px;
  place-items: center;
  border-radius: 11px;
  background: rgba(255, 255, 255, .08);
  color: #fb923c;
  font-size: 15px;
}

.dashboard-date div {
  display: flex;
  min-width: 0;
  flex-direction: column;
  gap: 2px;
}

.dashboard-date strong {
  overflow: hidden;
  color: #f8f1e8;
  font-size: 11px;
  font-weight: 750;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.dashboard-date span {
  color: #b7a99c;
  font-size: 9px;
}

.dashboard-refresh-button {
  display: inline-flex;
  height: 43px;
  align-items: center;
  justify-content: center;
  gap: 7px;
  padding: 0 15px;
  border: 1px solid rgba(255, 255, 255, .12);
  border-radius: 12px;
  background: linear-gradient(145deg, #191919 0%, #111111 54%, #090909 100%);
  box-shadow: 0 8px 18px rgba(0, 0, 0, .22);
  color: #fb923c;
  font-size: 11px;
  font-weight: 750;
  cursor: pointer;
  transition:
    transform .18s ease,
    box-shadow .18s ease,
    opacity .18s ease;
}

.dashboard-refresh-button:hover:not(:disabled) {
  transform: translateY(-1px);
  background: linear-gradient(145deg, #242424 0%, #171717 54%, #101010 100%);
  box-shadow: 0 11px 24px rgba(0, 0, 0, .3);
}

.dashboard-refresh-button:active:not(:disabled) {
  transform: translateY(0);
}

.dashboard-refresh-button:disabled {
  cursor: not-allowed;
  opacity: .62;
}

.dashboard-refresh-button .spinning {
  animation: dashboard-spin .9s linear infinite;
}

@keyframes dashboard-spin {
  to {
    transform: rotate(360deg);
  }
}

/* =========================================================
   ALERT / LOADING
========================================================= */

.dashboard-alert {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
  padding: 12px 14px;
  border: 1px solid #fecaca;
  border-radius: 14px;
  background: linear-gradient(135deg, #fff7f7, #fff);
  box-shadow: var(--shadow-sm);
  color: #991b1b;
  font-size: 11px;
}

.dashboard-alert > i {
  font-size: 14px;
}

.dashboard-alert button {
  margin-left: auto;
  padding: 6px 10px;
  border: 0;
  border-radius: 8px;
  background: #fee2e2;
  color: #b91c1c;
  font-size: 10px;
  font-weight: 800;
  cursor: pointer;
}

.dashboard-loading {
  display: flex;
  min-height: 360px;
  align-items: center;
  justify-content: center;
  gap: 10px;
  border: 1px solid var(--border);
  border-radius: 18px;
  background: rgba(255, 255, 255, .82);
  box-shadow: var(--shadow-md);
  color: var(--text-3);
  font-size: 12px;
}

/* =========================================================
   KPI CARDS
========================================================= */

.dashboard-kpi-grid {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 13px;
  margin-bottom: 14px;
}

.dashboard-kpi-card {
  position: relative;
  min-width: 0;
  padding: 15px;
  overflow: hidden;
  border: 1px solid var(--border);
  border-radius: 16px;
  background: rgba(255, 255, 255, .96);
  box-shadow: var(--shadow-sm);
  transition:
    transform .2s ease,
    border-color .2s ease,
    box-shadow .2s ease;
}

.dashboard-kpi-card::before {
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  border-radius: 16px 0 0 16px;
  background: #cbd5e1;
  content: "";
}

.dashboard-kpi-card:nth-child(1)::before {
  background: linear-gradient(#fb923c, #fdba74);
}

.dashboard-kpi-card:nth-child(2)::before {
  background: linear-gradient(#f97316, #fdba74);
}

.dashboard-kpi-card:nth-child(3)::before {
  background: linear-gradient(#ea580c, #fb923c);
}

.dashboard-kpi-card:nth-child(4)::before {
  background: linear-gradient(#f59e0b, #fdba74);
}

.dashboard-kpi-card:nth-child(5)::before {
  background: linear-gradient(#c2410c, #fb923c);
}

.dashboard-kpi-card:hover {
  transform: translateY(-3px);
  border-color: #d8e0ea;
  box-shadow: var(--shadow-lg);
}

.kpi-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 13px;
}

.kpi-icon {
  display: grid;
  width: 39px;
  height: 39px;
  place-items: center;
  border-radius: 12px;
  font-size: 16px;
}

.kpi-icon.green {
  background: var(--success-soft);
  color: #059669;
}

.kpi-icon.blue {
  background: var(--primary-soft);
  color: var(--primary);
}

.kpi-icon.purple {
  background: var(--purple-soft);
  color: var(--purple);
}

.kpi-icon.orange {
  background: #fff7ed;
  color: #ea580c;
}

.kpi-icon.cyan {
  background: var(--cyan-soft);
  color: var(--cyan);
}

.kpi-trend,
.kpi-mini-badge {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  padding: 5px 8px;
  border: 1px solid #e9edf2;
  border-radius: 999px;
  background: #f8fafc;
  color: #64748b;
  font-size: 9px;
  font-weight: 800;
  white-space: nowrap;
}

.kpi-trend.positive,
.kpi-mini-badge.success {
  border-color: #d1fae5;
  background: var(--success-soft);
  color: #059669;
}

.kpi-trend.negative {
  border-color: #fee2e2;
  background: var(--danger-soft);
  color: #dc2626;
}

.kpi-mini-badge.warning {
  border-color: #fde7ba;
  background: var(--warning-soft);
  color: #d97706;
}

.kpi-label {
  display: block;
  margin-bottom: 5px;
  color: #64748b;
  font-size: 11px;
  font-weight: 650;
}

.kpi-value {
  display: block;
  overflow: hidden;
  color: #0f172a;
  font-size: clamp(19px, 1.7vw, 24px);
  font-weight: 800;
  letter-spacing: -.4px;
  line-height: 1.2;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.kpi-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-top: 12px;
  padding-top: 10px;
  border-top: 1px solid #eef1f5;
}

.kpi-footer span {
  color: #94a3b8;
  font-size: 9px;
}

.kpi-footer strong {
  color: #475569;
  font-size: 10px;
  font-weight: 750;
}

.danger-text {
  color: #dc2626 !important;
}

.success-text {
  color: #059669 !important;
}

/* =========================================================
   MAIN PANELS
========================================================= */

.dashboard-main-grid {
  display: grid;
  grid-template-columns:
    minmax(0, 1.65fr)
    minmax(260px, .8fr)
    minmax(245px, .72fr);
  gap: 14px;
  margin-bottom: 14px;
}

.dashboard-bottom-grid {
  display: grid;
  grid-template-columns:
    minmax(0, .95fr)
    minmax(0, 1.22fr)
    minmax(245px, .68fr);
  gap: 14px;
  margin-bottom: 14px;
}

.dashboard-panel {
  min-width: 0;
  overflow: hidden;
  border: 1px solid var(--border);
  border-radius: 17px;
  background: rgba(255, 255, 255, .97);
  box-shadow: var(--shadow-md);
  transition:
    border-color .2s ease,
    box-shadow .2s ease;
}

.dashboard-panel:hover {
  border-color: #d9e2ec;
  box-shadow: 0 12px 32px rgba(15, 23, 42, .075);
}

.panel-header {
  display: flex;
  min-height: 65px;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 14px 16px;
  border-bottom: 1px solid #edf1f5;
  background: linear-gradient(180deg, #fff, #fcfdff);
}

.panel-title-group {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.panel-icon {
  display: grid;
  width: 35px;
  height: 35px;
  flex: 0 0 35px;
  place-items: center;
  border-radius: 11px;
  font-size: 14px;
}

.panel-icon.blue {
  background: var(--primary-soft);
  color: var(--primary);
}

.panel-icon.purple {
  background: var(--purple-soft);
  color: var(--purple);
}

.panel-icon.orange {
  background: #fff7ed;
  color: #ea580c;
}

.panel-icon.cyan {
  background: var(--cyan-soft);
  color: var(--cyan);
}

.panel-title-group h2 {
  margin: 0;
  color: #1e293b;
  font-size: 14px;
  font-weight: 800;
  letter-spacing: -.15px;
}

.panel-title-group p {
  margin: 3px 0 0;
  overflow: hidden;
  color: #94a3b8;
  font-size: 9.5px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.chart-total {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
}

.chart-total span {
  color: #94a3b8;
  font-size: 9px;
}

.chart-total strong {
  color: #0f172a;
  font-size: 13px;
  font-weight: 800;
}

/* =========================================================
   SALES CHART
========================================================= */

.sales-panel {
  min-height: 335px;
}

.sales-chart-controls {
  display: flex;
  align-items: center;
  gap: 12px;
}

.sales-period-picker {
  position: relative;
  z-index: 5;
}

.sales-period-select {
  display: flex;
  width: 112px;
  min-height: 34px;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 0 11px;
  border: 1px solid #dbe4f0;
  border-radius: 9px;
  background: #fff;
  color: #172554;
  font: inherit;
  font-size: 10px;
  font-weight: 700;
  cursor: pointer;
  transition: border-color .16s ease, box-shadow .16s ease;
}

.sales-period-select:hover,
.sales-period-select[aria-expanded="true"] {
  border-color: #fdba74;
  box-shadow: 0 0 0 3px rgba(249, 115, 22, .1);
}

.sales-period-select i {
  color: #475569;
  font-size: 10px;
}

.sales-period-menu {
  position: absolute;
  top: calc(100% + 6px);
  right: 0;
  width: 100%;
  padding: 5px;
  border: 1px solid #e6eaf0;
  border-radius: 10px;
  background: #fff;
  box-shadow: 0 12px 28px rgba(15, 23, 42, .14);
}

.sales-period-option {
  display: flex;
  width: 100%;
  min-height: 32px;
  align-items: center;
  justify-content: space-between;
  padding: 0 9px;
  border: 0;
  border-radius: 6px;
  background: transparent;
  color: #334155;
  font: inherit;
  font-size: 11px;
  text-align: left;
  cursor: pointer;
  transition: background .14s ease, color .14s ease;
}

.sales-period-option:hover,
.sales-period-option:focus-visible {
  outline: none;
  background: #fff7ed;
  color: #c2410c;
}

.sales-period-option.is-selected {
  background: #fff1e6;
  color: #c2410c;
  font-weight: 700;
}

.sales-period-option i {
  color: #ea580c;
}

.sales-period-menu-enter-active,
.sales-period-menu-leave-active {
  transition: opacity .12s ease, transform .12s ease;
  transform-origin: top right;
}

.sales-period-menu-enter-from,
.sales-period-menu-leave-to {
  opacity: 0;
  transform: translateY(-4px) scale(.98);
}

@media (max-width: 520px) {
  .sales-chart-controls {
    align-items: flex-end;
    flex-direction: column;
    gap: 6px;
  }
}

.chart-wrapper {
  min-height: 265px;
  padding: 18px 16px 12px 7px;
}

.chart-body {
  position: relative;
  width: 100%;
  height: 235px;
  min-width: 0;
}

.chart-grid {
  position: absolute;
  inset: 0 0 22px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.chart-grid span {
  width: 100%;
  border-top: 1px solid #eef1f5;
}

.sales-chart-svg {
  position: absolute;
  inset: 0 0 22px;
  width: 100%;
  height: calc(100% - 22px);
  overflow: visible;
}

.sales-chart-point {
  cursor: pointer;
  filter: drop-shadow(0 2px 3px rgba(37, 99, 235, .2));
  transition:
    r .15s ease,
    stroke-width .15s ease,
    filter .15s ease;
}

.sales-chart-point:hover {
  r: 7px;
  stroke-width: 3px;
  filter: drop-shadow(0 4px 5px rgba(37, 99, 235, .3));
}

.chart-x-axis {
  position: absolute;
  right: 0;
  bottom: 0;
  left: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: #94a3b8;
  font-size: 8.5px;
}

.sales-chart-tooltip {
  position: absolute;
  z-index: 20;
  display: flex;
  min-width: 132px;
  flex-direction: column;
  gap: 4px;
  padding: 9px 11px;
  border: 1px solid rgba(255, 255, 255, .09);
  border-radius: 10px;
  background: rgba(15, 23, 42, .96);
  box-shadow: 0 12px 28px rgba(15, 23, 42, .25);
  color: #fff;
  pointer-events: none;
  transform: translate(-50%, -100%);
  white-space: nowrap;
}

.sales-chart-tooltip::after {
  position: absolute;
  bottom: -5px;
  left: 50%;
  width: 10px;
  height: 10px;
  background: #0f172a;
  content: "";
  transform: translateX(-50%) rotate(45deg);
}

.sales-chart-tooltip strong {
  color: #fff;
  font-size: 10px;
  font-weight: 800;
}

.sales-chart-tooltip span {
  color: #cbd5e1;
  font-size: 9px;
}

.sales-chart-tooltip b {
  margin-left: 2px;
  color: #fff;
  font-size: 10px;
  font-weight: 800;
}

/* =========================================================
   ORDER STATUS
========================================================= */

.order-status-content {
  display: grid;
  grid-template-columns: minmax(125px, .85fr) minmax(115px, 1fr);
  gap: 13px;
  align-items: center;
  min-height: 264px;
  padding: 16px;
}

.status-visual {
  display: flex;
  align-items: center;
  justify-content: center;
}

.status-donut {
  position: relative;
  display: grid;
  width: 132px;
  height: 132px;
  place-items: center;
  overflow: visible;
  border-radius: 50%;
  box-shadow:
    inset 0 0 0 1px rgba(255, 255, 255, .55),
    0 10px 24px rgba(15, 23, 42, .08);
}

.status-donut::before {
  position: absolute;
  inset: 17px;
  z-index: 2;
  border-radius: 50%;
  background: #fff;
  box-shadow:
    0 0 0 1px #eef2f6,
    0 5px 15px rgba(15, 23, 42, .06);
  content: "";
}

.status-donut-segment {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  cursor: pointer;
  transition:
    filter .15s ease,
    transform .15s ease;
}

.status-donut-segment:hover {
  filter: brightness(.93);
}

.donut-inner {
  position: relative;
  z-index: 4;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  pointer-events: none;
}

.donut-inner strong {
  color: #0f172a;
  font-size: 23px;
  font-weight: 850;
  line-height: 1;
}

.donut-inner span {
  margin-top: 4px;
  color: #94a3b8;
  font-size: 9px;
  font-weight: 650;
}

.status-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.status-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 6px 7px;
  border-radius: 8px;
  transition: background .15s ease;
}

.status-item:hover {
  background: #f8fafc;
}

.status-item-left {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 7px;
  color: #64748b;
  font-size: 10px;
}

.status-item > strong {
  color: #334155;
  font-size: 10px;
  font-weight: 800;
}

.status-dot {
  width: 8px;
  height: 8px;
  flex: 0 0 8px;
  border-radius: 50%;
}

.status-dot.pending {
  background: #ff6565;
  box-shadow: 0 0 0 3px rgba(255, 101, 101, .12);
}

.status-dot.preparing {
  background: #ffad36;
  box-shadow: 0 0 0 3px rgba(255, 173, 54, .12);
}

.status-dot.ready {
  background: #ffd34e;
  box-shadow: 0 0 0 3px rgba(255, 211, 78, .14);
}

.status-dot.served {
  background: #409ae9;
  box-shadow: 0 0 0 3px rgba(64, 154, 233, .12);
}

.status-dot.completed {
  background: #f97316;
  box-shadow: 0 0 0 3px rgba(249, 115, 22, .14);
}

.status-dot.canceled {
  background: #9a3412;
  box-shadow: 0 0 0 3px rgba(154, 52, 18, .12);
}

.status-chart-tooltip {
  position: absolute;
  z-index: 30;
  top: -9px;
  left: 50%;
  display: flex;
  min-width: 112px;
  flex-direction: column;
  gap: 2px;
  padding: 8px 10px;
  border-radius: 9px;
  background: rgba(15, 23, 42, .96);
  box-shadow: 0 12px 24px rgba(15, 23, 42, .22);
  color: #fff;
  pointer-events: none;
  transform: translate(-50%, -100%);
  white-space: nowrap;
}

.status-chart-tooltip::after {
  position: absolute;
  bottom: -5px;
  left: 50%;
  width: 10px;
  height: 10px;
  background: #0f172a;
  content: "";
  transform: translateX(-50%) rotate(45deg);
}

.tooltip-status {
  color: #cbd5e1;
  font-size: 9px;
}

.status-chart-tooltip strong {
  color: #fff;
  font-size: 10px;
}

.status-chart-tooltip small {
  color: #94a3b8;
  font-size: 9px;
}

/* =========================================================
   PAYMENT METHODS
========================================================= */

.payment-list {
  display: flex;
  min-height: 223px;
  flex-direction: column;
  gap: 4px;
  padding: 12px 13px 7px;
}

.payment-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 9px;
  padding: 8px;
  border-radius: 10px;
  transition:
    background .15s ease,
    transform .15s ease;
}

.payment-item:hover {
  transform: translateX(2px);
  background: #f8fafc;
}

.payment-item-left {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 9px;
}

.payment-item-left > div {
  display: flex;
  min-width: 0;
  flex-direction: column;
  gap: 2px;
}

.payment-item-left strong {
  overflow: hidden;
  color: #334155;
  font-size: 10px;
  font-weight: 750;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.payment-item-left span:not(.payment-icon) {
  color: #94a3b8;
  font-size: 9px;
}

.payment-icon {
  display: grid;
  width: 32px;
  height: 32px;
  flex: 0 0 32px;
  place-items: center;
  border-radius: 10px;
  background: #f1f5f9;
  color: #64748b;
  font-size: 13px;
}

.payment-icon.cash,
.payment-progress .cash {
  background-color: #f97316;
}

.payment-icon.card,
.payment-progress .card {
  background-color: #c2410c;
}

.payment-icon.bkash,
.payment-progress .bkash {
  background-color: #e2136e;
}

.payment-icon.nagad,
.payment-progress .nagad {
  background-color: #f97316;
}

.payment-icon.bank-transfer,
.payment-progress .bank-transfer {
  background-color: #d97706;
}

.payment-icon.other,
.payment-progress .other {
  background-color: #64748b;
}

.payment-icon.cash,
.payment-icon.card,
.payment-icon.bkash,
.payment-icon.nagad,
.payment-icon.bank-transfer,
.payment-icon.other {
  color: #fff;
}

.payment-amount {
  color: #1e293b;
  font-size: 10px;
  font-weight: 800;
  white-space: nowrap;
}

.payment-progress {
  display: flex;
  height: 6px;
  overflow: hidden;
  margin: 0 14px 14px;
  border-radius: 999px;
  background: #eef2f6;
}

.payment-progress > span {
  min-width: 0;
  height: 100%;
}

.panel-empty {
  display: grid;
  min-height: 110px;
  place-items: center;
  padding: 20px 12px;
  color: #94a3b8;
  font-size: 10px;
  text-align: center;
}

/* =========================================================
   TOP SELLING ITEMS
========================================================= */

.top-items-table {
  padding: 4px 13px 13px;
}

.top-items-head,
.top-item-row {
  display: grid;
  grid-template-columns: 28px minmax(0, 1fr) 48px 83px;
  gap: 8px;
  align-items: center;
}

.top-items-head {
  padding: 8px 6px;
  border-bottom: 1px solid #eef2f6;
  color: #94a3b8;
  font-size: 8px;
  font-weight: 800;
  letter-spacing: .35px;
  text-transform: uppercase;
}

.top-items-head span:nth-child(3),
.top-items-head span:nth-child(4) {
  text-align: right;
}

.top-item-row {
  padding: 9px 6px;
  border-bottom: 1px solid #f1f4f7;
  border-radius: 10px;
  transition:
    background .15s ease,
    transform .15s ease;
}

.top-item-row:last-child {
  border-bottom: 0;
}

.top-item-row:hover {
  transform: translateX(2px);
  background: #f8fafc;
}

.item-rank {
  display: grid;
  width: 23px;
  height: 23px;
  place-items: center;
  border-radius: 7px;
  background: #f1f5f9;
  color: #526174;
  font-size: 9px;
  font-weight: 850;
}

.top-item-row:nth-of-type(2) .item-rank {
  background: #fff1e6;
  color: #c2410c;
}

.top-item-row:nth-of-type(3) .item-rank {
  background: #f8fafc;
  color: #64748b;
}

.top-item-row:nth-of-type(4) .item-rank {
  background: #fff7ed;
  color: #b45309;
}

.item-name {
  min-width: 0;
}

.item-name strong {
  display: block;
  overflow: hidden;
  color: #334155;
  font-size: 10.5px;
  font-weight: 750;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.item-progress {
  width: 88%;
  height: 4px;
  overflow: hidden;
  margin-top: 5px;
  border-radius: 999px;
  background: #edf2f7;
}

.item-progress span {
  display: block;
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(90deg, #c2410c, #fb923c);
}

.item-quantity {
  color: #64748b;
  font-size: 10px;
  text-align: right;
}

.item-sales {
  color: #1e293b;
  font-size: 10px;
  font-weight: 800;
  text-align: right;
  white-space: nowrap;
}

.view-all-button {
  padding: 6px 9px;
  border: 1px solid #dbeafe;
  border-radius: 8px;
  background: var(--primary-soft);
  color: var(--primary);
  font-size: 9.5px;
  font-weight: 800;
  cursor: pointer;
  transition:
    border-color .15s ease,
    background .15s ease,
    transform .15s ease;
}

.view-all-button:hover {
  transform: translateY(-1px);
  border-color: #fed7aa;
  background: #ffedd5;
  color: #c2410c;
}

/* =========================================================
   RECENT ORDERS
========================================================= */

.orders-table-wrapper {
  width: 100%;
  overflow-x: auto;
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 transparent;
}

.dashboard-orders-table {
  width: 100%;
  min-width: 590px;
  border-collapse: separate;
  border-spacing: 0;
}

.dashboard-orders-table th {
  padding: 10px 12px;
  border-bottom: 1px solid #e9edf2;
  background: #f8fafc;
  color: #8491a3;
  font-size: 8px;
  font-weight: 800;
  text-align: left;
  text-transform: uppercase;
  letter-spacing: .4px;
}

.dashboard-orders-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #f0f3f6;
  color: #526174;
  font-size: 10px;
  transition: background .15s ease;
}

.dashboard-orders-table tbody tr:hover td {
  background: #fbfdff;
}

.dashboard-orders-table tbody tr:last-child td {
  border-bottom: 0;
}

.order-number {
  color: var(--primary);
  font-size: 9.5px;
  font-weight: 800;
}

.customer-name {
  display: block;
  max-width: 105px;
  overflow: hidden;
  color: #334155;
  font-weight: 650;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.payment-method-text {
  color: #64748b;
  font-size: 9.5px;
}

.order-status-badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 7px;
  border-radius: 999px;
  font-size: 8.5px;
  font-weight: 800;
  white-space: nowrap;
}

.status-pending {
  background: #fff7ed;
  color: #c2410c;
}

.status-preparing {
  background: #eff6ff;
  color: #c2410c;
}

.status-ready {
  background: #fff5eb;
  color: #c2410c;
}

.status-served {
  background: #ecfeff;
  color: #0e7490;
}

.status-completed {
  background: #ecfdf5;
  color: #047857;
}

.status-canceled {
  background: #fef2f2;
  color: #dc2626;
}

.order-time {
  color: #94a3b8;
  font-size: 8.5px;
  white-space: nowrap;
}

.orders-empty {
  display: flex;
  min-height: 165px;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  color: #94a3b8;
}

.orders-empty > span {
  display: grid;
  width: 42px;
  height: 42px;
  place-items: center;
  margin-bottom: 8px;
  border-radius: 13px;
  background: #f1f5f9;
  color: #64748b;
  font-size: 16px;
}

.orders-empty strong {
  color: #64748b;
  font-size: 11px;
}

.orders-empty p {
  margin: 4px 0 0;
  font-size: 9px;
}

/* =========================================================
   ACCOUNTING
========================================================= */

.accounting-content {
  padding: 13px 15px 15px;
}

.accounting-info-box {
  display: flex;
  align-items: flex-start;
  gap: 9px;
  margin-bottom: 11px;
  padding: 11px;
  border: 1px solid #dbeafe;
  border-radius: 12px;
  background: linear-gradient(135deg, #eff6ff, #f8fbff);
}

.accounting-info-box > span {
  display: grid;
  width: 28px;
  height: 28px;
  flex: 0 0 28px;
  place-items: center;
  border-radius: 8px;
  background: #dbeafe;
  color: var(--primary);
  font-size: 12px;
}

.accounting-info-box strong {
  display: block;
  color: #334155;
  font-size: 10.5px;
  font-weight: 800;
}

.accounting-info-box p {
  margin: 4px 0 0;
  color: #7c8a9d;
  font-size: 8.5px;
  line-height: 1.55;
}

.accounting-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 9px 0;
  border-bottom: 1px solid #eef1f5;
}

.accounting-row:last-child {
  border-bottom: 0;
}

.accounting-row span {
  color: #7c8a9d;
  font-size: 10px;
}

.accounting-row strong {
  color: #334155;
  font-size: 10.5px;
  font-weight: 800;
}

/* =========================================================
   FOOTER SUMMARY
========================================================= */

.dashboard-footer-summary {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 13px;
}

.dashboard-footer-summary > div {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
  padding: 12px 14px;
  border: 1px solid var(--border);
  border-radius: 14px;
  background: rgba(255, 255, 255, .94);
  box-shadow: var(--shadow-sm);
  transition:
    transform .18s ease,
    box-shadow .18s ease;
}

.dashboard-footer-summary > div:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.footer-summary-icon {
  display: grid;
  width: 36px;
  height: 36px;
  flex: 0 0 36px;
  place-items: center;
  border-radius: 11px;
  font-size: 13px;
}

.footer-summary-icon.blue {
  background: var(--primary-soft);
  color: var(--primary);
}

.footer-summary-icon.green {
  background: var(--success-soft);
  color: #059669;
}

.footer-summary-icon.orange {
  background: #fff7ed;
  color: #ea580c;
}

.footer-summary-icon.purple {
  background: var(--purple-soft);
  color: var(--purple);
}

.dashboard-footer-summary > div > div {
  display: flex;
  min-width: 0;
  flex-direction: column;
  gap: 2px;
}

.dashboard-footer-summary strong {
  color: #1e293b;
  font-size: 13px;
  font-weight: 850;
}

.dashboard-footer-summary span:not(.footer-summary-icon) {
  overflow: hidden;
  color: #8a97a8;
  font-size: 9px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Existing helper classes preserved */
.status-item-hover,
.payment-item-hover,
.top-item-hover {
  transition: background .15s ease;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1400px) {
  .dashboard-kpi-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .dashboard-main-grid {
    grid-template-columns:
      minmax(0, 1.4fr)
      minmax(270px, .85fr);
  }

  .dashboard-main-grid .dashboard-panel:last-child {
    grid-column: span 2;
  }

  .payment-list {
    min-height: auto;
  }

  .dashboard-bottom-grid {
    grid-template-columns:
      minmax(0, 1fr)
      minmax(0, 1.1fr);
  }

  .accounting-panel {
    grid-column: span 2;
  }
}

@media (max-width: 1100px) {
  .restaurant-dashboard {
    padding: 14px;
  }

  .dashboard-main-grid,
  .dashboard-bottom-grid {
    grid-template-columns: 1fr;
  }

  .dashboard-main-grid .dashboard-panel:last-child,
  .accounting-panel {
    grid-column: auto;
  }

  .order-status-content {
    grid-template-columns: 165px 1fr;
  }
}

@media (max-width: 820px) {
  .restaurant-dashboard {
    padding: 10px;
    border-radius: 18px;
  }

  .dashboard-header {
    align-items: flex-start;
    flex-direction: column;
    padding: 17px;
  }

  .dashboard-header-actions {
    width: 100%;
    justify-content: space-between;
  }

  .dashboard-date {
    min-width: 0;
  }

  .dashboard-kpi-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
  }

  .dashboard-footer-summary {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .sales-panel {
    min-height: 315px;
  }
}

@media (max-width: 560px) {
  .restaurant-dashboard {
    padding: 8px;
    background: #f7f9fc;
  }

  .dashboard-header {
    gap: 10px;
    margin-bottom: 12px;
    padding: 12px;
    border-radius: 15px;
  }

  .dashboard-heading h1 {
    font-size: 18px;
    white-space: nowrap;
  }

  .dashboard-subtitle {
    overflow: hidden;
    margin-top: 4px;
    font-size: 10px;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .dashboard-header-actions {
    width: 100%;
    align-items: center;
    flex-direction: row;
    gap: 8px;
  }

  .dashboard-date {
    min-width: 0;
    flex: 1 1 auto;
    gap: 7px;
    padding: 6px 8px;
    border-radius: 10px;
  }

  .dashboard-date > i {
    width: 30px;
    height: 30px;
    flex-basis: 30px;
    border-radius: 8px;
    font-size: 13px;
  }

  .dashboard-date strong {
    font-size: 10px;
  }

  .dashboard-date span {
    font-size: 8px;
  }

  .dashboard-refresh-button {
    width: auto;
    height: 38px;
    flex: 0 0 auto;
    gap: 5px;
    padding: 0 11px;
    border-radius: 10px;
    font-size: 10px;
  }

  .dashboard-kpi-grid,
  .dashboard-footer-summary {
    grid-template-columns: 1fr;
  }

  .dashboard-panel,
  .dashboard-kpi-card {
    border-radius: 14px;
  }

  .panel-header {
    min-height: 58px;
    padding: 12px;
  }

  .panel-title-group h2 {
    font-size: 13px;
  }

  .chart-total {
    display: none;
  }

  .chart-wrapper {
    min-height: 240px;
    padding-right: 10px;
  }

  .order-status-content {
    grid-template-columns: 1fr;
  }

  .status-list {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .top-items-head,
  .top-item-row {
    grid-template-columns: 25px minmax(0, 1fr) 42px 75px;
    gap: 6px;
  }

  .dashboard-orders-table {
    min-width: 560px;
  }
}

/* Warm card surfaces to match the orange reference theme */
.dashboard-kpi-card {
  border-color: var(--border);
  background: #ffffff;
}

.dashboard-panel {
  border-color: var(--border);
  background: #ffffff;
}
.dashboard-panel .panel-header {
  border-bottom-color: #edf1f5;
  background: linear-gradient(180deg, #ffffff, #fcfdff);
}

.status-item:hover,
.top-item-row:hover,
.payment-item:hover {
  background: #f8fafc;
}
/* Keep the Recent Orders table inside its card without a horizontal scrollbar. */
.orders-table-wrapper {
  overflow: hidden;
}

.dashboard-orders-table {
  width: 100%;
  min-width: 0;
  table-layout: fixed;
}

.dashboard-orders-table th,
.dashboard-orders-table td {
  box-sizing: border-box;
  padding-right: 6px;
  padding-left: 6px;
  text-align: center;
}

.dashboard-orders-table th {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.dashboard-orders-table th:nth-child(1),
.dashboard-orders-table td:nth-child(1) { width: 14%; }
.dashboard-orders-table th:nth-child(2),
.dashboard-orders-table td:nth-child(2) { width: 25%; }
.dashboard-orders-table th:nth-child(3),
.dashboard-orders-table td:nth-child(3) { width: 15%; }
.dashboard-orders-table th:nth-child(4),
.dashboard-orders-table td:nth-child(4) { width: 15%; }
.dashboard-orders-table th:nth-child(5),
.dashboard-orders-table td:nth-child(5) { width: 14%; }
.dashboard-orders-table th:nth-child(6),
.dashboard-orders-table td:nth-child(6) { width: 17%; }

.customer-name,
.order-time {
  display: block;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
