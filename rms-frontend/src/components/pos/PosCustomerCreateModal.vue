<template>
  <Teleport to="body">
    <div v-if="show" class="pos-modal-backdrop" @click.self="close">
      <form class="pos-modal pos-customer-modal" @submit.prevent="submit">
        <header class="pos-modal-header">
          <div class="pos-modal-title">
            <span><i class="bi bi-person-plus"></i></span>
            <div><p>Guest Profile</p><h2>New Customer</h2></div>
          </div>
          <button type="button" class="pos-modal-close" @click="close">
            <i class="bi bi-x-lg"></i>
          </button>
        </header>

        <div class="pos-modal-body pos-form-grid">
          <label>
            <span>Customer Name</span>
            <input v-model.trim="form.name" type="text" placeholder="Enter customer name" />
          </label>
          <label>
            <span>Phone</span>
            <input v-model.trim="form.phone" type="text" placeholder="01XXXXXXXXX" />
          </label>
          <label class="full">
            <span>Email</span>
            <input v-model.trim="form.email" type="email" placeholder="customer@example.com" />
          </label>
          <label class="full">
            <span>Birthday <small>(Optional, for birthday offers)</small></span>
            <input v-model="form.date_of_birth" type="date" :max="todayDate" />
          </label>

          <p v-if="errorMessage" class="pos-field-error full">{{ errorMessage }}</p>

          <div class="pos-modal-note full">
            <i class="bi bi-info-circle"></i>
            <span>This customer will be saved by the existing order workflow when the POS order is confirmed.</span>
          </div>
        </div>

        <footer class="pos-modal-footer">
          <button type="button" class="pos-secondary-button" @click="close">Cancel</button>
          <button type="submit" class="pos-primary-button">Create & Select</button>
        </footer>
      </form>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'

const props = defineProps({ show: { type: Boolean, default: false } })
const emit = defineEmits(['close', 'create'])

const form = reactive({ name: '', phone: '', email: '', date_of_birth: '' })
const errorMessage = ref('')
const todayDate = computed(() => new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Dhaka' }).format(new Date()))

function reset() {
  form.name = ''
  form.phone = ''
  form.email = ''
  form.date_of_birth = ''
  errorMessage.value = ''
}

function close() {
  emit('close')
}

function submit() {
  errorMessage.value = ''

  if (form.name.trim().length < 2) {
    errorMessage.value = 'Customer name must contain at least 2 characters.'
    return
  }

  emit('create', {
    name: form.name.trim(),
    phone: form.phone.trim(),
    email: form.email.trim(),
    date_of_birth: form.date_of_birth || null,
  })
}

watch(() => props.show, (show) => {
  if (show) reset()
})
</script>
