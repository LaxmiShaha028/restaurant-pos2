<template>
  <section>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

      <div>
        <h5 class="fw-bold mb-1">
          Owner Ledger
        </h5>

        <small class="text-secondary">
          Simple business summary for restaurant owner
        </small>
      </div>


      <div class="d-flex gap-2">

        <button
          class="btn btn-danger"
          @click="downloadPdf"
          :disabled="pdfLoading"
        >
          <span v-if="pdfLoading">
            <span class="spinner-border spinner-border-sm me-1"></span>
            Exporting...
          </span>

          <span v-else>
            <i class="bi bi-file-earmark-pdf me-1"></i>
            Download PDF
          </span>
        </button>


        <button
          class="btn btn-dark"
          @click="printLedger"
        >
          <i class="bi bi-printer me-1"></i>
          Print
        </button>

      </div>

    </div>


    <!-- Filter -->
    <div class="card shadow-sm mb-4">

      <div class="card-body">

        <div class="row g-3 align-items-end">

          <div class="col-md-4">

            <label class="form-label">
              From Date
            </label>

            <input
              type="date"
              class="form-control"
              v-model="filters.from_date"
            />

          </div>


          <div class="col-md-4">

            <label class="form-label">
              To Date
            </label>

            <input
              type="date"
              class="form-control"
              v-model="filters.to_date"
            />

          </div>


          <div class="col-md-4">

            <button
              class="btn btn-primary"
              @click="loadLedger"
              :disabled="loading"
            >

              <span v-if="loading">
                Loading...
              </span>

              <span v-else>
                Generate Report
              </span>

            </button>

          </div>

        </div>

      </div>

    </div>


    <!-- Error -->
    <div
      v-if="error"
      class="alert alert-danger"
    >
      {{ error }}
    </div>


    <!-- Loading -->
    <div
      v-if="loading"
      class="text-center py-5"
    >
      <div class="spinner-border text-primary mb-2"></div>

      <div class="text-muted">
        Loading ledger...
      </div>
    </div>


    <!-- Ledger -->
    <div v-else>

      <div class="card shadow-sm">

        <div class="card-body">

          <div class="table-responsive">

            <table class="table table-bordered align-middle">

              <thead class="table-light">

                <tr>
                  <th>
                    Particular
                  </th>

                  <th class="text-end">
                    Amount
                  </th>
                </tr>

              </thead>


              <tbody>

                <tr>
                  <td>
                    Revenue
                  </td>

                  <td class="text-end text-success fw-semibold">
                    {{ money(summary.revenue) }}
                  </td>
                </tr>


                <tr>
                  <td>
                    Purchase
                  </td>

                  <td class="text-end">
                    {{ money(summary.purchase) }}
                  </td>
                </tr>


                <tr>
                  <td>
                    Purchase Return
                  </td>

                  <td class="text-end">
                    {{ money(summary.purchase_return) }}
                  </td>
                </tr>


                <tr>
                  <td>
                    Food Cost
                  </td>

                  <td class="text-end text-danger">
                    {{ money(summary.food_cost) }}
                  </td>
                </tr>


                <tr>
                  <td>
                    Expense
                  </td>

                  <td class="text-end text-danger">
                    {{ money(summary.expense) }}
                  </td>
                </tr>

              </tbody>


              <tfoot>

                <tr class="fw-bold">

                  <td>
                    Net Profit
                  </td>

                  <td
                    class="text-end"
                    :class="
                      summary.profit >= 0
                        ? 'text-primary'
                        : 'text-danger'
                    "
                  >
                    {{ money(summary.profit) }}
                  </td>

                </tr>

              </tfoot>

            </table>

          </div>

        </div>

      </div>

    </div>

  </section>
</template>


<script setup>
import {
  ref,
  watch,
  onMounted,
} from 'vue'

import reportService from '@/services/reportService'


const props = defineProps({

  filters: {
    type: Object,
    default: () => ({}),
  },

})


const loading =
  ref(false)


const pdfLoading =
  ref(false)


const error =
  ref('')


function today()
{
  return new Date()
    .toISOString()
    .slice(0, 10)
}


function startOfMonth()
{
  const date =
    new Date()

  date.setDate(1)

  return date
    .toISOString()
    .slice(0, 10)
}


const filters =
  ref({

    from_date:
      props.filters.date_from
      ??
      props.filters.from_date
      ??
      startOfMonth(),

    to_date:
      props.filters.date_to
      ??
      props.filters.to_date
      ??
      today(),

  })


const summary =
  ref({

    revenue: 0,

    purchase: 0,

    purchase_return: 0,

    food_cost: 0,

    expense: 0,

    profit: 0,

  })


async function loadLedger()
{
  loading.value =
    true

  error.value =
    ''


  try {

    const response =
      await reportService.getOwnerLedger({

        from_date:
          filters.value.from_date,

        to_date:
          filters.value.to_date,

      })


    const data =
      response?.data
      ??
      {}


    const apiSummary =
      data.summary
      ??
      {}


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

    }

  }
  catch (
    requestError
  ) {

    console.error(
      'Owner Ledger Error:',
      requestError
    )


    error.value =
      requestError?.response?.data?.message
      ??
      requestError?.message
      ??
      'Failed to load owner ledger.'

  }
  finally {

    loading.value =
      false

  }

}


async function downloadPdf()
{
  if (
    pdfLoading.value
  ) {
    return
  }


  pdfLoading.value =
    true


  try {

    const response =
      await reportService.downloadOwnerLedgerPdf({

        from_date:
          filters.value.from_date,

        to_date:
          filters.value.to_date,

      })


    const blob =
      new Blob(
        [
          response.data,
        ],
        {
          type:
            'application/pdf',
        }
      )


    const url =
      window.URL.createObjectURL(
        blob
      )


    const link =
      document.createElement(
        'a'
      )


    link.href =
      url


    link.download =
      'owner-ledger-report.pdf'


    document.body.appendChild(
      link
    )


    link.click()


    link.remove()


    window.URL.revokeObjectURL(
      url
    )

  }
  catch (
    requestError
  ) {

    console.error(
      'PDF Error:',
      requestError
    )

  }
  finally {

    pdfLoading.value =
      false

  }

}


function printLedger()
{
  window.print()
}


function money(
  value
) {

  return (
    '৳ '
    +
    Number(
      value
      ??
      0
    )
      .toLocaleString(
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
| Load automatically when page opens
|--------------------------------------------------------------------------
*/

onMounted(() => {

  loadLedger()

})


/*
|--------------------------------------------------------------------------
| Sync parent report filters
|--------------------------------------------------------------------------
*/

watch(

  () => props.filters,

  newFilters => {

    const newFrom =
      newFilters?.date_from
      ??
      newFilters?.from_date


    const newTo =
      newFilters?.date_to
      ??
      newFilters?.to_date


    let changed =
      false


    if (
      newFrom
      &&
      newFrom !== filters.value.from_date
    ) {

      filters.value.from_date =
        newFrom

      changed =
        true

    }


    if (
      newTo
      &&
      newTo !== filters.value.to_date
    ) {

      filters.value.to_date =
        newTo

      changed =
        true

    }


    if (
      changed
    ) {

      loadLedger()

    }

  },

  {
    deep:
      true,
  }

)
</script>


<style scoped>
table {
  font-size: 0.95rem;
}


th {
  font-weight: 600;
}


tfoot td {
  background-color: #f8f9fa;
}


@media print {

  button,
  input,
  label {

    display:
      none !important;

  }


  .card {

    border:
      none !important;

    box-shadow:
      none !important;

  }


  section {

    width:
      100%;

  }


  table {

    font-size:
      14px;

  }

}
</style>