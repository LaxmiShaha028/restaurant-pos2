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
          <span>Most popular item (28 days)</span>
          <strong>{{ popularItems[0]?.item_name || 'No data' }}</strong>
        </article>
      </div>

      <div v-if="meta.history_days_used === 0" class="forecast-message">
        No non-cancelled orders were found in the forecast history period.
      </div>

      <div class="forecast-panel">
        <h3>Expected menu demand</h3>
        <div class="forecast-table-scroll">
          <table>
            <thead><tr><th>Date</th><th>Menu item</th><th>Forecast quantity</th><th>Matching sales days</th></tr></thead>
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

      <div class="forecast-insights-grid">
        <div class="forecast-panel">
          <h3>Peak ordering hours · last 28 days</h3>
          <div class="forecast-table-scroll">
            <table>
              <thead><tr><th>Hour</th><th>Item quantity</th><th>Orders</th></tr></thead>
              <tbody>
                <tr v-for="row in peakHours.slice(0, 8)" :key="row.hour">
                  <td>{{ formatHour(row.hour) }}</td><td>{{ number(row.quantity_sold) }}</td><td>{{ row.order_count }}</td>
                </tr>
                <tr v-if="!peakHours.length"><td colspan="3" class="forecast-empty">No sales data yet.</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="forecast-panel">
          <h3>Popular menu items · last 28 days</h3>
          <div class="forecast-table-scroll">
            <table>
              <thead><tr><th>Menu item</th><th>Quantity ordered</th></tr></thead>
              <tbody>
                <tr v-for="row in popularItems" :key="row.menu_item_id">
                  <td>{{ row.item_name }}</td><td>{{ number(row.quantity_sold) }}</td>
                </tr>
                <tr v-if="!popularItems.length"><td colspan="2" class="forecast-empty">No sales data yet.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="forecast-panel">
        <h3>Ingredient purchase suggestions</h3>
        <p class="forecast-panel-note">Suggested quantity covers forecast usage and the restaurant minimum stock, after current stock and open purchase orders.</p>
        <div v-if="unmappedItems.length" class="forecast-message is-warning">
          Recipe mapping is missing for: {{ unmappedItems.map(item => item.item_name).join(', ') }}. Add recipe ingredients to calculate their stock needs.
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
import reportService from '@/services/reportService'

const loading = ref(false)
const error = ref('')
const forecastDays = ref(7)
const result = ref({})

const meta = computed(() => result.value.meta || {})
const forecast = computed(() => result.value.forecast || [])
const peakHours = computed(() => result.value.peak_hours || [])
const popularItems = computed(() => result.value.popular_items || [])
const ingredientRows = computed(() => result.value.ingredient_recommendations?.items || [])
const unmappedItems = computed(() => result.value.ingredient_recommendations?.unmapped_menu_items || [])
const forecastRows = computed(() => forecast.value.flatMap(day =>
  (day.items || []).map(item => ({ ...item, date: day.date, history_days_used: day.history_days_used })),
))
const forecastTotal = computed(() => forecastRows.value.reduce((total, row) => total + Number(row.forecast_quantity || 0), 0))

async function loadForecast() {
  loading.value = true
  error.value = ''
  try {
    const response = await reportService.getDemandForecast({ forecast_days: forecastDays.value })
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

function number(value) {
  return Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 2 })
}

function formatDate(value) {
  return new Date(`${value}T00:00:00`).toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatHour(hour) {
  const value = new Date()
  value.setHours(Number(hour), 0, 0, 0)
  return value.toLocaleTimeString(undefined, { hour: 'numeric', hour12: true })
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
.forecast-summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
.forecast-summary-card, .forecast-panel { min-width: 0; padding: 14px; border: 1px solid #f1e3d5; border-radius: 10px; background: #fff; }
.forecast-summary-card span { display: block; color: #667085; font-size: 11px; }
.forecast-summary-card strong { display: block; margin-top: 5px; color: #111827; font-size: 17px; }
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
@media (max-width: 760px) { .forecast-summary-grid { grid-template-columns: 1fr; } .forecast-insights-grid { grid-template-columns: 1fr; } }
</style>
