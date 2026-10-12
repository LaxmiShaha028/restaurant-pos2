<template>

<section class="balance-sheet-report">


    <!-- =========================================================
         Header
    ========================================================== -->

    <div
        class="d-flex justify-content-between align-items-center mb-3"
    >

        <div>

            <h5 class="fw-bold mb-1">

                Balance Sheet

            </h5>


            <small class="text-secondary">

                Assets, liabilities and equity position

            </small>

        </div>


        <ReportExportButton

            type="balance-sheet"

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

        Loading Balance Sheet...

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
             Assets
        ====================================================== -->

        <div class="card mb-3">


            <div class="card-header bg-primary text-white">

                Assets

            </div>


            <div class="table-responsive">

                <table class="table mb-0">


                    <thead>

                        <tr>

                            <th>
                                Account
                            </th>


                            <th class="text-end">
                                Balance
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <!-- Asset Details -->

                        <tr
                            v-for="
                                item in report.assets.details
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


                        <!-- Empty Assets -->

                        <tr
                            v-if="
                                !report.assets.details.length
                            "
                        >

                            <td
                                colspan="2"
                                class="text-center text-secondary py-3"
                            >

                                No asset records found for this period.

                            </td>

                        </tr>


                        <!-- Total Assets -->

                        <tr class="fw-bold">

                            <td>

                                Total Assets

                            </td>


                            <td class="text-end">

                                {{ money(report.assets.total) }}

                            </td>

                        </tr>


                    </tbody>

                </table>

            </div>

        </div>


        <!-- =====================================================
             Liabilities
        ====================================================== -->

        <div class="card mb-3">


            <div class="card-header bg-warning">

                Liabilities

            </div>


            <div class="table-responsive">

                <table class="table mb-0">


                    <thead>

                        <tr>

                            <th>
                                Account
                            </th>


                            <th class="text-end">
                                Balance
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <!-- Liability Details -->

                        <tr
                            v-for="
                                item in report.liabilities.details
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


                        <!-- Empty Liabilities -->

                        <tr
                            v-if="
                                !report.liabilities.details.length
                            "
                        >

                            <td
                                colspan="2"
                                class="text-center text-secondary py-3"
                            >

                                No liability records found for this period.

                            </td>

                        </tr>


                        <!-- Total Liabilities -->

                        <tr class="fw-bold">

                            <td>

                                Total Liabilities

                            </td>


                            <td class="text-end">

                                {{ money(report.liabilities.total) }}

                            </td>

                        </tr>


                    </tbody>

                </table>

            </div>

        </div>


        <!-- =====================================================
             Equity
        ====================================================== -->

        <div class="card mb-3">


            <div class="card-header bg-success text-white">

                Equity

            </div>


            <div class="table-responsive">

                <table class="table mb-0">


                    <thead>

                        <tr>

                            <th>
                                Account
                            </th>


                            <th class="text-end">
                                Balance
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <!-- Equity Details -->

                        <tr
                            v-for="
                                item in report.equity.details
                            "
                            :key="
                                item.account_id
                                ??
                                item.code
                            "
                        >

                            <td>

                                {{ item.account_name }}

                            </td>


                            <td class="text-end">

                                {{ money(item.balance) }}

                            </td>

                        </tr>


                        <!-- Empty Equity -->

                        <tr
                            v-if="
                                !report.equity.details.length
                            "
                        >

                            <td
                                colspan="2"
                                class="text-center text-secondary py-3"
                            >

                                No equity records found for this period.

                            </td>

                        </tr>


                        <!-- Total Equity -->

                        <tr class="fw-bold">

                            <td>

                                Total Equity

                            </td>


                            <td class="text-end">

                                {{ money(report.equity.total) }}

                            </td>

                        </tr>


                    </tbody>

                </table>

            </div>

        </div>


        <!-- =====================================================
             Balance Check
        ====================================================== -->

        <div
            class="alert"
            :class="
                balanceOk
                    ? 'alert-success'
                    : 'alert-danger'
            "
        >

            <strong>

                Balance Check:

            </strong>


            Assets

            {{ money(report.balance_check.assets) }}


            =


            Liabilities + Equity

            {{ money(
                report.balance_check.liabilities_equity
            ) }}


            <span
                v-if="balanceOk"
                class="ms-2"
            >

                ✅ Balanced

            </span>


            <span
                v-else
                class="ms-2"
            >

                ❌ Not Balanced

            </span>

        </div>


    </div>


</section>

</template>


<script setup>

import {
    computed,
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


/*
|--------------------------------------------------------------------------
| Error Message
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

        assets: {

            details: [],

            total:
                0,

        },


        liabilities: {

            details: [],

            total:
                0,

        },


        equity: {

            details: [],

            total:
                0,

        },


        balance_check: {

            assets:
                0,

            liabilities_equity:
                0,

        },

    })


/*
|--------------------------------------------------------------------------
| Balance Check
|--------------------------------------------------------------------------
|
| Accounting amounts may contain decimal values, so compare with a
| small tolerance instead of relying on exact floating-point equality.
|
*/

const balanceOk =
    computed(() => {

        const assets =
            Number(
                report
                    .value
                    .balance_check
                    .assets
                ??
                0
            )


        const liabilitiesEquity =
            Number(
                report
                    .value
                    .balance_check
                    .liabilities_equity
                ??
                0
            )


        return (
            Math.abs(
                assets -
                liabilitiesEquity
            )
            < 0.01
        )

    })


/*
|--------------------------------------------------------------------------
| Load Balance Sheet Report
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
                .getBalanceSheet(
                    props.filters
                )


        /*
        |--------------------------------------------------------------------------
        | Support:
        |
        | response.data
        | or
        | response
        |--------------------------------------------------------------------------
        */

        const data =
            response?.data
            ??
            response


        report.value =
            {

                assets: {

                    details:
                        Array.isArray(
                            data?.assets?.details
                        )
                            ? data.assets.details
                            : [],

                    total:
                        Number(
                            data?.assets?.total
                            ??
                            0
                        ),

                },


                liabilities: {

                    details:
                        Array.isArray(
                            data?.liabilities?.details
                        )
                            ? data.liabilities.details
                            : [],

                    total:
                        Number(
                            data?.liabilities?.total
                            ??
                            0
                        ),

                },


                equity: {

                    details:
                        Array.isArray(
                            data?.equity?.details
                        )
                            ? data.equity.details
                            : [],

                    total:
                        Number(
                            data?.equity?.total
                            ??
                            0
                        ),

                },


                balance_check: {

                    assets:
                        Number(
                            data
                                ?.balance_check
                                ?.assets
                            ??
                            0
                        ),

                    liabilities_equity:
                        Number(
                            data
                                ?.balance_check
                                ?.liabilities_equity
                            ??
                            0
                        ),

                },

            }

    }
    catch (error) {

        console.error(
            'Balance Sheet report error:',
            error
        )


        errorMessage.value =
            error
                ?.response
                ?.data
                ?.message
            ||
            'Unable to load Balance Sheet report.'

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

.balance-sheet-report {

    width:
        100%;

}


.balance-sheet-report .card {

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
        12px;

}


.balance-sheet-report .card-header {

    font-size:
        13px;

    font-weight:
        700;

}


.balance-sheet-report table {

    font-size:
        12px;

}


.balance-sheet-report th {

    font-weight:
        700;

}


.balance-sheet-report td,
.balance-sheet-report th {

    padding:
        11px 14px;

}


.balance-sheet-report .alert {

    margin-bottom:
        0;

    font-size:
        14px;

}


@media (
    max-width: 575px
) {

    .balance-sheet-report .d-flex {

        align-items:
            flex-start !important;

        flex-direction:
            column;

        gap:
            10px;

    }


    .balance-sheet-report td,
    .balance-sheet-report th {

        padding:
            9px 10px;

    }

}

</style>