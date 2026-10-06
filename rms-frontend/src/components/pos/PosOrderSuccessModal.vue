<template>
  <Teleport to="body">
    <div v-if="show" class="pos-modal-backdrop">
      <div class="pos-modal pos-success-modal">
        <div class="pos-success-icon">
          <i class="bi bi-check2"></i>
        </div>

        <h2>Order Created</h2>
        <p>
          {{ order?.order_number || 'The POS order' }} was created successfully
          and is now using the existing order and kitchen workflow.
        </p>

        <div class="pos-success-meta">
          <div>
            <span>Total</span>
            <strong>{{ order?.total_amount_formatted || money(order?.total_amount) }}</strong>
          </div>
          <div>
            <span>Payment</span>
            <strong>{{ order?.payment_status_label || formatLabel(order?.payment_status || 'due') }}</strong>
          </div>
        </div>

        <div class="pos-success-actions">
          <button type="button" class="pos-secondary-button" @click="emit('new-order')">
            New POS Order
          </button>
          <button type="button" class="pos-primary-button" @click="emit('orders')">
            Go to Orders
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  show: { type: Boolean, default: false },
  order: { type: Object, default: null },
})

const emit = defineEmits(['new-order', 'orders'])

function money(value) {
  return `৳ ${Number(value || 0).toLocaleString('en-BD', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

function formatLabel(value) {
  return String(value || '')
    .replaceAll('_', ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase())
}
</script>
