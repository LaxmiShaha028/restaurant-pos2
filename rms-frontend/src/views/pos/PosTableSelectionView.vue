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

      <template v-if="filteredTables.length">
        <div class="pos-table-grid">
          <PosTableCard
            v-for="table in paginatedTables"
            :key="table.id"
            :table="table"
            :selected="isSelected(table.id)"
            :primary="isPrimary(table.id)"
            @toggle="toggleTable"
          />
        </div>

        <PaginationControls
          v-if="filteredTables.length > pageSize"
          v-model:page="currentPage"
          :total="filteredTables.length"
          :per-page="pageSize"
          :show-summary="false"
        />
      </template>

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
import PosTableCard from '@/components/pos/PosTableCard.vue'
import PaginationControls from '@/components/shared/PaginationControls.vue'
import posOrderService from '@/services/posOrderService'
import '@/assets/css/pos/pos-order.css'

const router = useRouter()
const loading = ref(true)
const errorMessage = ref('')
const tables = ref([])
const selectedIds = ref([])
const search = ref('')
const currentPage = ref(1)
const pageSize = 12

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

const paginatedTables = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return filteredTables.value.slice(start, start + pageSize)
})

watch(search, () => {
  currentPage.value = 1
})

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
