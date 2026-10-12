<template>
  <section class="accounting-dashboard">

    <!-- =========================================================
         Header
    ========================================================== -->
    <div
      class="d-flex align-items-center justify-content-between mb-4"
    >
      <div>
        <h4 class="fw-bold mb-1 text-dark">
          Accounting Dashboard
        </h4>

        <p class="text-muted mb-0 small">
          Financial overview and accounting summary
        </p>
      </div>
    </div>


    <!-- =========================================================
         Loading
    ========================================================== -->
    <div
      v-if="loading"
      class="text-center py-5"
    >
      <div
        class="spinner-border text-primary mb-3"
        role="status"
        style="width: 2.5rem; height: 2.5rem;"
      ></div>

      <div class="text-muted">
        Loading dashboard...
      </div>
    </div>


    <!-- =========================================================
         Error
    ========================================================== -->
    <div
      v-else-if="error"
      class="alert alert-danger d-flex align-items-center shadow-sm"
    >
      <i
        class="bi bi-exclamation-triangle-fill me-2 fs-5"
      ></i>

      <div>
        {{ error }}
      </div>
    </div>


    <!-- =========================================================
         Dashboard
    ========================================================== -->
    <div v-else>

      <!-- =======================================================
           Financial Summary
      ======================================================== -->
      <div class="mb-3">

        <div class="d-flex align-items-center justify-content-between">
          <div>
            <h6 class="fw-bold text-dark mb-1">
              Financial Summary
            </h6>

            <small class="text-muted">
              Current reporting period
            </small>
          </div>
        </div>

      </div>


      <!-- =======================================================
           Summary Cards
      ======================================================== -->
      <div class="row g-3 mb-4">

        <!-- Revenue -->
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="card summary-card h-100 border-0 shadow-sm">

            <div class="card-body d-flex align-items-center">

              <div
                class="summary-icon bg-success-subtle text-success me-3"
              >
                <i class="bi bi-cash-stack fs-4"></i>
              </div>

              <div class="summary-content">
                <div class="text-muted small fw-medium mb-1">
                  Revenue
                </div>

                <div class="fs-5 fw-bold text-success">
                  {{ money(summary.revenue) }}
                </div>
              </div>

            </div>

            <div class="card-accent bg-success"></div>

          </div>
        </div>


        <!-- Purchase -->
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="card summary-card h-100 border-0 shadow-sm">

            <div class="card-body d-flex align-items-center">

              <div
                class="summary-icon bg-warning-subtle text-warning me-3"
              >
                <i class="bi bi-cart-check fs-4"></i>
              </div>

              <div class="summary-content">

                <div class="text-muted small fw-medium mb-1">
                  Purchase
                </div>

                <div class="fs-5 fw-bold text-warning-emphasis">
                  {{ money(summary.purchase) }}
                </div>

              </div>

            </div>

            <div class="card-accent bg-warning"></div>

          </div>
        </div>


        <!-- Purchase Return -->
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="card summary-card h-100 border-0 shadow-sm">

            <div class="card-body d-flex align-items-center">

              <div
                class="summary-icon bg-purple-subtle text-purple me-3"
              >
                <i class="bi bi-arrow-return-left fs-4"></i>
              </div>

              <div class="summary-content">

                <div class="text-muted small fw-medium mb-1">
                  Purchase Return
                </div>

                <div class="fs-5 fw-bold text-purple">
                  {{ money(summary.purchase_return) }}
                </div>

              </div>

            </div>

            <div class="card-accent bg-purple"></div>

          </div>
        </div>


        <!-- Food Cost -->
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="card summary-card h-100 border-0 shadow-sm">

            <div class="card-body d-flex align-items-center">

              <div
                class="summary-icon bg-orange-subtle text-orange me-3"
              >
                <i class="bi bi-basket2 fs-4"></i>
              </div>

              <div class="summary-content">

                <div class="text-muted small fw-medium mb-1">
                  Food Cost
                </div>

                <div class="fs-5 fw-bold text-orange">
                  {{ money(summary.food_cost) }}
                </div>

              </div>

            </div>

            <div class="card-accent bg-orange"></div>

          </div>
        </div>


        <!-- Operating Expense -->
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="card summary-card h-100 border-0 shadow-sm">

            <div class="card-body d-flex align-items-center">

              <div
                class="summary-icon bg-danger-subtle text-danger me-3"
              >
                <i class="bi bi-credit-card fs-4"></i>
              </div>

              <div class="summary-content">

                <div class="text-muted small fw-medium mb-1">
                  Expense
                </div>

                <div class="fs-5 fw-bold text-danger">
                  {{ money(summary.expense) }}
                </div>

              </div>

            </div>

            <div class="card-accent bg-danger"></div>

          </div>
        </div>


        <!-- Net Profit -->
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="card summary-card h-100 border-0 shadow-sm">

            <div class="card-body d-flex align-items-center">

              <div
                class="summary-icon bg-primary-subtle text-primary me-3"
              >
                <i class="bi bi-graph-up-arrow fs-4"></i>
              </div>

              <div class="summary-content">

                <div class="text-muted small fw-medium mb-1">
                  Net Profit
                </div>

                <div
                  class="fs-5 fw-bold"
                  :class="
                    summary.profit >= 0
                      ? 'text-primary'
                      : 'text-danger'
                  "
                >
                  {{ money(summary.profit) }}
                </div>

              </div>

            </div>

            <div
              class="card-accent"
              :class="
                summary.profit >= 0
                  ? 'bg-primary'
                  : 'bg-danger'
              "
            ></div>

          </div>
        </div>


        <!-- Total Assets -->
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="card summary-card h-100 border-0 shadow-sm">

            <div class="card-body d-flex align-items-center">

              <div
                class="summary-icon bg-info-subtle text-info me-3"
              >
                <i class="bi bi-building fs-4"></i>
              </div>

              <div class="summary-content">

                <div class="text-muted small fw-medium mb-1">
                  Total Assets
                </div>

                <div class="fs-5 fw-bold text-dark">
                  {{ money(summary.assets) }}
                </div>

              </div>

            </div>

            <div class="card-accent bg-info"></div>

          </div>
        </div>

      </div>


      <!-- =======================================================
           Charts
      ======================================================== -->
      <div class="row g-4 mb-4">

        <!-- Revenue vs Expense -->
        <div class="col-xl-7 col-lg-12">

          <div class="card border-0 shadow-sm h-100">

            <div
              class="card-header bg-transparent border-0 pt-4 pb-0 px-4"
            >
              <h6 class="fw-semibold mb-1 text-dark">
                Revenue vs Expense
              </h6>

              <small class="text-muted">
                Revenue, total cost and net profit
              </small>
            </div>


            <div class="card-body px-4 pb-4">

              <div class="dashboard-chart dashboard-bar-chart">

                <RevenueExpenseChart
                    :key="`${summary.revenue}-${summary.food_cost}-${summary.expense}-${summary.profit}`"
                    :revenue="summary.revenue"
                    :food-cost="summary.food_cost"
                    :expense="summary.expense"
                    :profit="summary.profit"
                />

              </div>

            </div>

          </div>

        </div>


        <!-- Payment Position -->
        <div class="col-xl-5 col-lg-12">

          <div class="card border-0 shadow-sm h-100">

            <div
              class="card-header bg-transparent border-0 pt-4 pb-0 px-4"
            >
              <h6 class="fw-semibold mb-1 text-dark">
                Payment Position
              </h6>

              <small class="text-muted">
                Cash, bank and digital payment balances
              </small>
            </div>


            <div class="card-body px-4 pb-4">

              <div class="dashboard-chart dashboard-pie-chart">

                <PaymentPositionChart
                  :payments="dashboard.payment_position"
                />

              </div>

            </div>

          </div>

        </div>

      </div>


      <!-- =======================================================
           Expense Breakdown
      ======================================================== -->
      <div
        v-if="dashboard.expense_breakdown?.length"
        class="card border-0 shadow-sm mb-4"
      >

        <div
          class="card-header bg-transparent border-0 pt-4 pb-0 px-4"
        >

          <h6 class="fw-semibold mb-1 text-dark">
            Expense Breakdown
          </h6>

          <small class="text-muted">
            Expense and food-cost account distribution
          </small>

        </div>


        <div class="card-body px-4 pb-4">

          <div class="expense-chart-wrapper">

            <ExpenseBreakdownChart
              :expenses="dashboard.expense_breakdown"
            />

          </div>

        </div>

      </div>


      <!-- =======================================================
           Recent Journals
      ======================================================== -->
      <div
        v-if="dashboard.recent_journals?.length"
        class="card border-0 shadow-sm"
      >

        <div
          class="card-header bg-transparent border-0 pt-4 pb-2 px-4"
        >

          <div>

            <h6 class="fw-semibold mb-1 text-dark">
              Recent Journal Entries
            </h6>

            <small class="text-muted">
              Latest accounting transactions
            </small>

          </div>

        </div>


        <div class="card-body px-0 pb-0">

          <RecentJournalTable
            :journals="dashboard.recent_journals"
          />

        </div>

      </div>

    </div>

  </section>
</template>


<script setup>
import {
  ref,
  watch,
} from 'vue'

import reportService from '@/services/reportService'

import RevenueExpenseChart
  from './charts/RevenueExpenseChart.vue'

import PaymentPositionChart
  from './charts/PaymentPositionChart.vue'

import ExpenseBreakdownChart
  from './charts/ExpenseBreakdownChart.vue'

import RecentJournalTable
  from './charts/RecentJournalTable.vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

  filters: {

    type: Object,

    default: () => ({}),

  },

})


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const loading =
  ref(false)


const error =
  ref('')


const summary =
  ref({

    revenue: 0,

    purchase: 0,

    purchase_return: 0,

    food_cost: 0,

    expense: 0,

    profit: 0,

    assets: 0,

  })


const dashboard =
  ref({

    payment_position: [],

    expense_breakdown: [],

    recent_journals: [],

  })


/*
|--------------------------------------------------------------------------
| Load Dashboard
|--------------------------------------------------------------------------
*/

async function loadDashboard()
{

  loading.value =
    true


  error.value =
    ''


  try {

    const response =
      await reportService
        .getAccountingDashboard({

          ...props.filters,

        })


    const data =
      response?.data
      ??
      {}


    const apiSummary =
      data.summary
      ??
      {}


    /*
    |--------------------------------------------------------------------------
    | Financial Summary
    |--------------------------------------------------------------------------
    */

    summary.value = {

      revenue:
        Number(
          apiSummary.revenue
          ??
          0
        ),

      purchase:
        Number(
          apiSummary.purchase
          ??
          0
        ),

      purchase_return:
        Number(
          apiSummary.purchase_return
          ??
          0
        ),

      food_cost:
        Number(
          apiSummary.food_cost
          ??
          0
        ),

      expense:
        Number(
          apiSummary.expense
          ??
          0
        ),

      profit:
        Number(
          apiSummary.profit
          ??
          0
        ),

      assets:
        Number(
          apiSummary.assets
          ??
          0
        ),

    }


    /*
    |--------------------------------------------------------------------------
    | Dashboard Detail Data
    |--------------------------------------------------------------------------
    */

    dashboard.value = {

      payment_position:
        Array.isArray(
          data.payment_position
        )
          ? data.payment_position
          : [],


      expense_breakdown:
        Array.isArray(
          data.expense_breakdown
        )
          ? data.expense_breakdown
          : [],


      recent_journals:
        Array.isArray(
          data.recent_journals
        )
          ? data.recent_journals
          : [],

    }

  }
  catch (
    requestError
  ) {

    console.error(
      'Accounting dashboard error:',
      requestError
    )


    error.value =

      requestError
        ?.response
        ?.data
        ?.message

      ??

      requestError
        ?.message

      ??

      'Failed to load accounting dashboard.'

  }
  finally {

    loading.value =
      false

  }

}


/*
|--------------------------------------------------------------------------
| Filter Watcher
|--------------------------------------------------------------------------
*/

watch(

  () => props.filters,

  () => {

    loadDashboard()

  },

  {
    deep: true,
    immediate: true,
  }

)


/*
|--------------------------------------------------------------------------
| Money
|--------------------------------------------------------------------------
*/

function money(
  value
) {

  return (

    '৳ '

    +

    Number(
      value
      ||
      0
    )
      .toLocaleString(
        'en-BD',
        {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        }
      )

  )

}
</script>


<style scoped>
.accounting-dashboard {
  width: 100%;
}


/* =========================================================
   Summary Cards
========================================================= */

.summary-card {
  position: relative;
  border-radius: 14px;
  overflow: hidden;

  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease;
}


.summary-card:hover {
  transform: translateY(-3px);

  box-shadow:
    0 10px 28px rgba(0, 0, 0, 0.09)
    !important;
}


.summary-card .card-body {
  min-height: 92px;
}


.summary-content {
  min-width: 0;
}


.summary-icon {
  width: 50px;
  height: 50px;

  border-radius: 13px;

  display: flex;

  align-items: center;

  justify-content: center;

  flex-shrink: 0;
}


.card-accent {
  position: absolute;

  left: 0;

  top: 0;

  bottom: 0;

  width: 4px;
}


/* =========================================================
   Cards
========================================================= */

.card {
  border-radius: 14px;
}


.card-header {
  border-radius: 14px 14px 0 0 !important;
}


/* =========================================================
   Chart Containers
========================================================= */

.dashboard-chart {
  position: relative;

  width: 100%;

  overflow: visible;
}


/*
|--------------------------------------------------------------------------
| BAR CHART
|--------------------------------------------------------------------------
*/

.dashboard-bar-chart {
  height: 340px;

  min-height: 340px;
}


/*
|--------------------------------------------------------------------------
| PIE / DOUGHNUT CHART
|--------------------------------------------------------------------------
|
| Important:
|
| Do NOT use max-height here.
| Chart.js needs an actual container height.
|
*/

.dashboard-pie-chart {
  width: 100%;

  height: 340px;

  min-height: 340px;

  display: flex;

  align-items: center;

  justify-content: center;

  overflow: visible;
}


/*
|--------------------------------------------------------------------------
| Expense Chart
|--------------------------------------------------------------------------
*/

.expense-chart-wrapper {
  position: relative;

  width: 100%;

  height: 360px;

  min-height: 360px;

  overflow: visible;
}


/*
|--------------------------------------------------------------------------
| Force child Chart.js canvas to use parent dimensions
|--------------------------------------------------------------------------
*/

.dashboard-chart :deep(canvas),
.expense-chart-wrapper :deep(canvas) {

  width: 100% !important;

  max-width: 100% !important;

  height: 100% !important;

  max-height: 100% !important;

}


/* =========================================================
   Custom colours
========================================================= */

.bg-success-subtle {
  background-color:
    rgba(
      25,
      135,
      84,
      0.12
    );
}


.bg-danger-subtle {
  background-color:
    rgba(
      220,
      53,
      69,
      0.12
    );
}


.bg-primary-subtle {
  background-color:
    rgba(
      13,
      110,
      253,
      0.12
    );
}


.bg-info-subtle {
  background-color:
    rgba(
      13,
      202,
      240,
      0.12
    );
}


.bg-warning-subtle {
  background-color:
    rgba(
      255,
      193,
      7,
      0.15
    );
}


.bg-purple-subtle {
  background-color:
    rgba(
      111,
      66,
      193,
      0.12
    );
}


.bg-orange-subtle {
  background-color:
    rgba(
      253,
      126,
      20,
      0.12
    );
}


.text-purple {
  color: #6f42c1 !important;
}


.bg-purple {
  background-color: #6f42c1 !important;
}


.text-orange {
  color: #fd7e14 !important;
}


.bg-orange {
  background-color: #fd7e14 !important;
}


/* =========================================================
   Responsive
========================================================= */

@media (
  max-width: 991.98px
) {

  .dashboard-bar-chart {
    height: 320px;

    min-height: 320px;
  }


  .dashboard-pie-chart {
    height: 360px;

    min-height: 360px;
  }


  .expense-chart-wrapper {
    height: 340px;

    min-height: 340px;
  }

}


@media (
  max-width: 575.98px
) {

  .summary-card .card-body {
    min-height: 84px;
  }


  .dashboard-bar-chart {
    height: 300px;

    min-height: 300px;
  }


  .dashboard-pie-chart {
    height: 330px;

    min-height: 330px;
  }


  .expense-chart-wrapper {
    height: 320px;

    min-height: 320px;
  }

}
</style>