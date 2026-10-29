<template>
  <section class="pos-workspace-page">
    <div v-if="loading" class="pos-full-loading">
      <span class="spinner-border"></span>
      <div><strong>Preparing POS</strong><p>Loading tables, menu, billing settings and waiter information.</p></div>
    </div>

    <div v-else-if="errorMessage && !menuItems.length" class="pos-load-error">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <h2>POS could not be loaded</h2>
      <p>{{ errorMessage }}</p>
      <button type="button" class="pos-primary-button" @click="loadPage">Try Again</button>
    </div>

    <template v-else>
      <header class="pos-workspace-header">
        <div class="pos-page-header-left">
          <button
            type="button"
            class="pos-back-button"
            title="Back to table selection"
            @click="backToTables"
          >
            <i class="bi bi-arrow-left"></i>
          </button>

          <span class="pos-page-header-icon">
            <i class="bi bi-cart3"></i>
          </span>

          <div>
            <p class="pos-eyebrow">POS Order</p>
            <h1>{{ settings.restaurant_name || 'Restaurant POS' }}</h1>
            <p class="pos-page-subtitle">
  Fast order entry using the existing order workflow
</p>
          </div>
        </div>

        <div class="pos-workspace-header-actions">
         <div class="pos-workspace-table-strip">
  <span>
    <i class="bi bi-grid-3x3-gap-fill"></i>
    Table
  </span>

  <strong>
    {{ selectedTableNames || 'No table selected' }}
  </strong>

  <button type="button" @click="backToTables">
    Change
  </button>
</div>

          <div class="pos-workspace-waiter">
            <span><i class="bi bi-person-badge"></i></span>
            <div><small>Waiter</small><strong>{{ waiterName }}</strong></div>
          </div>
        </div>
      </header>

      <div v-if="errorMessage" class="pos-alert pos-alert-danger pos-inline-alert">
        <i class="bi bi-exclamation-triangle-fill"></i><span>{{ errorMessage }}</span>
      </div>

      <div class="pos-main-layout">
        <main class="pos-menu-workspace">
          <div class="pos-menu-toolbar">
            <div class="pos-search-control large">
              <i class="bi bi-search"></i>
              <input v-model.trim="menuSearch" type="search" placeholder="Search menu..." />
            </div>
            <div class="pos-menu-counter"><strong>{{ filteredMenuItems.length }}</strong><span>menu items</span></div>
          </div>

          <div class="pos-menu-body">
            <aside class="pos-category-rail">
              <button type="button" :class="{ active: selectedCategory === 'all' }" @click="selectedCategory = 'all'">
                <i class="bi bi-book"></i> All Menu
              </button>
              <button
                v-for="category in categories"
                :key="category.id"
                type="button"
                :class="{ active: selectedCategory === String(category.id) }"
                @click="selectedCategory = String(category.id)"
              >
                {{ category.category_name }}
              </button>
            </aside>

            <div class="pos-menu-grid-scroll">
              <div v-if="filteredMenuItems.length" class="pos-menu-grid">
                <PosMenuCard
                  v-for="item in filteredMenuItems"
                  :key="item.id"
                  :item="item"
                  :disabled="submitting"
                  @select="selectMenuItem"
                />
              </div>

              <div v-else class="pos-empty-state">
                <span><i class="bi bi-search"></i></span>
                <h2>No menu items found</h2><p>Try another category or search keyword.</p>
              </div>
            </div>
          </div>
        </main>

        <PosOrderPanel
          :lines="cartDisplayLines"
          :total-quantity="totalQuantity"
          :table-names="selectedTableNames"
          :order-type="selectedTableIds.length ? 'dine_in' : 'parcel'"
          :waiter-name="waiterName"
          :subtotal="subtotal"
          :discount-type="discountType"
          :discount-value="discountValue"
          :discount-amount="discountAmount"
          :offer-preview="offerPreview"
          :vat-amount="vatAmount"
          :service-amount="serviceAmount"
          :total="grandTotal"
          :vat-label="vatLabel"
          :service-label="serviceLabel"
          :guest-count="guestCount"
          :selected-customer="selectedCustomer"
          :draft-customer="draftCustomer"
          :customer-results="customerResults"
          :customer-searching="customerSearching"
          :customer-search-error="customerSearchError"
          :order-note="orderNote"
          :can-submit="canSubmit"
          :submitting="submitting"
          @increase="increaseLine"
          @decrease="decreaseLine"
          @remove="removeLine"
          @clear-cart="clearCart"
          @update:discount-type="discountType = $event"
          @update:discount-value="discountValue = $event"
          @update:guest-count="guestCount = $event"
          @update:order-note="orderNote = $event"
          @search-customer="searchCustomer"
          @select-customer="selectCustomer"
          @clear-customer="clearCustomer"
          @update:customer-birthday="updateCustomerBirthday"
          @new-customer="showCustomerModal = true"
          @send-order="sendOrder"
          @pay="openPayment"
        />
      </div>
    </template>

    <PosFoodDetailsModal :show="showFoodModal" :item="activeMenuItem" @close="closeFoodModal" @add="addConfiguredItem" />
    <PosCustomerCreateModal :show="showCustomerModal" @close="showCustomerModal = false" @create="stageCustomer" />
    <PosPaymentModal :show="showPaymentModal" :total="grandTotal" :submitting="submitting" @close="showPaymentModal = false" @confirm="confirmPaymentOrder" />
    <PosOrderSuccessModal :show="showSuccessModal" :order="createdOrder" @new-order="startNewPosOrder" @orders="goToOrders" />
  </section>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PosMenuCard from '@/components/pos/PosMenuCard.vue'
import PosFoodDetailsModal from '@/components/pos/PosFoodDetailsModal.vue'
import PosOrderPanel from '@/components/pos/PosOrderPanel.vue'
import PosCustomerCreateModal from '@/components/pos/PosCustomerCreateModal.vue'
import PosPaymentModal from '@/components/pos/PosPaymentModal.vue'
import PosOrderSuccessModal from '@/components/pos/PosOrderSuccessModal.vue'
import posOrderService from '@/services/posOrderService'
import '@/assets/css/pos/pos-order.css'
import '@/assets/css/restaurant-page-theme.css'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const submitting = ref(false)
const errorMessage = ref('')
const tables = ref([])
const categories = ref([])
const menuItems = ref([])
const waiter = ref(null)
const settings = ref({
  restaurant_name: '', vat_type: 'percentage', vat_rate: 0,
  service_charge_type: 'percentage', service_charge_rate: 0,
})

const selectedTableIds = ref([])
const selectedCategory = ref('all')
const menuSearch = ref('')
const cart = ref([])
const activeMenuItem = ref(null)
const showFoodModal = ref(false)
const showCustomerModal = ref(false)
const showPaymentModal = ref(false)
const showSuccessModal = ref(false)
const createdOrder = ref(null)
const guestCount = ref(1)
const selectedCustomer = ref(null)
const draftCustomer = ref(null)
const customerResults = ref([])
const customerSearching = ref(false)
const customerSearchError = ref('')
const orderNote = ref('')
const discountType = ref('amount')
const discountValue = ref(0)
const offerPreview = ref(null)
let offerPreviewRequestId = 0

const selectedTables = computed(() =>
  selectedTableIds.value
    .map((id) => tables.value.find((table) => Number(table.id) === Number(id)))
    .filter(Boolean),
)
const primaryTable = computed(() => selectedTables.value[0] ?? null)
const selectedTableNames = computed(() => selectedTables.value.flatMap((table) => {
  if (!table?.table_name) return []
  return [table.table_name, ...(table.merged_table_names || [])]
}).join(', '))
const waiterName = computed(() => waiter.value?.name || waiter.value?.username || 'Current User')

const filteredMenuItems = computed(() => {
  const search = menuSearch.value.trim().toLowerCase()

  return menuItems.value.filter((item) => {
    const categoryId = item?.menu_category_id ?? item?.category_id ?? item?.category?.id ?? null
    const categoryMatches = selectedCategory.value === 'all' || String(categoryId) === String(selectedCategory.value)
    if (!categoryMatches) return false
    if (!search) return true

    return [item?.menu_name, item?.description, item?.category?.category_name]
      .filter(Boolean).join(' ').toLowerCase().includes(search)
  })
})

function itemVariants(item) {
  return Array.isArray(item?.variants) ? item.variants : []
}

function itemAddons(item) {
  const rows = item?.add_ons ?? item?.addOns ?? item?.addons ?? []
  return Array.isArray(rows) ? rows : []
}

function getItemById(id) {
  return menuItems.value.find((item) => Number(item.id) === Number(id)) ?? null
}

function findVariant(item, variantId) {
  if (!variantId) return null
  return itemVariants(item).find((variant) => Number(variant.id) === Number(variantId)) ?? null
}

function findSelectedAddons(item, addonIds) {
  const ids = new Set((addonIds || []).map(Number))
  return itemAddons(item).filter((addon) => ids.has(Number(addon.id)))
}

function calculateUnitPrice(line) {
  const item = getItemById(line.menu_item_id)
  if (!item) return 0
  const variant = findVariant(item, line.menu_item_variant_id)
  return variant ? Number(variant?.price || 0) : Number(item?.price ?? item?.base_price ?? 0) || 0
}

function calculateAddonUnitTotal(line) {
  const item = getItemById(line.menu_item_id)
  if (!item) return 0
  return findSelectedAddons(item, line.addon_ids).reduce((sum, addon) => sum + Number(addon?.price || 0), 0)
}

function lineTotal(line) {
  return Number(((calculateUnitPrice(line) + calculateAddonUnitTotal(line)) * Number(line.quantity || 1)).toFixed(2))
}

const cartDisplayLines = computed(() => cart.value.map((line) => {
  const item = getItemById(line.menu_item_id)
  const variant = findVariant(item, line.menu_item_variant_id)
  const addons = findSelectedAddons(item, line.addon_ids)
  const optionText = [
    variant ? (variant.variant_name || variant.name) : '',
    addons.length ? addons.map((addon) => addon.add_on_name || addon.name).filter(Boolean).join(', ') : '',
  ].filter(Boolean).join(' • ')

  return {
    ...line,
    name: item?.menu_name || 'Menu Item',
    option_text: optionText,
    unit_price: calculateUnitPrice(line),
    total: lineTotal(line),
  }
}))

const totalQuantity = computed(() => cart.value.reduce((sum, line) => sum + Number(line.quantity || 0), 0))
const subtotal = computed(() => Number(cart.value.reduce((sum, line) => sum + lineTotal(line), 0).toFixed(2)))
const manualDiscountAmount = computed(() => {
  const raw = Math.max(Number(discountValue.value) || 0, 0)
  if (discountType.value === 'percent') {
    return Number((subtotal.value * (Math.min(raw, 100) / 100)).toFixed(2))
  }
  return Math.min(Number(raw.toFixed(2)), subtotal.value)
})
const discountAmount = computed(() => {
  const offerDiscount = Number(offerPreview.value?.offer_discount_amount || 0)
  return offerPreview.value?.offer_id && offerDiscount >= manualDiscountAmount.value
    ? offerDiscount
    : manualDiscountAmount.value
})
const taxableAmount = computed(() => Math.max(Number((subtotal.value - discountAmount.value).toFixed(2)), 0))
const vatAmount = computed(() => {
  const rate = Number(settings.value?.vat_rate || 0)
  if (rate <= 0 || taxableAmount.value <= 0) return 0
  return settings.value?.vat_type === 'fixed'
    ? Number(rate.toFixed(2))
    : Number((taxableAmount.value * (rate / 100)).toFixed(2))
})
const serviceAmount = computed(() => {
  const rate = Number(settings.value?.service_charge_rate || 0)
  if (rate <= 0 || taxableAmount.value <= 0) return 0
  return settings.value?.service_charge_type === 'fixed'
    ? Number(rate.toFixed(2))
    : Number((taxableAmount.value * (rate / 100)).toFixed(2))
})
const grandTotal = computed(() => Number(Math.max(taxableAmount.value + vatAmount.value + serviceAmount.value, 0).toFixed(2)))
const vatLabel = computed(() => settings.value?.vat_type === 'fixed' ? 'VAT' : `VAT ${Number(settings.value?.vat_rate || 0)}%`)
const serviceLabel = computed(() => settings.value?.service_charge_type === 'fixed' ? 'Service Charge' : `Service Charge ${Number(settings.value?.service_charge_rate || 0)}%`)
const canSubmit = computed(() =>
  cart.value.length > 0 && !submitting.value
)

function parseTableQuery() {
  return String(route.query?.tables || '').split(',')
    .map((value) => Number(value.trim()))
    .filter((value) => Number.isInteger(value) && value > 0)
    .filter((value, index, array) => array.indexOf(value) === index)
}

function buildRowId() {
  if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') return crypto.randomUUID()
  return `${Date.now()}-${Math.random().toString(36).slice(2, 10)}`
}

function lineSignature(line) {
  const addons = [...(line.addon_ids || [])].map(Number).sort((a, b) => a - b).join(',')
  return [Number(line.menu_item_id), line.menu_item_variant_id ? Number(line.menu_item_variant_id) : '', addons, String(line.kitchen_note || '').trim()].join('|')
}

function addOrIncrement(incoming) {
  const normalized = {
    row_id: buildRowId(),
    menu_item_id: Number(incoming.menu_item_id),
    menu_item_variant_id: incoming.menu_item_variant_id ? Number(incoming.menu_item_variant_id) : null,
    addon_ids: Array.isArray(incoming.addon_ids) ? incoming.addon_ids.map(Number) : [],
    quantity: Math.max(1, Number(incoming.quantity || 1)),
    kitchen_note: String(incoming.kitchen_note || '').trim(),
  }

  const signature = lineSignature(normalized)
  const existing = cart.value.find((line) => lineSignature(line) === signature)
  if (existing) {
    existing.quantity += normalized.quantity
    return
  }
  cart.value.push(normalized)
}

function selectMenuItem(item) {
  const hasOptions = itemVariants(item).length > 0 || itemAddons(item).length > 0
  if (hasOptions) {
    activeMenuItem.value = item
    showFoodModal.value = true
    return
  }
  addOrIncrement({ menu_item_id: item.id, quantity: 1, addon_ids: [] })
}

function closeFoodModal() {
  showFoodModal.value = false
  activeMenuItem.value = null
}

function addConfiguredItem(line) {
  addOrIncrement(line)
  closeFoodModal()
}

function findLine(rowId) { return cart.value.find((line) => line.row_id === rowId) }
function increaseLine(rowId) { const line = findLine(rowId); if (line) line.quantity += 1 }
function decreaseLine(rowId) {
  const line = findLine(rowId)
  if (!line) return
  if (Number(line.quantity) <= 1) return removeLine(rowId)
  line.quantity -= 1
}
function removeLine(rowId) { cart.value = cart.value.filter((line) => line.row_id !== rowId) }
function clearCart() { cart.value = [] }

async function searchCustomer(query) {
  const value = String(query || '').trim()
  customerSearchError.value = ''
  if (value.length < 2) { customerResults.value = []; return }
  customerSearching.value = true
  try {
    customerResults.value = await posOrderService.searchCustomers(value)
  } catch (error) {
    customerResults.value = []
    customerSearchError.value = error?.response?.status === 403
      ? 'Customer search is not enabled for this role. You may create a new customer or continue as Walk-in.'
      : posOrderService.getErrorMessage(error, 'Unable to search customers.')
  } finally {
    customerSearching.value = false
  }
}

function selectCustomer(customer) {
  selectedCustomer.value = customer
  draftCustomer.value = null
  customerResults.value = []
}
function stageCustomer(customer) {
  draftCustomer.value = customer
  selectedCustomer.value = null
  showCustomerModal.value = false
  customerResults.value = []
}
function clearCustomer() {
  selectedCustomer.value = null
  draftCustomer.value = null
  customerResults.value = []
}

function updateCustomerBirthday(value) {
  if (selectedCustomer.value) {
    selectedCustomer.value = { ...selectedCustomer.value, date_of_birth: value || null }
  } else if (draftCustomer.value) {
    draftCustomer.value = { ...draftCustomer.value, date_of_birth: value || null }
  }
}

function composeOrderNote() {
  const pieces = [`POS Guest Count: ${Math.max(1, Number(guestCount.value || 1))}`]
  const note = String(orderNote.value || '').trim()
  if (note) pieces.push(note)
  return pieces.join('\n')
}

function buildPayload(payment = null) {
  const customer = selectedCustomer.value
  const draft = draftCustomer.value

  return {
    restaurant_table_id: primaryTable.value
  ? Number(primaryTable.value.id)
  : null,

merged_table_ids: selectedTableIds.value
  .slice(1)
  .filter((id) => !(primaryTable.value?.merged_table_ids || []).map(Number).includes(Number(id)))
  .map(Number),
    customer_id: customer?.id ? Number(customer.id) : null,
    customer_name: draft?.name || customer?.name || null,
    customer_phone: draft?.phone || customer?.phone || null,
    customer_email: draft?.email || customer?.email || null,
    date_of_birth: customer?.date_of_birth || draft?.date_of_birth || null,
    status: 'pending',
    discount_amount: manualDiscountAmount.value,
    tax_amount: 0,
    service_charge: 0,
    order_note: composeOrderNote(),
    paid_amount: payment ? Number(payment.amount) : 0,
    payment_method: payment ? payment.payment_method : null,
    payment_reference: payment?.reference || null,
    items: cart.value.map((line) => ({
      menu_item_id: Number(line.menu_item_id),
      menu_item_variant_id: line.menu_item_variant_id ? Number(line.menu_item_variant_id) : null,
      addon_ids: Array.isArray(line.addon_ids) ? line.addon_ids.map(Number) : [],
      quantity: Math.max(1, Number(line.quantity || 1)),
      kitchen_note: String(line.kitchen_note || '').trim() || null,
    })),
  }
}

async function submitOrder(payment = null) {
  if (!canSubmit.value) return
  submitting.value = true
  errorMessage.value = ''
  try {
    const response = await posOrderService.createOrder(buildPayload(payment))
    const body = response?.data ?? response ?? null
    createdOrder.value = body?.data ?? body
    showPaymentModal.value = false
    showSuccessModal.value = true
  } catch (error) {
    errorMessage.value = posOrderService.getErrorMessage(error, 'Unable to create the POS order.')
    showPaymentModal.value = false
  } finally {
    submitting.value = false
  }
}

function sendOrder() { void submitOrder(null) }
async function openPayment() {
  if (!canSubmit.value) return
  await refreshOfferPreview()
  showPaymentModal.value = true
}
function confirmPaymentOrder(payment) { void submitOrder(payment) }
async function backToTables() { await router.push({ name: 'pos-order-tables' }) }
async function startNewPosOrder() { showSuccessModal.value = false; await router.push({ name: 'pos-order-tables' }) }
async function goToOrders() { showSuccessModal.value = false; await router.push({ name: 'order-management' }) }

async function loadPage() {
  loading.value = true
  errorMessage.value = ''
  try {
    const [bootstrap, operationalSettings] = await Promise.all([
      posOrderService.loadBootstrap(),
      posOrderService.loadOperationalSettings(),
    ])

    tables.value = bootstrap.tables
    categories.value = bootstrap.categories
    menuItems.value = bootstrap.menuItems
    waiter.value = bootstrap.waiter
    settings.value = operationalSettings

    const requestedIds = parseTableQuery()
    const availableIds = new Set(
      tables.value.flatMap((table) => [
        Number(table.id),
        ...(table.merged_table_ids || []).map(Number),
      ])
    )

    selectedTableIds.value =
      requestedIds.filter((id) => availableIds.has(id))

  } catch (error) {
    errorMessage.value = posOrderService.getErrorMessage(error, 'Unable to load POS order information.')
  } finally {
    loading.value = false
  }
}

onMounted(loadPage)

watch([subtotal, manualDiscountAmount, () => selectedCustomer.value?.id, () => selectedCustomer.value?.date_of_birth, () => draftCustomer.value?.date_of_birth], () => {
  void refreshOfferPreview()
})

async function refreshOfferPreview() {
  const requestId = ++offerPreviewRequestId
  if (subtotal.value <= 0) {
    offerPreview.value = null
    return
  }
  try {
    const result = await posOrderService.previewDiscount({
      subtotal: subtotal.value,
      manual_discount: manualDiscountAmount.value,
      customer_id: selectedCustomer.value?.id || null,
      date_of_birth: selectedCustomer.value?.date_of_birth || draftCustomer.value?.date_of_birth || null,
    })
    if (requestId === offerPreviewRequestId) offerPreview.value = result || null
  } catch {
    if (requestId === offerPreviewRequestId) offerPreview.value = null
  }
}
</script>
