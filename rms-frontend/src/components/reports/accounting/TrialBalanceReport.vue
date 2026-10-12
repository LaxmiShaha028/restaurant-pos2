<template>

<section class="trial-balance-report">


    <!-- =========================================================
         Header
    ========================================================== -->

    <div
        class="d-flex justify-content-between align-items-center mb-3"
    >

        <div>

            <h5 class="fw-bold mb-1">

                Trial Balance

            </h5>


            <small class="text-secondary">

                Debit and credit verification

            </small>

        </div>


        <ReportExportButton

            type="trial-balance"

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

        Loading Trial Balance...

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


        <div class="table-responsive">


            <table class="table table-hover align-middle">


                <thead class="table-light">

                    <tr>

                        <th>
                            Code
                        </th>


                        <th>
                            Account Name
                        </th>


                        <th>
                            Type
                        </th>


                        <th class="text-end">
                            Debit
                        </th>


                        <th class="text-end">
                            Credit
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <!-- =================================================
                         Account Rows
                    ================================================== -->

                    <tr
                        v-for="
                            item in report.accounts
                        "
                        :key="
                            item.account_id
                        "
                    >

                        <td>

                            {{ item.code }}

                        </td>


                        <td>

                            {{ item.account_name }}

                        </td>


                        <td>

                            <span class="badge bg-secondary">

                                {{ item.account_type }}

                            </span>

                        </td>


                        <td class="text-end">

                            {{ money(item.debit) }}

                        </td>


                        <td class="text-end">

                            {{ money(item.credit) }}

                        </td>

                    </tr>


                    <!-- =================================================
                         Empty State
                    ================================================== -->

                    <tr
                        v-if="
                            report.accounts.length === 0
                        "
                    >

                        <td
                            colspan="5"
                            class="text-center text-secondary py-4"
                        >

                            No accounting data found for this period.

                        </td>

                    </tr>


                </tbody>


                <!-- =====================================================
                     Totals
                ====================================================== -->

                <tfoot class="table-light">

                    <tr>

                        <th colspan="3">

                            Total

                        </th>


                        <th class="text-end">

                            {{ money(report.total_debit) }}

                        </th>


                        <th class="text-end">

                            {{ money(report.total_credit) }}

                        </th>

                    </tr>

                </tfoot>


            </table>

        </div>


        <!-- =========================================================
             Balance Status
        ========================================================== -->

        <div
            class="alert mt-3"
            :class="
                report.is_balanced
                    ? 'alert-success'
                    : 'alert-danger'
            "
        >

            <strong>

                Trial Balance:

            </strong>


            <span
                v-if="report.is_balanced"
                class="ms-1"
            >

                ✅ Debit = Credit

            </span>


            <span
                v-else
                class="ms-1"
            >

                ❌ Difference Found

            </span>

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

        accounts: [],

        total_debit:
            0,

        total_credit:
            0,

        is_balanced:
            false,

    })


/*
|--------------------------------------------------------------------------
| Load Trial Balance Report
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
                .getTrialBalance(
                    props.filters
                )


        /*
        |--------------------------------------------------------------------------
        | Support both:
        |
        | response.data
        | response
        |--------------------------------------------------------------------------
        */

        const data =
            response?.data
            ??
            response


        report.value =
            {

                accounts:
                    Array.isArray(
                        data?.accounts
                    )
                        ? data.accounts
                        : [],


                total_debit:
                    Number(
                        data?.total_debit
                        ??
                        0
                    ),


                total_credit:
                    Number(
                        data?.total_credit
                        ??
                        0
                    ),


                is_balanced:
                    Boolean(
                        data?.is_balanced
                    ),

            }

    }
    catch (error) {

        console.error(
            'Trial Balance report error:',
            error
        )


        errorMessage.value =
            error
                ?.response
                ?.data
                ?.message
            ||
            'Unable to load Trial Balance report.'

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

.trial-balance-report {

    width:
        100%;

}


.trial-balance-report table {

    margin-bottom:
        0;

    font-size:
        12px;

}


.trial-balance-report th {

    font-weight:
        700;

}


.trial-balance-report td,
.trial-balance-report th {

    padding:
        11px 14px;

}


.trial-balance-report .badge {

    font-size:
        10px;

    font-weight:
        600;

}


.trial-balance-report .alert {

    margin-bottom:
        0;

    font-size:
        14px;

}


@media (
    max-width: 575px
) {

    .trial-balance-report .d-flex {

        align-items:
            flex-start !important;

        flex-direction:
            column;

        gap:
            10px;

    }


    .trial-balance-report td,
    .trial-balance-report th {

        padding:
            9px 10px;

    }

}

</style>