<template>
  <div class="chart-root">
    <Pie
      v-if="hasData"
      :data="chartData"
      :options="chartOptions"
    />

    <div
      v-else
      class="empty-chart"
    >
      <i class="bi bi-pie-chart fs-1 mb-2"></i>

      <div>
        No expense data available
      </div>
    </div>
  </div>
</template>


<script setup>
import {
  computed,
} from 'vue'

import {
  Pie,
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

  expenses: {
    type: Array,
    default: () => [],
  },

})


const colors = [

  '#0d6efd',
  '#dc3545',
  '#198754',
  '#ffc107',
  '#6f42c1',
  '#fd7e14',
  '#20c997',
  '#0dcaf0',
  '#d63384',
  '#6c757d',
  '#6610f2',
  '#1982c4',

]


const hasData = computed(() => {

  return props.expenses.some(
    item =>
      Number(
        item.amount
        ??
        item.balance
        ??
        0
      ) > 0
  )

})


const chartData = computed(() => {

  const items = props.expenses.filter(
    item =>
      Number(
        item.amount
        ??
        item.balance
        ??
        0
      ) !== 0
  )


  return {

    labels: items.map(
      item =>
        item.account_name
        ??
        item.name
        ??
        'Expense'
    ),


    datasets: [
      {

        label: 'Expense',

        data: items.map(
          item =>
            Math.abs(
              Number(
                item.amount
                ??
                item.balance
                ??
                0
              )
            )
        ),

        backgroundColor: items.map(
          (_, index) =>
            colors[index % colors.length]
        ),

        borderColor: '#ffffff',

        borderWidth: 2,

        hoverOffset: 10,

      },
    ],

  }

})


const chartOptions = {

  responsive: true,

  maintainAspectRatio: false,


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

      display: true,

      position: 'bottom',

      labels: {

        usePointStyle: true,

        pointStyle: 'circle',

        padding: 18,

        boxWidth: 10,

        boxHeight: 10,

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


          return (
            `${context.label}: `
            +
            value.toLocaleString(
              'en-BD',
              {
                style: 'currency',
                currency: 'BDT',
                minimumFractionDigits: 2,
              }
            )
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

  align-items: center;

  justify-content: center;

  color: #6c757d;

  text-align: center;
}
</style>