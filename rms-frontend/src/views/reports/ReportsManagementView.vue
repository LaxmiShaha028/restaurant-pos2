<template>

<section class="reports-page">

    <!-- =========================================================
         Page Header
    ========================================================== -->

    <header class="reports-page-header">

        <div class="reports-header-content">

            <div class="reports-header-icon">

                <i class="bi bi-bar-chart-line-fill"></i>

            </div>


            <div>

                <span class="reports-header-eyebrow">
                    BUSINESS INTELLIGENCE
                </span>


                <h1 class="reports-page-title">
                    Reports & Analytics
                </h1>


                <p class="reports-page-subtitle">
                    Monitor sales, expenses, purchases, inventory
                    and employee attendance from one place.
                </p>

            </div>

        </div>


        <div class="reports-header-meta">

            <div class="reports-header-meta-item">

                <i class="bi bi-calendar3"></i>

                <span>
                    {{ formattedFilterDate }}
                </span>

            </div>

        </div>

    </header>


    <!-- =========================================================
         Global Date Filter
    ========================================================== -->

    <section class="reports-filter-card">

        <div class="reports-section-heading">

            <div class="reports-section-icon filter-icon">

                <i class="bi bi-funnel-fill"></i>

            </div>


            <div>

                <h2>
                    Report Filters
                </h2>

                <p>
                    Select a date range to update all reports.
                </p>

            </div>

        </div>


        <div class="reports-filter-grid">

            <!-- From Date -->

            <div class="reports-filter-group">

                <label for="report-date-from">

                    From Date

                </label>


                <div class="reports-input-wrapper">

                    <i class="bi bi-calendar-event"></i>

                    <input
                        id="report-date-from"
                        type="date"
                        class="form-control"
                        v-model="filters.date_from"
                        :max="filters.date_to || undefined"
                    />

                </div>

            </div>


            <!-- To Date -->

            <div class="reports-filter-group">

                <label for="report-date-to">

                    To Date

                </label>


                <div class="reports-input-wrapper">

                    <i class="bi bi-calendar-check"></i>

                    <input
                        id="report-date-to"
                        type="date"
                        class="form-control"
                        v-model="filters.date_to"
                        :min="filters.date_from || undefined"
                    />

                </div>

            </div>


            <!-- Actions -->

            <div class="reports-filter-actions">

                <button
                    type="button"
                    class="reports-primary-button"
                    :disabled="loadingSummary"
                    @click="applyFilter"
                >

                    <span
                        v-if="loadingSummary"
                        class="reports-button-spinner"
                    ></span>


                    <i
                        v-else
                        class="bi bi-search"
                    ></i>


                    <span>

                        {{
                            loadingSummary
                                ? 'Loading...'
                                : 'Apply Filter'
                        }}

                    </span>

                </button>


                <button
                    type="button"
                    class="reports-secondary-button"
                    :disabled="loadingSummary"
                    @click="resetFilter"
                >

                    <i class="bi bi-arrow-counterclockwise"></i>

                    Reset

                </button>

            </div>

        </div>


        <!-- Date Validation -->

        <div
            v-if="dateError"
            class="reports-filter-error"
        >

            <i class="bi bi-exclamation-circle-fill"></i>

            {{ dateError }}

        </div>

    </section>


    <!-- =========================================================
         KPI / Summary Cards
    ========================================================== -->

    <section class="reports-summary-grid">

        <!-- Net Sales -->

        <article class="reports-summary-card sales-card">

            <div class="reports-summary-top">

                <div>

                    <span class="reports-summary-label">
                        Net Sales
                    </span>

                    <strong class="reports-summary-value">
                        {{ money(summary.net_sales) }}
                    </strong>

                </div>


                <div class="reports-summary-icon">

                    <i class="bi bi-graph-up-arrow"></i>

                </div>

            </div>


            <div class="reports-summary-footer">

                <span>
                    Revenue generated
                </span>

                <i class="bi bi-arrow-up-right"></i>

            </div>

        </article>


        <!-- Expense -->

        <article class="reports-summary-card expense-card">

            <div class="reports-summary-top">

                <div>

                    <span class="reports-summary-label">
                        Total Expense
                    </span>

                    <strong class="reports-summary-value">
                        {{ money(expense.total_amount) }}
                    </strong>

                </div>


                <div class="reports-summary-icon">

                    <i class="bi bi-wallet2"></i>

                </div>

            </div>


            <div class="reports-summary-footer">

                <span>
                    Business expenses
                </span>

                <i class="bi bi-receipt"></i>

            </div>

        </article>


        <!-- Orders -->

        <article class="reports-summary-card orders-card">

            <div class="reports-summary-top">

                <div>

                    <span class="reports-summary-label">
                        Total Orders
                    </span>

                    <strong class="reports-summary-value">
                        {{ summary.total_orders || 0 }}
                    </strong>

                </div>


                <div class="reports-summary-icon">

                    <i class="bi bi-bag-check"></i>

                </div>

            </div>


            <div class="reports-summary-footer">

                <span>
                    Orders processed
                </span>

                <i class="bi bi-cart-check"></i>

            </div>

        </article>


        <!-- Collection -->

        <article class="reports-summary-card collection-card">

            <div class="reports-summary-top">

                <div>

                    <span class="reports-summary-label">
                        Collection
                    </span>

                    <strong class="reports-summary-value">
                        {{ money(summary.collected_amount) }}
                    </strong>

                </div>


                <div class="reports-summary-icon">

                    <i class="bi bi-cash-coin"></i>

                </div>

            </div>


            <div class="reports-summary-footer">

                <span>
                    Payments collected
                </span>

                <i class="bi bi-check2-circle"></i>

            </div>

        </article>

    </section>


    <!-- =========================================================
         Reports Workspace
    ========================================================== -->

    <section class="reports-workspace">

        <!-- Workspace Header -->

        <div class="reports-workspace-header">

            <div>

                <span class="reports-workspace-eyebrow">
                    REPORT CENTER
                </span>

                <h2>
                    Detailed Reports
                </h2>

                <p>
                    Explore detailed business and accounting
                    reports using the sections below.
                </p>

            </div>


            <div class="reports-current-range">

                <i class="bi bi-calendar-range"></i>

                <span>

                    {{ formattedFilterDate }}

                </span>

            </div>

        </div>


        <!-- =====================================================
             Report Group Switch
        ====================================================== -->

        <div class="report-group-switch-wrapper">

            <div class="report-group-switch">

                <!-- Business Reports -->

                <button
                    type="button"
                    class="report-group-button"
                    :class="{
                        active:
                            activeReportGroup === 'business'
                    }"
                    @click="openBusinessReports"
                >

                    <i class="bi bi-bar-chart-fill"></i>

                    <span>
                        Business Reports
                    </span>

                </button>


                <!-- Accounting Reports -->

                <button
                    type="button"
                    class="report-group-button"
                    :class="{
                        active:
                            activeReportGroup === 'accounting'
                    }"
                    @click="openAccountingReports"
                >

                    <i class="bi bi-calculator-fill"></i>

                    <span>
                        Accounting Reports
                    </span>

                </button>

            </div>

        </div>


        <!-- =====================================================
             Business Report Tabs
        ====================================================== -->

        <div
            v-if="
                activeReportGroup === 'business'
            "
            class="reports-tabs-wrapper"
        >

            <div class="reports-tabs">

                <button
                    v-for="
                        tab in businessTabs
                    "
                    :key="tab.key"
                    type="button"
                    class="reports-tab"
                    :class="{
                        active:
                            activeTab === tab.key
                    }"
                    @click="
                        activeTab = tab.key
                    "
                >

                    <span class="reports-tab-icon">

                        <i
                            class="bi"
                            :class="tab.icon"
                        ></i>

                    </span>


                    <span class="reports-tab-content">

                        <strong>
                            {{ tab.label }}
                        </strong>

                        <small>
                            {{ tab.description }}
                        </small>

                    </span>


                    <i
                        class="bi bi-chevron-right reports-tab-arrow"
                    ></i>

                </button>

            </div>

        </div>


        <!-- =====================================================
             Accounting Report Tabs
        ====================================================== -->

        <div
            v-else
            class="reports-tabs-wrapper"
        >

            <div class="reports-tabs accounting-tabs">

                <button
                    v-for="
                        tab in accountingTabs
                    "
                    :key="tab.key"
                    type="button"
                    class="reports-tab accounting-report-tab"
                    :class="{
                        active:
                            activeTab === tab.key
                    }"
                    @click="
                        activeTab = tab.key
                    "
                >

                    <span class="reports-tab-icon">

                        <i
                            class="bi"
                            :class="tab.icon"
                        ></i>

                    </span>


                    <span class="reports-tab-content">

                        <strong>
                            {{ tab.label }}
                        </strong>

                        <small>
                            {{ tab.description }}
                        </small>

                    </span>


                    <i
                        class="bi bi-chevron-right reports-tab-arrow"
                    ></i>

                </button>

            </div>

        </div>


        <!-- =====================================================
             Report Content
        ====================================================== -->

        <div class="reports-content">

            <!-- =================================================
                 BUSINESS REPORTS
            ================================================== -->

            <template
                v-if="
                    activeReportGroup === 'business'
                "
            >

            <!-- Business Overview -->

    <BusinessOverview
        v-if="
            activeTab === 'business-overview'
        "
        :filters="appliedFilters"
/>

                <!-- Orders -->

                <OrdersReport
                    v-if="
                        activeTab === 'orders'
                    "
                    :filters="appliedFilters"
                />


                <!-- Expenses -->

                <ExpensesReport
                    v-else-if="
                        activeTab === 'expenses'
                    "
                    :filters="appliedFilters"
                />


                <!-- Purchase -->

                <PurchaseReport
                    v-else-if="
                        activeTab === 'purchase'
                    "
                    :filters="appliedFilters"
                />


                <!-- Stock -->

                <StockReport
                    v-else-if="
                        activeTab === 'stock'
                    "
                    :filters="appliedFilters"
                />


                <!-- Attendance -->

                <AttendanceReport
                    v-else-if="
                        activeTab === 'attendance'
                    "
                    :filters="appliedFilters"
                />

            </template>


            <!-- =================================================
                 ACCOUNTING REPORTS
            ================================================== -->

            <template
                v-else
            >
                <!-- Accounting Dashboard -->

                <AccountingDashboard
                    v-if="
                        activeTab === 'accounting-dashboard'
                    "
                    :filters="appliedFilters"
                />


                <!-- Owner Ledger -->

                <OwnerLedger
                    v-else-if="
                        activeTab === 'owner-ledger'
                    "
                    :filters="appliedFilters"
                />


                <!-- Profit & Loss -->

                <ProfitLoss
                    v-else-if="
                        activeTab === 'profit-loss'
                    "
                    :filters="appliedFilters"
                />


                <!-- Balance Sheet -->

                <BalanceSheet
                    v-else-if="
                        activeTab === 'balance-sheet'
                    "
                    :filters="appliedFilters"
                />


                <!-- Trial Balance -->

                <TrialBalance
                    v-else-if="
                        activeTab === 'trial-balance'
                    "
                    :filters="appliedFilters"
                />


                <!-- General Ledger -->

                <GeneralLedger
                    v-else-if="
                        activeTab === 'general-ledger'
                    "
                    :filters="appliedFilters"
                />

            </template>

        </div>

    </section>

</section>

</template>


<script setup>

import {
    computed,
    onMounted,
    ref,
    watch,
} from 'vue'


import {
    useRoute,
    useRouter,
} from 'vue-router'


import reportService
    from '@/services/reportService'


/*
|--------------------------------------------------------------------------
| Business Report Components
|--------------------------------------------------------------------------
*/

import OrdersReport
    from '@/components/reports/OrdersReport.vue'


import ExpensesReport
    from '@/components/reports/ExpensesReport.vue'


import PurchaseReport
    from '@/components/reports/PurchaseReport.vue'


import StockReport
    from '@/components/reports/StockReport.vue'


import AttendanceReport
    from '@/components/reports/AttendanceReport.vue'


/*
|--------------------------------------------------------------------------
| Accounting Report Components
|--------------------------------------------------------------------------
*/

import AccountingDashboard
    from '@/components/reports/accounting/AccountingDashboard.vue'

import BusinessOverview
    from '@/views/accounting/BusinessOverview.vue'


import OwnerLedger
    from '@/components/reports/accounting/OwnerLedger.vue'


import ProfitLoss
    from '@/components/reports/accounting/ProfitLossReport.vue'


import BalanceSheet
    from '@/components/reports/accounting/BalanceSheetReport.vue'


import TrialBalance
    from '@/components/reports/accounting/TrialBalanceReport.vue'


import GeneralLedger
    from '@/components/reports/accounting/GeneralLedgerReport.vue'


/*
|--------------------------------------------------------------------------
| Router
|--------------------------------------------------------------------------
*/

const router =
    useRouter()


const route =
    useRoute()


/*
|--------------------------------------------------------------------------
| Active Report Group
|--------------------------------------------------------------------------
*/

const activeReportGroup =
    ref('business')


/*
|--------------------------------------------------------------------------
| Active Tab
|--------------------------------------------------------------------------
*/

const activeTab =
    ref('orders')


/*
|--------------------------------------------------------------------------
| Today
|--------------------------------------------------------------------------
*/

const today =
    new Date()
        .toISOString()
        .substring(
            0,
            10
        )


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const currentDate = new Date()
    .toISOString()
    .slice(0, 10)

const filters = ref({
    date_from: currentDate,
    date_to: currentDate,
})

const appliedFilters = ref({
    date_from: currentDate,
    date_to: currentDate,
})


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const summary =
    ref({

        net_sales:
            0,

        total_orders:
            0,

        collected_amount:
            0,

    })


/*
|--------------------------------------------------------------------------
| Expense
|--------------------------------------------------------------------------
*/

const expense =
    ref({

        total_amount:
            0,

    })


/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

const loadingSummary =
    ref(false)


/*
|--------------------------------------------------------------------------
| Date Error
|--------------------------------------------------------------------------
*/

const dateError =
    ref('')


/*
|--------------------------------------------------------------------------
| Business Report Tabs
|--------------------------------------------------------------------------
*/

const businessTabs = [

    {
    key:'business-overview',

    routeName:'business-overview',

    label:'Business Overview',

    description:
        'Complete restaurant performance overview',

    icon:
        'bi-speedometer2',
},
    {
        key:
            'orders',

        label:
            'Orders',

        description:
            'Sales & order activity',

        icon:
            'bi-receipt-cutoff',
    },


    {
        key:
            'expenses',

        label:
            'Expenses',

        description:
            'Business spending',

        icon:
            'bi-wallet2',
    },


    {
        key:
            'purchase',

        label:
            'Purchase',

        description:
            'Supplier purchases',

        icon:
            'bi-box-seam',
    },


    {
        key:
            'stock',

        label:
            'Stock',

        description:
            'Inventory movement',

        icon:
            'bi-boxes',
    },


    {
        key:
            'attendance',

        label:
            'Attendance',

        description:
            'Staff history',

        icon:
            'bi-calendar-check',
    },

]


/*
|--------------------------------------------------------------------------
| Accounting Report Tabs
|--------------------------------------------------------------------------
*/

const accountingTabs = [

    {
        key:
            'accounting-dashboard',

        routeName:
            'accounting-dashboard',

        label:
            'Dashboard',

        description:
            'Accounting overview',

        icon:
            'bi-speedometer2',
    },


    {
        key:
            'owner-ledger',

        routeName:
            'owner-ledger',

        label:
            'Owner Ledger',

        description:
            'Owner income & contribution',

        icon:
            'bi-person-lines-fill',
    },


    {
        key:
            'profit-loss',

        routeName:
            'profit-loss',

        label:
            'Profit & Loss',

        description:
            'Income & expense analysis',

        icon:
            'bi-graph-up-arrow',
    },


    {
        key:
            'balance-sheet',

        routeName:
            'balance-sheet',

        label:
            'Balance Sheet',

        description:
            'Assets & liabilities',

        icon:
            'bi-bank',
    },


    {
        key:
            'trial-balance',

        routeName:
            'trial-balance',

        label:
            'Trial Balance',

        description:
            'Debit credit verification',

        icon:
            'bi-calculator',
    },


    {
        key:
            'general-ledger',

        routeName:
            'general-ledger',

        label:
            'General Ledger',

        description:
            'Account transactions',

        icon:
            'bi-journal-text',
    },

]


/*
|--------------------------------------------------------------------------
| Accounting Route Names
|--------------------------------------------------------------------------
*/

const accountingRouteNames = [

    'accounting-dashboard',

    'owner-ledger',

    'profit-loss',

    'balance-sheet',

    'trial-balance',

    'general-ledger',

]


/*
|--------------------------------------------------------------------------
| Sync Active Report State From Route
|--------------------------------------------------------------------------
*/

function syncReportStateFromRoute()
{

    const currentRouteName =
        route.name


    /*
    |--------------------------------------------------------------------------
    | Accounting Route
    |--------------------------------------------------------------------------
    */

    if (
        accountingRouteNames.includes(
            currentRouteName
        )
    ) {

        activeReportGroup.value =
            'accounting'


        activeTab.value =
            currentRouteName


        return

    }


    /*
    |--------------------------------------------------------------------------
    | Business Route
    |--------------------------------------------------------------------------
    */

    activeReportGroup.value =
        'business'


    /*
    |--------------------------------------------------------------------------
    | Keep Existing Business Tab
    |--------------------------------------------------------------------------
    */

    if (
        !businessTabs.some(
            tab =>
                tab.key ===
                activeTab.value
        )
    ) {

        activeTab.value =
    'business-overview'

    }

}


/*
|--------------------------------------------------------------------------
| Open Business Reports
|--------------------------------------------------------------------------
*/

async function openBusinessReports()
{

    activeReportGroup.value =
        'business'


    activeTab.value =
        'business-overview'


    /*
    |--------------------------------------------------------------------------
    | If current route is an accounting route,
    | return to main reports page.
    |--------------------------------------------------------------------------
    */

    if (
        route.name !== 'reports'
    ) {

        await router.push({

            name:
                'reports',

        })

    }

}


/*
|--------------------------------------------------------------------------
| Open Accounting Reports
|--------------------------------------------------------------------------
*/

async function openAccountingReports()
{

    activeReportGroup.value =
        'accounting'


    activeTab.value =
    'accounting-dashboard'


    /*
    |--------------------------------------------------------------------------
    | Keep existing accounting route structure.
    |
    | The route is only used to enter the accounting section.
    | After entering, tabs render components directly.
    |--------------------------------------------------------------------------
    */

    if (
        route.name !== 'accounting-dashboard'
    ) {

        await router.push({

            name:
                'accounting-dashboard',

        })

    }

}


/*
|--------------------------------------------------------------------------
| Watch Route Changes
|--------------------------------------------------------------------------
*/

watch(

    () =>
        route.name,

    () => {

        syncReportStateFromRoute()

    },

    {
        immediate:
            true,
    }

)


/*
|--------------------------------------------------------------------------
| Active Accounting Report Label
|--------------------------------------------------------------------------
*/

const activeAccountingReportLabel =
    computed(() => {

        const report =
            accountingTabs.find(
                tab =>
                    tab.key ===
                    activeTab.value
            )


        return report
            ?.label
            ||
            'Accounting Report'

    })


/*
|--------------------------------------------------------------------------
| Formatted Date Range
|--------------------------------------------------------------------------
*/

const formattedFilterDate =
    computed(() => {

        const from =
            formatDate(
                filters.value.date_from
            )


        const to =
            formatDate(
                filters.value.date_to
            )


        if (
            !filters.value.date_from
            &&
            !filters.value.date_to
        ) {

            return 'All dates'

        }


        if (
            from === to
        ) {

            return from

        }


        return `${from} — ${to}`

    })


/*
|--------------------------------------------------------------------------
| Validate Dates
|--------------------------------------------------------------------------
*/

function validateDates()
{

    dateError.value =
        ''


    const from =
        filters.value.date_from


    const to =
        filters.value.date_to


    if (
        !from
        ||
        !to
    ) {

        dateError.value =
            'Please select both From Date and To Date.'

        return false

    }


    if (
        to < from
    ) {

        dateError.value =
            'To Date cannot be earlier than From Date.'

        return false

    }


    return true

}


/*
|--------------------------------------------------------------------------
| Load Summary
|--------------------------------------------------------------------------
*/

async function loadSummary()
{

    if (
        !validateDates()
    ) {

        return

    }


    loadingSummary.value =
        true


    try {

        /*
        |--------------------------------------------------------------------------
        | Sales Summary
        |--------------------------------------------------------------------------
        */

        const sales =
            await reportService
                .getSummary(
                    filters.value
                )


        summary.value =
            {

                net_sales:
                    Number(
                        sales
                            ?.data
                            ?.net_sales
                        ||
                        0
                    ),

                total_orders:
                    Number(
                        sales
                            ?.data
                            ?.total_orders
                        ||
                        0
                    ),

                collected_amount:
                    Number(
                        sales
                            ?.data
                            ?.collected_amount
                        ||
                        0
                    ),

            }


        /*
        |--------------------------------------------------------------------------
        | Expense Summary
        |--------------------------------------------------------------------------
        */

        const exp =
            await reportService
                .getExpenseSummary(
                    filters.value
                )


        expense.value =
            {

                total_amount:
                    Number(
                        exp
                            ?.data
                            ?.total_amount
                        ||
                        0
                    ),

            }

    }
    catch (error) {

        console.error(
            'Reports summary error:',
            error
        )

    }
    finally {

        loadingSummary.value =
            false

    }

}


/*
|--------------------------------------------------------------------------
| Apply Filter
|--------------------------------------------------------------------------
*/

function applyFilter()
{
    if (!validateDates()) {
        return
    }


    const newFilters = {

        date_from:
            filters.value.date_from,

        date_to:
            filters.value.date_to,

    }


    console.log(
        'APPLYING REPORT FILTER:',
        newFilters
    )


    /*
    |--------------------------------------------------------------------------
    | Update Applied Filters
    |--------------------------------------------------------------------------
    */

    appliedFilters.value = {
        ...newFilters,
    }


    /*
    |--------------------------------------------------------------------------
    | Reload Summary Cards
    |--------------------------------------------------------------------------
    */

    loadSummary()

}


/*
|--------------------------------------------------------------------------
| Reset Filter
|--------------------------------------------------------------------------
*/

function resetFilter()
{

    filters.value = {

        date_from:
            today,

        date_to:
            today,

    }


    dateError.value =
        ''


    loadSummary()

}


/*
|--------------------------------------------------------------------------
| Format Date
|--------------------------------------------------------------------------
*/

function formatDate(
    value
) {

    if (!value) {

        return '—'

    }


    const date =
        new Date(
            `${value}T00:00:00`
        )


    if (
        Number.isNaN(
            date.getTime()
        )
    ) {

        return value

    }


    return new Intl.DateTimeFormat(
        'en-GB',
        {

            day:
                '2-digit',

            month:
                'short',

            year:
                'numeric',

        }
    ).format(
        date
    )

}


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
            value || 0
        ).toLocaleString(
            'en-BD',
            {

                minimumFractionDigits:
                    2,

                maximumFractionDigits:
                    2,

            }
        )

    )

}


/*
|--------------------------------------------------------------------------
| Initial Load
|--------------------------------------------------------------------------
*/

onMounted(
    () => {

        loadSummary()

    }
)

</script>


<style scoped>

/*
|--------------------------------------------------------------------------
| Main Page
|--------------------------------------------------------------------------
*/

.reports-page {

    width: 100%;

    padding:
        2px 0 28px;

    color:
        var(
            --text-primary,
            #0f172a
        );

}


/* ==========================================================================
   HEADER
   ========================================================================== */

.reports-page-header {
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

.reports-header-content {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 7px;
  flex: 1;
}

.reports-header-icon {
  display: inline-flex;
  width: 28px;
  height: 28px;
  flex: 0 0 28px;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  background: #eff6ff;
  color: #2563eb;
  font-size: 11px;
}

.reports-header-eyebrow {
  display: block;
  margin: 0 0 1px;
  color: #2563eb;
  font-size: 7px;
  font-weight: 700;
  line-height: 1.1;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}

.reports-page-title {
  margin: 0;
  color: #172033;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.2;
}

.reports-page-subtitle {
  max-width: 580px;
  margin: 2px 0 0;
  overflow: hidden;
  color: #667085;
  font-size: 8px;
  line-height: 1.25;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.reports-header-meta {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.reports-header-meta-item {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 5px 8px;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  background: #f8fafc;
  color: #667085;
  font-size: 8px;
  font-weight: 600;
}


/*
|--------------------------------------------------------------------------
| Filter Card
|--------------------------------------------------------------------------
*/

.reports-filter-card {

    padding:
        20px;

    margin-bottom:
        20px;

    border:
        1px solid
        rgba(
            148,
            163,
            184,
            0.18
        );

    border-radius:
        16px;

    background:
        var(
            --card-bg,
            #ffffff
        );

    box-shadow:
        0 6px 20px
        rgba(
            15,
            23,
            42,
            0.03
        );

}


.reports-section-heading {

    display:
        flex;

    align-items:
        center;

    gap:
        11px;

    margin-bottom:
        16px;

}


.reports-section-icon {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    width:
        38px;

    height:
        38px;

    border-radius:
        10px;

    font-size:
        15px;

}


.filter-icon {

    color:
        #1d4ed8;

    background:
        #eff6ff;

}


.reports-section-heading h2 {

    margin:
        0;

    font-size:
        15px;

    font-weight:
        750;

}


.reports-section-heading p {

    margin:
        3px 0 0;

    color:
        var(
            --text-muted,
            #64748b
        );

    font-size:
        11px;

}


.reports-filter-grid {

    display:
        grid;

    grid-template-columns:
        minmax(
            200px,
            1fr
        )
        minmax(
            200px,
            1fr
        )
        auto;

    gap:
        13px;

    align-items:
        end;

}


.reports-filter-group label {

    display:
        block;

    margin-bottom:
        7px;

    font-size:
        11px;

    font-weight:
        700;

}


.reports-input-wrapper {

    position:
        relative;

}


.reports-input-wrapper i {

    position:
        absolute;

    top:
        50%;

    left:
        12px;

    z-index:
        2;

    color:
        #64748b;

    transform:
        translateY(-50%);

    font-size:
        13px;

}


.reports-input-wrapper .form-control {

    min-height:
        42px;

    padding-left:
        35px;

    border:
        1px solid
        rgba(
            148,
            163,
            184,
            0.35
        );

    border-radius:
        10px;

    background:
        var(
            --input-bg,
            #ffffff
        );

    box-shadow:
        none;

}


.reports-input-wrapper .form-control:focus {

    border-color:
        #93c5fd;

    box-shadow:
        0 0 0 3px
        rgba(
            37,
            99,
            235,
            0.08
        );

}


.reports-filter-actions {

    display:
        flex;

    gap:
        8px;

}


.reports-primary-button,
.reports-secondary-button {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        7px;

    min-height:
        42px;

    padding:
        0 15px;

    border-radius:
        10px;

    font-size:
        12px;

    font-weight:
        700;

    cursor:
        pointer;

    transition:
        all
        0.2s
        ease;

}


.reports-primary-button {

    border:
        0;

    color:
        #ffffff;

    background:
        #2563eb;

}


.reports-primary-button:hover {

    background:
        #1d4ed8;

    transform:
        translateY(-1px);

}


.reports-secondary-button {

    border:
        1px solid
        rgba(
            148,
            163,
            184,
            0.35
        );

    color:
        inherit;

    background:
        transparent;

}


.reports-secondary-button:hover {

    background:
        rgba(
            148,
            163,
            184,
            0.06
        );

}


.reports-primary-button:disabled,
.reports-secondary-button:disabled {

    cursor:
        not-allowed;

    opacity:
        0.55;

}


.reports-button-spinner {

    width:
        14px;

    height:
        14px;

    border:
        2px solid
        rgba(
            255,
            255,
            255,
            0.4
        );

    border-top-color:
        #ffffff;

    border-radius:
        50%;

    animation:
        reports-spin
        0.7s
        linear
        infinite;

}


.reports-filter-error {

    display:
        flex;

    align-items:
        center;

    gap:
        7px;

    margin-top:
        12px;

    padding:
        9px 11px;

    border:
        1px solid
        #fecaca;

    border-radius:
        9px;

    color:
        #991b1b;

    background:
        #fef2f2;

    font-size:
        11px;

}


/*
|--------------------------------------------------------------------------
| Summary Grid
|--------------------------------------------------------------------------
*/

.reports-summary-grid {

    display:
        grid;

    grid-template-columns:
        repeat(
            4,
            minmax(
                0,
                1fr
            )
        );

    gap:
        14px;

    margin-bottom:
        20px;

}


.reports-summary-card {

    position:
        relative;

    overflow:
        hidden;

    padding:
        18px;

    border:
        1px solid
        rgba(
            148,
            163,
            184,
            0.18
        );

    border-radius:
        15px;

    background:
        var(
            --card-bg,
            #ffffff
        );

    box-shadow:
        0 6px 20px
        rgba(
            15,
            23,
            42,
            0.03
        );

    transition:
        transform
        0.2s
        ease,
        box-shadow
        0.2s
        ease;

}


.reports-summary-card::before {

    position:
        absolute;

    top:
        0;

    left:
        0;

    width:
        100%;

    height:
        3px;

    content:
        "";

}


.sales-card::before {

    background:
        #2563eb;

}


.expense-card::before {

    background:
        #dc2626;

}


.orders-card::before {

    background:
        #7c3aed;

}


.collection-card::before {

    background:
        #059669;

}


.reports-summary-card:hover {

    transform:
        translateY(-2px);

    box-shadow:
        0 10px 25px
        rgba(
            15,
            23,
            42,
            0.07
        );

}


.reports-summary-top {

    display:
        flex;

    align-items:
        flex-start;

    justify-content:
        space-between;

    gap:
        10px;

}


.reports-summary-label {

    display:
        block;

    color:
        var(
            --text-muted,
            #64748b
        );

    font-size:
        10px;

    font-weight:
        700;

    text-transform:
        uppercase;

    letter-spacing:
        0.03em;

}


.reports-summary-value {

    display:
        block;

    margin-top:
        7px;

    font-size:
        21px;

    font-weight:
        800;

}


.reports-summary-icon {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    width:
        39px;

    height:
        39px;

    flex-shrink:
        0;

    border-radius:
        11px;

    background:
        rgba(
            37,
            99,
            235,
            0.08
        );

    color:
        #2563eb;

}


.expense-card .reports-summary-icon {

    color:
        #dc2626;

    background:
        rgba(
            220,
            38,
            38,
            0.08
        );

}


.orders-card .reports-summary-icon {

    color:
        #7c3aed;

    background:
        rgba(
            124,
            58,
            237,
            0.08
        );

}


.collection-card .reports-summary-icon {

    color:
        #059669;

    background:
        rgba(
            5,
            150,
            105,
            0.08
        );

}


.reports-summary-footer {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        10px;

    margin-top:
        14px;

    padding-top:
        11px;

    border-top:
        1px solid
        rgba(
            148,
            163,
            184,
            0.12
        );

    color:
        var(
            --text-muted,
            #64748b
        );

    font-size:
        10px;

}


/*
|--------------------------------------------------------------------------
| Workspace
|--------------------------------------------------------------------------
*/

.reports-workspace {

    overflow:
        hidden;

    border:
        1px solid
        rgba(
            148,
            163,
            184,
            0.18
        );

    border-radius:
        17px;

    background:
        var(
            --card-bg,
            #ffffff
        );

    box-shadow:
        0 8px 25px
        rgba(
            15,
            23,
            42,
            0.04
        );

}


.reports-workspace-header {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        20px;

    padding:
        20px 22px;

    border-bottom:
        1px solid
        rgba(
            148,
            163,
            184,
            0.14
        );

}


.reports-workspace-eyebrow {

    display:
        block;

    margin-bottom:
        3px;

    color:
        #64748b;

    font-size:
        9px;

    font-weight:
        800;

    letter-spacing:
        0.11em;

}


.reports-workspace-header h2 {

    margin:
        0;

    font-size:
        17px;

    font-weight:
        800;

}


.reports-workspace-header p {

    margin:
        5px 0 0;

    color:
        var(
            --text-muted,
            #64748b
        );

    font-size:
        11px;

}


.reports-current-range {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        7px;

    padding:
        9px 12px;

    border:
        1px solid
        rgba(
            37,
            99,
            235,
            0.15
        );

    border-radius:
        10px;

    color:
        #1d4ed8;

    background:
        #eff6ff;

    font-size:
        10px;

    font-weight:
        700;

    white-space:
        nowrap;

}


/*
|--------------------------------------------------------------------------
| Report Group Switch
|--------------------------------------------------------------------------
*/

.report-group-switch-wrapper {

    padding:
        14px 16px 0;

}


.report-group-switch {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        4px;

    padding:
        4px;

    border:
        1px solid
        rgba(
            148,
            163,
            184,
            0.2
        );

    border-radius:
        11px;

    background:
        #f8fafc;

}


.report-group-button {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        7px;

    min-height:
        36px;

    padding:
        0 14px;

    border:
        0;

    border-radius:
        8px;

    color:
        #64748b;

    background:
        transparent;

    font-size:
        11px;

    font-weight:
        700;

    cursor:
        pointer;

    transition:
        all
        0.2s
        ease;

}


.report-group-button:hover {

    color:
        #1d4ed8;

    background:
        rgba(
            37,
            99,
            235,
            0.05
        );

}


.report-group-button.active {

    color:
        #ffffff;

    background:
        #2563eb;

    box-shadow:
        0 2px 7px
        rgba(
            37,
            99,
            235,
            0.18
        );

}


.report-group-button i {

    font-size:
        11px;

}


/*
|--------------------------------------------------------------------------
| Tabs
|--------------------------------------------------------------------------
*/

.reports-tabs-wrapper {

    padding:
        14px 16px 0;

}


.reports-tabs {

    display:
        grid;

    grid-template-columns:
        repeat(
            5,
            minmax(
                0,
                1fr
            )
        );

    gap:
        8px;

}


.reports-tab {

    position:
        relative;

    display:
        flex;

    align-items:
        center;

    gap:
        9px;

    min-width:
        0;

    padding:
        11px 12px;

    border:
        1px solid
        rgba(
            148,
            163,
            184,
            0.18
        );

    border-radius:
        11px;

    color:
        var(
            --text-muted,
            #64748b
        );

    background:
        transparent;

    text-align:
        left;

    text-decoration:
        none;

    cursor:
        pointer;

    transition:
        all
        0.2s
        ease;

}


.reports-tab:hover {

    border-color:
        rgba(
            37,
            99,
            235,
            0.2
        );

    background:
        rgba(
            37,
            99,
            235,
            0.025
        );

}


.reports-tab.active {

    border-color:
        rgba(
            37,
            99,
            235,
            0.22
        );

    color:
        #1d4ed8;

    background:
        #eff6ff;

}


.reports-tab-icon {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    width:
        34px;

    height:
        34px;

    flex-shrink:
        0;

    border-radius:
        9px;

    color:
        #64748b;

    background:
        rgba(
            148,
            163,
            184,
            0.08
        );

}


.reports-tab.active .reports-tab-icon {

    color:
        #2563eb;

    background:
        rgba(
            37,
            99,
            235,
            0.1
        );

}


.reports-tab-content {

    min-width:
        0;

    display:
        flex;

    flex-direction:
        column;

}


.reports-tab-content strong {

    overflow:
        hidden;

    color:
        inherit;

    font-size:
        11px;

    white-space:
        nowrap;

    text-overflow:
        ellipsis;

}


.reports-tab-content small {

    overflow:
        hidden;

    margin-top:
        2px;

    color:
        var(
            --text-muted,
            #64748b
        );

    font-size:
        8px;

    white-space:
        nowrap;

    text-overflow:
        ellipsis;

}


.reports-tab-arrow {

    margin-left:
        auto;

    color:
        #94a3b8;

    font-size:
        9px;

}


.reports-tab.active .reports-tab-arrow {

    color:
        #2563eb;

}


/*
|--------------------------------------------------------------------------
| Accounting Tabs
|--------------------------------------------------------------------------
*/

.accounting-tabs {

    grid-template-columns:
        repeat(
            5,
            minmax(
                0,
                1fr
            )
        );

}


/*
|--------------------------------------------------------------------------
| Content
|--------------------------------------------------------------------------
*/

.reports-content {

    padding:
        18px;

}


/*
|--------------------------------------------------------------------------
| Animation
|--------------------------------------------------------------------------
*/

@keyframes reports-spin {

    to {

        transform:
            rotate(
                360deg
            );

    }

}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (
    max-width: 1200px
) {

    .reports-summary-grid {

        grid-template-columns:
            repeat(
                2,
                minmax(
                    0,
                    1fr
                )
            );

    }


    .reports-tabs {

        grid-template-columns:
            repeat(
                3,
                minmax(
                    0,
                    1fr
                )
            );

    }


    .accounting-tabs {

        grid-template-columns:
            repeat(
                3,
                minmax(
                    0,
                    1fr
                )
            );

    }

}


@media (
    max-width: 900px
) {

    .reports-page-header {

        align-items:
            flex-start;

        flex-direction:
            column;

    }


    .reports-header-meta {

        width:
            100%;

    }


    .reports-filter-grid {

        grid-template-columns:
            repeat(
                2,
                minmax(
                    0,
                    1fr
                )
            );

    }


    .reports-filter-actions {

        grid-column:
            1 / -1;

    }


    .reports-current-range {

        display:
            none;

    }

}


@media (
    max-width: 700px
) {

    .reports-tabs,
    .accounting-tabs {

        grid-template-columns:
            repeat(
                2,
                minmax(
                    0,
                    1fr
                )
            );

    }


    .reports-workspace-header {

        align-items:
            flex-start;

        flex-direction:
            column;

    }


    .report-group-switch {

        display:
            flex;

        width:
            100%;

    }


    .report-group-button {

        flex:
            1;

    }

}


@media (
    max-width: 575px
) {

    .reports-page-header {

        padding:
            18px;

        border-radius:
            14px;

    }


    .reports-header-content {

        align-items:
            flex-start;

    }


    .reports-header-icon {

        width:
            46px;

        height:
            46px;

        font-size:
            19px;

    }


    .reports-page-title {

        font-size:
            20px;

    }


    .reports-page-subtitle {

        font-size:
            11px;

    }


    .reports-filter-card {

        padding:
            16px;

    }


    .reports-filter-grid {

        grid-template-columns:
            1fr;

    }


    .reports-filter-actions {

        display:
            grid;

        grid-template-columns:
            repeat(
                2,
                minmax(
                    0,
                    1fr
                )
            );

        grid-column:
            auto;

    }


    .reports-summary-grid {

        grid-template-columns:
            1fr;

    }


    .report-group-switch-wrapper {

        padding:
            12px 12px 0;

    }


    .report-group-switch {

        width:
            100%;

        display:
            grid;

        grid-template-columns:
            1fr
            1fr;

    }


    .report-group-button {

        width:
            100%;

        padding:
            0 8px;

        font-size:
            10px;

    }


    .reports-tabs,
    .accounting-tabs {

        grid-template-columns:
            1fr;

    }


    .reports-content {

        padding:
            13px;

    }


    .reports-workspace {

        border-radius:
            14px;

    }

}

</style>


<style src="@/assets/css/reports/reports-compact.css"></style>