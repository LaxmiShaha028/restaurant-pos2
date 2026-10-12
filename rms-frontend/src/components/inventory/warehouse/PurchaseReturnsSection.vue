<template>
  <section
    class="purchase-returns-section"
    :aria-busy="loading"
  >
    <div class="warehouse-lot-section-heading">
      <div>
        <h3>Purchase Returns</h3>
        <p>
          Physical return history and supplier settlement work queue.
        </p>
      </div>

      <span class="warehouse-lot-result-pill">
        {{ meta.total }} return(s)
      </span>
    </div>


    <div class="warehouse-lot-filter-card">
      <div class="warehouse-expiry-filter-row">
        <div class="warehouse-lot-field warehouse-lot-search-field">
          <label for="purchase-return-search">
            Search
          </label>

          <div class="warehouse-lot-search-input">
            <i class="bi bi-search"></i>

            <input
              id="purchase-return-search"
              v-model.trim="search"
              type="search"
              placeholder="Return no, supplier, item, lot or batch"
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
          Apply
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
      {{ errorMessage }}
    </div>


    <div
      v-if="loading && returns.length === 0"
      class="warehouse-lot-loading-state"
    >
      <span class="spinner-border spinner-border-sm"></span>
      Loading Purchase Returns...
    </div>


    <div
      v-else-if="!loading && returns.length === 0"
      class="warehouse-lot-empty-state"
    >
      <i class="bi bi-arrow-return-left"></i>

      <strong>No Purchase Returns found</strong>

      <p>
        Return an eligible supplier-linked warehouse lot to create the first record.
      </p>
    </div>


    <div
      v-else
      class="purchase-return-list"
    >
      <article
        v-for="purchaseReturn in returns"
        :key="purchaseReturn.id"
        class="purchase-return-row-card"
      >
        <header>
          <div>
            <span
              class="purchase-return-status"
              :class="
                `purchase-return-status-${purchaseReturn.settlement_status}`
              "
            >
              {{ purchaseReturn.settlement_status_label }}
            </span>

            <h4>{{ purchaseReturn.return_no }}</h4>

            <p>
              {{ purchaseReturn.supplier_name }}
              ·
              {{ purchaseReturn.returned_at_label }}
            </p>
          </div>

          <div class="purchase-return-row-credit">
            <span>Supplier Credit Value</span>
            <strong>{{ purchaseReturn.settlement_target_amount_formatted }}</strong>
          </div>
        </header>


        <div class="purchase-return-row-grid">
          <div>
            <span>Allocated</span>
            <strong>{{ purchaseReturn.allocated_amount_formatted }}</strong>
          </div>

          <div>
            <span>Unallocated</span>
            <strong>{{ purchaseReturn.unallocated_amount_formatted }}</strong>
          </div>

          <div>
            <span>Supplier Credit</span>
            <strong>{{ purchaseReturn.supplier_credit_balance_formatted }}</strong>
          </div>

          <div>
            <span>Refund Receivable</span>
            <strong>{{ purchaseReturn.refund_receivable_balance_formatted }}</strong>
          </div>

          <div>
            <span>Refund Received</span>
            <strong>{{ purchaseReturn.cash_refund_total_formatted }}</strong>
          </div>
        </div>


        <footer>
          <div class="purchase-return-row-items">
            <span
              v-for="item in normalizedItems(purchaseReturn)"
              :key="item.id"
            >
              {{ item.item_name }}
              ·
              {{ item.quantity_formatted }}
              ·
              {{ item.lot_no }}
            </span>
          </div>

          <div class="purchase-return-row-actions">
            <button
              type="button"
              class="purchase-return-details-button"
              @click="openDetails(purchaseReturn)"
            >
              <i class="bi bi-eye"></i>
              Details
            </button>

            <button
              type="button"
              class="purchase-return-pdf-button"
              :disabled="
                pdfLoadingId ===
                purchaseReturn.id
              "
              @click="openPdf(purchaseReturn)"
            >
              <span
                v-if="
                  pdfLoadingId ===
                  purchaseReturn.id
                "
                class="spinner-border spinner-border-sm"
              ></span>

              <i
                v-else
                class="bi bi-file-earmark-pdf"
              ></i>

              PDF
            </button>

            <button
              v-if="canManage"
              type="button"
              class="purchase-return-settle-button"
              @click="openSettlement(purchaseReturn)"
            >
              <i class="bi bi-wallet2"></i>

              {{
                Number(
                  purchaseReturn.unallocated_amount || 0,
                ) > 0
                  ? 'Settle'
                  : Number(
                      purchaseReturn.refund_receivable_balance || 0,
                    ) > 0
                      ? 'Receive Refund'
                      : 'View Settlement'
              }}
            </button>
          </div>
        </footer>
      </article>
    </div>


    <div
      v-if="meta.last_page > 1"
      class="warehouse-lot-pagination"
    >
      <span>
        Showing {{ meta.from || 0 }}-{{ meta.to || 0 }} of {{ meta.total }}
      </span>

      <div>
        <button
          type="button"
          :disabled="loading || meta.current_page <= 1"
          @click="changePage(meta.current_page - 1)"
        >
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
        </button>
      </div>
    </div>


    <PurchaseReturnSettlementModal
      :show="showSettlement"
      :purchase-return="selectedReturn"
      @close="closeSettlement"
      @changed="handleSettlementChanged"
    />

    <PurchaseReturnDetailsModal
      :show="showDetails"
      :purchase-return="detailsReturn"
      @close="closeDetails"
    />
  </section>
</template>


<script setup>
import {
  reactive,
  ref,
  watch,
} from 'vue'

import purchaseReturnService
  from '@/services/purchaseReturnService'

import PurchaseReturnSettlementModal
  from '@/components/inventory/warehouse/PurchaseReturnSettlementModal.vue'

import PurchaseReturnDetailsModal
  from '@/components/inventory/warehouse/PurchaseReturnDetailsModal.vue'

import inventoryDocumentService
  from '@/services/inventoryDocumentService'


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
  'changed',
])


const returns =
  ref([])

const loading =
  ref(false)

const errorMessage =
  ref('')

const search =
  ref('')

const page =
  ref(1)

const meta =
  reactive({
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0,
    from: null,
    to: null,
  })

const showSettlement =
  ref(false)

const selectedReturn =
  ref(null)

const showDetails =
  ref(false)

const detailsReturn =
  ref(null)

const pdfLoadingId =
  ref(null)


async function loadReturns()
{
  loading.value = true
  errorMessage.value = ''

  try {
    const response =
      await purchaseReturnService
        .getPurchaseReturns({
          search:
            search.value
            || undefined,

          page:
            page.value,

          per_page:
            20,
        })

    returns.value =
      Array.isArray(response?.data)
        ? response.data
        : []

    Object.assign(
      meta,
      response?.meta
      || {},
    )

    page.value =
      Number(meta.current_page)
      || 1
  } catch (error) {
    returns.value = []

    errorMessage.value =
      purchaseReturnService
        .getErrorMessage(
          error,
          'Unable to load Purchase Return history.',
        )
  } finally {
    loading.value = false
  }
}


async function applySearch()
{
  page.value = 1
  await loadReturns()
}


async function clearSearch()
{
  search.value = ''
  page.value = 1
  await loadReturns()
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
  ) {
    return
  }

  page.value = target
  await loadReturns()
}


async function openDetails(
  purchaseReturn,
)
{
  if (
    !purchaseReturn?.id
  ) {
    return
  }

  try {
    const response =
      await purchaseReturnService
        .getPurchaseReturn(
          purchaseReturn.id,
        )

    detailsReturn.value =
      response?.data
      || purchaseReturn
  } catch {
    detailsReturn.value =
      purchaseReturn
  }

  showDetails.value = true
}


function closeDetails()
{
  showDetails.value = false
  detailsReturn.value = null
}


async function openPdf(
  purchaseReturn,
)
{
  if (
    !purchaseReturn?.id
    ||
    pdfLoadingId.value
  ) {
    return
  }

  pdfLoadingId.value =
    purchaseReturn.id

  errorMessage.value = ''

  try {
    await inventoryDocumentService
      .openPurchaseReturnPdf(
        purchaseReturn.id,
        purchaseReturn.return_no,
      )
  } catch (error) {
    errorMessage.value =
      inventoryDocumentService
        .getErrorMessage(
          error,
          'Unable to open Purchase Return PDF.',
        )
  } finally {
    pdfLoadingId.value = null
  }
}


async function openSettlement(
  purchaseReturn,
)
{
  if (
    !purchaseReturn?.id
  ) {
    return
  }

  try {
    const response =
      await purchaseReturnService
        .getPurchaseReturn(
          purchaseReturn.id,
        )

    selectedReturn.value =
      response?.data
      || purchaseReturn
  } catch {
    selectedReturn.value =
      purchaseReturn
  }

  showSettlement.value = true
}


function closeSettlement()
{
  showSettlement.value = false
  selectedReturn.value = null
}


async function handleSettlementChanged()
{
  if (
    selectedReturn.value?.id
  ) {
    try {
      const response =
        await purchaseReturnService
          .getPurchaseReturn(
            selectedReturn.value.id,
          )

      selectedReturn.value =
        response?.data
        || selectedReturn.value
    } catch {
      // Main list refresh below remains authoritative.
    }
  }

  await loadReturns()

  emit('changed')
}


function normalizedItems(
  purchaseReturn,
)
{
  const items =
    purchaseReturn?.items?.data
    ??
    purchaseReturn?.items
    ??
    []

  return Array.isArray(items)
    ? items
    : []
}


watch(
  () => props.refreshKey,
  (next, previous) => {
    if (
      next !== previous
    ) {
      void loadReturns()
    }
  },
  {
    immediate: true,
  },
)
</script>
