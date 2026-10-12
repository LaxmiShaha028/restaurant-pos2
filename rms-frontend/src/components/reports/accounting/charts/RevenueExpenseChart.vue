<template>

  <div class="chart-root">

    <Bar
      :data="chartData"
      :options="chartOptions"
    />

  </div>

</template>


<script setup>

import {
  computed,
} from 'vue'


import {
  Bar,
} from 'vue-chartjs'


import {
  Chart as ChartJS,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
} from 'chart.js'


ChartJS.register(
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
)


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

  revenue: {

    type: Number,

    default: 0,

  },


  foodCost: {

    type: Number,

    default: 0,

  },


  expense: {

    type: Number,

    default: 0,

  },


  profit: {

    type: Number,

    default: 0,

  },

})



/*
|--------------------------------------------------------------------------
| Total Cost
|--------------------------------------------------------------------------
*/

const totalCost = computed(() => {

  return (

    Number(
      props.foodCost || 0
    )

    +

    Number(
      props.expense || 0
    )

  )

})


/*
|--------------------------------------------------------------------------
| Chart Data
|--------------------------------------------------------------------------
*/

const chartData = computed(() => {

  return {

    labels: [

      'Revenue',

      'Total Cost',

      'Net Profit',

    ],


    datasets: [

      {

        label:
          'Amount',


        data: [

          Number(
            props.revenue || 0
          ),

          totalCost.value,

          Number(
            props.profit || 0
          ),

        ],


        backgroundColor: [

          '#198754',

          '#dc3545',

          props.profit >= 0
            ? '#0d6efd'
            : '#fd7e14',

        ],


        borderRadius:
          8,


        borderSkipped:
          false,


        maxBarThickness:
          75,


        minBarLength:
          3,

      },

    ],

  }

})
console.log('TOTAL COST CHECK:', {
  revenue: Number(props.revenue || 0),
  foodCost: Number(props.foodCost || 0),
  expense: Number(props.expense || 0),
  totalCost: totalCost.value,
  profit: Number(props.profit || 0),
})


/*
|--------------------------------------------------------------------------
| Chart Options
|--------------------------------------------------------------------------
*/

const chartOptions = {

  responsive:
    true,


  maintainAspectRatio:
    false,


  interaction: {

    intersect:
      false,

    mode:
      'index',

  },


  scales: {

    x: {

      grid: {

        display:
          false,

      },


      ticks: {

        font: {

          weight:
            '600',

        },

      },

    },


    y: {

      beginAtZero:
        true,


      grid: {

        color:
          'rgba(0, 0, 0, 0.06)',

      },


      ticks: {

        callback(value) {

          return (

            '৳ '

            +

            Number(
              value
            ).toLocaleString(
              'en-BD'
            )

          )

        },

      },

    },

  },


  plugins: {

    legend: {

      display:
        false,

    },


    tooltip: {

      callbacks: {

        label(context) {

          const value =
            Number(
              context.raw ?? 0
            )


          return (

            'Amount: '

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

          )

        },

      },

    },

  },

}

</script>


<style scoped>

.chart-root {

  position:
    relative;

  width:
    100%;

  height:
    100%;

  min-height:
    300px;

}


.chart-root :deep(canvas) {

  width:
    100% !important;

  height:
    100% !important;

}

</style>