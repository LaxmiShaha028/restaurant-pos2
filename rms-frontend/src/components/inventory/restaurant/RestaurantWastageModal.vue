<template>
  <div
    v-if="show"
    class="restaurant-wastage-modal-backdrop"
    role="presentation"
    @mousedown.self="closeModal"
  >
    <section
      class="restaurant-wastage-modal"
      role="dialog"
      aria-modal="true"
      aria-labelledby="restaurant-wastage-modal-title"
    >
      <!-- Header -->
      <header class="restaurant-wastage-modal-header">
        <div class="restaurant-wastage-modal-heading">
          <span class="restaurant-wastage-modal-icon">
            <i
              class="bi bi-trash3"
              aria-hidden="true"
            ></i>
          </span>

          <div>
            <span class="restaurant-wastage-eyebrow">
              Restaurant Inventory
            </span>

            <h3 id="restaurant-wastage-modal-title">
              Record Damage / Wastage
            </h3>

            <p>
              Reduce restaurant stock and preserve the historical
              inventory-loss value.
            </p>
          </div>
        </div>

        <button
          type="button"
          class="restaurant-wastage-modal-close"
          :disabled="submitting"
          aria-label="Close damage / wastage modal"
          @click="closeModal"
        >
          <i
            class="bi bi-x-lg"
            aria-hidden="true"
          ></i>
        </button>
      </header>


      <!-- Error -->
      <div
        v-if="errorMessage"
        class="restaurant-wastage-modal-error"
        role="alert"
      >
        <i
          class="bi bi-exclamation-triangle"
          aria-hidden="true"
        ></i>

        <span>
          {{ errorMessage }}
        </span>
      </div>


      <!-- Material Summary -->
      <div class="restaurant-wastage-material-card">
        <div>
          <span>
            Raw Material
          </span>

          <strong>
            {{ materialName }}
          </strong>

          <small>
            {{ materialCategory }}
          </small>
        </div>

        <div>
          <span>
            Available
          </span>

          <strong>
            {{ quantityText(availableQuantity) }}
          </strong>
        </div>

        <div>
          <span>
            Avg. Unit Cost
          </span>

          <strong>
            {{ moneyText(averageUnitCost, 4) }}
          </strong>

          <small>
            per {{ unit }}
          </small>
        </div>
      </div>


      <form
        class="restaurant-wastage-form"
        @submit.prevent="submitForm"
      >
        <div class="restaurant-wastage-form-grid">
          <!-- Quantity -->
          <div class="restaurant-wastage-form-group">
            <label for="restaurant-wastage-quantity">
              Damage / Wastage Quantity
              <span aria-hidden="true">*</span>
            </label>

            <div class="restaurant-wastage-input-unit">
              <input
                id="restaurant-wastage-quantity"
                v-model.trim="form.quantity"
                type="number"
                min="0"
                :max="availableQuantity"
                step="0.0001"
                inputmode="decimal"
                autocomplete="off"
                placeholder="0"
                :disabled="submitting"
                @input="clearLocalError('quantity')"
              />

              <span>
                {{ unit }}
              </span>
            </div>

            <small
              v-if="fieldError('quantity')"
              class="restaurant-wastage-field-error"
            >
              {{ fieldError('quantity') }}
            </small>
          </div>


          <!-- Reason -->
          <div class="restaurant-wastage-form-group">
            <label for="restaurant-wastage-reason">
              Reason
              <span aria-hidden="true">*</span>
            </label>

            <select
              id="restaurant-wastage-reason"
              v-model="form.reason"
              :disabled="submitting"
              @change="clearLocalError('reason')"
            >
              <option value="">
                Select reason
              </option>

              <option
                v-for="option in reasonOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>

            <small
              v-if="fieldError('reason')"
              class="restaurant-wastage-field-error"
            >
              {{ fieldError('reason') }}
            </small>
          </div>


          <!-- Date / Time -->
          <div class="restaurant-wastage-form-group">
            <label for="restaurant-wastage-date">
              Damage Date & Time
            </label>

            <input
              id="restaurant-wastage-date"
              v-model="form.wasted_at"
              type="datetime-local"
              :disabled="submitting"
            />

            <small class="restaurant-wastage-field-help">
              Leave unchanged to record the current local date and time.
            </small>

            <small
              v-if="fieldError('wasted_at')"
              class="restaurant-wastage-field-error"
            >
              {{ fieldError('wasted_at') }}
            </small>
          </div>


          <!-- Notes -->
          <div
            class="
              restaurant-wastage-form-group
              restaurant-wastage-form-group-wide
            "
          >
            <label for="restaurant-wastage-notes">
              Notes
            </label>

            <textarea
              id="restaurant-wastage-notes"
              v-model="form.notes"
              rows="3"
              maxlength="2000"
              placeholder="Optional details, e.g. refrigerator problem"
              :disabled="submitting"
            ></textarea>

            <div class="restaurant-wastage-notes-meta">
              <small>
                Optional
              </small>

              <small>
                {{ form.notes.length }}/2000
              </small>
            </div>

            <small
              v-if="fieldError('notes')"
              class="restaurant-wastage-field-error"
            >
              {{ fieldError('notes') }}
            </small>
          </div>
        </div>


        <!-- Preview -->
        <section class="restaurant-wastage-preview">
          <div class="restaurant-wastage-preview-heading">
            <div>
              <span>
                Inventory Preview
              </span>

              <strong>
                This is not a cash payment
              </strong>
            </div>

            <span class="restaurant-wastage-accounting-badge">
              Inventory Loss
            </span>
          </div>

          <div class="restaurant-wastage-preview-grid">
            <article>
              <span>
                Before Stock
              </span>

              <strong>
                {{ quantityText(availableQuantity) }}
              </strong>
            </article>

            <article>
              <span>
                Wastage
              </span>

              <strong class="restaurant-wastage-negative">
                -{{ quantityText(validQuantity) }}
              </strong>
            </article>

            <article>
              <span>
                After Stock
              </span>

              <strong>
                {{ quantityText(quantityAfter) }}
              </strong>
            </article>

            <article class="restaurant-wastage-loss-card">
              <span>
                Estimated Loss
              </span>

              <strong>
                {{ moneyText(estimatedLoss, 2) }}
              </strong>
            </article>
          </div>

          <p>
            The loss uses the restaurant stock average unit cost at the
            moment this record is saved. Future cost changes will not alter
            this historical loss.
          </p>
        </section>


        <!-- Footer -->
        <footer class="restaurant-wastage-modal-footer">
          <button
            type="button"
            class="restaurant-wastage-cancel"
            :disabled="submitting"
            @click="closeModal"
          >
            Cancel
          </button>

          <button
            type="submit"
            class="restaurant-wastage-submit"
            :disabled="
              submitting
              ||
              availableQuantity <= 0
            "
          >
            <span
              v-if="submitting"
              class="spinner-border spinner-border-sm"
              aria-hidden="true"
            ></span>

            <i
              v-else
              class="bi bi-trash3"
              aria-hidden="true"
            ></i>

            {{
              submitting
                ? 'Recording...'
                : 'Record Damage / Wastage'
            }}
          </button>
        </footer>
      </form>
    </section>
  </div>
</template>


<script setup>
import {
  computed,
  reactive,
  watch,
} from 'vue'


/*
|--------------------------------------------------------------------------
| Props / Emits
|--------------------------------------------------------------------------
*/

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },

  stock: {
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


/*
|--------------------------------------------------------------------------
| Reasons
|--------------------------------------------------------------------------
*/

const reasonOptions = [
  {
    value: 'spoiled',
    label: 'Spoiled',
  },
  {
    value: 'expired',
    label: 'Expired',
  },
  {
    value: 'spillage',
    label: 'Spillage',
  },
  {
    value: 'burnt_overcooked',
    label: 'Burnt / Overcooked',
  },
  {
    value: 'contaminated',
    label: 'Contaminated',
  },
  {
    value: 'damaged_packaging',
    label: 'Damaged Packaging',
  },
  {
    value: 'quality_rejected',
    label: 'Quality Rejected',
  },
  {
    value: 'staff_handling_loss',
    label: 'Staff Handling Loss',
  },
  {
    value: 'other',
    label: 'Other',
  },
]


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = reactive({
  quantity: '',
  reason: '',
  wasted_at: '',
  notes: '',
})


const localErrors =
  reactive({})


/*
|--------------------------------------------------------------------------
| Stock Snapshot
|--------------------------------------------------------------------------
*/

const rawMaterialId = computed(() => {
  return (
    props.stock?.raw_material_id
    ??
    props.stock?.raw_material?.id
    ??
    null
  )
})


const materialName = computed(() => {
  return (
    props.stock?.raw_material?.material_name
    ||
    props.stock?.material_name
    ||
    'Unknown Material'
  )
})


const materialCategory = computed(() => {
  return (
    props.stock?.raw_material?.category
    ||
    props.stock?.category
    ||
    'No category'
  )
})


const unit = computed(() => {
  return (
    props.stock?.raw_material?.base_unit
    ||
    props.stock?.unit
    ||
    props.stock?.base_unit
    ||
    'unit'
  )
})


const availableQuantity = computed(() => {
  return nonNegativeNumber(
    props.stock?.quantity,
  )
})


const averageUnitCost = computed(() => {
  return nonNegativeNumber(
    props.stock?.average_unit_cost,
  )
})


const validQuantity = computed(() => {
  const number =
    Number(
      form.quantity,
    )

  if (
    !Number.isFinite(number)
    ||
    number <= 0
  ) {
    return 0
  }

  return Math.min(
    roundDecimal(
      number,
      4,
    ),
    availableQuantity.value,
  )
})


const quantityAfter = computed(() => {
  return Math.max(
    0,
    roundDecimal(
      availableQuantity.value
      -
      validQuantity.value,
      4,
    ),
  )
})


const estimatedLoss = computed(() => {
  return roundDecimal(
    validQuantity.value
    *
    averageUnitCost.value,
    4,
  )
})


/*
|--------------------------------------------------------------------------
| Reset
|--------------------------------------------------------------------------
*/

function resetForm()
{
  form.quantity = ''
  form.reason = ''
  form.wasted_at =
    localDateTimeValue()
  form.notes = ''

  Object.keys(localErrors)
    .forEach(
      (key) => {
        delete localErrors[key]
      },
    )
}


watch(
  [
    () => props.show,
    rawMaterialId,
  ],
  ([show]) => {
    if (show) {
      resetForm()
    }
  },
  {
    immediate: true,
  },
)


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function validateForm()
{
  Object.keys(localErrors)
    .forEach(
      (key) => {
        delete localErrors[key]
      },
    )

  if (!rawMaterialId.value) {
    localErrors.raw_material_id =
      'A valid raw material is required.'
  }

  const quantity =
    Number(
      form.quantity,
    )

  if (
    !Number.isFinite(quantity)
    ||
    quantity <= 0
  ) {
    localErrors.quantity =
      'Enter a damage / wastage quantity greater than zero.'
  }
  else if (
    decimalPlaces(
      form.quantity,
    ) > 4
  ) {
    localErrors.quantity =
      'Quantity may contain up to 4 decimal places.'
  }
  else if (
    quantity >
    availableQuantity.value
  ) {
    localErrors.quantity =
      `Quantity cannot exceed available stock (${quantityText(availableQuantity.value)}).`
  }

  if (!form.reason) {
    localErrors.reason =
      'Select a damage / wastage reason.'
  }

  if (
    form.notes.length > 2000
  ) {
    localErrors.notes =
      'Notes cannot exceed 2000 characters.'
  }

  return (
    Object.keys(localErrors)
      .length === 0
  )
}


/*
|--------------------------------------------------------------------------
| Submit / Close
|--------------------------------------------------------------------------
*/

function submitForm()
{
  if (
    props.submitting
    ||
    !validateForm()
  ) {
    return
  }

  emit(
    'submit',
    {
      raw_material_id:
        Number(
          rawMaterialId.value,
        ),

      quantity:
        roundDecimal(
          Number(
            form.quantity,
          ),
          4,
        ),

      reason:
        form.reason,

      wasted_at:
        form.wasted_at
        ||
        null,

      notes:
        form.notes.trim()
        ||
        null,
    },
  )
}


function closeModal()
{
  if (props.submitting) {
    return
  }

  emit('close')
}


/*
|--------------------------------------------------------------------------
| Errors
|--------------------------------------------------------------------------
*/

function clearLocalError(
  key,
)
{
  if (
    Object.prototype
      .hasOwnProperty
      .call(
        localErrors,
        key,
      )
  ) {
    delete localErrors[key]
  }
}


function fieldError(
  key,
)
{
  if (localErrors[key]) {
    return localErrors[key]
  }

  const messages =
    props.serverErrors?.[key]

  if (
    Array.isArray(messages)
    &&
    messages.length > 0
  ) {
    return String(
      messages[0],
    )
  }

  if (
    typeof messages === 'string'
  ) {
    return messages
  }

  return ''
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function nonNegativeNumber(
  value,
)
{
  const number =
    Number(
      value,
    )

  if (
    !Number.isFinite(number)
    ||
    number < 0
  ) {
    return 0
  }

  return number
}


function roundDecimal(
  value,
  places,
)
{
  const factor =
    10 ** places

  return Math.round(
    (
      Number(value)
      +
      Number.EPSILON
    )
    *
    factor,
  ) / factor
}


function decimalPlaces(
  value,
)
{
  const raw =
    String(
      value
      ??
      '',
    ).trim()

  if (!raw.includes('.')) {
    return 0
  }

  return raw
    .split('.')[1]
    ?.length
    ??
    0
}


function quantityText(
  value,
)
{
  const number =
    nonNegativeNumber(
      value,
    )

  const formatted =
    number.toLocaleString(
      'en-BD',
      {
        maximumFractionDigits: 4,
      },
    )

  return `${formatted} ${unit.value}`
}


function moneyText(
  value,
  maximumFractionDigits = 2,
)
{
  const number =
    nonNegativeNumber(
      value,
    )

  return `৳ ${number.toLocaleString(
    'en-BD',
    {
      minimumFractionDigits: 2,
      maximumFractionDigits,
    },
  )}`
}


function localDateTimeValue()
{
  const date =
    new Date()

  const local =
    new Date(
      date.getTime()
      -
      date.getTimezoneOffset()
      *
      60000,
    )

  return local
    .toISOString()
    .slice(
      0,
      16,
    )
}
</script>
