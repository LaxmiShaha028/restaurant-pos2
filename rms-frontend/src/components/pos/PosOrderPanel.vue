<template>
  <aside class="pos-order-panel">
    <div class="pos-order-tabs">
      <button type="button" :class="{ active: activeTab === 'order' }" @click="activeTab = 'order'">
        Order Details <span>{{ totalQuantity }}</span>
      </button>
      <button type="button" :class="{ active: activeTab === 'guest' }" @click="activeTab = 'guest'">
        Guest Info
      </button>
    </div>

    <div v-if="activeTab === 'order'" class="pos-order-tab-content">
      <div class="pos-order-context">
        <div>
  <span>Order Type</span>

  <strong>
    <i
      :class="
        orderType === 'parcel'
          ? 'bi bi-bag'
          : 'bi bi-fork-knife'
      "
    ></i>

    {{
      orderType === 'parcel'
        ? 'Parcel'
        : 'Dine In'
    }}
  </strong>
</div>
        <div><span>Tables</span><strong>{{ tableNames || 'No table' }}</strong></div>
        <div><span>Waiter</span><strong>{{ waiterName || 'Current User' }}</strong></div>
      </div>

      <div class="pos-cart-heading">
        <div><h3>Food Items</h3><p>{{ totalQuantity }} selected</p></div>
        <button v-if="lines.length" type="button" @click="emit('clear-cart')">Clear All</button>
      </div>

      <div class="pos-cart-list">
        <div v-if="!lines.length" class="pos-cart-empty">
          <span><i class="bi bi-basket"></i></span>
          <h3>Your order is empty</h3>
          <p>Select menu cards to add food items.</p>
        </div>

        <article v-for="line in lines" :key="line.row_id" class="pos-cart-line">
          <div class="pos-cart-line-copy">
            <h4>{{ line.name }}</h4>
            <p v-if="line.option_text">{{ line.option_text }}</p>
            <small v-if="line.kitchen_note"><i class="bi bi-chat-left-text"></i> {{ line.kitchen_note }}</small>
          </div>

          <div class="pos-cart-line-actions">
            <div class="pos-inline-qty">
              <button type="button" @click="emit('decrease', line.row_id)"><i class="bi bi-dash"></i></button>
              <strong>{{ line.quantity }}</strong>
              <button type="button" @click="emit('increase', line.row_id)"><i class="bi bi-plus"></i></button>
            </div>
            <strong class="pos-cart-line-total">{{ money(line.total) }}</strong>
            <button type="button" class="pos-cart-remove" @click="emit('remove', line.row_id)"><i class="bi bi-x-lg"></i></button>
          </div>
        </article>
      </div>

      <div class="pos-bill-controls">
        <div class="pos-discount-control">
          <div>
            <span>Discount</span>
            <select :value="discountType" @change="emit('update:discount-type', $event.target.value)">
              <option value="amount">Amount</option>
              <option value="percent">Percent</option>
            </select>
          </div>
          <input
            :value="discountValue"
            type="number"
            min="0"
            step="0.01"
            @input="emit('update:discount-value', Number($event.target.value || 0))"
          />
        </div>

        <div class="pos-bill-row"><span>Subtotal</span><strong>{{ money(subtotal) }}</strong></div>
        <div class="pos-bill-row"><span>Discount</span><strong>− {{ money(discountAmount) }}</strong></div>
        <div class="pos-bill-row"><span>{{ vatLabel }}</span><strong>{{ money(vatAmount) }}</strong></div>
        <div class="pos-bill-row"><span>{{ serviceLabel }}</span><strong>{{ money(serviceAmount) }}</strong></div>

        <div class="pos-bill-total">
          <div><span>Total</span><small>Final payable amount</small></div>
          <strong>{{ money(total) }}</strong>
        </div>
      </div>
    </div>

    <div v-else class="pos-order-tab-content">
      <PosGuestInfo
        :guest-count="guestCount"
        :selected-customer="selectedCustomer"
        :draft-customer="draftCustomer"
        :results="customerResults"
        :searching="customerSearching"
        :search-error="customerSearchError"
        :order-note="orderNote"
        @update:guest-count="emit('update:guest-count', $event)"
        @update:order-note="emit('update:order-note', $event)"
        @search="emit('search-customer', $event)"
        @select-customer="emit('select-customer', $event)"
        @clear-customer="emit('clear-customer')"
        @new-customer="emit('new-customer')"
      />
    </div>

    <div class="pos-order-actions">
      <button
        type="button"
        class="pos-send-order-button"
        :disabled="!canSubmit || submitting"
        @click="emit('send-order')"
      >
        <i class="bi bi-fire"></i>
        <span>Send Order<small>Pay later</small></span>
      </button>

      <button
        type="button"
        class="pos-pay-button"
        :disabled="!canSubmit || submitting"
        @click="emit('pay')"
      >
        <span v-if="submitting" class="spinner-border spinner-border-sm"></span>
        <i v-else class="bi bi-credit-card"></i>
        Pay {{ money(total) }}
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import PosGuestInfo from '@/components/pos/PosGuestInfo.vue'

defineProps({
  lines: { type: Array, default: () => [] },
  totalQuantity: { type: Number, default: 0 },
  orderType: {
  type: String,
  default: 'dine_in',
},
  tableNames: { type: String, default: '' },
  waiterName: { type: String, default: '' },
  subtotal: { type: Number, default: 0 },
  discountType: { type: String, default: 'amount' },
  discountValue: { type: Number, default: 0 },
  discountAmount: { type: Number, default: 0 },
  vatAmount: { type: Number, default: 0 },
  serviceAmount: { type: Number, default: 0 },
  total: { type: Number, default: 0 },
  vatLabel: { type: String, default: 'VAT' },
  serviceLabel: { type: String, default: 'Service Charge' },
  guestCount: { type: Number, default: 1 },
  selectedCustomer: { type: Object, default: null },
  draftCustomer: { type: Object, default: null },
  customerResults: { type: Array, default: () => [] },
  customerSearching: { type: Boolean, default: false },
  customerSearchError: { type: String, default: '' },
  orderNote: { type: String, default: '' },
  canSubmit: { type: Boolean, default: false },
  submitting: { type: Boolean, default: false },
})

const emit = defineEmits([
  'increase', 'decrease', 'remove', 'clear-cart',
  'update:discount-type', 'update:discount-value',
  'update:guest-count', 'update:order-note',
  'search-customer', 'select-customer', 'clear-customer',
  'new-customer', 'send-order', 'pay',
])

const activeTab = ref('order')

function money(value) {
  return `৳ ${Number(value || 0).toLocaleString('en-BD', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}
</script>
