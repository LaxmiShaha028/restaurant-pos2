<template>
  <section class="demand-forecast-report">
    <header class="forecast-header">
      <div>
        <h2>Food Demand Forecast</h2>
        <p>Uses non-cancelled orders from matching weekdays in the previous eight weeks.</p>
      </div>
      <div class="forecast-range-switch" role="group" aria-label="Forecast period">
        <button :class="{ active: forecastDays === 1 }" type="button" :disabled="loading" @click="setRange(1)">Next day</button>
        <button :class="{ active: forecastDays === 7 }" type="button" :disabled="loading" @click="setRange(7)">Next 7 days</button>
      </div>
    </header>

    <div v-if="error" class="forecast-message is-error">{{ error }}</div>
    <div v-else-if="loading" class="forecast-message">Loading demand forecast…</div>
    <template v-else>
      <div class="forecast-summary-grid">
        <article class="forecast-summary-card">
          <span>Forecast menu item quantity</span>
          <strong>{{ number(forecastTotal) }}</strong>
        </article>
        <article class="forecast-summary-card">
          <span>Peak sales hour</span>
          <strong>{{ peakHours[0] ? formatHour(peakHours[0].hour) : 'No data' }}</strong>
        </article>
        <article class="forecast-summary-card">
          <span>Most popular item ({{ chartRangeLabel.toLowerCase() }})</span>
          <strong>{{ popularItems[0]?.item_name || 'No data' }}</strong>
        </article>
      </div>

      <div class="forecast-chart-filter">
        <span>Chart period</span>
        <div class="forecast-range-switch" role="group" aria-label="Chart history period">
          <button :class="{ active: chartDays === 1 }" type="button" :disabled="loading" @click="setChartRange(1)">Yesterday</button>
          <button :class="{ active: chartDays === 7 }" type="button" :disabled="loading" @click="setChartRange(7)">7 days</button>
          <button :class="{ active: chartDays === 28 }" type="button" :disabled="loading" @click="setChartRange(28)">28 days</button>
        </div>
      </div>

      <div class="forecast-chart-grid">
        <div class="forecast-panel forecast-chart-panel">
          <h3>Forecast vs actual · {{ chartRangeLabel.toLowerCase() }}</h3>
          <div v-if="comparisonDaily.length" class="forecast-chart-canvas">
            <Bar :data="comparisonChartData" :options="comparisonChartOptions" />
          </div>
          <div v-else class="forecast-empty">No sales comparison data yet.</div>
        </div>
      </div>

      <div class="forecast-insights-grid">
        <div class="forecast-panel forecast-chart-panel">
          <h3>Best-selling menu items · {{ chartRangeLabel.toLowerCase() }}</h3>
          <div v-if="peakHours.length" class="forecast-chart-canvas">
            <Bar :data="hourlyChartData" :options="hourlyChartOptions" />
          </div>
          <div v-else class="forecast-empty">No hourly sales data yet.</div>
        </div>

        <div class="forecast-panel forecast-chart-panel">
          <h3>Popular item sales share · {{ chartRangeLabel.toLowerCase() }}</h3>
          <div v-if="popularItems.length" class="forecast-chart-canvas">
            <Pie :data="popularItemsChartData" :options="popularItemsChartOptions" />
          </div>
          <div v-else class="forecast-empty">No menu item sales data yet.</div>
        </div>
      </div>

      <div v-if="meta.history_days_used === 0" class="forecast-message">
        No non-cancelled orders were found in the forecast history period.
      </div>

      <div class="forecast-panel">
        <h3>Expected menu demand</h3>
        <div class="forecast-table-scroll">
          <table>
            <thead><tr><th>Date</th><th>Menu item</th><th>Forecast quantity</th><th>Matching weekdays used</th></tr></thead>
            <tbody>
              <tr v-for="row in forecastRows" :key="`${row.date}-${row.menu_item_id}-${row.variant_id || 0}`">
                <td>{{ formatDate(row.date) }}</td>
                <td>{{ row.item_name }}</td>
                <td>{{ number(row.forecast_quantity) }}</td>
                <td>{{ row.history_days_used }}</td>
              </tr>
              <tr v-if="!forecastRows.length"><td colspan="4" class="forecast-empty">No positive demand was forecast for this period.</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="forecast-panel">
        <h3>Forecast compared with actual sales</h3>
        <p class="forecast-panel-note">
          {{ chartRangeLabel }} ({{ formatDate(comparison.from) }}–{{ formatDate(comparison.to) }}). Daily estimates use matching weekdays from the previous 8 weeks.
        </p>
        <div class="forecast-comparison-summary">
          <div><span>Forecast</span><strong>{{ number(comparison.forecast_quantity) }}</strong></div>
          <div><span>Actual sales</span><strong>{{ number(comparison.actual_quantity) }}</strong></div>
          <div><span>Overall accuracy</span><strong>{{ comparison.accuracy_percent == null ? 'No data' : number(comparison.accuracy_percent) + '%' }}</strong></div>
        </div>
        <div class="forecast-table-scroll">
          <table>
            <thead><tr><th>Menu item</th><th>Forecast quantity</th><th>Actual quantity</th><th>Actual − forecast</th><th>Accuracy</th></tr></thead>
            <tbody>
              <tr v-for="row in comparisonItems" :key="`${row.menu_item_id}-${row.variant_id || 0}`">
                <td>{{ row.item_name }}</td>
                <td>{{ number(row.forecast_quantity) }}</td>
                <td>{{ number(row.actual_quantity) }}</td>
                <td :class="row.difference > 0 ? 'forecast-over' : row.difference < 0 ? 'forecast-under' : ''">{{ row.difference > 0 ? '+' : '' }}{{ number(row.difference) }}</td>
                <td>{{ row.accuracy_percent == null ? '—' : number(row.accuracy_percent) + '%' }}</td>
              </tr>
              <tr v-if="!comparisonItems.length"><td colspan="5" class="forecast-empty">No sales to compare for this period.</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="forecast-panel">
        <h3>Ingredient purchase suggestions</h3>
        <p class="forecast-panel-note">Suggested quantity covers forecast usage and the restaurant minimum stock, after current stock and open purchase orders.</p>
        <div v-if="unmappedItems.length" class="forecast-message is-warning">
          Recipe mapping is missing for: {{ unmappedItems.map(item => item.item_name).join(', ') }}. Add recipe ingredients to calculate their stock needs.
        </div>
        <div v-if="unmappedAddons.length" class="forecast-message is-warning">
          Recipe mapping is missing for add-ons: {{ unmappedAddons.map(item => item.addon_name).join(', ') }}. Add their ingredient recipes to include those ingredients in purchase suggestions.
        </div>
        <div class="forecast-table-scroll">
          <table>
            <thead><tr><th>Ingredient</th><th>Forecast usage</th><th>Current stock</th><th>Incoming</th><th>Suggested purchase</th></tr></thead>
            <tbody>
              <tr v-for="row in ingredientRows" :key="row.raw_material_id">
                <td>{{ row.material_name }} <small>{{ row.unit }}</small></td>
                <td>{{ number(row.forecast_usage) }} {{ row.unit }}</td>
                <td>{{ number(row.current_stock) }} {{ row.unit }}</td>
                <td>{{ number(row.incoming_quantity) }} {{ row.unit }}</td>
                <td v-if="row.unit_mismatch" class="forecast-warning-text">Unit mismatch; check recipe</td>
                <td v-else>{{ number(row.recommended_purchase_quantity) }} {{ row.unit }}</td>
              </tr>
              <tr v-if="!ingredientRows.length"><td colspan="5" class="forecast-empty">No ingredient recommendations available for this period.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <p class="forecast-footnote">Historical period: {{ meta.history_from }} to {{ meta.history_to }}. Forecast is a baseline estimate; review quantities before ordering.</p>
    </template>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { Bar, Pie } from 'vue-chartjs'
import {
  ArcElement,
  BarElement,
  CategoryScale,
  Chart as ChartJS,
  Legend,
  LinearScale,
  Tooltip,
} from 'chart.js'
import reportService from '@/services/reportService'

ChartJS.register(ArcElement, BarElement, CategoryScale, LinearScale, Tooltip, Legend)

const loading = ref(false)
const error = ref('')
const forecastDays = ref(7)
const chartDays = ref(28)
const result = ref({})

const meta = computed(() => result.value.meta || {})
const forecast = computed(() => result.value.forecast || [])
const peakHours = computed(() => result.value.peak_hours || [])
const popularItems = computed(() => result.value.popular_items || [])
const ingredientRows = computed(() => result.value.ingredient_recommendations?.items || [])
const unmappedItems = computed(() => result.value.ingredient_recommendations?.unmapped_menu_items || [])
const unmappedAddons = computed(() => result.value.ingredient_recommendations?.unmapped_addons || [])
const comparison = computed(() => result.value.forecast_comparison || {})
const comparisonItems = computed(() => comparison.value.items || [])
const comparisonDaily = computed(() => comparison.value.daily || [])
const chartRangeLabel = computed(() => chartDays.value === 1 ? 'Yesterday' : `Last ${chartDays.value} days`)
const forecastRows = computed(() => forecast.value.flatMap(day =>
  (day.items || []).map(item => ({ ...item, date: day.date, history_days_used: day.history_days_used })),
))
const forecastTotal = computed(() => forecastRows.value.reduce((total, row) => total + Number(row.forecast_quantity || 0), 0))
const comparisonChartData = computed(() => ({
  labels: comparisonDaily.value.map(row => formatDate(row.date)),
  datasets: [
    { label: 'Forecast', data: comparisonDaily.value.map(row => Number(row.forecast_quantity || 0)), backgroundColor: '#ff9a4d', borderRadius: 4 },
    { label: 'Actual sales', data: comparisonDaily.value.map(row => Number(row.actual_quantity || 0)), backgroundColor: '#171717', borderRadius: 4 },
  ],
}))
const hourlyChartData = computed(() => {
  return {
    labels: popularItems.value.map(row => row.item_name),
    datasets: [{
      label: 'Items sold',
      data: popularItems.value.map(row => Number(row.quantity_sold || 0)),
      backgroundColor: '#f97316',
      borderRadius: 4,
    }],
  }
})
const popularItemsChartData = computed(() => ({
  labels: popularItems.value.map(row => row.item_name),
  datasets: [{
    data: popularItems.value.map(row => Number(row.quantity_sold || 0)),
    backgroundColor: ['#f97316', '#171717', '#fbbf24', '#16a34a', '#3b82f6', '#a855f7', '#ec4899', '#14b8a6', '#64748b', '#ef4444'],
    borderColor: '#fff',
    borderWidth: 2,
  }],
}))
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true, font: { size: 10 } } } },
  scales: {
    x: { grid: { display: false }, ticks: { color: '#667085', maxRotation: 0, autoSkip: true, font: { size: 9 } } },
    y: { beginAtZero: true, grid: { color: '#edf0f4' }, ticks: { color: '#667085', precision: 0, font: { size: 9 } } },
  },
}
const comparisonChartOptions = { ...chartOptions, scales: { ...chartOptions.scales, y: { ...chartOptions.scales.y, ticks: { ...chartOptions.scales.y.ticks, precision: 2 } } } }
const hourlyChartOptions = {
  ...chartOptions,
  indexAxis: 'y',
  plugins: { ...chartOptions.plugins, legend: { display: false } },
  scales: {
    x: { beginAtZero: true, grid: { color: '#edf0f4' }, ticks: { color: '#667085', precision: 0, font: { size: 9 } } },
    y: { grid: { display: false }, ticks: { color: '#667085', autoSkip: false, font: { size: 9 } } },
  },
}
const popularItemsChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true, font: { size: 10 } } } },
}

async function loadForecast() {
  loading.value = true
  error.value = ''
  try {
    const response = await reportService.getDemandForecast({ forecast_days: forecastDays.value, chart_days: chartDays.value })
    result.value = response?.data || {}
  } catch (requestError) {
    error.value = requestError?.response?.data?.message || 'Unable to load the demand forecast.'
  } finally {
    loading.value = false
  }
}

function setRange(days) {
  if (forecastDays.value === days) return
  forecastDays.value = days
  loadForecast()
}

function setChartRange(days) {
  if (chartDays.value === days) return
  chartDays.value = days
  loadForecast()
}

function number(value) {
  return Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 2 })
}

function formatDate(value) {
  if (!value) return '—'
  return new Date(`${value}T00:00:00`).toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatHour(hour) {
  const localHour = ((Number(hour) % 24) + 24) % 24
  const displayHour = localHour % 12 || 12
  return `${displayHour} ${localHour < 12 ? 'AM' : 'PM'}`
}

onMounted(loadForecast)
</script>

<style scoped>
.demand-forecast-report { display: grid; gap: 16px; color: #172033; }
.forecast-header { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
.forecast-header h2 { margin: 0; font-size: 18px; font-weight: 750; }
.forecast-header p, .forecast-panel-note, .forecast-footnote { margin: 4px 0 0; color: #667085; font-size: 12px; }
.forecast-range-switch { display: inline-flex; gap: 4px; padding: 3px; border: 1px solid #f1e3d5; border-radius: 8px; background: #fffaf5; }
.forecast-range-switch button { padding: 7px 11px; border: 0; border-radius: 6px; background: transparent; color: #475467; font-size: 12px; cursor: pointer; }
.forecast-range-switch button.active { background: #171717; color: #f97316; }
.forecast-range-switch button:disabled { opacity: .55; cursor: wait; }
.forecast-chart-filter { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.forecast-chart-filter > span { color: #667085; font-size: 11px; font-weight: 650; }
.forecast-summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
.forecast-summary-card, .forecast-panel { min-width: 0; padding: 14px; border: 1px solid #f1e3d5; border-radius: 10px; background: #fff; }
.forecast-summary-card span { display: block; color: #667085; font-size: 11px; }
.forecast-summary-card strong { display: block; margin-top: 5px; color: #111827; font-size: 17px; }
.forecast-chart-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
.forecast-chart-panel { min-height: 275px; }
.forecast-chart-canvas { position: relative; height: 220px; }
.forecast-comparison-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin: 10px 0; }
.forecast-comparison-summary > div { display: grid; gap: 3px; padding: 9px 10px; border: 1px solid #f1e3d5; border-radius: 8px; background: #fffaf5; }
.forecast-comparison-summary span { color: #667085; font-size: 10px; }
.forecast-comparison-summary strong { color: #172033; font-size: 13px; }
.forecast-over { color: #b42318 !important; }
.forecast-under { color: #027a48 !important; }
.forecast-panel h3 { margin: 0 0 10px; font-size: 14px; font-weight: 700; }
.forecast-insights-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.forecast-table-scroll { overflow-x: auto; }
.forecast-panel table { width: 100%; border-collapse: collapse; font-size: 12px; }
.forecast-panel th, .forecast-panel td { padding: 8px 9px; border-bottom: 1px solid #f1f2f4; text-align: left; white-space: nowrap; }
.forecast-panel th { background: #fff7ed; color: #667085; font-size: 10px; font-weight: 700; }
.forecast-panel td small { color: #667085; }
.forecast-empty { padding: 15px !important; color: #8a94a6; text-align: center !important; }
.forecast-message { padding: 11px 13px; border: 1px solid #e7e5e4; border-radius: 8px; background: #fafaf9; color: #57534e; font-size: 12px; }
.forecast-message.is-error { border-color: #fecaca; background: #fef2f2; color: #b91c1c; }
.forecast-message.is-warning { margin-bottom: 10px; border-color: #fed7aa; background: #fff7ed; color: #9a3412; }
.forecast-warning-text { color: #c2410c; font-weight: 650; }
.forecast-footnote { font-size: 11px; }
@media (max-width: 760px) { .forecast-summary-grid, .forecast-comparison-summary, .forecast-insights-grid, .forecast-chart-grid { grid-template-columns: 1fr; } }
</style>
