<template>

<section class="general-ledger-report">


    <!-- =========================================================
         Header
    ========================================================== -->

    <div
        class="d-flex justify-content-between align-items-center mb-3"
    >

        <div>

            <h5 class="fw-bold mb-1">

                General Ledger

            </h5>


            <small class="text-secondary">

                Account transaction history

            </small>

        </div>


        <ReportExportButton

            type="general-ledger"

            :filters="filters"

        />

    </div>


    <!-- =========================================================
         Account Select
    ========================================================== -->

    <div class="card mb-3">

        <div class="card-body">

            <label
                for="ledger-account"
                class="form-label fw-bold"
            >

                Select Account

            </label>


            <select
                id="ledger-account"
                class="form-select"
                v-model="selectedAccount"
                :disabled="accountsLoading"
                @change="handleAccountChange"
            >

                <option value="">

                    Select account

                </option>


                <option
                    v-for="
                        account in accounts
                    "
                    :key="
                        account.id
                    "
                    :value="
                        account.id
                    "
                >

                    {{ account.code }}

                    -

                    {{ account.name }}

                </option>

            </select>


            <!-- Account Loading -->

            <small
                v-if="accountsLoading"
                class="text-secondary d-block mt-2"
            >

                Loading accounts...

            </small>


            <!-- Account Error -->

            <div
                v-if="accountsError"
                class="text-danger small mt-2"
            >

                {{ accountsError }}

            </div>

        </div>

    </div>


    <!-- =========================================================
         Ledger Loading
    ========================================================== -->

    <div
        v-if="loading"
        class="text-center py-5"
    >

        Loading Ledger...

    </div>


    <!-- =========================================================
         Ledger Error
    ========================================================== -->

    <div
        v-else-if="errorMessage"
        class="alert alert-danger"
    >

        <i class="bi bi-exclamation-circle me-1"></i>

        {{ errorMessage }}

    </div>


    <!-- =========================================================
         No Account Selected
    ========================================================== -->

    <div
        v-else-if="!selectedAccount"
        class="ledger-empty-state"
    >

        <div class="ledger-empty-icon">

            <i class="bi bi-journal-text"></i>

        </div>


        <h6>

            Select an account

        </h6>


        <p>

            Choose an account above to view its
            transaction history.

        </p>

    </div>


    <!-- =========================================================
         Ledger
    ========================================================== -->

    <div
        v-else-if="ledger.account"
    >


        <!-- Closing Balance -->

        <div class="alert alert-primary">

            <strong>

                {{ ledger.account.name }}

            </strong>


            <br>


            Closing Balance:

            {{ money(ledger.closing_balance) }}

        </div>


        <!-- =====================================================
             Transactions
        ====================================================== -->

        <div class="table-responsive">

            <table class="table table-hover align-middle">


                <thead class="table-light">

                    <tr>

                        <th>
                            Date
                        </th>


                        <th>
                            Journal
                        </th>


                        <th>
                            Description
                        </th>


                        <th class="text-end">
                            Debit
                        </th>


                        <th class="text-end">
                            Credit
                        </th>


                        <th class="text-end">
                            Balance
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <!-- Transaction Rows -->

                    <tr
                        v-for="
                            (item, index) in ledger.transactions
                        "
                        :key="
                            transactionKey(
                                item,
                                index
                            )
                        "
                    >

                        <td>

                            {{ item.date }}

                        </td>


                        <td>

                            {{ item.journal_number }}

                        </td>


                        <td>

                            {{ item.description }}

                        </td>


                        <td class="text-end">

                            {{ money(item.debit) }}

                        </td>


                        <td class="text-end">

                            {{ money(item.credit) }}

                        </td>


                        <td class="text-end">

                            {{ money(item.balance) }}

                        </td>

                    </tr>


                    <!-- Empty Transactions -->

                    <tr
                        v-if="
                            ledger.transactions.length === 0
                        "
                    >

                        <td
                            colspan="6"
                            class="text-center text-secondary py-4"
                        >

                            No transactions found for this
                            account and date range.

                        </td>

                    </tr>


                </tbody>

            </table>

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


const accountsLoading =
    ref(false)


/*
|--------------------------------------------------------------------------
| Errors
|--------------------------------------------------------------------------
*/

const errorMessage =
    ref('')


const accountsError =
    ref('')


/*
|--------------------------------------------------------------------------
| Selected Account
|--------------------------------------------------------------------------
*/

const selectedAccount =
    ref('')


/*
|--------------------------------------------------------------------------
| Accounts
|--------------------------------------------------------------------------
*/

const accounts =
    ref([])


/*
|--------------------------------------------------------------------------
| Ledger State
|--------------------------------------------------------------------------
*/

const ledger =
    ref({

        account:
            null,

        transactions:
            [],

        closing_balance:
            0,

    })


/*
|--------------------------------------------------------------------------
| Load Ledger Accounts
|--------------------------------------------------------------------------
*/

async function loadAccounts()
{

    accountsLoading.value =
        true


    accountsError.value =
        ''


    try {

        const response =
            await reportService
                .getLedgerAccounts()


        const data =
            response?.data
            ??
            response


        accounts.value =
            Array.isArray(data)
                ? data
                : []

    }
    catch (error) {

        console.error(
            'Ledger accounts error:',
            error
        )


        accounts.value =
            []


        accountsError.value =
            error
                ?.response
                ?.data
                ?.message
            ||
            'Unable to load ledger accounts.'

    }
    finally {

        accountsLoading.value =
            false

    }

}


/*
|--------------------------------------------------------------------------
| Handle Account Change
|--------------------------------------------------------------------------
*/

function handleAccountChange()
{

    errorMessage.value =
        ''


    ledger.value = {

        account:
            null,

        transactions:
            [],

        closing_balance:
            0,

    }


    if (
        selectedAccount.value
    ) {

        loadLedger()

    }

}


/*
|--------------------------------------------------------------------------
| Load General Ledger
|--------------------------------------------------------------------------
*/

async function loadLedger()
{

    if (
        !selectedAccount.value
    ) {

        return

    }


    loading.value =
        true


    errorMessage.value =
        ''


    try {

        const response =
            await reportService
                .getGeneralLedger({

                    account_id:
                        selectedAccount.value,

                    ...props.filters,

                })


        const data =
            response?.data
            ??
            response


        ledger.value =
            {

                account:
                    data?.account
                    ??
                    null,

                transactions:
                    Array.isArray(
                        data?.transactions
                    )
                        ? data.transactions
                        : [],

                closing_balance:
                    Number(
                        data?.closing_balance
                        ??
                        0
                    ),

            }

    }
    catch (error) {

        console.error(
            'General Ledger report error:',
            error
        )


        errorMessage.value =
            error
                ?.response
                ?.data
                ?.message
            ||
            'Unable to load General Ledger report.'

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

        if (
            selectedAccount.value
        ) {

            loadLedger()

        }

    },

    {

        deep:
            true,

    }

)


/*
|--------------------------------------------------------------------------
| Transaction Key
|--------------------------------------------------------------------------
*/

function transactionKey(
    item,
    index
) {

    return (

        item.id
        ??
        item.journal_entry_id
        ??
        item.journal_number
        ??
        `${item.date}-${index}`

    )

}


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


/*
|--------------------------------------------------------------------------
| Initial Load
|--------------------------------------------------------------------------
*/

loadAccounts()

</script>


<style scoped>

.general-ledger-report {

    width:
        100%;

}


.general-ledger-report .card {

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


.general-ledger-report .form-select {

    min-height:
        40px;

    font-size:
        12px;

}


.general-ledger-report table {

    margin-bottom:
        0;

    font-size:
        12px;

}


.general-ledger-report th {

    font-weight:
        700;

    white-space:
        nowrap;

}


.general-ledger-report td,
.general-ledger-report th {

    padding:
        10px 12px;

}


.general-ledger-report .alert {

    font-size:
        13px;

}


.ledger-empty-state {

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    justify-content:
        center;

    min-height:
        220px;

    padding:
        30px;

    border:
        1px dashed
        rgba(
            148,
            163,
            184,
            0.3
        );

    border-radius:
        12px;

    background:
        #f8f1e9;

    text-align:
        center;

}


.ledger-empty-icon {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    width:
        48px;

    height:
        48px;

    margin-bottom:
        10px;

    border-radius:
        12px;

    color:
        #f97316;

    background:
        #fff1e6;

    font-size:
        20px;

}


.ledger-empty-state h6 {

    margin:
        0;

    font-size:
        14px;

    font-weight:
        700;

}


.ledger-empty-state p {

    margin:
        5px 0 0;

    color:
        #64748b;

    font-size:
        11px;

}


@media (
    max-width: 575px
) {

    .general-ledger-report .d-flex {

        align-items:
            flex-start !important;

        flex-direction:
            column;

        gap:
            10px;

    }


    .general-ledger-report td,
    .general-ledger-report th {

        padding:
            8px 9px;

    }

}

</style>