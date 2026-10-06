<template>
  <Teleport to="body">
    <Transition name="inventory-modal">
      <div
        v-if="show"
        class="warehouse-lot-modal-backdrop"
        @click.self="requestClose"
      >
        <section
          class="warehouse-lot-modal purchase-return-modal"
          role="dialog"
          aria-modal="true"
          aria-labelledby="purchase-return-modal-title"
        >
          <header class="warehouse-lot-modal-header">
            <div>
              <span class="warehouse-lot-modal-eyebrow">
                Supplier Stock Return
              </span>

              <h2 id="purchase-return-modal-title">
                Purchase Return
              </h2>

              <p>
                Return the exact warehouse lot to its linked supplier.
              </p>
            </div>

            <button
              type="button"
              class="warehouse-lot-icon-button"
              :disabled="submitting"
              aria-label="Close Purchase Return"
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


            <div
              v-if="lot && !returnEligible"
              class="warehouse-lot-alert warehouse-lot-alert-warning"
            >
              <i class="bi bi-exclamation-circle"></i>

              <div>
                <strong>Return is not available yet</strong>

                <p>
                  {{ eligibilityMessage }}
                </p>
              </div>
            </div>


            <section
              v-if="lot"
              class="purchase-return-context"
            >
              <article>
                <span>Supplier</span>
                <strong>
                  {{ lot.supplier_name || 'Not linked' }}
                </strong>
              </article>

              <article>
                <span>Material</span>
                <strong>
                  {{ lot.material_name }}
                </strong>
              </article>

              <article>
                <span>Lot</span>
                <strong>
                  {{ lot.lot_no }}
                </strong>
              </article>

              <article>
                <span>Available</span>
                <strong>
                  {{ lot.quantity_formatted }}
                </strong>
              </article>
            </section>


            <section
              v-if="lot"
              class="purchase-return-source-card"
            >
              <div>
                <span>Supplier Batch</span>
                <strong>
                  {{ lot.supplier_batch_no || 'Not provided' }}
                </strong>
              </div>

              <div>
                <span>Expiry</span>
                <strong>
                  {{ lot.expiry_date_label || 'Unknown' }}
                </strong>
              </div>

              <div>
                <span>Original PO</span>
                <strong>
                  {{ lot.purchase_order?.label || 'Unknown / Legacy' }}
                </strong>
              </div>

              <div>
                <span>Original GRN</span>
                <strong>
                  {{ lot.purchase_order_receipt?.label || 'Unknown / Legacy' }}
                </strong>
              </div>
            </section>


            <div class="purchase-return-form-grid">
              <div class="warehouse-lot-field">
                <label for="purchase-return-quantity">
                  Return Quantity
                  <span>*</span>
                </label>

                <input
                  id="purchase-return-quantity"
                  v-model="form.quantity"
                  type="number"
                  min="0"
                  step="0.0001"
                  :max="maxQuantity"
                  :disabled="submitting || !returnEligible"
                  placeholder="0"
                />

                <small class="warehouse-lot-field-help">
                  Maximum:
                  {{ lot?.quantity_formatted || '0' }}
                </small>

                <small
                  v-if="fieldError('items.0.quantity')"
                  class="warehouse-lot-field-error"
                >
                  {{ fieldError('items.0.quantity') }}
                </small>
              </div>


              <div class="warehouse-lot-field">
                <label for="purchase-return-reason">
                  Return Reason
                  <span>*</span>
                </label>

                <select
                  id="purchase-return-reason"
                  v-model="form.reason"
                  :disabled="submitting || !returnEligible"
                >
                  <option
                    v-for="reason in purchaseReturnService.reasons"
                    :key="reason.value"
                    :value="reason.value"
                  >
                    {{ reason.label }}
                  </option>
                </select>

                <small
                  v-if="fieldError('items.0.reason')"
                  class="warehouse-lot-field-error"
                >
                  {{ fieldError('items.0.reason') }}
                </small>
              </div>


              <div class="warehouse-lot-field">
                <label for="purchase-return-date">
                  Return Date / Time
                </label>

                <input
                  id="purchase-return-date"
                  v-model="form.returned_at"
                  type="datetime-local"
                  :max="nowLocal()"
                  :disabled="submitting || !returnEligible"
                />

                <small
                  v-if="fieldError('returned_at')"
                  class="warehouse-lot-field-error"
                >
                  {{ fieldError('returned_at') }}
                </small>
              </div>


              <div class="purchase-return-credit-preview">
                <span>
                  Estimated Supplier Credit
                </span>

                <strong>
                  {{ money(expectedSupplierCredit) }}
                </strong>

                <small>
                  {{ compactNumber(validQuantity) }}
                  {{ lot?.unit || '' }}
                  ×
                  {{ lot?.unit_cost_formatted || '৳ 0.00' }}
                </small>
              </div>
            </div>


            <div class="warehouse-lot-field">
              <label for="purchase-return-item-notes">
                Return Item Notes
              </label>

              <textarea
                id="purchase-return-item-notes"
                v-model.trim="form.item_notes"
                rows="2"
                maxlength="500"
                :disabled="submitting || !returnEligible"
                placeholder="Optional reason detail"
              ></textarea>

              <small
                v-if="fieldError('items.0.notes')"
                class="warehouse-lot-field-error"
              >
                {{ fieldError('items.0.notes') }}
              </small>
            </div>


            <div class="warehouse-lot-field">
              <label for="purchase-return-notes">
                Return Note
              </label>

              <textarea
                id="purchase-return-notes"
                v-model.trim="form.notes"
                rows="2"
                maxlength="1000"
                :disabled="submitting || !returnEligible"
                placeholder="Optional note for this Purchase Return"
              ></textarea>
            </div>


            <div class="purchase-return-accounting-note">
              <i class="bi bi-journal-check"></i>

              <div>
                <strong>
                  Phase 6 records the physical return only
                </strong>

                <p>
                  Warehouse quantity and the exact lot will decrease now.
                  Supplier payable / supplier credit / refund / cash settlement
                  will be posted separately in the next accounting phase.
                </p>
              </div>
            </div>


            <div
              v-if="
                lot
                &&
                (
                  !lot.purchase_order_id
                  ||
                  !lot.purchase_order_receipt_id
                )
              "
              class="warehouse-lot-alert warehouse-lot-alert-warning"
            >
              <i class="bi bi-info-circle"></i>

              <div>
                <strong>Legacy / manually enriched lot</strong>

                <p>
                  Original PO or GRN is unavailable. The Purchase Return will
                  preserve the current supplier, lot, batch, expiry and cost snapshots.
                </p>
              </div>
            </div>
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
              type="button"
              class="purchase-return-submit-button"
              :disabled="submitting || !canSubmit"
              @click="submit"
            >
              <span
                v-if="submitting"
                class="spinner-border spinner-border-sm"
              ></span>

              <i
                v-else
                class="bi bi-arrow-return-left"
              ></i>

              {{
                submitting
                  ? 'Recording Return...'
                  : 'Record Purchase Return'
              }}
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

import purchaseReturnService
  from '@/services/purchaseReturnService'


const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },

  lot: {
    type: Object,
    default: null,
  },
})


const emit = defineEmits([
  'close',
  'created',
])


const submitting =
  ref(false)

const serverErrors =
  ref({})

const errorMessage =
  ref('')


const form =
  reactive({
    quantity: '',
    reason: 'other',
    returned_at: '',
    item_notes: '',
    notes: '',
  })


const maxQuantity =
  computed(() => {
    const value =
      Number(
        props.lot?.remaining_quantity,
      )

    return Number.isFinite(value)
      &&
      value > 0
        ? value
        : 0
  })


const returnEligible =
  computed(() => {
    return Boolean(
      props.lot?.return_eligible
      ??
      (
        Number(
          props.lot?.supplier_id,
        ) > 0
        &&
        maxQuantity.value > 0
      )
    )
  })


const eligibilityMessage =
  computed(() => {
    return (
      props.lot?.return_eligibility_message
      ||
      (
        Number(
          props.lot?.supplier_id,
        ) <= 0
          ? 'Assign a supplier to this lot before Purchase Return.'
          : 'This lot has no remaining warehouse quantity.'
      )
    )
  })


const validQuantity =
  computed(() => {
    const quantity =
      Number(
        form.quantity,
      )

    if (
      !Number.isFinite(quantity)
      ||
      quantity <= 0
    ) {
      return 0
    }

    return quantity
  })


const expectedSupplierCredit =
  computed(() => {
    const cost =
      Number(
        props.lot?.unit_cost,
      )

    if (
      !Number.isFinite(cost)
    ) {
      return 0
    }

    return (
      validQuantity.value
      *
      cost
    )
  })


const canSubmit =
  computed(() => {
    return (
      returnEligible.value
      &&
      validQuantity.value > 0
      &&
      validQuantity.value <= maxQuantity.value
      &&
      Boolean(form.reason)
      &&
      !submitting.value
    )
  })


watch(
  () => props.show,
  (show) => {
    if (
      !show
    ) {
      return
    }

    resetForm()
  },
)


watch(
  () => props.lot?.id,
  () => {
    if (
      props.show
    ) {
      resetForm()
    }
  },
)


function resetForm()
{
  form.quantity = ''
  form.reason =
    defaultReason(
      props.lot,
    )

  form.returned_at =
    nowLocal()

  form.item_notes = ''
  form.notes = ''

  serverErrors.value = {}
  errorMessage.value = ''
}


function defaultReason(
  lot,
)
{
  if (
    lot?.status ===
    'expired'
  ) {
    return 'expired'
  }

  if (
    lot?.status ===
    'critical'
    ||
    lot?.status ===
    'expiring_soon'
  ) {
    return 'near_expiry'
  }

  return 'other'
}


async function submit()
{
  if (
    !canSubmit.value
  ) {
    return
  }

  submitting.value = true
  serverErrors.value = {}
  errorMessage.value = ''

  try {
    const response =
      await purchaseReturnService
        .createPurchaseReturn({
          returned_at:
            form.returned_at
            || null,

          notes:
            form.notes.trim()
            || null,

          items: [
            {
              warehouse_stock_lot_id:
                Number(
                  props.lot.id,
                ),

              quantity:
                Number(
                  form.quantity,
                ),

              reason:
                form.reason,

              notes:
                form.item_notes.trim()
                || null,
            },
          ],
        })

    emit(
      'created',
      response?.data
      || null,
    )
  } catch (error) {
    serverErrors.value =
      purchaseReturnService
        .getValidationErrors(
          error,
        )

    errorMessage.value =
      purchaseReturnService
        .getErrorMessage(
          error,
          'Unable to record Purchase Return.',
        )
  } finally {
    submitting.value = false
  }
}


function requestClose()
{
  if (
    submitting.value
  ) {
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

  if (
    typeof value === 'string'
    &&
    value
  ) {
    return value
  }

  if (
    field ===
    'items.0.quantity'
    &&
    validQuantity.value >
    maxQuantity.value
  ) {
    return 'Return quantity cannot exceed available lot quantity.'
  }

  return ''
}


function compactNumber(
  value,
)
{
  const number =
    Number(value)

  if (
    !Number.isFinite(number)
  ) {
    return '0'
  }

  return number
    .toFixed(4)
    .replace(/0+$/, '')
    .replace(/\.$/, '')
}


function money(
  value,
)
{
  const amount =
    Number(value)

  return `৳ ${
    Number.isFinite(amount)
      ? amount.toFixed(2)
      : '0.00'
  }`
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
