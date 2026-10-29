<template>
  <main class="offers-page table-management-page">
    <header class="offers-header table-management-header">
      <div class="table-management-header-left">
        <span class="table-management-header-icon"><i class="bi bi-tags"></i></span>
        <div>
        <p class="table-management-eyebrow">Promotions</p>
        <h1 class="table-management-title">Offer Management</h1>
        <p class="table-management-description">Set automatic discounts, customer promotions and usage limits.</p>
        </div>
      </div>
      <button class="btn btn-primary table-management-add primary-button" type="button" @click="startCreate">
        <i class="bi bi-plus-lg"></i><span>Create offer</span>
      </button>
    </header>

    <div v-if="successMessage" class="offer-alert success">{{ successMessage }}</div>
    <div v-if="errorMessage" class="offer-alert error">{{ errorMessage }}</div>

    <section class="offer-metrics">
      <article><span><i class="bi bi-tags"></i></span><div><small>Total offers</small><strong>{{ summary.total_offers || 0 }}</strong></div></article>
      <article><span class="green"><i class="bi bi-lightning-charge"></i></span><div><small>Active offers</small><strong>{{ summary.active_offers || 0 }}</strong></div></article>
      <article><span class="blue"><i class="bi bi-ticket-perforated"></i></span><div><small>Redemptions</small><strong>{{ summary.redemptions || 0 }}</strong></div></article>
      <article><span class="purple"><i class="bi bi-cash-stack"></i></span><div><small>Discount given</small><strong>{{ money(summary.discount_total) }}</strong></div></article>
    </section>

    <nav class="offer-tabs" aria-label="Offer management sections">
      <button :class="{ active: activeTab === 'offers' }" @click="activeTab = 'offers'">Offers</button>
      <button :class="{ active: activeTab === 'reports' }" @click="openReports">Performance report</button>
    </nav>

    <section v-if="showForm" class="offer-form-card">
      <header><div><h2>{{ editingId ? 'Edit offer' : 'Create offer' }}</h2><p>Eligible offers are automatically applied when an order is created.</p></div><button class="icon-button" aria-label="Close form" @click="closeForm"><i class="bi bi-x-lg"></i></button></header>
      <form @submit.prevent="saveOffer">
        <div class="form-grid">
          <label class="wide">Offer name<input v-model.trim="form.name" required maxlength="120" placeholder="e.g. Happy hour — half price starters" /></label>
          <label>Promotion type<select v-model="form.type"><option value="automatic">Automatic time offer</option><option value="weekday_weekend">Weekday / weekend pricing</option><option value="birthday">Birthday promotion</option><option value="loyalty">Loyalty reward</option></select></label>
          <label>Discount type<select v-model="form.discount_type"><option value="percentage">Percentage (%)</option><option value="fixed">Fixed amount (৳)</option></select></label>
          <label>Discount value<input v-model.number="form.discount_value" type="number" min="0.01" step="0.01" required /></label>
          <label>Minimum order (৳)<input v-model.number="form.minimum_order_amount" type="number" min="0" step="0.01" /></label>
          <fieldset class="wide day-field"><legend>Valid days <small>(leave empty for every day)</small></legend><label v-for="day in weekDays" :key="day.value" class="day-option"><input v-model="form.days_of_week" type="checkbox" :value="day.value" /><span>{{ day.label }}</span></label></fieldset>
          <label>Start time<input v-model="form.start_time" type="time" /></label>
          <label>End time<input v-model="form.end_time" type="time" /></label>
          <label>Valid from<input v-model="form.valid_from" type="date" /></label>
          <label>Valid until<input v-model="form.valid_until" type="date" /></label>
          <label>Total usage limit<input v-model.number="form.usage_limit" type="number" min="1" placeholder="Unlimited" /></label>
          <label>Uses per customer<input v-model.number="form.per_customer_limit" type="number" min="1" placeholder="Unlimited" /></label>
          <label v-if="form.type === 'loyalty'">Minimum completed orders<input v-model.number="form.minimum_customer_orders" type="number" min="0" /></label>
          <label v-if="form.type === 'birthday'">Birthday window (days)<input v-model.number="form.birthday_window_days" type="number" min="0" max="30" /></label>
          <label class="wide">Description<textarea v-model.trim="form.description" rows="2" maxlength="2000" placeholder="Offer terms or internal notes"></textarea></label>
          <label class="active-toggle"><input v-model="form.is_active" type="checkbox" /> Offer is active</label>
        </div>
        <footer><button class="secondary-button" type="button" @click="closeForm">Cancel</button><button class="primary-button" type="submit" :disabled="saving"><i class="bi bi-check2"></i> {{ saving ? 'Saving…' : 'Save offer' }}</button></footer>
      </form>
    </section>

    <section v-if="activeTab === 'offers'" class="offer-list-card">
      <header><div><h2>Offers</h2><p>{{ offers.length }} promotion{{ offers.length === 1 ? '' : 's' }} configured</p></div><button class="refresh-button" :disabled="loading" @click="loadOffers"><i class="bi bi-arrow-clockwise"></i> Refresh</button></header>
      <div v-if="loading" class="offer-empty"><span class="spinner-border spinner-border-sm"></span> Loading offers…</div>
      <div v-else-if="!offers.length" class="offer-empty"><i class="bi bi-tag"></i><strong>No offers yet</strong><span>Create an offer to start scheduling discounts.</span></div>
      <div v-else class="table-scroll"><table><thead><tr><th>Offer</th><th>Type</th><th>Discount</th><th>Schedule</th><th>Validity</th><th>Usage</th><th>Status</th><th>ACTIONS</th></tr></thead><tbody><tr v-for="offer in offers" :key="offer.id"><td><strong>{{ offer.name }}</strong><small>{{ offer.description || '—' }}</small></td><td><span class="type-badge">{{ typeName(offer.type) }}</span></td><td><strong>{{ discountLabel(offer) }}</strong><small>Min. {{ money(offer.minimum_order_amount) }}</small></td><td><small>{{ daySummary(offer.days_of_week) }}</small><small>{{ timeSummary(offer) }}</small></td><td><small>{{ offer.valid_from || 'Always' }}</small><small>to {{ offer.valid_until || 'No end date' }}</small></td><td><strong>{{ offer.redemption_count || 0 }}<small v-if="offer.usage_limit"> / {{ offer.usage_limit }}</small></strong><small>{{ money(offer.discount_total) }} given</small></td><td><span class="status-badge" :class="offer.is_active ? 'on' : 'off'">{{ offer.is_active ? 'Active' : 'Inactive' }}</span></td><td><div class="row-actions"><button title="Edit" aria-label="Edit offer" @click="startEdit(offer)"><i class="bi bi-pencil"></i></button><button title="Delete" aria-label="Delete offer" @click="deleteOffer(offer)"><i class="bi bi-x-circle"></i></button><button :title="offer.is_active ? 'Deactivate' : 'Activate'" :aria-label="offer.is_active ? 'Deactivate offer' : 'Activate offer'" @click="toggleOffer(offer)"><i class="bi bi-arrow-left-right"></i></button></div></td></tr></tbody></table></div>
    </section>

    <section v-else class="offer-list-card report-card">
      <header><div><h2>Promotional performance</h2><p>Discount usage and net sales for the selected period.</p></div><button class="refresh-button" :disabled="reportLoading" @click="loadReport"><i class="bi bi-arrow-clockwise"></i> Refresh</button></header>
      <form class="report-filters" @submit.prevent="loadReport"><label>From<input v-model="reportFilters.from" type="date" /></label><label>To<input v-model="reportFilters.to" type="date" /></label><button class="primary-button" type="submit">Apply</button></form>
      <div class="report-totals"><div><small>Redemptions</small><strong>{{ reportTotals.redemptions || 0 }}</strong></div><div><small>Discount total</small><strong>{{ money(reportTotals.discount_total) }}</strong></div><div><small>Net sales</small><strong>{{ money(reportTotals.net_sales) }}</strong></div></div>
      <div v-if="reportLoading" class="offer-empty"><span class="spinner-border spinner-border-sm"></span> Loading report…</div>
      <div v-else class="table-scroll"><table><thead><tr><th>Offer</th><th>Type</th><th>Redemptions</th><th>Discount total</th><th>Net sales</th></tr></thead><tbody><tr v-for="row in reportRows" :key="row.offer_id"><td><strong>{{ row.name }}</strong></td><td>{{ typeName(row.type) }}</td><td>{{ row.redemptions }}</td><td>{{ money(row.discount_total) }}</td><td>{{ money(row.net_sales) }}</td></tr><tr v-if="!reportRows.length"><td colspan="5" class="empty-row">No offer usage for this period.</td></tr></tbody></table></div>
    </section>
  </main>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import offerService from '@/services/offerService'

const weekDays = [
  { value: 'mon', label: 'Mon' }, { value: 'tue', label: 'Tue' },
  { value: 'wed', label: 'Wed' }, { value: 'thu', label: 'Thu' },
  { value: 'fri', label: 'Fri' }, { value: 'sat', label: 'Sat' },
  { value: 'sun', label: 'Sun' },
]

const newOffer = () => ({
  name: '', description: '', type: 'automatic', discount_type: 'percentage',
  discount_value: 10, days_of_week: ['mon', 'tue', 'wed', 'thu', 'fri'],
  start_time: '', end_time: '', valid_from: '', valid_until: '',
  minimum_order_amount: 0, usage_limit: null, per_customer_limit: null,
  minimum_customer_orders: 5, birthday_window_days: 7, is_active: true,
})

const activeTab = ref('offers')
const offers = ref([])
const summary = ref({})
const loading = ref(false)
const saving = ref(false)
const showForm = ref(false)
const editingId = ref(null)
const successMessage = ref('')
const errorMessage = ref('')
const form = reactive(newOffer())
const reportLoading = ref(false)
const reportRows = ref([])
const reportTotals = ref({})
const reportFilters = reactive({ from: '', to: '' })

const money = (value) => `৳ ${Number(value || 0).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
const typeName = (type) => ({ automatic: 'Automatic', weekday_weekend: 'Weekday / weekend', birthday: 'Birthday', loyalty: 'Loyalty' }[type] || type)
const discountLabel = (offer) => offer.discount_type === 'percentage' ? `${Number(offer.discount_value)}% off` : `${money(offer.discount_value)} off`
const daySummary = (days) => !days?.length || days.length === 7 ? 'Every day' : days.map((day) => weekDays.find((entry) => entry.value === day)?.label || day).join(', ')
const timeSummary = (offer) => offer.start_time && offer.end_time ? `${String(offer.start_time).slice(0, 5)}–${String(offer.end_time).slice(0, 5)}` : 'All day'

function clearMessages() {
  errorMessage.value = ''
  successMessage.value = ''
}

async function loadOffers() {
  loading.value = true
  try {
    const response = await offerService.list()
    offers.value = response?.data || []
    summary.value = response?.summary || {}
  } catch (error) {
    errorMessage.value = offerService.getErrorMessage(error, 'Could not load offers.')
  } finally {
    loading.value = false
  }
}

function startCreate() {
  clearMessages()
  editingId.value = null
  Object.assign(form, newOffer())
  showForm.value = true
}

function startEdit(offer) {
  clearMessages()
  editingId.value = offer.id
  Object.assign(form, newOffer(), {
    ...offer,
    start_time: offer.start_time ? String(offer.start_time).slice(0, 5) : '',
    end_time: offer.end_time ? String(offer.end_time).slice(0, 5) : '',
    valid_from: offer.valid_from || '',
    valid_until: offer.valid_until || '',
    usage_limit: offer.usage_limit ?? null,
    per_customer_limit: offer.per_customer_limit ?? null,
  })
  showForm.value = true
}

function closeForm() {
  showForm.value = false
  editingId.value = null
}

async function saveOffer() {
  clearMessages()
  saving.value = true
  const payload = {
    ...form,
    start_time: form.start_time || null,
    end_time: form.end_time || null,
    valid_from: form.valid_from || null,
    valid_until: form.valid_until || null,
    usage_limit: form.usage_limit || null,
    per_customer_limit: form.per_customer_limit || null,
    minimum_order_amount: Number(form.minimum_order_amount || 0),
  }
  try {
    if (editingId.value) await offerService.update(editingId.value, payload)
    else await offerService.create(payload)
    successMessage.value = editingId.value ? 'Offer updated.' : 'Offer created.'
    closeForm()
    await loadOffers()
  } catch (error) {
    errorMessage.value = offerService.getErrorMessage(error, 'Could not save offer.')
  } finally {
    saving.value = false
  }
}

async function toggleOffer(offer) {
  startEdit(offer)
  form.is_active = !offer.is_active
  await saveOffer()
}

async function deleteOffer(offer) {
  if (!window.confirm(`Delete “${offer.name}”? Offers with order history must be deactivated.`)) return
  clearMessages()
  try {
    await offerService.remove(offer.id)
    successMessage.value = 'Offer deleted.'
    await loadOffers()
  } catch (error) {
    errorMessage.value = offerService.getErrorMessage(error, 'Could not delete offer.')
  }
}

async function openReports() {
  activeTab.value = 'reports'
  await loadReport()
}

async function loadReport() {
  reportLoading.value = true
  try {
    const response = await offerService.report(reportFilters)
    reportRows.value = response?.data?.offers || []
    reportTotals.value = response?.data?.totals || {}
  } catch (error) {
    errorMessage.value = offerService.getErrorMessage(error, 'Could not load offer report.')
  } finally {
    reportLoading.value = false
  }
}

onMounted(loadOffers)
</script>

<style scoped>
.offers-page{padding:20px;color:#172033}.offers-header{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:18px}.offers-eyebrow{margin:0 0 4px;color:#f97316;font-size:10px;font-weight:900;letter-spacing:.13em}.offers-header h1{margin:0;font-size:23px;font-weight:850}.offers-header p{margin:5px 0 0;color:#7b8798;font-size:12px}.primary-button,.secondary-button,.refresh-button{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:36px;padding:8px 12px;border:0;border-radius:7px;font-size:11px;font-weight:750;cursor:pointer}.primary-button{background:#111827;color:#fff}.primary-button:disabled{opacity:.6;cursor:wait}.secondary-button,.refresh-button{border:1px solid #dce3ec;background:#fff;color:#344054}.offer-alert{margin:0 0 12px;padding:10px 12px;border-radius:7px;font-size:12px}.offer-alert.success{border:1px solid #bbf7d0;background:#f0fdf4;color:#15803d}.offer-alert.error{border:1px solid #fecaca;background:#fef2f2;color:#b91c1c}.offer-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:14px}.offer-metrics article{display:flex;align-items:center;gap:10px;padding:12px;border:1px solid #e5eaf0;border-radius:9px;background:#fff}.offer-metrics article>span{display:grid;width:34px;height:34px;place-items:center;border-radius:8px;background:#fff7ed;color:#f97316}.offer-metrics article>span.green{background:#ecfdf5;color:#059669}.offer-metrics article>span.blue{background:#eff6ff;color:#2563eb}.offer-metrics article>span.purple{background:#f5f3ff;color:#7c3aed}.offer-metrics small,.report-totals small{display:block;color:#758195;font-size:10px}.offer-metrics strong{display:block;margin-top:2px;font-size:16px}.offer-tabs{display:flex;gap:5px;margin-bottom:12px;padding:4px;border:1px solid #e5eaf0;border-radius:8px;background:#f8fafc;width:max-content}.offer-tabs button{padding:7px 12px;border:0;border-radius:6px;background:transparent;color:#64748b;font-size:11px;font-weight:700}.offer-tabs button.active{background:#fff;color:#111827;box-shadow:0 1px 3px #1112}.offer-list-card,.offer-form-card{margin-bottom:14px;border:1px solid #e5eaf0;border-radius:10px;background:#fff;box-shadow:0 2px 8px #15223808}.offer-list-card>header,.offer-form-card>header{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 15px;border-bottom:1px solid #edf0f4}.offer-list-card h2,.offer-form-card h2{margin:0;font-size:14px;font-weight:800}.offer-list-card header p,.offer-form-card header p{margin:3px 0 0;color:#8290a2;font-size:10px}.offer-form-card form{padding:14px}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.form-grid>label{display:flex;flex-direction:column;gap:4px;color:#475569;font-size:10px;font-weight:750}.form-grid>label.wide,.day-field{grid-column:1/-1}.form-grid input:not([type=checkbox]),.form-grid select,.form-grid textarea{width:100%;min-height:34px;padding:7px 9px;border:1px solid #dbe2eb;border-radius:6px;background:#fff;color:#182235;font:12px Arial,sans-serif}.form-grid textarea{resize:vertical}.day-field{display:flex;flex-wrap:wrap;gap:6px;margin:0;padding:8px;border:1px solid #e1e7ef;border-radius:7px}.day-field legend{float:none;width:auto;margin:0 0 3px;padding:0 4px;color:#475569;font-size:10px;font-weight:750}.day-field legend small{color:#8a94a6;font-weight:400}.day-option{display:flex;align-items:center;gap:4px;padding:5px 7px;border-radius:5px;background:#f8fafc;color:#475569;font-size:10px}.day-option input,.active-toggle input{accent-color:#f97316}.form-grid .active-toggle{grid-column:1/-1;display:flex;align-items:center;flex-direction:row;gap:7px}.offer-form-card form>footer{display:flex;justify-content:flex-end;gap:8px;margin-top:14px;padding-top:11px;border-top:1px solid #edf0f4}.icon-button{width:30px;height:30px;border:1px solid #e5eaf0;border-radius:6px;background:#fff;color:#667085}.table-scroll{overflow-x:auto}table{width:100%;border-collapse:collapse}th{padding:9px 11px;background:#f8fafc;color:#708096;text-align:left;font-size:9px;letter-spacing:.04em;text-transform:uppercase}td{padding:10px 11px;border-top:1px solid #edf0f4;color:#344054;font-size:10px;vertical-align:middle}td strong{display:block;color:#182235;font-size:10px}td small{display:block;margin-top:3px;color:#8994a4;font-size:9px;white-space:nowrap}.type-badge,.status-badge{display:inline-block;padding:4px 7px;border-radius:999px;background:#f1f5f9;color:#475569;font-size:9px;font-weight:700;white-space:nowrap}.status-badge.on{background:#dcfce7;color:#15803d}.status-badge.off{background:#f1f5f9;color:#64748b}.row-actions{display:flex;gap:4px}.row-actions button{width:26px;height:26px;border:1px solid #e3e8ef;border-radius:5px;background:#fff;color:#475569}.row-actions button:hover{border-color:#fed7aa;color:#ea580c}.offer-empty{display:flex;min-height:100px;align-items:center;justify-content:center;gap:7px;color:#8893a4;font-size:11px}.offer-empty strong{color:#344054}.offer-empty>i{font-size:19px}.report-filters{display:flex;align-items:end;gap:8px;padding:12px 15px;border-bottom:1px solid #edf0f4}.report-filters label{display:flex;flex-direction:column;gap:4px;color:#475569;font-size:10px;font-weight:700}.report-filters input{height:32px;padding:5px 8px;border:1px solid #dbe2eb;border-radius:6px}.report-totals{display:flex;gap:30px;padding:12px 15px}.report-totals strong{display:block;margin-top:3px;font-size:15px}.empty-row{text-align:center;color:#8994a4}@media(max-width:800px){.offer-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.offers-page{padding:12px}.offers-header h1{font-size:20px}}@media(max-width:520px){.offers-header{align-items:flex-start;flex-direction:column}.form-grid{grid-template-columns:1fr}.form-grid>label.wide,.day-field{grid-column:auto}.report-totals{justify-content:space-between;gap:8px}.report-filters{flex-wrap:wrap}}
.offers-page.table-management-page{min-height:100%;padding:18px;border-radius:24px;background:radial-gradient(circle at 0 0,rgba(249,115,22,.055),transparent 28%),radial-gradient(circle at 100% 8%,rgba(251,146,60,.045),transparent 25%),#f4f1ed;color:#111827}.offers-header.table-management-header{min-height:100px;margin:0 0 12px;padding:18px 20px;border:1px solid #eee3da;border-radius:14px;background:#fff;box-shadow:0 1px 2px rgba(15,23,42,.04)}.offers-header .table-management-header-icon{width:40px;height:40px;flex:0 0 40px;border-radius:12px;background:#fff1e6;color:#f97316}.offers-header .table-management-eyebrow,.offers-header .table-management-title,.offers-header .table-management-description{color:#111827}.offers-header .table-management-eyebrow{margin:0 0 1px;font-size:10px;font-weight:700;line-height:1.1;letter-spacing:.03em;text-transform:uppercase}.offers-header .table-management-title{margin:0;font-size:15px;font-weight:700;line-height:1.2}.offers-header .table-management-description{max-width:650px;margin:2px 0 0;color:#111827;font-size:12px;line-height:1.3;white-space:nowrap}.offers-header .table-management-add,.offers-header .table-management-add:hover{min-height:28px;margin:0;padding:7px 8px;border:1px solid #292929;border-radius:10px;background:linear-gradient(145deg,#191919 0%,#111 54%,#090909 100%);color:#f97316}.offers-header .table-management-add i{color:#f97316}.offer-metrics{gap:8px;margin-bottom:8px}.offer-metrics article,.offer-list-card,.offer-form-card{border-color:#eee3da;border-radius:14px;background:#fff;box-shadow:0 1px 2px rgba(15,23,42,.04)}.offer-metrics article{min-height:52px;padding:8px 10px}.offer-tabs{margin:0 0 8px;border-color:#eee3da;background:#fff}.offer-tabs button.active{background:#fff1e6;color:#c2410c;box-shadow:0 1px 2px rgba(15,23,42,.08)}.offer-list-card,.offer-form-card{margin-bottom:8px}.offer-list-card>header,.offer-form-card>header{padding:10px 12px}.offer-form-card form{padding:12px}.form-grid{gap:8px}.offer-list-card>header,.offer-form-card>header,.offer-form-card form>footer{border-color:#eee3da}th{background:#f8f3ea;color:#111827}td{color:#111827}.row-actions button,.row-actions button:nth-child(3){display:inline-grid;width:28px;min-width:28px;height:28px;min-height:28px;align-items:center;justify-content:center;padding:0;border:1px solid #f6dfa0;border-radius:8px;background:#fff2c7;color:#d97706;line-height:1;cursor:pointer;transition:border-color .16s ease,background .16s ease,box-shadow .16s ease,transform .16s ease}.row-actions button i{color:#d97706;font-size:13px}.row-actions button:nth-child(2){border-color:#ffb7bf;background:#fff;color:#f06470}.row-actions button:nth-child(2) i{color:#f06470}.row-actions button:hover{transform:translateY(-1px);border-color:#f2cf6f;background:#ffe8a3;color:#b45309;box-shadow:0 3px 8px #0f172a1a}.row-actions button:nth-child(2):hover{border-color:#f47b87;background:#fff3f4;color:#f06470}.secondary-button,.refresh-button{border-color:#fed7aa;background:#fff1e6;color:#111827}.secondary-button:hover,.refresh-button:hover{border-color:#fdba74;background:#ffedd5;color:#c2410c}.offer-alert{margin-bottom:8px;padding:8px 10px}@media(max-width:767.98px){.offers-page.table-management-page{padding:10px;border-radius:18px}.offers-header.table-management-header{padding:14px}}
</style>

<style src="@/assets/css/table-management.css"></style>
