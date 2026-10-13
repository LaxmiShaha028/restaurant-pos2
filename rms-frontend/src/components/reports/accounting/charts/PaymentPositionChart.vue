<template>
  <div class="chart-root">

    <Doughnut
      v-if="hasData"
      :data="chartData"
      :options="chartOptions"
    />

    <div
      v-else
      class="empty-chart"
    >
      <i class="bi bi-wallet2 fs-1 mb-2"></i>

      <div>
        No payment balance available
      </div>
    </div>

  </div>
</template>


<script setup>
import {
  computed,
} from 'vue'

import {
  Doughnut,
} from 'vue-chartjs'

import {
  Chart as ChartJS,
  Tooltip,
  Legend,
  ArcElement,
} from 'chart.js'


ChartJS.register(
  Tooltip,
  Legend,
  ArcElement
)


const props = defineProps({

  payments: {
    type: Array,
    default: () => [],
  },

})


/*
|--------------------------------------------------------------------------
| Payment Colours
|--------------------------------------------------------------------------
*/

const paymentColors = {

  cash:
    '#198754',

  bank:
    '#0d6efd',

  bank_transfer:
    '#0d6efd',

  card:
    '#c2410c',

  card_settlement:
    '#c2410c',

  bkash:
    '#d63384',

  bkash_wallet:
    '#d63384',

  nagad:
    '#fd7e14',

  nagad_wallet:
    '#fd7e14',

  mixed:
    '#ffc107',

  mixed_payment_clearing:
    '#ffc107',

  other:
    '#6c757d',

}


const fallbackColors = [

  '#198754',
  '#0d6efd',
  '#c2410c',
  '#d63384',
  '#fd7e14',
  '#ffc107',
  '#0dcaf0',
  '#20c997',

]


const normalizedPayments = computed(() => {

  return props.payments
    .map(
      item => ({

        ...item,

        value:
          Math.abs(
            Number(
              item.balance
              ??
              item.amount
              ??
              0
            )
          ),

      })
    )
    .filter(
      item =>
        item.value > 0
    )

})


const hasData = computed(() => {

  return normalizedPayments.value.length > 0

})


const chartData = computed(() => {

  return {

    labels:
      normalizedPayments.value.map(
        item =>
          item.account_name
          ??
          item.name
          ??
          'Payment'
      ),


    datasets: [
      {

        label:
          'Balance',

        data:
          normalizedPayments.value.map(
            item =>
              item.value
          ),


        backgroundColor:
          normalizedPayments.value.map(
            (item, index) => {

              const key =
                String(
                  item.system_key
                  ??
                  item.payment_method
                  ??
                  ''
                )
                  .toLowerCase()


              return (
                paymentColors[key]
                ??
                fallbackColors[
                  index
                  %
                  fallbackColors.length
                ]
              )

            }
          ),


        borderColor:
          '#ffffff',

        borderWidth:
          3,


        hoverOffset:
          10,

      },
    ],

  }

})


const chartOptions = {

  responsive:
    true,


  maintainAspectRatio:
    false,


  cutout:
    '58%',


  layout: {

    padding: {

      top: 10,

      right: 10,

      bottom: 10,

      left: 10,

    },

  },


  plugins: {

    legend: {

      display:
        true,


      position:
        'bottom',


      labels: {

        usePointStyle:
          true,


        pointStyle:
          'circle',


        padding:
          16,


        boxWidth:
          10,


        boxHeight:
          10,

      },

    },


    tooltip: {

      callbacks: {

        label(context) {

          const value =
            Number(
              context.raw
              ??
              0
            )


          const dataset =
            context.dataset.data
            ??
            []


          const total =
            dataset.reduce(
              (
                sum,
                item
              ) =>
                sum
                +
                Number(
                  item
                  ??
                  0
                ),
              0
            )


          const percentage =
            total > 0

              ? (
                  (
                    value
                    /
                    total
                  )
                  *
                  100
                )
                  .toFixed(1)

              : '0.0'


          return (
            `${context.label}: `
            +
            value.toLocaleString(
              'en-BD',
              {
                style:
                  'currency',

                currency:
                  'BDT',

                minimumFractionDigits:
                  2,
              }
            )
            +
            ` (${percentage}%)`
          )

        },

      },

    },

  },

}
</script>


<style scoped>
.chart-root {
  position: relative;

  width: 100%;

  height: 100%;

  min-height: 320px;
}


.chart-root :deep(canvas) {
  width: 100% !important;

  height: 100% !important;
}


.empty-chart {
  min-height: 300px;

  display: flex;

  flex-direction: column;

  justify-content: center;

  align-items: center;

  color: #6c757d;

  text-align: center;
}
</style>