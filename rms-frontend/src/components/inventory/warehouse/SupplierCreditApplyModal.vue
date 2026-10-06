<template>
  <Teleport to="body">
    <Transition name="inventory-modal">
      <div
        v-if="show"
        class="warehouse-lot-modal-backdrop"
        @click.self="requestClose"
      >
        <section
          class="warehouse-lot-modal supplier-credit-apply-modal"
          role="dialog"
          aria-modal="true"
          aria-labelledby="supplier-credit-apply-title"
        >
          <header class="warehouse-lot-modal-header">
            <div>
              <span class="warehouse-lot-modal-eyebrow">
                Supplier Account Credit
              </span>

              <h2 id="supplier-credit-apply-title">
                Apply Supplier Credit
              </h2>

              <p>
                Use an existing Purchase Return credit against another Purchase Order.
              </p>
            </div>

            <button
              type="button"
              class="warehouse-lot-icon-button"
              :disabled="submitting"
              @click="requestClose"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </header>


          <div class="warehouse-lot-modal-body">
            <div
              v-if="errorMessage"
              class="warehouse-lot-alert warehouse-lot-alert-danger"
            >
              <i class="bi bi-exclamation-triangle"></i>
              {{ errorMessage }}
            </div>


            <section
              v-if="credit"
              class="supplier-credit-apply-summary"
            >
              <article>
                <span>Supplier</span>
                <strong>{{ credit.supplier_name }}</strong>
              </article>

              <article>
                <span>Source Return</span>
                <strong>{{ credit.return_no || '-' }}</strong>
              </article>

              <article>
                <span>Credit Issued</span>
                <strong>{{ credit.issued_amount_formatted }}</strong>
              </article>

              <article>
                <span>Available</span>
                <strong>{{ credit.available_amount_formatted }}</strong>
              </article>
            </section>


            <div
              v-if="loadingOrders"
              class="warehouse-lot-loading-state"
            >
              <span class="spinner-border spinner-border-sm"></span>
              Loading eligible Purchase Orders...
            </div>


            <div
              v-else-if="eligibleOrders.length === 0"
              class="warehouse-lot-empty-state"
            >
              <i class="bi bi-receipt"></i>

              <strong>No eligible Purchase Order found</strong>

              <p>
                Supplier Credit is intentionally not applied back to the original Purchase Order.
                Create or use another open Purchase Order from the same supplier.
              </p>
            </div>


            <template v-else>
              <div class="purchase-return-settlement-form-grid">
                <div class="warehouse-lot-field purchase-return-settlement-wide">
                  <label for="supplier-credit-target-po">
                    Target Purchase Order
                    <span>*</span>
                  </label>

                  <select
                    id="supplier-credit-target-po"
                    v-model="form.purchase_order_id"
                    :disabled="submitting"
                    @change="clampAmount"
                  >
                    <option value="">
                      Select Purchase Order
                    </option>

                    <option
                      v-for="order in eligibleOrders"
                      :key="order.id"
                      :value="String(order.id)"
                    >
                      PO #{{ order.id }}
                      ·
                      {{ order.order_date_label || order.order_date_value || '-' }}
                      ·
                      Due {{ order.due_amount_formatted || money(order.due_amount) }}
                    </option>
                  </select>

                  <small
                    v-if="fieldError('purchase_order_id')"
                    class="warehouse-lot-field-error"
                  >
                    {{ fieldError('purchase_order_id') }}
                  </small>
                </div>


                <div class="warehouse-lot-field">
                  <label for="supplier-credit-amount">
                    Apply Amount
                    <span>*</span>
                  </label>

                  <input
                    id="supplier-credit-amount"
                    v-model="form.amount"
                    type="number"
                    min="0.01"
                    step="0.01"
                    :max="maxAmount"
                    :disabled="submitting"
                  />

                  <small class="warehouse-lot-field-help">
                    Maximum {{ money(maxAmount) }}
                  </small>

                  <small
                    v-if="fieldError('amount')"
                    class="warehouse-lot-field-error"
                  >
                    {{ fieldError('amount') }}
                  </small>
                </div>


                <div class="warehouse-lot-field">
                  <label for="supplier-credit-applied-at">
                    Applied Date / Time
                  </label>

                  <input
                    id="supplier-credit-applied-at"
                    v-model="form.applied_at"
                    type="datetime-local"
                    :max="nowLocal()"
                    :disabled="submitting"
                  />
                </div>


                <div class="warehouse-lot-field purchase-return-settlement-wide">
                  <label for="supplier-credit-notes">
                    Notes
                  </label>

                  <textarea
                    id="supplier-credit-notes"
                    v-model.trim="form.notes"
                    rows="2"
                    maxlength="2000"
                    :disabled="submitting"
                    placeholder="Optional Supplier Credit application note"
                  ></textarea>
                </div>
              </div>


              <div class="supplier-credit-effect-note">
                <i class="bi bi-shield-check"></i>

                <div>
                  <strong>No cash payment is created</strong>

                  <p>
                    Purchase Order due decreases, but paid amount and payment history remain unchanged.
                  </p>
                </div>
              </div>
            </template>
          </div>


          <footer class="warehouse-lot-modal-footer">
            <button
              type="button"
              class="warehouse-lot-secondary-button"
              :disabled="submitting"
              @click="requestClose"
            >
              Cancel
            </button>

            <button
              v-if="eligibleOrders.length"
              type="button"
              class="supplier-credit-apply-button"
              :disabled="submitting || !canSubmit"
              @click="submit"
            >
              <span
                v-if="submitting"
                class="spinner-border spinner-border-sm"
              ></span>

              <i
                v-else
                class="bi bi-check2-circle"
              ></i>

              Apply Credit
            </button>
          </footer>
        </section>
      </div>
    </Transition>
  </Teleport>
</template>


<script setup>
import {
  computed,
  reactive,
  ref,
  watch,
} from 'vue'

import supplierCreditService
  from '@/services/supplierCreditService'


const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },

  credit: {
    type: Object,
    default: null,
  },
})


const emit = defineEmits([
  'close',
  'applied',
])


const loadingOrders =
  ref(false)

const submitting =
  ref(false)

const eligibleOrders =
  ref([])

const serverErrors =
  ref({})

const errorMessage =
  ref('')


const form =
  reactive({
    purchase_order_id: '',
    amount: '',
    applied_at: '',
    notes: '',
  })


const selectedOrder =
  computed(() => {
    const id =
      Number(
        form.purchase_order_id,
      )

    return eligibleOrders.value
      .find(
        (order) =>
          Number(order.id) === id,
      )
      || null
  })


const maxAmount =
  computed(() => {
    const available =
      Number(
        props.credit?.available_amount
        || 0,
      )

    const due =
      Number(
        selectedOrder.value?.due_amount
        || 0,
      )

    return Math.max(
      0,
      Math.min(
        available,
        due,
      ),
    )
  })


const canSubmit =
  computed(() => {
    const amount =
      Number(
        form.amount,
      )

    return (
      Boolean(
        selectedOrder.value,
      )
      &&
      Number.isFinite(amount)
      &&
      amount > 0
      &&
      amount <= maxAmount.value
      &&
      !submitting.value
    )
  })


watch(
  () => props.show,
  (show) => {
    if (show) {
      void initialize()
    }
  },
)


watch(
  () => props.credit?.id,
  () => {
    if (props.show) {
      void initialize()
    }
  },
)


async function initialize()
{
  resetForm()

  if (
    !props.credit?.id
  ) {
    eligibleOrders.value = []
    return
  }

  loadingOrders.value = true
  errorMessage.value = ''

  try {
    const response =
      await supplierCreditService
        .getEligiblePurchaseOrders(
          props.credit.id,
        )

    const data =
      response?.data?.data
      ??
      response?.data
      ??
      []

    eligibleOrders.value =
      Array.isArray(data)
        ? data
        : []

    if (
      eligibleOrders.value[0]?.id
    ) {
      form.purchase_order_id =
        String(
          eligibleOrders.value[0].id,
        )

      clampAmount()
    }
  } catch (error) {
    eligibleOrders.value = []

    errorMessage.value =
      supplierCreditService
        .getErrorMessage(
          error,
          'Unable to load eligible Purchase Orders.',
        )
  } finally {
    loadingOrders.value = false
  }
}


function resetForm()
{
  form.purchase_order_id = ''
  form.amount = ''
  form.applied_at =
    nowLocal()
  form.notes = ''

  serverErrors.value = {}
  errorMessage.value = ''
}


function clampAmount()
{
  if (
    maxAmount.value <= 0
  ) {
    form.amount = ''
    return
  }

  const amount =
    Number(
      form.amount,
    )

  if (
    !Number.isFinite(amount)
    ||
    amount <= 0
    ||
    amount > maxAmount.value
  ) {
    form.amount =
      maxAmount.value
        .toFixed(2)
  }
}


async function submit()
{
  if (
    !canSubmit.value
    ||
    !props.credit?.id
  ) {
    return
  }

  submitting.value = true
  serverErrors.value = {}
  errorMessage.value = ''

  try {
    const response =
      await supplierCreditService
        .applyCredit({
          source_settlement_id:
            Number(
              props.credit.id,
            ),

          purchase_order_id:
            Number(
              form.purchase_order_id,
            ),

          amount:
            Number(
              form.amount,
            ),

          applied_at:
            form.applied_at
            || null,

          notes:
            form.notes.trim()
            || null,
        })

    emit(
      'applied',
      response?.data
      || null,
    )
  } catch (error) {
    serverErrors.value =
      supplierCreditService
        .getValidationErrors(
          error,
        )

    errorMessage.value =
      supplierCreditService
        .getErrorMessage(
          error,
          'Unable to apply Supplier Credit.',
        )
  } finally {
    submitting.value = false
  }
}


function requestClose()
{
  if (submitting.value) {
    return
  }

  emit('close')
}


function fieldError(
  field,
)
{
  const value =
    serverErrors.value?.[field]

  if (
    Array.isArray(value)
    &&
    value.length
  ) {
    return String(value[0])
  }

  return value
    ? String(value)
    : ''
}


function money(
  value,
)
{
  return `৳ ${Number(value || 0).toFixed(2)}`
}


function nowLocal()
{
  const date =
    new Date()

  const offset =
    date.getTimezoneOffset()

  const local =
    new Date(
      date.getTime()
      -
      offset
      *
      60000,
    )

  return local
    .toISOString()
    .slice(0, 16)
}
</script>
