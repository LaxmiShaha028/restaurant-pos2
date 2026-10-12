<template>
  <section
    class="warehouse-lot-section"
    :aria-busy="loading"
  >
    <div class="warehouse-lot-section-heading">
      <div>
        <h3>Batches / Lots</h3>
        <p>
          Trace each physical warehouse batch back to its purchase,
          supplier, expiry and original cost.
        </p>
      </div>

      <span class="warehouse-lot-result-pill">
        {{ meta.total }} lot(s)
      </span>
    </div>


    <div class="warehouse-lot-filter-card">
      <div class="warehouse-lot-filter-grid">
        <div class="warehouse-lot-field warehouse-lot-search-field">
          <label for="warehouse-lot-search">
            Search
          </label>

          <div class="warehouse-lot-search-input">
            <i class="bi bi-search"></i>

            <input
              id="warehouse-lot-search"
              v-model.trim="filters.search"
              type="search"
              placeholder="Lot, material, supplier, batch or GRN"
              :disabled="loading"
              @keyup.enter="applyFilters"
            />
          </div>
        </div>


        <div class="warehouse-lot-field">
          <label for="warehouse-lot-status">
            Status
          </label>

          <select
            id="warehouse-lot-status"
            v-model="filters.status"
            :disabled="loading"
          >
            <option value="">All statuses</option>
            <option value="healthy">Healthy</option>
            <option value="expiring_soon">Expiring Soon</option>
            <option value="critical">Critical</option>
            <option value="expired">Expired</option>
            <option value="unknown_expiry">Expiry Unknown</option>
            <option value="depleted">Depleted</option>
          </select>
        </div>


        <div class="warehouse-lot-field">
          <label for="warehouse-lot-source">
            Source
          </label>

          <select
            id="warehouse-lot-source"
            v-model="filters.source_type"
            :disabled="loading"
          >
            <option value="">All sources</option>
            <option value="purchase_receive">Purchase Receive</option>
            <option value="legacy_opening">Legacy Opening</option>
            <option value="warehouse_adjustment_in">Warehouse Adjustment In</option>
          </select>
        </div>


        <div class="warehouse-lot-field">
          <label for="warehouse-lot-stock">
            Quantity
          </label>

          <select
            id="warehouse-lot-stock"
            v-model="filters.has_stock"
            :disabled="loading"
          >
            <option value="">All lots</option>
            <option value="1">Remaining stock only</option>
            <option value="0">Depleted only</option>
          </select>
        </div>
      </div>


      <div class="warehouse-lot-filter-actions">
        <button
          type="button"
          class="warehouse-lot-secondary-button"
          :disabled="loading || !hasActiveFilters"
          @click="clearFilters"
        >
          Clear
        </button>

        <button
          type="button"
          class="warehouse-lot-primary-button"
          :disabled="loading"
          @click="applyFilters"
        >
          <span
            v-if="loading"
            class="spinner-border spinner-border-sm"
          ></span>

          <i
            v-else
            class="bi bi-funnel"
          ></i>

          Apply Filters
        </button>
      </div>
    </div>


    <div
      v-if="errorMessage"
      class="warehouse-lot-alert warehouse-lot-alert-danger"
    >
      <i class="bi bi-exclamation-triangle"></i>
      {{ errorMessage }}
    </div>


    <div
      v-if="loading && lots.length === 0"
      class="warehouse-lot-loading-state"
    >
      <span class="spinner-border spinner-border-sm"></span>
      Loading warehouse lots...
    </div>


    <div
      v-else-if="!loading && lots.length === 0"
      class="warehouse-lot-empty-state"
    >
      <i class="bi bi-box-seam"></i>

      <strong>No warehouse lots found</strong>

      <p>
        Purchase Receive and warehouse adjustment-in operations
        will create lots automatically.
      </p>
    </div>


    <div
      v-else
      class="warehouse-lot-table-card"
    >
      <div class="table-responsive">
        <table class="warehouse-lot-table">
          <thead>
            <tr>
              <th>Lot / Batch</th>
              <th>Material</th>
              <th>Source</th>
              <th>Remaining</th>
              <th>Cost / Value</th>
              <th>Expiry</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="lot in lots"
              :key="lot.id"
            >
              <td>
                <div class="warehouse-lot-primary-cell">
                  <strong>
                    {{ lot.lot_no }}
                  </strong>

                  <small>
                    {{
                      lot.supplier_batch_no
                        ? `Batch: ${lot.supplier_batch_no}`
                        : 'Supplier batch not provided'
                    }}
                  </small>
                </div>
              </td>

              <td>
                <div class="warehouse-lot-primary-cell">
                  <strong>
                    {{ lot.material_name }}
                  </strong>

                  <small>
                    {{ lot.category || 'Uncategorized' }}
                  </small>
                </div>
              </td>

              <td>
                <div class="warehouse-lot-primary-cell">
                  <span>
                    {{ lot.source_type_label }}
                  </span>

                  <small>
                    {{ lot.supplier_name || 'No supplier link' }}
                  </small>
                </div>
              </td>

              <td>
                <div class="warehouse-lot-primary-cell">
                  <strong>
                    {{ lot.quantity_formatted }}
                  </strong>

                  <small>
                    of {{ lot.received_quantity_formatted }}
                  </small>
                </div>
              </td>

              <td>
                <div class="warehouse-lot-primary-cell">
                  <strong>
                    {{ lot.unit_cost_formatted }}
                  </strong>

                  <small>
                    Value {{ lot.remaining_value_formatted }}
                  </small>
                </div>
              </td>

              <td>
                <div class="warehouse-lot-primary-cell">
                  <strong>
                    {{ lot.expiry_date_label || 'Unknown' }}
                  </strong>

                  <small>
                    {{ daysLabel(lot) }}
                  </small>
                </div>
              </td>

              <td>
                <span
                  class="warehouse-lot-status"
                  :class="
                    `warehouse-lot-status-${lot.status || 'unknown'}`
                  "
                >
                  {{ lot.status_label }}
                </span>
              </td>

              <td>
                <div class="warehouse-lot-row-actions">
                  <button
                    type="button"
                    class="warehouse-lot-view-button"
                    @click="openDetails(lot)"
                  >
                    <i class="bi bi-eye"></i>
                    View
                  </button>

                  <button
                    v-if="canManage"
                    type="button"
                    class="warehouse-lot-edit-button"
                    @click="openMetadataEdit(lot)"
                  >
                    <i class="bi bi-pencil"></i>
                    Edit
                  </button>

                  <button
                    v-if="canManage"
                    type="button"
                    class="warehouse-lot-return-button"
                    :disabled="!canReturn(lot)"
                    :title="returnTitle(lot)"
                    @click="openPurchaseReturn(lot)"
                  >
                    <i class="bi bi-arrow-return-left"></i>
                    Return
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>


      <div class="warehouse-lot-pagination">
        <span>
          Showing
          {{ meta.from || 0 }}
          -
          {{ meta.to || 0 }}
          of
          {{ meta.total }}
        </span>

        <div>
          <button
            type="button"
            :disabled="loading || meta.current_page <= 1"
            @click="changePage(meta.current_page - 1)"
          >
            <i class="bi bi-chevron-left"></i>
            Previous
          </button>

          <span>
            Page {{ meta.current_page }} / {{ meta.last_page }}
          </span>

          <button
            type="button"
            :disabled="loading || meta.current_page >= meta.last_page"
            @click="changePage(meta.current_page + 1)"
          >
            Next
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>


    <WarehouseLotDetailsModal
      :show="showDetails"
      :lot-id="selectedLotId"
      @close="closeDetails"
    />

    <WarehouseLotMetadataModal
      :show="showMetadataEdit"
      :lot="editingLot"
      :submitting="metadataSubmitting"
      :server-errors="metadataServerErrors"
      :error-message="metadataErrorMessage"
      @close="closeMetadataEdit"
      @submit="saveMetadata"
    />

    <PurchaseReturnModal
      :show="showPurchaseReturn"
      :lot="returnLot"
      @close="closePurchaseReturn"
      @created="handlePurchaseReturnCreated"
    />
  </section>
</template>


<script setup>
import {
  computed,
  onMounted,
  reactive,
  ref,
  watch,
} from 'vue'

import warehouseLotService
  from '@/services/warehouseLotService'

import WarehouseLotDetailsModal
  from '@/components/inventory/warehouse/WarehouseLotDetailsModal.vue'

import WarehouseLotMetadataModal
  from '@/components/inventory/warehouse/WarehouseLotMetadataModal.vue'

import PurchaseReturnModal
  from '@/components/inventory/warehouse/PurchaseReturnModal.vue'


const props = defineProps({
  refreshKey: {
    type: Number,
    default: 0,
  },

  canManage: {
    type: Boolean,
    default: false,
  },
})


const emit = defineEmits([
  'summary-changed',
  'purchase-return-created',
])


const lots =
  ref([])

const loading =
  ref(false)

const errorMessage =
  ref('')

const meta =
  reactive(
    warehouseLotService
      .createDefaultMeta(),
  )

const filters =
  reactive({
    search: '',
    status: '',
    source_type: '',
    has_stock: '1',
    page: 1,
    per_page: 10,
  })

const showDetails =
  ref(false)

const selectedLotId =
  ref(null)

const showMetadataEdit =
  ref(false)

const editingLot =
  ref(null)

const metadataSubmitting =
  ref(false)

const metadataServerErrors =
  ref({})

const metadataErrorMessage =
  ref('')

const showPurchaseReturn =
  ref(false)

const returnLot =
  ref(null)

let reloadRequested =
  false


const hasActiveFilters =
  computed(() => {
    return Boolean(
      filters.search
      ||
      filters.status
      ||
      filters.source_type
      ||
      filters.has_stock !== '1',
    )
  })


async function loadLots()
{
  if (
    loading.value
  ) {
    reloadRequested = true
    return
  }

  loading.value = true
  errorMessage.value = ''

  try {
    const response =
      await warehouseLotService
        .getLots({
          search:
            filters.search
            || undefined,

          status:
            filters.status
            || undefined,

          source_type:
            filters.source_type
            || undefined,

          has_stock:
            filters.has_stock === ''
              ? undefined
              : Number(filters.has_stock),

          sort_by:
            'expiry_date',

          sort_direction:
            'asc',

          page:
            filters.page,

          per_page:
            filters.per_page,
        })

    lots.value =
      Array.isArray(response?.data)
        ? response.data
        : []

    Object.assign(
      meta,
      warehouseLotService
        .createDefaultMeta(),
      response?.meta
      || {},
    )

    filters.page =
      Number(meta.current_page)
      || 1

    await emitSummary()
  } catch (error) {
    lots.value = []

    errorMessage.value =
      warehouseLotService
        .getErrorMessage(
          error,
          'Unable to load warehouse lots.',
        )
  } finally {
    loading.value = false

    if (
      reloadRequested
    ) {
      reloadRequested = false
      void loadLots()
    }
  }
}


async function emitSummary()
{
  try {
    const response =
      await warehouseLotService
        .getSummary()

    if (
      response?.data
      &&
      typeof response.data === 'object'
    ) {
      emit(
        'summary-changed',
        response.data,
      )
    }
  } catch {
    // Lot list should remain usable if badge summary fails.
  }
}


async function applyFilters()
{
  filters.page = 1
  await loadLots()
}


async function clearFilters()
{
  filters.search = ''
  filters.status = ''
  filters.source_type = ''
  filters.has_stock = '1'
  filters.page = 1

  await loadLots()
}


async function changePage(
  page,
)
{
  const target =
    Number(page)

  if (
    !Number.isInteger(target)
    ||
    target < 1
    ||
    target > Number(meta.last_page)
    ||
    target === Number(meta.current_page)
  ) {
    return
  }

  filters.page = target
  await loadLots()
}


function openDetails(
  lot,
)
{
  const id =
    Number(
      lot?.id,
    )

  if (
    !Number.isInteger(id)
    ||
    id <= 0
  ) {
    return
  }

  selectedLotId.value = id
  showDetails.value = true
}


function closeDetails()
{
  showDetails.value = false
  selectedLotId.value = null
}


function openMetadataEdit(
  lot,
)
{
  if (
    !props.canManage
    ||
    metadataSubmitting.value
    ||
    !lot?.id
  ) {
    return
  }

  editingLot.value = {
    ...lot,
  }

  metadataServerErrors.value = {}
  metadataErrorMessage.value = ''
  showMetadataEdit.value = true
}


function closeMetadataEdit()
{
  if (
    metadataSubmitting.value
  ) {
    return
  }

  showMetadataEdit.value = false
  editingLot.value = null
  metadataServerErrors.value = {}
  metadataErrorMessage.value = ''
}


async function saveMetadata(
  payload,
)
{
  if (
    !props.canManage
    ||
    metadataSubmitting.value
    ||
    !payload?.lot_id
  ) {
    return
  }

  metadataSubmitting.value = true
  metadataServerErrors.value = {}
  metadataErrorMessage.value = ''

  try {
    await warehouseLotService
      .updateMetadata(
        payload.lot_id,
        {
          supplier_id:
            payload.supplier_id,

          supplier_batch_no:
            payload.supplier_batch_no,

          manufactured_at:
            payload.manufactured_at,

          expiry_date:
            payload.expiry_date,

          manual_notes:
            payload.manual_notes,

          change_reason:
            payload.change_reason,
        },
      )

    showMetadataEdit.value = false
    editingLot.value = null

    await loadLots()
  } catch (error) {
    metadataServerErrors.value =
      warehouseLotService
        .getValidationErrors(
          error,
        )

    metadataErrorMessage.value =
      warehouseLotService
        .getErrorMessage(
          error,
          'Unable to update warehouse lot information.',
        )
  } finally {
    metadataSubmitting.value = false
  }
}


function canReturn(
  lot,
)
{
  return Boolean(
    props.canManage
    &&
    (
      lot?.return_eligible
      ??
      (
        Number(
          lot?.supplier_id,
        ) > 0
        &&
        Number(
          lot?.remaining_quantity,
        ) > 0
      )
    )
  )
}


function returnTitle(
  lot,
)
{
  if (
    canReturn(lot)
  ) {
    return 'Return this exact lot to supplier'
  }

  return (
    lot?.return_eligibility_message
    ||
    (
      Number(
        lot?.supplier_id,
      ) <= 0
        ? 'Assign a supplier before Purchase Return.'
        : 'No remaining quantity is available.'
    )
  )
}


function openPurchaseReturn(
  lot,
)
{
  if (
    !canReturn(lot)
  ) {
    return
  }

  returnLot.value = {
    ...lot,
  }

  showPurchaseReturn.value = true
}


function closePurchaseReturn()
{
  showPurchaseReturn.value = false
  returnLot.value = null
}


function handlePurchaseReturnCreated(
  purchaseReturn,
)
{
  showPurchaseReturn.value = false
  returnLot.value = null

  emit(
    'purchase-return-created',
    purchaseReturn,
  )
}


function daysLabel(
  lot,
)
{
  if (
    lot?.days_until_expiry === null
    ||
    lot?.days_until_expiry === undefined
  ) {
    return 'No expiry tracked'
  }

  const days =
    Number(
      lot.days_until_expiry,
    )

  if (
    !Number.isFinite(days)
  ) {
    return 'No expiry tracked'
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

  return `${days} day(s) left`
}


watch(
  () => props.refreshKey,
  (next, previous) => {
    if (
      next !== previous
    ) {
      void loadLots()
    }
  },
)


onMounted(() => {
  void loadLots()
})
</script>
