<template>
  <section
    class="restaurant-wastage-history"
    :aria-busy="loading"
  >
    <!-- Header -->
    <div class="restaurant-wastage-history-header">
      <div>
        <h3>
          Damage / Wastage History
        </h3>

        <p>
          Immutable restaurant inventory-loss records with historical
          quantity and cost snapshots.
        </p>
      </div>

      <button
        type="button"
        class="restaurant-wastage-history-refresh"
        :disabled="loading"
        @click="refresh"
      >
        <i
          class="bi bi-arrow-clockwise"
          :class="{
            'inventory-refresh-spin':
              loading,
          }"
          aria-hidden="true"
        ></i>

        {{
          loading
            ? 'Refreshing...'
            : 'Refresh'
        }}
      </button>
    </div>


    <!-- Error -->
    <div
      v-if="errorMessage"
      class="restaurant-wastage-history-error"
      role="alert"
    >
      <i
        class="bi bi-exclamation-triangle"
        aria-hidden="true"
      ></i>

      <span>
        {{ errorMessage }}
      </span>

      <button
        type="button"
        :disabled="loading"
        @click="loadWastages"
      >
        Try Again
      </button>
    </div>


    <!-- Filters -->
    <div class="restaurant-wastage-history-filters">
      <div class="restaurant-wastage-history-filter-grid">
        <div class="restaurant-wastage-history-filter-group">
          <label for="restaurant-wastage-history-search">
            Search
          </label>

          <input
            id="restaurant-wastage-history-search"
            v-model.trim="filters.search"
            type="text"
            maxlength="180"
            autocomplete="off"
            placeholder="Wastage no, material or notes"
            :disabled="loading"
            @keyup.enter="applyFilters"
          />
        </div>


        <div class="restaurant-wastage-history-filter-group">
          <label for="restaurant-wastage-history-reason">
            Reason
          </label>

          <select
            id="restaurant-wastage-history-reason"
            v-model="filters.reason"
            :disabled="loading"
          >
            <option value="">
              All Reasons
            </option>

            <option
              v-for="option in reasonOptions"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
        </div>


        <div class="restaurant-wastage-history-filter-group">
          <label for="restaurant-wastage-history-date-from">
            From Date
          </label>

          <input
            id="restaurant-wastage-history-date-from"
            v-model="filters.date_from"
            type="date"
            :max="
              filters.date_to
              ||
              undefined
            "
            :disabled="loading"
            @change="clearValidationError"
          />
        </div>


        <div class="restaurant-wastage-history-filter-group">
          <label for="restaurant-wastage-history-date-to">
            To Date
          </label>

          <input
            id="restaurant-wastage-history-date-to"
            v-model="filters.date_to"
            type="date"
            :min="
              filters.date_from
              ||
              undefined
            "
            :disabled="loading"
            @change="clearValidationError"
          />
        </div>


        <div class="restaurant-wastage-history-filter-group">
          <label for="restaurant-wastage-history-per-page">
            Rows Per Page
          </label>

          <select
            id="restaurant-wastage-history-per-page"
            v-model.number="filters.per_page"
            :disabled="loading"
            @change="applyFilters"
          >
            <option :value="10">
              10 rows
            </option>

            <option :value="20">
              20 rows
            </option>

            <option :value="50">
              50 rows
            </option>

            <option :value="100">
              100 rows
            </option>
          </select>
        </div>
      </div>


      <div
        v-if="validationError"
        class="restaurant-wastage-history-validation"
        role="alert"
      >
        {{ validationError }}
      </div>


      <div class="restaurant-wastage-history-filter-actions">
        <button
          type="button"
          class="restaurant-wastage-history-clear"
          :disabled="loading"
          @click="clearFilters"
        >
          <i
            class="bi bi-arrow-counterclockwise"
            aria-hidden="true"
          ></i>

          Clear
        </button>

        <button
          type="button"
          class="restaurant-wastage-history-apply"
          :disabled="loading"
          @click="applyFilters"
        >
          <i
            class="bi bi-funnel"
            aria-hidden="true"
          ></i>

          Apply Filters
        </button>
      </div>
    </div>


    <!-- Summary -->
    <div class="restaurant-wastage-history-summary">
      <article>
        <span>
          Total Records
        </span>

        <strong>
          {{ meta.total }}
        </strong>
      </article>

      <article>
        <span>
          Records on Page
        </span>

        <strong>
          {{ wastages.length }}
        </strong>
      </article>

      <article class="restaurant-wastage-history-loss-summary">
        <span>
          Loss on Page
        </span>

        <strong>
          {{ moneyText(pageLoss) }}
        </strong>
      </article>

      <article>
        <span>
          Cash Effect
        </span>

        <strong>
          ৳ 0.00
        </strong>
      </article>
    </div>


    <!-- Loading -->
    <div
      v-if="
        loading
        &&
        wastages.length === 0
      "
      class="restaurant-wastage-history-state"
      role="status"
      aria-live="polite"
    >
      <span
        class="spinner-border"
        aria-hidden="true"
      ></span>

      <strong>
        Loading damage / wastage history...
      </strong>
    </div>


    <!-- Empty -->
    <div
      v-else-if="
        !loading
        &&
        wastages.length === 0
      "
      class="restaurant-wastage-history-state"
      role="status"
    >
      <i
        class="bi bi-trash3"
        aria-hidden="true"
      ></i>

      <strong>
        No damage / wastage history found
      </strong>

      <p>
        Recorded restaurant inventory losses will appear here.
      </p>
    </div>


    <!-- History Cards -->
    <template v-else>
      <div class="restaurant-wastage-history-list">
        <article
          v-for="(
            wastage,
            index
          ) in wastages"
          :key="
            wastageKey(
              wastage,
              index,
            )
          "
          class="restaurant-wastage-history-card"
        >
          <header class="restaurant-wastage-history-card-header">
            <div>
              <span>
                Wastage Record
              </span>

              <strong>
                {{ wastageNumber(wastage) }}
              </strong>
            </div>

            <div class="restaurant-wastage-history-card-date">
              <i
                class="bi bi-calendar3"
                aria-hidden="true"
              ></i>

              {{ wastageDate(wastage) }}
            </div>
          </header>


          <div class="restaurant-wastage-history-card-body">
            <div class="restaurant-wastage-history-metrics">
              <div>
                <span>
                  Material
                </span>

                <strong>
                  {{ mainItemName(wastage) }}
                </strong>
              </div>

              <div>
                <span>
                  Quantity
                </span>

                <strong>
                  {{ mainItemQuantity(wastage) }}
                </strong>
              </div>

              <div>
                <span>
                  Reason
                </span>

                <strong>
                  {{ mainItemReason(wastage) }}
                </strong>
              </div>

              <div class="restaurant-wastage-history-loss">
                <span>
                  Inventory Loss
                </span>

                <strong>
                  {{ totalLossText(wastage) }}
                </strong>
              </div>

              <div>
                <span>
                  Recorded By
                </span>

                <strong>
                  {{ recordedByName(wastage) }}
                </strong>
              </div>
            </div>


            <div
              v-if="wastage?.notes"
              class="restaurant-wastage-history-notes"
            >
              <i
                class="bi bi-chat-left-text"
                aria-hidden="true"
              ></i>

              <span>
                {{ wastage.notes }}
              </span>
            </div>


            <button
              type="button"
              class="restaurant-wastage-history-details-toggle"
              :aria-expanded="isExpanded(wastage)"
              @click="toggleExpanded(wastage)"
            >
              <i
                class="bi"
                :class="
                  isExpanded(wastage)
                    ? 'bi-chevron-up'
                    : 'bi-chevron-down'
                "
                aria-hidden="true"
              ></i>

              {{
                isExpanded(wastage)
                  ? 'Hide Details'
                  : 'View Details'
              }}
            </button>


            <div
              v-if="isExpanded(wastage)"
              class="restaurant-wastage-history-details"
            >
              <div class="restaurant-wastage-history-details-heading">
                <div>
                  <h4>
                    Inventory Loss Snapshot
                  </h4>

                  <p>
                    Historical record. It cannot be edited or deleted.
                  </p>
                </div>

                <span class="restaurant-wastage-history-immutable">
                  <i
                    class="bi bi-lock"
                    aria-hidden="true"
                  ></i>

                  Immutable
                </span>
              </div>


              <div class="restaurant-wastage-history-table-scroll">
                <table class="restaurant-wastage-history-table">
                  <thead>
                    <tr>
                      <th>
                        Material
                      </th>

                      <th>
                        Reason
                      </th>

                      <th>
                        Quantity
                      </th>

                      <th>
                        Unit Cost
                      </th>

                      <th>
                        Before
                      </th>

                      <th>
                        After
                      </th>

                      <th>
                        Loss
                      </th>

                      <th>
                        Notes
                      </th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr
                      v-for="(
                        item,
                        itemIndex
                      ) in wastageItems(wastage)"
                      :key="
                        wastageItemKey(
                          wastage,
                          item,
                          itemIndex,
                        )
                      "
                    >
                      <td>
                        <strong>
                          {{ itemName(item) }}
                        </strong>
                      </td>

                      <td>
                        {{ itemReason(item) }}
                      </td>

                      <td>
                        {{ itemQuantity(item) }}
                      </td>

                      <td>
                        {{ itemUnitCost(item) }}
                      </td>

                      <td>
                        {{
                          quantityOnly(
                            item?.restaurant_quantity_before,
                          )
                        }}
                      </td>

                      <td>
                        {{
                          quantityOnly(
                            item?.restaurant_quantity_after,
                          )
                        }}
                      </td>

                      <td>
                        {{ itemLoss(item) }}
                      </td>

                      <td>
                        {{ item?.notes || '—' }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>


              <div class="restaurant-wastage-history-accounting-note">
                <i
                  class="bi bi-calculator"
                  aria-hidden="true"
                ></i>

                <div>
                  <strong>
                    Accounting classification:
                    Inventory Wastage Loss
                  </strong>

                  <span>
                    Inventory value decreases, but no cash movement is
                    created at the moment of wastage.
                  </span>
                </div>
              </div>
            </div>
          </div>
        </article>
      </div>


      <InventoryPagination
        :meta="meta"
        :loading="loading"
        @page-change="changePage"
      />
    </template>
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

import InventoryPagination
  from '@/components/inventory/shared/InventoryPagination.vue'

import inventoryWastageService
  from '@/services/inventoryWastageService'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
  refreshKey: {
    type: Number,
    default: 0,
  },
})


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const wastages =
  ref([])

const loading =
  ref(false)

const errorMessage =
  ref('')

const validationError =
  ref('')

const expandedIds =
  ref(
    new Set(),
  )

let reloadRequestedWhileLoading =
  false


const filters = reactive({
  search: '',
  reason: '',
  date_from: '',
  date_to: '',
  page: 1,
  per_page: 10,
})


const meta =
  ref(
    createDefaultMeta(),
  )


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
| Computed
|--------------------------------------------------------------------------
*/

const pageLoss = computed(() => {
  return wastages.value
    .reduce(
      (
        total,
        wastage,
      ) => {
        const loss =
          Number(
            wastage?.total_loss,
          )

        return (
          total
          +
          (
            Number.isFinite(loss)
              ? loss
              : 0
          )
        )
      },
      0,
    )
})


/*
|--------------------------------------------------------------------------
| Load
|--------------------------------------------------------------------------
*/

async function loadWastages()
{
  if (loading.value) {
    reloadRequestedWhileLoading =
      true

    return false
  }

  loading.value = true
  errorMessage.value = ''

  let succeeded =
    false

  try {
    const response =
      await inventoryWastageService
        .getWastages({
          search:
            filters.search
            ||
            undefined,

          reason:
            filters.reason
            ||
            undefined,

          date_from:
            filters.date_from
            ||
            undefined,

          date_to:
            filters.date_to
            ||
            undefined,

          page:
            filters.page,

          per_page:
            filters.per_page,
        })

    wastages.value =
      Array.isArray(
        response?.data,
      )
        ? response.data
            .filter(
              (wastage) =>
                wastage
                &&
                typeof wastage ===
                  'object',
            )
        : []

    meta.value =
      normalizeMeta(
        response?.meta,
      )

    filters.page =
      meta.value.current_page

    pruneExpanded()

    succeeded =
      true
  }
  catch (error) {
    wastages.value = []

    meta.value =
      createDefaultMeta()

    errorMessage.value =
      inventoryWastageService
        .getWastageErrorMessage(
          error,
          'Unable to load damage / wastage history.',
        )
  }
  finally {
    loading.value = false

    if (
      reloadRequestedWhileLoading
    ) {
      reloadRequestedWhileLoading =
        false

      void loadWastages()
    }
  }

  return succeeded
}


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

async function applyFilters()
{
  if (loading.value) {
    return
  }

  validationError.value = ''

  if (!validateDateRange()) {
    return
  }

  filters.page = 1

  expandedIds.value =
    new Set()

  await loadWastages()
}


async function clearFilters()
{
  if (loading.value) {
    return
  }

  filters.search = ''
  filters.reason = ''
  filters.date_from = ''
  filters.date_to = ''
  filters.page = 1
  filters.per_page =
    filters.per_page || 10

  validationError.value = ''

  expandedIds.value =
    new Set()

  await loadWastages()
}


function clearValidationError()
{
  validationError.value = ''
}


function validateDateRange()
{
  if (
    filters.date_from
    &&
    !isValidDate(
      filters.date_from,
    )
  ) {
    validationError.value =
      'Please select a valid From Date.'

    return false
  }

  if (
    filters.date_to
    &&
    !isValidDate(
      filters.date_to,
    )
  ) {
    validationError.value =
      'Please select a valid To Date.'

    return false
  }

  if (
    filters.date_from
    &&
    filters.date_to
    &&
    filters.date_to <
      filters.date_from
  ) {
    validationError.value =
      'To Date cannot be earlier than From Date.'

    return false
  }

  return true
}


/*
|--------------------------------------------------------------------------
| Pagination / Refresh
|--------------------------------------------------------------------------
*/

async function changePage(
  page,
)
{
  if (loading.value) {
    return
  }

  const target =
    Number(
      page,
    )

  if (
    !Number.isInteger(target)
    ||
    target < 1
    ||
    target >
      meta.value.last_page
    ||
    target ===
      meta.value.current_page
  ) {
    return
  }

  filters.page =
    target

  expandedIds.value =
    new Set()

  await loadWastages()
}


async function refresh()
{
  await loadWastages()
}


/*
|--------------------------------------------------------------------------
| Expand
|--------------------------------------------------------------------------
*/

function wastageIdentity(
  wastage,
)
{
  const id =
    wastage?.id

  if (
    id !== null
    &&
    id !== undefined
    &&
    id !== ''
  ) {
    return String(id)
  }

  return String(
    wastage?.wastage_no
    ||
    '',
  ).trim()
}


function isExpanded(
  wastage,
)
{
  const id =
    wastageIdentity(
      wastage,
    )

  return Boolean(
    id
    &&
    expandedIds.value
      .has(id),
  )
}


function toggleExpanded(
  wastage,
)
{
  const id =
    wastageIdentity(
      wastage,
    )

  if (!id) {
    return
  }

  const next =
    new Set(
      expandedIds.value,
    )

  if (next.has(id)) {
    next.delete(id)
  }
  else {
    next.add(id)
  }

  expandedIds.value =
    next
}


function pruneExpanded()
{
  const visible =
    new Set(
      wastages.value
        .map(
          (wastage) =>
            wastageIdentity(
              wastage,
            ),
        )
        .filter(Boolean),
    )

  expandedIds.value =
    new Set(
      Array.from(
        expandedIds.value,
      ).filter(
        (id) =>
          visible.has(id),
      ),
    )
}


/*
|--------------------------------------------------------------------------
| Display Helpers
|--------------------------------------------------------------------------
*/

function wastageItems(
  wastage,
)
{
  return Array.isArray(
    wastage?.items,
  )
    ? wastage.items
    : []
}


function mainItem(
  wastage,
)
{
  return (
    wastageItems(
      wastage,
    )[0]
    ||
    null
  )
}


function mainItemName(
  wastage,
)
{
  return itemName(
    mainItem(
      wastage,
    ),
  )
}


function mainItemQuantity(
  wastage,
)
{
  return itemQuantity(
    mainItem(
      wastage,
    ),
  )
}


function mainItemReason(
  wastage,
)
{
  return itemReason(
    mainItem(
      wastage,
    ),
  )
}


function itemName(
  item,
)
{
  return (
    item?.item_name
    ||
    item?.raw_material?.material_name
    ||
    'Unknown Material'
  )
}


function itemReason(
  item,
)
{
  return (
    item?.reason_label
    ||
    formatLabel(
      item?.reason,
    )
    ||
    '—'
  )
}


function itemQuantity(
  item,
)
{
  const formatted =
    String(
      item?.quantity_formatted
      ||
      '',
    ).trim()

  if (formatted) {
    return formatted
  }

  const unit =
    item?.unit
    ||
    item?.raw_material?.base_unit
    ||
    ''

  return `${quantityOnly(item?.quantity)} ${unit}`
    .trim()
}


function itemUnitCost(
  item,
)
{
  const formatted =
    String(
      item?.unit_cost_formatted
      ||
      '',
    ).trim()

  return formatted
    ||
    moneyText(
      item?.unit_cost,
      4,
    )
}


function itemLoss(
  item,
)
{
  const formatted =
    String(
      item?.total_loss_formatted
      ||
      '',
    ).trim()

  return formatted
    ||
    moneyText(
      item?.total_loss,
    )
}


function totalLossText(
  wastage,
)
{
  const formatted =
    String(
      wastage?.total_loss_formatted
      ||
      '',
    ).trim()

  return formatted
    ||
    moneyText(
      wastage?.total_loss,
    )
}


function recordedByName(
  wastage,
)
{
  return (
    wastage?.recorded_by?.name
    ||
    wastage?.created_by?.name
    ||
    'Unknown User'
  )
}


function wastageNumber(
  wastage,
)
{
  return (
    wastage?.wastage_no
    ||
    (
      wastage?.id
        ? `Wastage #${wastage.id}`
        : 'Wastage unavailable'
    )
  )
}


function wastageDate(
  wastage,
)
{
  return (
    wastage?.wasted_at_label
    ||
    formatDateTime(
      wastage?.wasted_at,
    )
  )
}


function wastageKey(
  wastage,
  index,
)
{
  const identity =
    wastageIdentity(
      wastage,
    )

  return identity
    ? `restaurant-wastage-${identity}`
    : `restaurant-wastage-row-${index}`
}


function wastageItemKey(
  wastage,
  item,
  index,
)
{
  if (item?.id) {
    return `restaurant-wastage-item-${item.id}`
  }

  return `${wastageKey(wastage, 0)}-item-${index}`
}


function quantityOnly(
  value,
)
{
  const number =
    Number(
      value,
    )

  if (!Number.isFinite(number)) {
    return '0'
  }

  return number.toLocaleString(
    'en-BD',
    {
      maximumFractionDigits: 4,
    },
  )
}


function moneyText(
  value,
  maximumFractionDigits = 2,
)
{
  const number =
    Number(
      value,
    )

  const safe =
    Number.isFinite(number)
      ? Math.max(0, number)
      : 0

  return `৳ ${safe.toLocaleString(
    'en-BD',
    {
      minimumFractionDigits: 2,
      maximumFractionDigits,
    },
  )}`
}


function formatLabel(
  value,
)
{
  const text =
    String(
      value
      ||
      '',
    )
      .trim()
      .replaceAll(
        '_',
        ' ',
      )

  if (!text) {
    return ''
  }

  return text.replace(
    /\b\w/g,
    (letter) =>
      letter.toUpperCase(),
  )
}


function formatDateTime(
  value,
)
{
  if (!value) {
    return '—'
  }

  const date =
    new Date(
      value,
    )

  if (
    Number.isNaN(
      date.getTime(),
    )
  ) {
    return String(value)
  }

  return date.toLocaleString(
    'en-BD',
    {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    },
  )
}


function isValidDate(
  value,
)
{
  return /^\d{4}-\d{2}-\d{2}$/
    .test(
      String(
        value,
      ),
    )
}


function createDefaultMeta()
{
  return {
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    from: null,
    to: null,
  }
}


function normalizeMeta(
  value,
)
{
  const source =
    value
    &&
    typeof value === 'object'
      ? value
      : {}

  const currentPage =
    positiveInteger(
      source.current_page,
      1,
    )

  const lastPage =
    positiveInteger(
      source.last_page,
      1,
    )

  return {
    current_page:
      Math.min(
        currentPage,
        lastPage,
      ),

    last_page:
      lastPage,

    per_page:
      positiveInteger(
        source.per_page,
        filters.per_page,
      ),

    total:
      nonNegativeInteger(
        source.total,
        0,
      ),

    from:
      source.from
      ??
      null,

    to:
      source.to
      ??
      null,
  }
}


function positiveInteger(
  value,
  fallback,
)
{
  const number =
    Number(
      value,
    )

  if (
    !Number.isFinite(number)
    ||
    number < 1
  ) {
    return fallback
  }

  return Math.floor(
    number,
  )
}


function nonNegativeInteger(
  value,
  fallback,
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
    return fallback
  }

  return Math.floor(
    number,
  )
}


/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {
  void loadWastages()
})


watch(
  () => props.refreshKey,
  () => {
    void loadWastages()
  },
)
</script>
