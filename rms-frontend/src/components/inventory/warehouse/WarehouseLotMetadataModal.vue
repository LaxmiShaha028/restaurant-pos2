<template>
  <Teleport to="body">
    <Transition name="inventory-modal">
      <div
        v-if="show"
        class="warehouse-lot-modal-backdrop"
        @click.self="requestClose"
      >
        <section
          class="warehouse-lot-modal warehouse-lot-metadata-modal"
          role="dialog"
          aria-modal="true"
          aria-labelledby="warehouse-lot-metadata-title"
        >
          <header class="warehouse-lot-modal-header">
            <div>
              <span class="warehouse-lot-modal-eyebrow">
                Manual Lot Update
              </span>

              <h2 id="warehouse-lot-metadata-title">
                Update Batch / Expiry
              </h2>

              <p>
                Correct or enrich lot information without changing quantity,
                cost or accounting history.
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
              v-if="lot"
              class="warehouse-lot-metadata-context"
            >
              <div>
                <span>Lot</span>
                <strong>{{ lot.lot_no }}</strong>
              </div>

              <div>
                <span>Material</span>
                <strong>{{ lot.material_name }}</strong>
              </div>

              <div>
                <span>Remaining</span>
                <strong>{{ lot.quantity_formatted }}</strong>
              </div>

              <div>
                <span>Current Status</span>
                <strong>{{ lot.status_label }}</strong>
              </div>
            </section>


            <div class="warehouse-lot-metadata-grid">
              <div class="warehouse-lot-field">
                <label for="warehouse-lot-edit-supplier">
                  Supplier
                </label>

                <select
                  id="warehouse-lot-edit-supplier"
                  v-model="form.supplier_id"
                  :disabled="
                    submitting
                    ||
                    suppliersLoading
                    ||
                    !supplierEditable
                  "
                >
                  <option value="">
                    Unknown / Not linked
                  </option>

                  <option
                    v-for="supplier in suppliers"
                    :key="supplier.id"
                    :value="String(supplier.id)"
                  >
                    {{ supplierName(supplier) }}
                  </option>
                </select>

                <small
                  v-if="!supplierEditable"
                  class="warehouse-lot-field-help"
                >
                  Purchase-receive supplier is locked to the original PO.
                </small>

                <small
                  v-if="fieldError('supplier_id')"
                  class="warehouse-lot-field-error"
                >
                  {{ fieldError('supplier_id') }}
                </small>
              </div>


              <div class="warehouse-lot-field">
                <label for="warehouse-lot-edit-batch">
                  Supplier Batch No.
                </label>

                <input
                  id="warehouse-lot-edit-batch"
                  v-model.trim="form.supplier_batch_no"
                  type="text"
                  maxlength="120"
                  :disabled="submitting"
                  placeholder="e.g. RICE-SEP-A"
                />

                <small
                  v-if="fieldError('supplier_batch_no')"
                  class="warehouse-lot-field-error"
                >
                  {{ fieldError('supplier_batch_no') }}
                </small>
              </div>


              <div class="warehouse-lot-field">
                <label for="warehouse-lot-edit-manufactured">
                  Manufacturing Date
                </label>

                <input
                  id="warehouse-lot-edit-manufactured"
                  v-model="form.manufactured_at"
                  type="date"
                  :disabled="submitting"
                />

                <small
                  v-if="fieldError('manufactured_at')"
                  class="warehouse-lot-field-error"
                >
                  {{ fieldError('manufactured_at') }}
                </small>
              </div>


              <div class="warehouse-lot-field">
                <label for="warehouse-lot-edit-expiry">
                  Expiry Date
                </label>

                <input
                  id="warehouse-lot-edit-expiry"
                  v-model="form.expiry_date"
                  type="date"
                  :disabled="submitting"
                />

                <small class="warehouse-lot-field-help">
                  A past date is allowed if you discover the stock is already expired.
                </small>

                <small
                  v-if="fieldError('expiry_date')"
                  class="warehouse-lot-field-error"
                >
                  {{ fieldError('expiry_date') }}
                </small>
              </div>
            </div>


            <div class="warehouse-lot-field">
              <label for="warehouse-lot-edit-notes">
                Manual Notes
              </label>

              <textarea
                id="warehouse-lot-edit-notes"
                v-model.trim="form.manual_notes"
                rows="3"
                maxlength="2000"
                :disabled="submitting"
                placeholder="Optional information you learned later about this lot"
              ></textarea>

              <small class="warehouse-lot-field-help">
                System/source notes are preserved separately and cannot be overwritten.
              </small>

              <small
                v-if="fieldError('manual_notes')"
                class="warehouse-lot-field-error"
              >
                {{ fieldError('manual_notes') }}
              </small>
            </div>


            <div class="warehouse-lot-field">
              <label for="warehouse-lot-edit-reason">
                Change Reason
                <span>*</span>
              </label>

              <textarea
                id="warehouse-lot-edit-reason"
                v-model.trim="form.change_reason"
                rows="2"
                maxlength="1000"
                :disabled="submitting"
                placeholder="Example: Expiry date confirmed from supplier packaging"
              ></textarea>

              <small class="warehouse-lot-field-help">
                Required. Every manual change is stored in an immutable audit history.
              </small>

              <small
                v-if="fieldError('change_reason')"
                class="warehouse-lot-field-error"
              >
                {{ fieldError('change_reason') }}
              </small>
            </div>


            <div
              v-if="expiryWarning"
              class="warehouse-lot-alert warehouse-lot-alert-warning"
            >
              <i class="bi bi-exclamation-circle"></i>

              <div>
                <strong>{{ expiryWarning.title }}</strong>
                <p>{{ expiryWarning.message }}</p>
              </div>
            </div>


            <div class="warehouse-lot-metadata-protected">
              <i class="bi bi-shield-lock"></i>

              <div>
                <strong>Protected accounting fields</strong>

                <p>
                  Material, received quantity, remaining quantity, original unit cost,
                  source type, PO and GRN cannot be changed here.
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
              class="warehouse-lot-primary-button"
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

              {{
                submitting
                  ? 'Saving...'
                  : 'Save Lot Update'
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

import purchaseOrderService
  from '@/services/purchaseOrderService'

import warehouseLotService
  from '@/services/warehouseLotService'


const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },

  lot: {
    type: Object,
    default: null,
  },

  submitting: {
    type: Boolean,
    default: false,
  },

  serverErrors: {
    type: Object,
    default: () => ({}),
  },

  errorMessage: {
    type: String,
    default: '',
  },
})


const emit = defineEmits([
  'close',
  'submit',
])


const suppliers =
  ref([])

const suppliersLoading =
  ref(false)


const form = reactive({
  supplier_id: '',
  supplier_batch_no: '',
  manufactured_at: '',
  expiry_date: '',
  manual_notes: '',
  change_reason: '',
})


const supplierEditable =
  computed(() => {
    return (
      props.lot?.can_edit_supplier
      ??
      props.lot?.source_type !==
        'purchase_receive'
    )
  })


const canSubmit =
  computed(() => {
    return Boolean(
      props.lot?.id
      &&
      form.change_reason.trim()
      &&
      datesValid.value
      &&
      changed.value
    )
  })


const datesValid =
  computed(() => {
    if (
      form.manufactured_at
      &&
      form.expiry_date
      &&
      form.expiry_date
      <
      form.manufactured_at
    ) {
      return false
    }

    return true
  })


const changed =
  computed(() => {
    if (
      !props.lot
    ) {
      return false
    }

    return (
      normalizedSupplierId(form.supplier_id)
      !==
      normalizedSupplierId(
        props.lot.supplier_id,
      )
      ||
      clean(form.supplier_batch_no)
      !==
      clean(props.lot.supplier_batch_no)
      ||
      clean(form.manufactured_at)
      !==
      clean(props.lot.manufactured_at)
      ||
      clean(form.expiry_date)
      !==
      clean(props.lot.expiry_date)
      ||
      clean(form.manual_notes)
      !==
      clean(props.lot.manual_notes)
    )
  })


const expiryWarning =
  computed(() => {
    if (
      !form.expiry_date
    ) {
      return {
        title:
          'Expiry will be unknown',

        message:
          'Unknown-expiry lots remain transferable after all dated non-expired lots.',
      }
    }

    const today =
      todayDate()

    if (
      form.expiry_date
      <
      today
    ) {
      return {
        title:
          'This lot will become Expired',

        message:
          'Its remaining quantity will immediately be blocked from normal Restaurant Transfer.',
      }
    }

    const days =
      differenceInDays(
        today,
        form.expiry_date,
      )

    if (
      days <= 7
    ) {
      return {
        title:
          'This lot will be Critical',

        message:
          'It will appear in Expiring Soon and should be used or returned quickly.',
      }
    }

    if (
      days <= 30
    ) {
      return {
        title:
          'This lot will be Expiring Soon',

        message:
          'It will appear in the 30-day expiry watch list.',
      }
    }

    return null
  })


async function loadSuppliers()
{
  suppliersLoading.value = true

  try {
    const response =
      await purchaseOrderService
        .getSuppliers()

    suppliers.value =
      extractList(response)
        .filter(
          (supplier) =>
            supplier
            &&
            supplier.id,
        )
  } catch {
    suppliers.value = []
  } finally {
    suppliersLoading.value = false
  }
}


function resetFromLot()
{
  form.supplier_id =
    props.lot?.supplier_id
      ? String(props.lot.supplier_id)
      : ''

  form.supplier_batch_no =
    props.lot?.supplier_batch_no
    ||
    ''

  form.manufactured_at =
    props.lot?.manufactured_at
    ||
    ''

  form.expiry_date =
    props.lot?.expiry_date
    ||
    ''

  form.manual_notes =
    props.lot?.manual_notes
    ||
    ''

  form.change_reason = ''
}


function submit()
{
  if (
    !canSubmit.value
    ||
    props.submitting
  ) {
    return
  }

  emit(
    'submit',
    {
      lot_id:
        Number(props.lot.id),

      supplier_id:
        normalizedSupplierId(
          form.supplier_id,
        ),

      supplier_batch_no:
        clean(form.supplier_batch_no)
        ||
        null,

      manufactured_at:
        clean(form.manufactured_at)
        ||
        null,

      expiry_date:
        clean(form.expiry_date)
        ||
        null,

      manual_notes:
        clean(form.manual_notes)
        ||
        null,

      change_reason:
        form.change_reason.trim(),
    },
  )
}


function requestClose()
{
  if (
    props.submitting
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
    props.serverErrors?.[field]

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
    field === 'expiry_date'
    &&
    !datesValid.value
  ) {
    return 'Expiry date cannot be earlier than manufacturing date.'
  }

  return ''
}


function supplierName(
  supplier,
)
{
  return (
    supplier?.supplier_name
    ||
    supplier?.company_name
    ||
    supplier?.name
    ||
    `Supplier #${supplier?.id || ''}`
  )
}


function extractList(
  response,
)
{
  const candidates = [
    response?.data?.data,
    response?.data,
    response,
  ]

  for (
    const candidate
    of candidates
  ) {
    if (
      Array.isArray(candidate)
    ) {
      return candidate
    }
  }

  return []
}


function normalizedSupplierId(
  value,
)
{
  if (
    value ===
    null
    ||
    value ===
    undefined
    ||
    value ===
    ''
  ) {
    return null
  }

  const id =
    Number(value)

  return Number.isInteger(id)
    &&
    id > 0
      ? id
      : null
}


function clean(
  value,
)
{
  return String(
    value
    ??
    '',
  ).trim()
}


function todayDate()
{
  const date =
    new Date()

  const year =
    date.getFullYear()

  const month =
    String(
      date.getMonth() + 1,
    ).padStart(2, '0')

  const day =
    String(
      date.getDate(),
    ).padStart(2, '0')

  return `${year}-${month}-${day}`
}


function differenceInDays(
  from,
  to,
)
{
  const start =
    new Date(
      `${from}T00:00:00`,
    )

  const end =
    new Date(
      `${to}T00:00:00`,
    )

  return Math.floor(
    (
      end.getTime()
      -
      start.getTime()
    )
    /
    86400000,
  )
}


watch(
  () => props.show,
  (show) => {
    if (
      !show
    ) {
      return
    }

    resetFromLot()

    void loadSuppliers()
  },
)


watch(
  () => props.lot?.id,
  () => {
    if (
      props.show
    ) {
      resetFromLot()
    }
  },
)
</script>
