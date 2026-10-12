<template>
  <Teleport to="body">
    <Transition name="inventory-modal">
      <div
        v-if="show"
        class="warehouse-lot-modal-backdrop"
        @click.self="requestClose"
      >
        <section
          class="warehouse-lot-modal"
          role="dialog"
          aria-modal="true"
          aria-labelledby="warehouse-lot-detail-title"
        >
          <header class="warehouse-lot-modal-header">
            <div>
              <span class="warehouse-lot-modal-eyebrow">
                Lot Traceability
              </span>

              <h2 id="warehouse-lot-detail-title">
                {{
                  lot?.lot_no
                  ||
                  'Warehouse Lot'
                }}
              </h2>

              <p>
                Physical source, expiry and immutable lot movement history.
              </p>
            </div>

            <button
              type="button"
              class="warehouse-lot-icon-button"
              :disabled="loading"
              aria-label="Close lot details"
              @click="requestClose"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </header>


          <div class="warehouse-lot-modal-body">
            <div
              v-if="loading"
              class="warehouse-lot-loading-state"
            >
              <span class="spinner-border spinner-border-sm"></span>
              Loading lot details...
            </div>

            <div
              v-else-if="errorMessage"
              class="warehouse-lot-alert warehouse-lot-alert-danger"
            >
              <i class="bi bi-exclamation-triangle"></i>
              {{ errorMessage }}
            </div>

            <template v-else-if="lot">
              <div class="warehouse-lot-detail-topline">
                <div>
                  <strong>
                    {{ lot.material_name }}
                  </strong>

                  <small>
                    {{ lot.category || 'Uncategorized' }}
                  </small>
                </div>

                <span
                  class="warehouse-lot-status"
                  :class="
                    `warehouse-lot-status-${lot.status || 'unknown'}`
                  "
                >
                  {{ lot.status_label || 'Unknown' }}
                </span>
              </div>


              <div class="warehouse-lot-detail-grid">
                <article>
                  <span>Remaining</span>
                  <strong>{{ lot.quantity_formatted }}</strong>
                </article>

                <article>
                  <span>Originally Received</span>
                  <strong>{{ lot.received_quantity_formatted }}</strong>
                </article>

                <article>
                  <span>Original Unit Cost</span>
                  <strong>{{ lot.unit_cost_formatted }}</strong>
                </article>

                <article>
                  <span>Remaining Value</span>
                  <strong>{{ lot.remaining_value_formatted }}</strong>
                </article>
              </div>


              <section class="warehouse-lot-detail-card">
                <h3>Source</h3>

                <dl class="warehouse-lot-definition-grid">
                  <div>
                    <dt>Source Type</dt>
                    <dd>{{ lot.source_type_label || '—' }}</dd>
                  </div>

                  <div>
                    <dt>Supplier</dt>
                    <dd>{{ lot.supplier_name || 'Unknown / Not Applicable' }}</dd>
                  </div>

                  <div>
                    <dt>Original PO</dt>
                    <dd>{{ lot.purchase_order?.label || 'Unknown / Not Applicable' }}</dd>
                  </div>

                  <div>
                    <dt>Original GRN</dt>
                    <dd>{{ lot.purchase_order_receipt?.label || 'Unknown / Not Applicable' }}</dd>
                  </div>

                  <div>
                    <dt>Supplier Batch</dt>
                    <dd>{{ lot.supplier_batch_no || 'Not provided' }}</dd>
                  </div>

                  <div>
                    <dt>Received At</dt>
                    <dd>{{ lot.received_at_label || 'Unknown' }}</dd>
                  </div>
                </dl>
              </section>


              <section class="warehouse-lot-detail-card">
                <h3>Expiry</h3>

                <dl class="warehouse-lot-definition-grid">
                  <div>
                    <dt>Manufacturing Date</dt>
                    <dd>{{ lot.manufactured_at_label || 'Not provided' }}</dd>
                  </div>

                  <div>
                    <dt>Expiry Date</dt>
                    <dd>{{ lot.expiry_date_label || 'Unknown' }}</dd>
                  </div>

                  <div>
                    <dt>Days Until Expiry</dt>
                    <dd>{{ daysLabel(lot) }}</dd>
                  </div>

                  <div>
                    <dt>FEFO Position</dt>
                    <dd>
                      {{
                        lot.expiry_date
                          ? 'Expiry-controlled lot'
                          : 'Unknown expiry — used after dated lots'
                      }}
                    </dd>
                  </div>
                </dl>
              </section>


              <section
                v-if="lot.notes"
                class="warehouse-lot-detail-card"
              >
                <h3>System / Source Notes</h3>
                <p class="warehouse-lot-detail-notes">
                  {{ lot.notes }}
                </p>
              </section>


              <section
                v-if="lot.manual_notes"
                class="warehouse-lot-detail-card"
              >
                <h3>Manual Notes</h3>
                <p class="warehouse-lot-detail-notes">
                  {{ lot.manual_notes }}
                </p>
              </section>


              <section
                v-if="metadataHistory.length"
                class="warehouse-lot-detail-card"
              >
                <div class="warehouse-lot-card-heading">
                  <div>
                    <h3>Manual Update History</h3>
                    <p>
                      Immutable audit trail of batch, expiry and supplier corrections.
                    </p>
                  </div>

                  <span>
                    {{ metadataHistory.length }} change(s)
                  </span>
                </div>

                <div class="warehouse-lot-metadata-history-list">
                  <article
                    v-for="change in metadataHistory"
                    :key="change.id"
                    class="warehouse-lot-metadata-history-row"
                  >
                    <div>
                      <strong>
                        {{ change.created_at_label || 'Unknown time' }}
                      </strong>

                      <small>
                        {{ change.changed_by?.name || 'System / Unknown User' }}
                      </small>
                    </div>

                    <p>
                      {{ change.change_reason }}
                    </p>

                    <div class="warehouse-lot-change-fields">
                      <span
                        v-for="field in change.changed_fields"
                        :key="field"
                      >
                        {{ fieldLabel(field) }}
                      </span>
                    </div>
                  </article>
                </div>
              </section>


              <section class="warehouse-lot-detail-card">
                <div class="warehouse-lot-card-heading">
                  <div>
                    <h3>Lot Movement History</h3>
                    <p>
                      Immutable physical quantity audit for this exact lot.
                    </p>
                  </div>

                  <span>
                    {{ movements.length }} movement(s)
                  </span>
                </div>

                <div
                  v-if="movements.length === 0"
                  class="warehouse-lot-empty-small"
                >
                  No lot movement history found.
                </div>

                <div
                  v-else
                  class="warehouse-lot-movement-list"
                >
                  <article
                    v-for="movement in movements"
                    :key="movement.id"
                    class="warehouse-lot-movement-row"
                  >
                    <div class="warehouse-lot-movement-icon">
                      <i
                        class="bi"
                        :class="
                          movement.direction === 'in'
                            ? 'bi-arrow-down-left'
                            : 'bi-arrow-up-right'
                        "
                      ></i>
                    </div>

                    <div class="warehouse-lot-movement-main">
                      <strong>
                        {{ movement.movement_type_label }}
                      </strong>

                      <small>
                        {{ movement.occurred_at_label || 'Unknown time' }}
                      </small>

                      <p v-if="movement.notes">
                        {{ movement.notes }}
                      </p>
                    </div>

                    <div class="warehouse-lot-movement-qty">
                      <strong>
                        {{
                          movement.direction === 'in'
                            ? '+'
                            : '-'
                        }}
                        {{ compactNumber(movement.quantity) }}
                        {{ movement.unit }}
                      </strong>

                      <small>
                        {{ compactNumber(movement.quantity_before) }}
                        →
                        {{ compactNumber(movement.quantity_after) }}
                      </small>
                    </div>
                  </article>
                </div>
              </section>
            </template>
          </div>

          <footer class="warehouse-lot-modal-footer">
            <button
              type="button"
              class="warehouse-lot-secondary-button"
              :disabled="loading"
              @click="requestClose"
            >
              Close
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
  ref,
  watch,
} from 'vue'

import warehouseLotService
  from '@/services/warehouseLotService'


const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },

  lotId: {
    type: Number,
    default: null,
  },
})


const emit = defineEmits([
  'close',
])


const lot =
  ref(null)

const loading =
  ref(false)

const errorMessage =
  ref('')

const metadataHistory =
  ref([])


const movements =
  computed(() => {
    return Array.isArray(
      lot.value?.movements,
    )
      ? lot.value.movements
      : []
  })


async function loadLot()
{
  const id =
    Number(
      props.lotId,
    )

  if (
    !props.show
    ||
    !Number.isInteger(id)
    ||
    id <= 0
  ) {
    lot.value = null
    return
  }

  loading.value = true
  errorMessage.value = ''

  try {
    const [
      response,
      historyResponse,
    ] =
      await Promise.all([
        warehouseLotService
          .getLot(id),

        warehouseLotService
          .getMetadataHistory(id),
      ])

    lot.value =
      response?.data
      &&
      typeof response.data === 'object'
        ? response.data
        : null

    metadataHistory.value =
      Array.isArray(
        historyResponse?.data,
      )
        ? historyResponse.data
        : []

    if (
      !lot.value
    ) {
      throw new Error(
        'Warehouse lot information was not found.',
      )
    }
  } catch (error) {
    lot.value = null
    metadataHistory.value = []

    errorMessage.value =
      warehouseLotService
        .getErrorMessage(
          error,
          'Unable to load warehouse lot details.',
        )
  } finally {
    loading.value = false
  }
}


function requestClose()
{
  if (
    loading.value
  ) {
    return
  }

  emit('close')
}


function daysLabel(
  item,
)
{
  if (
    item?.days_until_expiry === null
    ||
    item?.days_until_expiry === undefined
  ) {
    return 'Unknown'
  }

  const days =
    Number(
      item.days_until_expiry,
    )

  if (
    !Number.isFinite(days)
  ) {
    return 'Unknown'
  }

  if (
    days < 0
  ) {
    return `${Math.abs(days)} day(s) expired`
  }

  if (
    days === 0
  ) {
    return 'Expires today'
  }

  return `${days} day(s) remaining`
}


function fieldLabel(
  field,
)
{
  const labels = {
    supplier_id: 'Supplier',
    supplier_batch_no: 'Supplier Batch',
    manufactured_at: 'Manufacturing Date',
    expiry_date: 'Expiry Date',
    manual_notes: 'Manual Notes',
  }

  return (
    labels[field]
    ||
    String(field || '')
      .replace(/_/g, ' ')
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
    .replace(/0+$/, '')
    .replace(/\.$/, '')
}


watch(
  [
    () => props.show,
    () => props.lotId,
  ],
  () => {
    void loadLot()
  },
  {
    immediate: true,
  },
)
</script>
