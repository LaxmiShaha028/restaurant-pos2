<template>
  <Teleport to="body">
    <div v-if="show" class="pos-modal-backdrop" @click.self="close">
      <form class="pos-modal pos-payment-modal" @submit.prevent="submit">
        <header class="pos-modal-header">
          <div class="pos-modal-title">
            <span><i class="bi bi-credit-card"></i></span>
            <div><p>POS Checkout</p><h2>Order Payment</h2></div>
          </div>
          <button type="button" class="pos-modal-close" :disabled="submitting" @click="close"><i class="bi bi-x-lg"></i></button>
        </header>

        <div class="pos-modal-body">
          <div class="pos-payment-total"><span>Order Total</span><strong>{{ money(total) }}</strong></div>

          <div class="pos-payment-methods">
            <label
              v-for="method in methods"
              :key="method.value"
              class="pos-payment-method"
              :class="{ selected: paymentMethod === method.value }"
            >
              <input v-model="paymentMethod" type="radio" name="pos-payment-method" :value="method.value" />
              <span class="icon"><i :class="method.icon"></i></span>
              <span>{{ method.label }}</span>
              <i class="bi bi-check-circle-fill check"></i>
            </label>
          </div>

          <div class="pos-payment-form-grid">
            <label>
              <span>Amount</span>
              <input v-model.number="amount" type="number" min="0.01" :max="total" step="0.01" />
              <small>You may collect a partial payment. The remaining balance will stay Due.</small>
            </label>
            <label>
              <span>Reference</span>
              <input v-model.trim="reference" type="text" placeholder="Optional transaction/reference ID" />
            </label>
          </div>

          <p v-if="errorMessage" class="pos-field-error">{{ errorMessage }}</p>

          <div class="pos-payment-breakdown">
            <div><span>Total</span><strong>{{ money(total) }}</strong></div>
            <div><span>Paying Now</span><strong class="paid">{{ money(validAmount) }}</strong></div>
            <div><span>Remaining Due</span><strong :class="{ due: remainingDue > 0 }">{{ money(remainingDue) }}</strong></div>
          </div>
        </div>

        <footer class="pos-modal-footer">
          <button type="button" class="pos-secondary-button" :disabled="submitting" @click="close">Cancel</button>
          <button type="submit" class="pos-primary-button" :disabled="submitting">
            <span v-if="submitting" class="spinner-border spinner-border-sm"></span>
            <i v-else class="bi bi-check2-circle"></i>
            Confirm Payment & Order
          </button>
        </footer>
      </form>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: false },
  total: { type: Number, default: 0 },
  submitting: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'confirm'])
const paymentMethod = ref('')
const amount = ref(0)
const reference = ref('')
const errorMessage = ref('')

const methods = [
  { value: 'cash', label: 'Cash', icon: 'bi bi-cash-stack' },
  { value: 'card', label: 'Card', icon: 'bi bi-credit-card' },
  { value: 'bkash', label: 'bKash', icon: 'bi bi-phone' },
  { value: 'nagad', label: 'Nagad', icon: 'bi bi-phone-vibrate' },
  { value: 'bank_transfer', label: 'Bank Transfer', icon: 'bi bi-bank' },
]

const validAmount = computed(() => {
  const value = Number(amount.value)
  return Number.isFinite(value)
    ? Math.max(0, Math.min(value, Number(props.total || 0)))
    : 0
})

const remainingDue = computed(() =>
  Math.max(Number((Number(props.total || 0) - validAmount.value).toFixed(2)), 0),
)

function money(value) {
  return `৳ ${Number(value || 0).toLocaleString('en-BD', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

function reset() {
  paymentMethod.value = ''
  amount.value = Number(props.total || 0)
  reference.value = ''
  errorMessage.value = ''
}

function close() {
  if (!props.submitting) emit('close')
}

function submit() {
  errorMessage.value = ''
  if (!paymentMethod.value) {
    errorMessage.value = 'Please select a payment method.'
    return
  }
  if (validAmount.value <= 0) {
    errorMessage.value = 'Payment amount must be greater than zero.'
    return
  }

  emit('confirm', {
    amount: validAmount.value,
    payment_method: paymentMethod.value,
    reference: reference.value.trim(),
  })
}

watch(() => props.show, (show) => {
  if (show) reset()
})
</script>
