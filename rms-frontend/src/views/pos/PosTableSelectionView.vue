<template>
  <section class="pos-page pos-table-selection-page">
    <header class="pos-page-header">
      <div class="pos-page-header-left">
        <span class="pos-page-header-icon">
          <i class="bi bi-columns-gap"></i>
        </span>

        <div>
          <p class="pos-eyebrow">Operations</p>
          <h1>POS Order</h1>
          <p class="pos-page-subtitle">
            Select available tables or tables with seated reservations. The first selected table becomes the primary table.
          </p>
        </div>
      </div>

      <div class="pos-table-header-actions">
        <button type="button" class="pos-secondary-button" :disabled="loading" @click="clearSelection">
          <i class="bi bi-x-circle"></i>
          Clear
        </button>
        <button
          type="button"
          class="pos-primary-button"
          :disabled="loading"
          @click="continueToOrder"
        >
          <i class="bi bi-arrow-right-circle"></i>
          {{ selectedIds.length ? 'Continue to Order' : 'Continue Without Table' }}
        </button>
        <button type="button" class="pos-light-button pos-refresh-button" :disabled="loading" @click="loadTables">
          <i class="bi bi-arrow-clockwise" :class="{ 'pos-spin': loading }"></i>
          Refresh
        </button>
      </div>
    </header>

    <div v-if="errorMessage" class="pos-alert pos-alert-danger">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <span>{{ errorMessage }}</span>
    </div>

    <div v-if="loading" class="pos-table-loading">
      <span class="spinner-border"></span>
      <div>
        <strong>Loading available tables</strong>
        <p>Checking the latest restaurant floor availability.</p>
      </div>
    </div>

    <template v-else>
      <div class="pos-table-selection-toolbar">
        <div class="pos-search-control">
          <i class="bi bi-search"></i>
          <input v-model.trim="search" type="search" placeholder="Search table or section..." />
        </div>

        <div class="pos-selection-count">
          <strong>{{ selectedIds.length }}</strong>
          <span>tables selected</span>
        </div>
      </div>

      <div v-if="filteredTables.length" class="pos-table-workspace">
        <aside class="pos-table-list-panel">
          <div class="pos-floor-panel-heading">
            <div>
              <h2>Tables</h2>
              <p>Select one or more restaurant tables.</p>
            </div>
            <span class="pos-floor-count">{{ filteredTables.length }}</span>
          </div>

          <div class="pos-table-list">
            <button
              v-for="table in paginatedTables"
              :key="table.id"
              type="button"
              class="pos-table-list-item"
              :class="{ 'is-selected': isSelected(table.id), 'is-primary': isPrimary(table.id) }"
              @click="toggleTable(table)"
            >
              <span class="pos-table-list-check">
                <i :class="isSelected(table.id) ? 'bi bi-check-lg' : 'bi bi-plus-lg'"></i>
              </span>
              <span class="pos-table-list-copy">
                <strong>{{ formatTableName(table.table_name || ('Table ' + table.id)) }}</strong>
                <small>{{ table.section || 'Restaurant Floor' }}</small>
              </span>
              <span class="pos-table-list-meta">
                <strong>{{ table.capacity || 0 }} seats</strong>
                <small :class="{ reserved: table.has_seated_reservation }">{{ tableStatus(table) }}</small>
              </span>
            </button>
          </div>

          <PaginationControls
            v-if="filteredTables.length > pageSize"
            v-model:page="currentPage"
            :total="filteredTables.length"
            :per-page="pageSize"
            :show-summary="false"
          />
        </aside>

        <section class="pos-floor-layout-panel">
          <div class="pos-floor-panel-heading pos-floor-map-heading">
            <h2 class="pos-manage-tables-title">Manage Tables</h2>
            <div class="pos-floor-header-row">
              <div class="pos-floor-title-and-legend">
                <div class="pos-floor-legend">
                  <span><i class="available"></i> Available</span>
                  <span><i class="reserved"></i> Reserved</span>
                  <span><i class="selected"></i> Selected</span>
                </div>
              </div>
              <div class="pos-floor-section-tabs" role="tablist" aria-label="Restaurant sections">
                <button
                  v-for="section in sections"
                  :key="section.value"
                  type="button"
                  role="tab"
                  :aria-selected="activeSection === section.value"
                  :class="{ 'is-active': activeSection === section.value }"
                  @click="activeSection = section.value"
                >{{ section.label }}</button>
              </div>
            </div>
          </div>

          <div class="pos-floor-map">
            <div v-for="{ table, seats } in floorTables" :key="table.id" class="pos-floor-table-position" :style="tableStyle(table)" :class="{ 'is-selected': isSelected(table.id), 'is-reserved': table.has_seated_reservation }">
              <div v-if="seats.top.length" class="pos-floor-seat-row is-top" aria-hidden="true">
                <span v-for="seat in seats.top" :key="'top-' + seat"></span>
              </div>

              <div class="pos-floor-table-middle">
                <div v-if="seats.left.length" class="pos-floor-seat-column is-left" aria-hidden="true">
                  <span v-for="seat in seats.left" :key="'left-' + seat"></span>
                </div>

                <button
                  type="button"
                  class="pos-floor-table"
                  :class="[{ 'is-selected': isSelected(table.id), 'is-reserved': table.has_seated_reservation }, tableSizeClass(table), tableShapeClass(table)]"
                  :aria-pressed="isSelected(table.id)"
                  @click="toggleTable(table)"
                >
                  <strong>{{ formatTableName(table.table_name || ('Table ' + table.id)) }}</strong>
                  <span>{{ table.section || 'Restaurant Floor' }}</span>
                  <small><i class="bi bi-people"></i> {{ table.capacity || 0 }} seats</small>
                  <b v-if="isPrimary(table.id)" class="pos-floor-primary">Primary</b>
                </button>

                <div v-if="seats.right.length" class="pos-floor-seat-column is-right" aria-hidden="true">
                  <span v-for="seat in seats.right" :key="'right-' + seat"></span>
                </div>
              </div>

              <div v-if="seats.bottom.length" class="pos-floor-seat-row is-bottom" aria-hidden="true">
                <span v-for="seat in seats.bottom" :key="'bottom-' + seat"></span>
              </div>
            </div>
          </div>
        </section>
      </div>

      <div v-else class="pos-empty-state">
        <span><i class="bi bi-grid-3x3-gap"></i></span>
        <h2>No POS tables found</h2>
        <p>Refresh the floor or clear the search filter.</p>
      </div>
    </template>

  </section>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import PaginationControls from '@/components/shared/PaginationControls.vue'
import posOrderService from '@/services/posOrderService'
import '@/assets/css/pos/pos-order.css'

const router = useRouter()
const loading = ref(true)
const errorMessage = ref('')
const tables = ref([])
const selectedIds = ref([])
const activeSection = ref('ac')
const sections = [
  { value: 'ac', label: 'AC' },
  { value: 'non_ac', label: 'Non-AC' },
  { value: 'outdoor', label: 'Outdoor' },
]
const search = ref('')
const currentPage = ref(1)
const pageSize = 12

const filteredTables = computed(() => {
  const term = search.value.trim().toLowerCase()
  return tables.value.filter((table) => {
    if (String(table?.section || '').toLowerCase() !== activeSection.value) return false
    if (!term) return true

    return [table?.table_name, table?.section]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()
      .includes(term)
  })
})

const paginatedTables = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return filteredTables.value.slice(start, start + pageSize)
})

const floorTables = computed(() =>
  paginatedTables.value.map((table) => ({ table, seats: seatLayout(table) })),
)

watch(search, () => {
  currentPage.value = 1
})

function formatTableName(value) {
  return String(value ?? '').replace(/(\d+)$/, (digits) =>
    digits.length === 1 ? digits.padStart(2, '0') : digits,
  )
}
function isSelected(tableId) {
  return selectedIds.value.some((id) => Number(id) === Number(tableId))
}

function isPrimary(tableId) {
  return selectedIds.value.length > 0 && Number(selectedIds.value[0]) === Number(tableId)
}

function tableStatus(table) {
  return table?.has_seated_reservation ? 'Reserved' : 'Available'
}
function tableSizeClass(table) {
  const capacity = Math.max(0, Number(table?.capacity) || 0)
  if (capacity <= 2) return 'seats-capacity-2'
  if (capacity <= 4) return 'seats-capacity-4'
  if (capacity <= 6) return 'seats-capacity-6'
  if (capacity <= 8) return 'seats-capacity-8'
  if (capacity <= 10) return 'seats-capacity-10'
  return 'seats-capacity-12'
}

// Width x height (px) for every capacity, so each table size looks different.
const TABLE_SIZES = {
  1: [70, 70],
  2: [80, 80],
  3: [90, 90],
  4: [100, 100],
  5: [110, 85],
  6: [120, 85],
  7: [135, 90],
  8: [150, 95],
  9: [165, 100],
  10: [180, 105],
  11: [195, 110],
  12: [210, 115],
}

function tableDimensions(table) {
  const capacity = Math.max(0, Math.floor(Number(table?.capacity) || 0))
  const shape = String(table?.table_shape || table?.shape || '').toLowerCase()
  let [width, height] = TABLE_SIZES[Math.min(Math.max(capacity, 1), 12)]

  if (capacity > 12) {
    width = Math.min(210 + (capacity - 12) * 10, 300)
    height = 115 + Math.floor((capacity - 12) / 4) * 8
  }

  // Round and square tables look better with equal sides.
  if (['round', 'circle', 'circular', 'square'].includes(shape)) {
    width = height = Math.max(70, Math.round(((width + height) / 2) * 0.9))
  }

  return { width, height }
}

function tableStyle(table) {
  const { width, height } = tableDimensions(table)
  return { '--tw': width, '--th': height }
}

function tableShapeClass(table) {
  const shape = String(table?.table_shape || table?.shape || '').toLowerCase()
  if (['round', 'circle', 'circular'].includes(shape)) return 'shape-round'
  if (['square'].includes(shape)) return 'shape-square'
  if (['oval', 'ellipse', 'elliptical'].includes(shape)) return 'shape-oval'
  return 'shape-rectangle'
}

function seatLayout(table) {
  const capacity = Math.max(0, Math.floor(Number(table?.capacity) || 0))
  const shape = String(table?.table_shape || table?.shape || '').toLowerCase()
  const counts = { top: 0, right: 0, bottom: 0, left: 0 }
  const isRoundish = ['round', 'circle', 'circular', 'square'].includes(shape)

  if (isRoundish || capacity <= 4) {
    // Spread chairs evenly around the table. Two-seaters sit on the short ends.
    const sides = capacity <= 2 ? ['left', 'right'] : ['top', 'bottom', 'left', 'right']
    for (let seat = 0; seat < capacity; seat += 1) counts[sides[seat % sides.length]] += 1
  } else {
    // Rectangular and oval tables place most chairs along the longer sides.
    // Keep both long sides equal so the table looks balanced.
    const longSideSeats = Math.min(capacity, 2 * Math.round((capacity * 0.6) / 2))
    counts.top = Math.ceil(longSideSeats / 2)
    counts.bottom = Math.floor(longSideSeats / 2)
    const endSeats = capacity - longSideSeats
    counts.left = Math.ceil(endSeats / 2)
    counts.right = Math.floor(endSeats / 2)
  }

  return Object.fromEntries(Object.entries(counts).map(([side, count]) => [side, Array.from({ length: count }, (_, index) => index + 1)]))
}

function toggleTable(table) {
  const id = Number(table?.id)
  if (!id) return

  if (isSelected(id)) {
    selectedIds.value = selectedIds.value.filter((currentId) => Number(currentId) !== id)
    return
  }

  selectedIds.value = [...selectedIds.value, id]
}

function clearSelection() {
  selectedIds.value = []
}

async function loadTables() {
  loading.value = true
  errorMessage.value = ''

  try {
    const data = await posOrderService.loadBootstrap()
    tables.value = Array.isArray(data?.tables) ? data.tables : []

    const availableIds = new Set(tables.value.map((table) => Number(table.id)))
    selectedIds.value = selectedIds.value.filter((id) => availableIds.has(Number(id)))
  } catch (error) {
    tables.value = []
    errorMessage.value = posOrderService.getErrorMessage(error, 'Unable to load available tables.')
  } finally {
    loading.value = false
  }
}

async function continueToOrder() {
  const query = {}

  if (selectedIds.value.length) {
    query.tables = selectedIds.value.join(',')
  }

  await router.push({
    name: 'pos-order-create',
    query,
  })
}

onMounted(loadTables)
</script>
