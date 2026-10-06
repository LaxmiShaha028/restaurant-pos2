<template>
  <Teleport to="body">
    <Transition name="inventory-modal">
      <div
        v-if="show"
        class="warehouse-lot-modal-backdrop"
        @click.self="requestClose"
      >
        <section
          class="warehouse-lot-modal purchase-return-details-modal"
          role="dialog"
          aria-modal="true"
          aria-labelledby="purchase-return-details-title"
        >
          <header class="warehouse-lot-modal-header">
            <div>
              <span class="warehouse-lot-modal-eyebrow">
                Purchase Return History
              </span>

              <h2 id="purchase-return-details-title">
                {{ purchaseReturn?.return_no || 'Purchase Return' }}
              </h2>

              <p>
                Physical return, lot snapshots and supplier settlement history.
              </p>
            </div>

            <div class="purchase-return-details-header-actions">
              <button
                type="button"
                class="purchase-return-pdf-button"
                :disabled="pdfLoading || !purchaseReturn?.id"
                @click="openPdf"
              >
                <span
                  v-if="pdfLoading"
                  class="spinner-border spinner-border-sm"
                ></span>

                <i
                  v-else
                  class="bi bi-file-earmark-pdf"
                ></i>

                PDF / Print
              </button>

              <button
                type="button"
                class="warehouse-lot-icon-button"
                :disabled="pdfLoading"
                @click="requestClose"
              >
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </header>


          <div class="warehouse-lot-modal-body">
            <div
              v-if="pdfError"
              class="warehouse-lot-alert warehouse-lot-alert-danger"
            >
              {{ pdfError }}
            </div>


            <section
              v-if="purchaseReturn"
              class="purchase-return-details-summary"
            >
              <article>
                <span>Supplier</span>
                <strong>{{ purchaseReturn.supplier_name }}</strong>
              </article>

              <article>
                <span>Returned At</span>
                <strong>{{ purchaseReturn.returned_at_label }}</strong>
              </article>

              <article>
                <span>Return Credit</span>
                <strong>{{ purchaseReturn.settlement_target_amount_formatted }}</strong>
              </article>

              <article>
                <span>Settlement</span>
                <strong>{{ purchaseReturn.settlement_status_label }}</strong>
              </article>
            </section>


            <section class="warehouse-lot-detail-card">
              <div class="warehouse-lot-card-heading">
                <div>
                  <h3>Returned Items</h3>
                  <p>
                    Immutable snapshots of the exact warehouse lots returned.
                  </p>
                </div>

                <span>
                  {{ items.length }} item(s)
                </span>
              </div>


              <div class="table-responsive">
                <table class="purchase-return-details-table">
                  <thead>
                    <tr>
                      <th>Item</th>
                      <th>Lot / Batch</th>
                      <th>Expiry</th>
                      <th>Qty</th>
                      <th>Reason</th>
                      <th>Return Cost</th>
                      <th>Credit</th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr
                      v-for="item in items"
                      :key="item.id"
                    >
                      <td>
                        <strong>{{ item.item_name }}</strong>
                      </td>

                      <td>
                        <strong>{{ item.lot_no }}</strong>
                        <small>{{ item.supplier_batch_no || 'No supplier batch' }}</small>
                      </td>

                      <td>{{ item.expiry_date_label || 'Unknown' }}</td>

                      <td>{{ item.quantity_formatted }}</td>

                      <td>{{ item.reason_label }}</td>

                      <td>{{ item.return_unit_cost_formatted }}</td>

                      <td>
                        <strong>
                          {{ item.expected_supplier_credit_formatted }}
                        </strong>
                      </td>
                    </tr>

                    <tr v-if="items.length === 0">
                      <td
                        colspan="7"
                        class="text-center"
                      >
                        No returned items found.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </section>


            <section class="warehouse-lot-detail-card">
              <div class="warehouse-lot-card-heading">
                <div>
                  <h3>Accounting Snapshot</h3>
                  <p>
                    Historical physical-return valuation captured at posting time.
                  </p>
                </div>
              </div>

              <div class="purchase-return-details-accounting-grid">
                <div>
                  <span>Supplier Credit Target</span>
                  <strong>{{ purchaseReturn?.settlement_target_amount_formatted }}</strong>
                </div>

                <div>
                  <span>Inventory Value Reduction</span>
                  <strong>{{ purchaseReturn?.inventory_value_reduction_formatted }}</strong>
                </div>

                <div>
                  <span>Valuation Variance</span>
                  <strong>{{ purchaseReturn?.valuation_variance_formatted }}</strong>
                </div>

                <div>
                  <span>Unallocated</span>
                  <strong>{{ purchaseReturn?.unallocated_amount_formatted }}</strong>
                </div>
              </div>
            </section>


            <section class="warehouse-lot-detail-card">
              <div class="warehouse-lot-card-heading">
                <div>
                  <h3>Supplier Settlement Ledger</h3>
                  <p>
                    Immutable payable / credit / receivable / refund events.
                  </p>
                </div>

                <span>
                  {{ settlements.length }} allocation(s)
                </span>
              </div>


              <div
                v-if="settlements.length === 0"
                class="warehouse-lot-empty-state"
              >
                <i class="bi bi-wallet2"></i>
                <strong>No settlement recorded yet</strong>
              </div>


              <div
                v-else
                class="purchase-return-details-settlement-list"
              >
                <article
                  v-for="settlement in settlements"
                  :key="settlement.id"
                >
                  <div>
                    <strong>{{ settlement.settlement_type_label }}</strong>

                    <small>
                      {{ settlement.settlement_no }}
                      ·
                      {{ settlement.settled_at_label }}
                    </small>
                  </div>

                  <div>
                    <strong>{{ settlement.amount_formatted }}</strong>

                    <small>
                      {{
                        settlement.cash_effect
                          ? 'Cash effect'
                          : 'No cash effect'
                      }}
                    </small>
                  </div>

                  <div
                    v-if="
                      normalizedChildren(settlement).length
                      ||
                      normalizedCreditApplications(settlement).length
                    "
                    class="purchase-return-details-child-ledger"
                  >
                    <div
                      v-for="child in normalizedChildren(settlement)"
                      :key="`child-${child.id}`"
                    >
                      <span>
                        {{ child.settlement_type_label }}
                        ·
                        {{ child.settled_at_label }}
                      </span>

                      <strong>{{ child.amount_formatted }}</strong>
                    </div>

                    <div
                      v-for="application in normalizedCreditApplications(settlement)"
                      :key="`credit-${application.id}`"
                    >
                      <span>
                        Supplier Credit Applied
                        ·
                        PO #{{ application.purchase_order_id }}
                        ·
                        {{ application.applied_at_label }}
                      </span>

                      <strong>{{ application.amount_formatted }}</strong>
                    </div>
                  </div>
                </article>
              </div>
            </section>


            <section
              v-if="purchaseReturn?.notes"
              class="warehouse-lot-detail-card"
            >
              <h3>Return Notes</h3>
              <p class="warehouse-lot-detail-notes">
                {{ purchaseReturn.notes }}
              </p>
            </section>
          </div>


          <footer class="warehouse-lot-modal-footer">
            <button
              type="button"
              class="warehouse-lot-secondary-button"
              :disabled="pdfLoading"
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
} from 'vue'

import inventoryDocumentService
  from '@/services/inventoryDocumentService'


const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },

  purchaseReturn: {
    type: Object,
    default: null,
  },
})


const emit = defineEmits([
  'close',
])


const pdfLoading =
  ref(false)

const pdfError =
  ref('')


const items =
  computed(() => {
    const value =
      props.purchaseReturn?.items?.data
      ??
      props.purchaseReturn?.items
      ??
      []

    return Array.isArray(value)
      ? value
      : []
  })


const settlements =
  computed(() => {
    const value =
      props.purchaseReturn?.settlements?.data
      ??
      props.purchaseReturn?.settlements
      ??
      []

    return Array.isArray(value)
      ? value
      : []
  })


async function openPdf()
{
  if (
    pdfLoading.value
    ||
    !props.purchaseReturn?.id
  ) {
    return
  }

  pdfLoading.value = true
  pdfError.value = ''

  try {
    await inventoryDocumentService
      .openPurchaseReturnPdf(
        props.purchaseReturn.id,
        props.purchaseReturn.return_no,
      )
  } catch (error) {
    pdfError.value =
      inventoryDocumentService
        .getErrorMessage(
          error,
          'Unable to open Purchase Return PDF.',
        )
  } finally {
    pdfLoading.value = false
  }
}


function requestClose()
{
  if (
    pdfLoading.value
  ) {
    return
  }

  emit('close')
}


function normalizedChildren(
  settlement,
)
{
  const value =
    settlement?.children?.data
    ??
    settlement?.children
    ??
    []

  return Array.isArray(value)
    ? value
    : []
}


function normalizedCreditApplications(
  settlement,
)
{
  const value =
    settlement?.credit_applications?.data
    ??
    settlement?.credit_applications
    ??
    []

  return Array.isArray(value)
    ? value
    : []
}
</script>
