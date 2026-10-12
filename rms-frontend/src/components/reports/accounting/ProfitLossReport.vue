<template>

<section class="profit-loss-report">


    <!-- =========================================================
         Report Header
    ========================================================== -->

    <div
        class="d-flex justify-content-between align-items-center mb-3"
    >

        <div>

            <h5 class="fw-bold mb-1">

                Profit & Loss Statement

            </h5>


            <small class="text-secondary">

                Revenue and expense analysis

            </small>

        </div>


        <ReportExportButton

            type="profit-loss"

            :filters="filters"

        />

    </div>


    <!-- =========================================================
         Loading
    ========================================================== -->

    <div
        v-if="loading"
        class="text-center py-5"
    >

        Loading Profit & Loss...

    </div>


    <!-- =========================================================
         Error
    ========================================================== -->

    <div
        v-else-if="errorMessage"
        class="alert alert-danger"
    >

        <i class="bi bi-exclamation-circle me-1"></i>

        {{ errorMessage }}

    </div>


    <!-- =========================================================
         Report
    ========================================================== -->

    <div v-else>


        <!-- =====================================================
             Revenue
        ====================================================== -->

        <div class="card mb-3">


            <div class="card-header bg-success text-white">

                Revenue

            </div>


            <div class="table-responsive">

                <table class="table mb-0">


                    <thead>

                        <tr>

                            <th>
                                Account
                            </th>


                            <th class="text-end">
                                Amount
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <!-- Revenue Details -->

                        <tr
                            v-for="
                                item in report.revenue.details
                            "
                            :key="
                                item.account_id
                            "
                        >

                            <td>

                                {{ item.account_name }}

                            </td>


                            <td class="text-end">

                                {{ money(item.balance) }}

                            </td>

                        </tr>


                        <!-- Empty Revenue -->

                        <tr
                            v-if="
                                !report.revenue.details.length
                            "
                        >

                            <td
                                colspan="2"
                                class="text-center text-secondary py-3"
                            >

                                No revenue recorded for this period.

                            </td>

                        </tr>


                        <!-- Revenue Total -->

                        <tr>

                            <th>

                                Total Revenue

                            </th>


                            <th class="text-end">

                                {{ money(report.revenue.total) }}

                            </th>

                        </tr>


                    </tbody>

                </table>

            </div>

        </div>


        <!-- =====================================================
             Expenses
        ====================================================== -->

        <div class="card mb-3">


            <div class="card-header bg-danger text-white">

                Expenses

            </div>


            <div class="table-responsive">

                <table class="table mb-0">


                    <thead>

                        <tr>

                            <th>
                                Account
                            </th>


                            <th class="text-end">
                                Amount
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <!-- Expense Details -->

                        <tr
                            v-for="
                                item in report.expenses.details
                            "
                            :key="
                                item.account_id
                            "
                        >

                            <td>

                                {{ item.account_name }}

                            </td>


                            <td class="text-end">

                                {{ money(item.balance) }}

                            </td>

                        </tr>


                        <!-- Empty Expenses -->

                        <tr
                            v-if="
                                !report.expenses.details.length
                            "
                        >

                            <td
                                colspan="2"
                                class="text-center text-secondary py-3"
                            >

                                No expenses recorded for this period.

                            </td>

                        </tr>


                        <!-- Expense Total -->

                        <tr>

                            <th>

                                Total Expense

                            </th>


                            <th class="text-end">

                                {{ money(report.expenses.total) }}

                            </th>

                        </tr>


                    </tbody>

                </table>

            </div>

        </div>


        <!-- =====================================================
             Net Profit / Loss
        ====================================================== -->

        <div
            class="alert"
            :class="{
                'alert-primary':
                    Number(report.net_profit || 0) >= 0,

                'alert-danger':
                    Number(report.net_profit || 0) < 0
            }"
        >

            <strong>

                {{
                    Number(report.net_profit || 0) >= 0
                        ? 'Net Profit:'
                        : 'Net Loss:'
                }}

            </strong>


            {{ money(Math.abs(Number(report.net_profit || 0))) }}

        </div>


    </div>


</section>

</template>


<script setup>

import {
    ref,
    watch,
} from 'vue'


import reportService
    from '@/services/reportService'


import ReportExportButton
    from '@/components/reports/ReportExportButton.vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    filters: {

        type:
            Object,

        default:
            () => ({}),

    },

})


/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

const loading =
    ref(false)
const pdfLoading = ref(false)


/*
|--------------------------------------------------------------------------
| Error
|--------------------------------------------------------------------------
*/

const errorMessage =
    ref('')


/*
|--------------------------------------------------------------------------
| Report State
|--------------------------------------------------------------------------
*/

const report =
    ref({

        revenue: {

            details: [],

            total: 0,

        },

        expenses: {

            details: [],

            total: 0,

        },

        net_profit:
            0,

    })


/*
|--------------------------------------------------------------------------
| Load Profit & Loss Report
|--------------------------------------------------------------------------
*/

async function loadReport()
{

    loading.value =
        true


    errorMessage.value =
        ''


    try {

        const response =
            await reportService
                .getProfitLoss(
                    props.filters
                )


        /*
        |--------------------------------------------------------------------------
        | API Response
        |--------------------------------------------------------------------------
        |
        | Expected:
        |
        | {
        |     data: {
        |         revenue: {...},
        |         expenses: {...},
        |         net_profit: 0
        |     }
        | }
        |
        */

        const data =
            response?.data
            ??
            response


        report.value =
            {

                revenue: {

                    details:
                        Array.isArray(
                            data?.revenue?.details
                        )
                            ? data.revenue.details
                            : [],

                    total:
                        Number(
                            data?.revenue?.total
                            ??
                            0
                        ),

                },


                expenses: {

                    details:
                        Array.isArray(
                            data?.expenses?.details
                        )
                            ? data.expenses.details
                            : [],

                    total:
                        Number(
                            data?.expenses?.total
                            ??
                            0
                        ),

                },


                net_profit:
                    Number(
                        data?.net_profit
                        ??
                        0
                    ),

            }

    }
    catch (error) {

        console.error(
            'Profit & Loss report error:',
            error
        )


        errorMessage.value =
            error
                ?.response
                ?.data
                ?.message
            ||
            'Unable to load Profit & Loss report.'

    }
    finally {

        loading.value =
            false

    }

}


/*
|--------------------------------------------------------------------------
| Watch Filters
|--------------------------------------------------------------------------
*/

watch(

    () =>
        props.filters,

    () => {

        loadReport()

    },

    {

        deep:
            true,

        immediate:
            true,

    }

)


/*
|--------------------------------------------------------------------------
| Money Formatter
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

</script>


<style scoped>

.profit-loss-report {

    width:
        100%;

}


.profit-loss-report .card {

    border:
        1px solid
        rgba(
            148,
            163,
            184,
            0.18
        );

    border-radius:
        12px;

    overflow:
        hidden;

}


.profit-loss-report .card-header {

    font-size:
        13px;

    font-weight:
        700;

}


.profit-loss-report table {

    font-size:
        12px;

}


.profit-loss-report th {

    font-weight:
        700;

}


.profit-loss-report td,
.profit-loss-report th {

    padding:
        11px 14px;

}


.profit-loss-report .alert {

    margin-bottom:
        0;

    font-size:
        14px;

}


@media (
    max-width: 575px
) {

    .profit-loss-report .d-flex {

        align-items:
            flex-start !important;

        flex-direction:
            column;

        gap:
            10px;

    }


    .profit-loss-report td,
    .profit-loss-report th {

        padding:
            9px 10px;

    }

}

</style>