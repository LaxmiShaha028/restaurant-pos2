<template>
  <Teleport to="body">
    <Transition name="inventory-modal">
      <div
        v-if="show"
        class="daily-transfer-backdrop"
        @click.self="requestClose"
      >
        <section
          class="daily-transfer-modal"
          role="dialog"
          aria-modal="true"
          aria-labelledby="daily-transfer-title"
        >
          <header class="daily-transfer-header">
            <div>
              <span class="daily-transfer-eyebrow">
                Saved Transfer Preset
              </span>

              <h2 id="daily-transfer-title">
                Daily Transfer
              </h2>

              <p>
                Load a saved template, adjust today's quantities,
                then use the normal warehouse → restaurant transfer flow.
              </p>
            </div>

            <button
              type="button"
              class="daily-transfer-icon-btn"
              :disabled="submitting"
              aria-label="Close daily transfer"
              @click="requestClose"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </header>


          <div class="daily-transfer-body">
            <div
              v-if="displayError"
              class="daily-transfer-alert daily-transfer-alert-danger"
              role="alert"
            >
              <i class="bi bi-exclamation-triangle"></i>
              <span>{{ displayError }}</span>
            </div>


            <div
              v-if="templatesLoading"
              class="daily-transfer-loading"
            >
              <span class="spinner-border spinner-border-sm"></span>
              Loading active transfer templates...
            </div>


            <template v-else>
              <div
                v-if="templates.length === 0"
                class="daily-transfer-empty"
              >
                <i class="bi bi-bookmark-plus"></i>

                <div>
                  <strong>
                    No active Daily Transfer template yet
                  </strong>

                  <p>
                    Create a reusable template first. It will save the
                    normal quantity for each raw material.
                  </p>
                </div>

                <button
                  type="button"
                  @click="emit('manage-templates')"
                >
                  Manage Templates
                </button>
              </div>


              <template v-else>
                <section class="daily-transfer-card">
                  <div class="daily-transfer-grid">
                    <div class="daily-transfer-field">
                      <label for="daily-transfer-template">
                        Template
                        <span>*</span>
                      </label>

                      <select
                        id="daily-transfer-template"
                        v-model="selectedTemplateId"
                        :disabled="submitting"
                        @change="applySelectedTemplate"
                      >
                        <option value="">
                          Select template
                        </option>

                        <option
                          v-for="template in templates"
                          :key="template.id"
                          :value="String(template.id)"
                        >
                          {{ template.name }}
                        </option>
                      </select>
                    </div>


                    <div class="daily-transfer-field">
                      <label for="daily-transfer-date">
                        Transfer Date &amp; Time
                      </label>

                      <input
                        id="daily-transfer-date"
                        v-model="form.transferred_at"
                        type="datetime-local"
                        :disabled="submitting"
                      />
                    </div>
                  </div>


                  <div class="daily-transfer-field daily-transfer-field-full">
                    <label for="daily-transfer-notes">
                      Transfer Notes
                    </label>

                    <textarea
                      id="daily-transfer-notes"
                      v-model="form.notes"
                      rows="2"
                      maxlength="2000"
                      :disabled="submitting"
                      placeholder="Optional notes for today's transfer"
                    ></textarea>
                  </div>
                </section>


                <section
                  v-if="selectedTemplate"
                  class="daily-transfer-card"
                >
                  <div class="daily-transfer-section-heading">
                    <div>
                      <h3>
                        Today's Transfer Quantities
                      </h3>

                      <p>
                        Template values are only defaults. Editing them
                        here does not change the saved template.
                      </p>
                    </div>

                    <button
                      type="button"
                      class="daily-transfer-secondary-btn"
                      :disabled="submitting"
                      @click="emit('manage-templates')"
                    >
                      <i class="bi bi-sliders"></i>
                      Manage Templates
                    </button>
                  </div>


                  <div
                    v-if="warehouseStocksLoading"
                    class="daily-transfer-loading"
                  >
                    <span class="spinner-border spinner-border-sm"></span>
                    Loading warehouse availability...
                  </div>


                  <div
                    v-else-if="form.items.length === 0"
                    class="daily-transfer-empty compact"
                  >
                    <i class="bi bi-inboxes"></i>
                    <span>
                      This template has no usable items.
                    </span>
                  </div>


                  <div
                    v-else
                    class="daily-transfer-items"
                  >
                    <article
                      v-for="(item, index) in form.items"
                      :key="item.row_key"
                      class="daily-transfer-item"
                      :class="{
                        'daily-transfer-item-warning':
                          itemHasAvailabilityProblem(item),
                      }"
                    >
                      <div class="daily-transfer-item-main">
                        <div class="daily-transfer-item-title">
                          <strong>
                            {{ item.item_name }}
                          </strong>

                          <small>
                            {{ item.unit }}
                          </small>
                        </div>

                        <div class="daily-transfer-availability">
                          <span>
                            Warehouse
                          </span>

                          <strong>
                            {{ availableDisplay(item) }}
                          </strong>
                        </div>
                      </div>


                      <div class="daily-transfer-item-controls">
                        <div class="daily-transfer-field">
                          <label
                            :for="`daily-transfer-qty-${item.row_key}`"
                          >
                            Transfer Quantity
                          </label>

                          <div class="daily-transfer-quantity">
                            <input
                              :id="`daily-transfer-qty-${item.row_key}`"
                              v-model="item.quantity"
                              type="text"
                              inputmode="decimal"
                              autocomplete="off"
                              :disabled="submitting"
                              @input="sanitizeQuantity(item)"
                            />

                            <span>
                              {{ item.unit }}
                            </span>
                          </div>

                          <small
                            v-if="itemValidationMessage(item)"
                            class="daily-transfer-field-error"
                          >
                            {{ itemValidationMessage(item) }}
                          </small>

                          <small
                            v-else-if="serverItemError(index)"
                            class="daily-transfer-field-error"
                          >
                            {{ serverItemError(index) }}
                          </small>
                        </div>


                        <button
                          type="button"
                          class="daily-transfer-remove-btn"
                          :disabled="submitting"
                          title="Remove from today's transfer only"
                          @click="removeItem(index)"
                        >
                          <i class="bi bi-trash3"></i>
                        </button>
                      </div>
                    </article>
                  </div>


                  <div class="daily-transfer-summary">
                    <div>
                      <span>
                        Selected items
                      </span>

                      <strong>
                        {{ form.items.length }}
                      </strong>
                    </div>

                    <div>
                      <span>
                        Template
                      </span>

                      <strong>
                        {{ selectedTemplate.name }}
                      </strong>
                    </div>
                  </div>
                </section>
              </template>
            </template>
          </div>


          <footer class="daily-transfer-footer">
            <button
              type="button"
              class="daily-transfer-secondary-btn"
              :disabled="submitting"
              @click="requestClose"
            >
              Cancel
            </button>

            <button
              type="button"
              class="daily-transfer-primary-btn"
              :disabled="!canSubmit"
              @click="submit"
            >
              <span
                v-if="submitting"
                class="spinner-border spinner-border-sm"
              ></span>

              <i
                v-else
                class="bi bi-arrow-left-right"
              ></i>

              {{
                submitting
                  ? 'Transferring...'
                  : 'Confirm Daily Transfer'
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

import stockTransferTemplateService
  from '@/services/stockTransferTemplateService'


const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },

  warehouseStocks: {
    type: Array,
    default: () => [],
  },

  warehouseStocksLoading: {
    type: Boolean,
    default: false,
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

  refreshKey: {
    type: Number,
    default: 0,
  },
})


const emit = defineEmits([
  'close',
  'submit',
  'refresh-warehouse',
  'manage-templates',
])


const templates =
  ref([])

const templatesLoading =
  ref(false)

const localError =
  ref('')

const selectedTemplateId =
  ref('')

let rowSequence =
  0


const form = reactive({
  transferred_at: '',
  notes: '',
  items: [],
})


const selectedTemplate =
  computed(() => {

    const id =
      Number(
        selectedTemplateId.value
      )

    if (
      !Number.isInteger(id)
      ||
      id <= 0
    ) {
      return null
    }

    return (
      templates.value.find(
        (template) =>
          Number(template?.id) === id,
      )
      ||
      null
    )
  })


const displayError =
  computed(() => {

    return (
      localError.value
      ||
      props.errorMessage
      ||
      ''
    )
  })


const canSubmit =
  computed(() => {

    if (
      props.submitting
      ||
      props.warehouseStocksLoading
      ||
      templatesLoading.value
      ||
      !selectedTemplate.value
      ||
      form.items.length === 0
    ) {
      return false
    }

    return form.items.every(
      (item) =>
        itemValidationMessage(item) === '',
    )
  })


async function loadTemplates()
{
  templatesLoading.value =
    true

  localError.value =
    ''

  try {
    const response =
      await stockTransferTemplateService
        .getTemplates({
          active: 1,
        })

    templates.value =
      Array.isArray(response?.data)
        ? response.data
        : []

    if (
      templates.value.length === 0
    ) {
      selectedTemplateId.value = ''
      form.items = []
      return
    }

    const stillExists =
      templates.value.some(
        (template) =>
          String(template.id)
          ===
          String(selectedTemplateId.value),
      )

    if (
      !stillExists
    ) {
      selectedTemplateId.value =
        String(
          templates.value[0].id
        )
    }

    applySelectedTemplate()
  } catch (error) {
    templates.value = []

    localError.value =
      stockTransferTemplateService
        .getErrorMessage(
          error,
          'Unable to load Daily Transfer templates.',
        )
  } finally {
    templatesLoading.value =
      false
  }
}


function applySelectedTemplate()
{
  localError.value = ''

  const template =
    selectedTemplate.value

  if (
    !template
  ) {
    form.items = []
    form.notes = ''
    return
  }

  const items =
    Array.isArray(template?.items)
      ? template.items
      : []

  form.items =
    items.map(
      (item) => ({
        row_key:
          `daily-${++rowSequence}`,

        raw_material_id:
          Number(
            item?.raw_material_id
            ?? 0
          ),

        item_name:
          item?.item_name
          ||
          item?.raw_material
            ?.material_name
          ||
          'Raw Material',

        unit:
          item?.unit
          ||
          item?.raw_material
            ?.base_unit
          ||
          '',

        quantity:
          quantityString(
            item?.quantity
          ),

        notes:
          item?.notes
          ||
          '',
      }),
    )

  form.notes =
    `Daily Transfer — ${template.name}`
}


function removeItem(
  index,
)
{
  if (
    props.submitting
  ) {
    return
  }

  form.items.splice(
    index,
    1,
  )
}


function sanitizeQuantity(
  item,
)
{
  let value =
    String(
      item?.quantity
      ??
      '',
    )

  value =
    value.replace(
      /[^\d.]/g,
      '',
    )

  const firstDot =
    value.indexOf('.')

  if (
    firstDot >= 0
  ) {
    value =
      value.slice(
        0,
        firstDot + 1,
      )
      +
      value
        .slice(
          firstDot + 1,
        )
        .replace(
          /\./g,
          '',
        )
        .slice(
          0,
          4,
        )
  }

  item.quantity =
    value
}


function submit()
{
  localError.value = ''

  if (
    !canSubmit.value
  ) {
    localError.value =
      'Please fix the highlighted transfer quantities before continuing.'

    return
  }

  emit(
    'submit',
    {
      transferred_at:
        form.transferred_at
        ||
        null,

      notes:
        String(
          form.notes
          ||
          '',
        ).trim()
        ||
        null,

      items:
        form.items.map(
          (item) => ({
            raw_material_id:
              Number(
                item.raw_material_id
              ),

            quantity:
              quantityString(
                item.quantity
              ),

            notes:
              String(
                item.notes
                ||
                '',
              ).trim()
              ||
              null,
          }),
        ),
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


function findWarehouseStock(
  rawMaterialId,
)
{
  const id =
    Number(
      rawMaterialId
    )

  return (
    props.warehouseStocks.find(
      (stock) =>
        stockRawMaterialId(stock)
        ===
        id,
    )
    ||
    null
  )
}


function stockRawMaterialId(
  stock,
)
{
  return Number(
    stock?.raw_material_id
    ??
    stock?.raw_material?.id
    ??
    0,
  )
}


function warehouseQuantity(
  item,
)
{
  const stock =
    findWarehouseStock(
      item?.raw_material_id,
    )

  const quantity =
    Number(
      stock?.quantity
      ??
      0,
    )

  return Number.isFinite(quantity)
    ? Math.max(0, quantity)
    : 0
}


function availableDisplay(
  item,
)
{
  const quantity =
    warehouseQuantity(item)

  return `${compactNumber(quantity)} ${item?.unit || ''}`.trim()
}


function itemValidationMessage(
  item,
)
{
  const stock =
    findWarehouseStock(
      item?.raw_material_id,
    )

  if (
    !stock
  ) {
    return 'This material currently has no transferable warehouse stock. Remove it from today’s transfer.'
  }

  const available =
    warehouseQuantity(item)

  if (
    available <= 0
  ) {
    return 'Warehouse quantity is zero. Remove this item or replenish warehouse stock.'
  }

  const quantity =
    parsedQuantity(
      item?.quantity,
    )

  if (
    quantity === null
    ||
    quantity <= 0
  ) {
    return 'Enter a quantity greater than zero with up to 4 decimal places.'
  }

  if (
    quantity > available
  ) {
    return `Only ${compactNumber(available)} ${item?.unit || ''} is available in warehouse.`
  }

  return ''
}


function itemHasAvailabilityProblem(
  item,
)
{
  return (
    itemValidationMessage(item)
    !==
    ''
  )
}


function serverItemError(
  index,
)
{
  const fields = [
    `items.${index}.quantity`,
    `items.${index}.raw_material_id`,
  ]

  for (
    const field
    of fields
  ) {
    const value =
      props.serverErrors?.[field]

    if (
      Array.isArray(value)
      &&
      value.length
    ) {
      return String(
        value[0]
      )
    }

    if (
      typeof value === 'string'
      &&
      value
    ) {
      return value
    }
  }

  return ''
}


function parsedQuantity(
  value,
)
{
  const raw =
    String(
      value
      ??
      '',
    ).trim()

  if (
    !/^\d+(?:\.\d{0,4})?$/.test(
      raw,
    )
  ) {
    return null
  }

  const number =
    Number(raw)

  return Number.isFinite(number)
    ? number
    : null
}


function quantityString(
  value,
)
{
  const parsed =
    Number(value)

  if (
    !Number.isFinite(parsed)
  ) {
    return ''
  }

  return parsed
    .toFixed(4)
    .replace(
      /0+$/,
      '',
    )
    .replace(
      /\.$/,
      '',
    )
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
    .replace(
      /0+$/,
      '',
    )
    .replace(
      /\.$/,
      '',
    )
}


function reset()
{
  selectedTemplateId.value = ''
  form.transferred_at = ''
  form.notes = ''
  form.items = []
  localError.value = ''
}


watch(
  () => props.show,
  async (show) => {

    if (
      !show
    ) {
      reset()
      return
    }

    await loadTemplates()

    emit(
      'refresh-warehouse'
    )
  },
)


watch(
  () => props.refreshKey,
  () => {

    if (
      props.show
    ) {
      void loadTemplates()
    }
  },
)
</script>
