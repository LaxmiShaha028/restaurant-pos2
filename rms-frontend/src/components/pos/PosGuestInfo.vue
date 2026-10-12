<template>
  <section class="pos-guest-panel">
    <div class="pos-guest-heading">
      <div><h3>Guest Information</h3><p>Optional customer details for this order.</p></div>
    </div>

    <label class="pos-guest-field">
      <span>Guest Count</span>
      <input
        :value="guestCount"
        type="number"
        min="1"
        max="1000"
        @input="emit('update:guest-count', Math.max(1, Number($event.target.value || 1)))"
      />
    </label>

    <div class="pos-customer-search-block">
      <div class="pos-customer-search-header">
        <span>Find Customer</span>
        <button type="button" @click="emit('new-customer')">
          <i class="bi bi-person-plus"></i> New
        </button>
      </div>

      <div class="pos-customer-search">
        <i class="bi bi-search"></i>
        <input v-model="query" type="search" placeholder="Search name, phone or email..." />
        <span v-if="searching" class="spinner-border spinner-border-sm"></span>
      </div>

      <p v-if="searchError" class="pos-search-help error">{{ searchError }}</p>

      <div v-if="query.trim().length >= 2 && !searching && results.length" class="pos-customer-results">
        <button
          v-for="customer in results"
          :key="customer.id"
          type="button"
          @click="emit('select-customer', customer)"
        >
          <span class="avatar">{{ (customer.name || 'C').charAt(0).toUpperCase() }}</span>
          <span>
            <strong>{{ customer.name }}</strong>
            <small>{{ [customer.phone, customer.email].filter(Boolean).join(' • ') || 'No contact details' }}</small>
          </span>
        </button>
      </div>

      <div v-if="selectedCustomer" class="pos-selected-customer">
        <span class="icon"><i class="bi bi-person-check-fill"></i></span>
        <div>
          <strong>{{ selectedCustomer.name }}</strong>
          <small>{{ selectedCustomer.phone || selectedCustomer.email || 'Existing customer' }}</small>
        </div>
        <button type="button" @click="emit('clear-customer')"><i class="bi bi-x-lg"></i></button>
      </div>

      <div v-else-if="draftCustomer" class="pos-selected-customer new">
        <span class="icon"><i class="bi bi-person-plus-fill"></i></span>
        <div>
          <strong>{{ draftCustomer.name }}</strong>
          <small>New customer • {{ draftCustomer.phone || draftCustomer.email || 'No contact details' }}</small>
        </div>
        <button type="button" @click="emit('clear-customer')"><i class="bi bi-x-lg"></i></button>
      </div>

      <div v-else class="pos-walkin-note">
        <i class="bi bi-person"></i>
        <span>No customer selected — this order will continue as <strong>Walk-in Customer</strong>.</span>
      </div>
    </div>

    <label class="pos-guest-field">
      <span>Order Note</span>
      <textarea
        :value="orderNote"
        rows="3"
        placeholder="Optional note for this order..."
        @input="emit('update:order-note', $event.target.value)"
      ></textarea>
    </label>
  </section>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue'

const props = defineProps({
  guestCount: { type: Number, default: 1 },
  selectedCustomer: { type: Object, default: null },
  draftCustomer: { type: Object, default: null },
  results: { type: Array, default: () => [] },
  searching: { type: Boolean, default: false },
  searchError: { type: String, default: '' },
  orderNote: { type: String, default: '' },
})

const emit = defineEmits([
  'update:guest-count', 'update:order-note', 'search', 'select-customer',
  'clear-customer', 'new-customer',
])

const query = ref('')
let timer = null

watch(query, (value) => {
  if (timer) clearTimeout(timer)
  timer = setTimeout(() => emit('search', value), 350)
})

onBeforeUnmount(() => {
  if (timer) clearTimeout(timer)
})
</script>
