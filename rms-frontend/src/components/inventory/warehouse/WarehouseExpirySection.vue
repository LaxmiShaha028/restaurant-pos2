<template>
  <section
    class="warehouse-expiry-section"
    :aria-busy="loading"
  >
    <div class="warehouse-lot-section-heading">
      <div>
        <h3>Expiring Soon</h3>

        <p>
          Prioritize supplier return or consumption before stock becomes a loss.
        </p>
      </div>

      <span
        class="warehouse-lot-result-pill warehouse-lot-risk-pill"
      >
        {{ summary.expiry_watch_count }} lot(s) need attention
      </span>
    </div>


    <div class="warehouse-expiry-summary">
      <article class="warehouse-expiry-summary-expired">
        <span>Expired</span>
        <strong>{{ summary.expired }}</strong>
        <small>{{ summary.expired_value_formatted }}</small>
      </article>

      <article class="warehouse-expiry-summary-critical">
        <span>Critical ≤ {{ summary.critical_days }} days</span>
        <strong>{{ summary.critical }}</strong>
        <small>Immediate action</small>
      </article>

      <article class="warehouse-expiry-summary-soon">
        <span>Within {{ summary.expiring_soon_days }} days</span>
        <strong>{{ summary.expiring_soon }}</strong>
        <small>Plan return / use</small>
      </article>

      <article>
        <span>Total Value at Risk</span>
        <strong>{{ summary.risk_value_formatted }}</strong>
        <small>Original lot cost basis</small>
      </article>
    </div>


    <div class="warehouse-lot-filter-card">
      <div class="warehouse-expiry-filter-row">
        <div class="warehouse-lot-field warehouse-lot-search-field">
          <label for="warehouse-expiry-search">
            Search at-risk stock
          </label>

          <div class="warehouse-lot-search-input">
            <i class="bi bi-search"></i>

            <input
              id="warehouse-expiry-search"
              v-model.trim="search"
              type="search"
              placeholder="Material, supplier, lot or batch"
              :disabled="loading"
              @keyup.enter="applySearch"
            />
          </div>
        </div>

        <button
          type="button"
          class="warehouse-lot-primary-button"
          :disabled="loading"
          @click="applySearch"
        >
          Search
        </button>

        <button
          v-if="search"
          type="button"
          class="warehouse-lot-secondary-button"
          :disabled="loading"
          @click="clearSearch"
        >
          Clear
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
      Loading expiry watch...
    </div>


    <div
      v-else-if="!loading && lots.length === 0"
      class="warehouse-lot-empty-state warehouse-expiry-empty"
    >
      <i class="bi bi-shield-check"></i>

      <strong>No expiry risk found</strong>

      <p>
        No remaining dated lot is expired or within the configured
        {{ summary.expiring_soon_days }}-day watch window.
      </p>
    </div>


    <div
      v-else
      class="warehouse-expiry-list"
    >
      <article
        v-for="lot in lots"
        :key="lot.id"
        class="warehouse-expiry-card"
        :class="
          `warehouse-expiry-card-${lot.status || 'unknown'}`
        "
      >
        <div class="warehouse-expiry-card-main">
          <div class="warehouse-expiry-card-title">
            <span
              class="warehouse-lot-status"
              :class="
                `warehouse-lot-status-${lot.status || 'unknown'}`
              "
            >
              {{ lot.status_label }}
            </span>

            <h4>
              {{ lot.material_name }}
            </h4>

            <p>
              {{ lot.lot_no }}
              <template v-if="lot.supplier_batch_no">
                · Batch {{ lot.supplier_batch_no }}
              </template>
            </p>
          </div>


          <div class="warehouse-expiry-days">
            <strong>
              {{ urgencyNumber(lot) }}
            </strong>

            <span>
              {{ urgencyLabel(lot) }}
            </span>
          </div>
        </div>


        <div class="warehouse-expiry-card-grid">
          <div>
            <span>Remaining</span>
            <strong>{{ lot.quantity_formatted }}</strong>
          </div>

          <div>
            <span>Expiry</span>
            <strong>{{ lot.expiry_date_label }}</strong>
          </div>

          <div>
            <span>Supplier</span>
            <strong>{{ lot.supplier_name || 'Unknown' }}</strong>
          </div>

          <div>
            <span>Value at Risk</span>
            <strong>{{ lot.remaining_value_formatted }}</strong>
          </div>
        </div>


        <div class="warehouse-expiry-card-footer">
          <div>
            <span>
              {{ lot.purchase_order?.label || 'Original PO unknown' }}
            </span>

            <span>
              {{ lot.purchase_order_receipt?.label || 'GRN unknown' }}
            </span>
          </div>

          <div class="warehouse-lot-row-actions">
            <button
              type="button"
              class="warehouse-lot-view-button"
              @click="openDetails(lot)"
            >
              <i class="bi bi-eye"></i>
              View Lot
            </button>

            <button
              v-if="canManage"
              type="button"
              class="warehouse-lot-edit-button"
              @click="openMetadataEdit(lot)"
            >
              <i class="bi bi-pencil-square"></i>
              Edit Expiry
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
              Return to Supplier
            </button>
          </div>
        </div>
      </article>
    </div>


    <div
      v-if="meta.last_page > 1"
      class="warehouse-lot-pagination warehouse-expiry-pagination"
    >
      <span>
        {{ meta.total }} at-risk lot(s)
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


    <div class="warehouse-expiry-next-step">
      <i class="bi bi-arrow-return-left"></i>

      <div>
        <strong>Purchase Return is active</strong>

        <p>
          Supplier-linked lots with remaining warehouse quantity can now be
          returned directly. Supplier financial settlement remains separate
          and will be posted in the next accounting phase.
        </p>
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

const search =
  ref('')

const meta =
  reactive(
    warehouseLotService
      .createDefaultMeta(),
  )

const summary =
  reactive(
    warehouseLotService
      .createDefaultSummary(),
  )

const page =
  ref(1)

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


async function loadExpiryWatch()
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
    const [
      listResponse,
      summaryResponse,
    ] =
      await Promise.all([
        warehouseLotService
          .getLots({
            scope:
              'expiry_watch',

            search:
              search.value
              || undefined,

            sort_by:
              'expiry_date',

            sort_direction:
              'asc',

            page:
              page.value,

            per_page:
              20,
          }),

        warehouseLotService
          .getSummary(),
      ])

    lots.value =
      Array.isArray(
        listResponse?.data,
      )
        ? listResponse.data
        : []

    Object.assign(
      meta,
      warehouseLotService
        .createDefaultMeta(),
      listResponse?.meta
      || {},
    )

    page.value =
      Number(meta.current_page)
      || 1

    Object.assign(
      summary,
      warehouseLotService
        .createDefaultSummary(),
      summaryResponse?.data
      || {},
    )

    emit(
      'summary-changed',
      {
        ...summary,
      },
    )
  } catch (error) {
    lots.value = []

    errorMessage.value =
      warehouseLotService
        .getErrorMessage(
          error,
          'Unable to load expiring warehouse lots.',
        )
  } finally {
    loading.value = false

    if (
      reloadRequested
    ) {
      reloadRequested = false
      void loadExpiryWatch()
    }
  }
}


async function applySearch()
{
  page.value = 1
  await loadExpiryWatch()
}


async function clearSearch()
{
  search.value = ''
  page.value = 1
  await loadExpiryWatch()
}


async function changePage(
  nextPage,
)
{
  const target =
    Number(nextPage)

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

  page.value = target
  await loadExpiryWatch()
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

    await loadExpiryWatch()
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


function urgencyNumber(
  lot,
)
{
  const days =
    Number(
      lot?.days_until_expiry,
    )

  if (
    !Number.isFinite(days)
  ) {
    return '—'
  }

  if (
    days < 0
  ) {
    return Math.abs(days)
  }

  return days
}


function urgencyLabel(
  lot,
)
{
  const days =
    Number(
      lot?.days_until_expiry,
    )

  if (
    !Number.isFinite(days)
  ) {
    return 'days'
  }

  if (
    days < 0
  ) {
    return 'days expired'
  }

  if (
    days === 0
  ) {
    return 'expires today'
  }

  return 'days left'
}


watch(
  () => props.refreshKey,
  (next, previous) => {
    if (
      next !== previous
    ) {
      void loadExpiryWatch()
    }
  },
)


onMounted(() => {
  void loadExpiryWatch()
})
</script>
