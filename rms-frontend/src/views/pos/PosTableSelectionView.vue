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
            Select one or more available tables. The first selected table becomes the primary table.
          </p>
        </div>
      </div>

      <button type="button" class="pos-light-button" :disabled="loading" @click="loadTables">
        <i class="bi bi-arrow-clockwise" :class="{ 'pos-spin': loading }"></i>
        Refresh
      </button>
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

      <div v-if="filteredTables.length" class="pos-table-grid">
        <PosTableCard
          v-for="table in filteredTables"
          :key="table.id"
          :table="table"
          :selected="isSelected(table.id)"
          :primary="isPrimary(table.id)"
          @toggle="toggleTable"
        />
      </div>

      <div v-else class="pos-empty-state">
        <span><i class="bi bi-grid-3x3-gap"></i></span>
        <h2>No available tables found</h2>
        <p>Refresh the floor or clear the search filter.</p>
      </div>
    </template>

    <footer v-if="!loading" class="pos-table-selection-footer">
      <div class="pos-selected-summary">
        <div>
          <span>Primary Table</span>
          <strong>{{ primaryTable?.table_name || 'Not selected' }}</strong>
        </div>
        <div>
          <span>Total Capacity</span>
          <strong>{{ totalCapacity }} seats</strong>
        </div>
      </div>

      <div class="pos-footer-actions">
        <button type="button" class="pos-secondary-button" @click="clearSelection">Clear</button>
        <button
          type="button"
          class="pos-primary-button"
          @click="continueToOrder"
        >
          {{ selectedIds.length ? 'Continue to Order' : 'Continue Without Table' }}
          <i class="bi bi-arrow-right"></i>
        </button>
      </div>
    </footer>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import PosTableCard from '@/components/pos/PosTableCard.vue'
import posOrderService from '@/services/posOrderService'
import '@/assets/css/pos/pos-order.css'

const router = useRouter()
const loading = ref(true)
const errorMessage = ref('')
const tables = ref([])
const selectedIds = ref([])
const search = ref('')

const filteredTables = computed(() => {
  const term = search.value.trim().toLowerCase()
  if (!term) return tables.value

  return tables.value.filter((table) =>
    [table?.table_name, table?.section]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()
      .includes(term),
  )
})

const selectedTables = computed(() =>
  selectedIds.value
    .map((id) => tables.value.find((table) => Number(table.id) === Number(id)))
    .filter(Boolean),
)

const primaryTable = computed(() => selectedTables.value[0] ?? null)
const totalCapacity = computed(() =>
  selectedTables.value.reduce((total, table) => total + Number(table?.capacity || 0), 0),
)

function isSelected(tableId) {
  return selectedIds.value.some((id) => Number(id) === Number(tableId))
}

function isPrimary(tableId) {
  return selectedIds.value.length > 0 && Number(selectedIds.value[0]) === Number(tableId)
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
