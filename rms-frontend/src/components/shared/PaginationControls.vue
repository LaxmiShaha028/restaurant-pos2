<script setup>

import {
  computed,
  watch,
} from 'vue'

const props = defineProps({
  page: {
    type: Number,
    default: 1,
  },
  total: {
    type: Number,
    default: 0,
  },
  perPage: {
    type: Number,
    default: 10,
  },
  showSummary: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['update:page'])

const pageCount = computed(() =>
  Math.max(1, Math.ceil(props.total / props.perPage)),
)

const rangeStart = computed(() =>
  props.total === 0 ? 0 : (props.page - 1) * props.perPage + 1,
)

const rangeEnd = computed(() =>
  Math.min(props.page * props.perPage, props.total),
)

watch(pageCount, (count) => {
  if (props.page > count) {
    emit('update:page', count)
  }
})

function goToPage(page) {
  emit('update:page', Math.min(Math.max(1, page), pageCount.value))
}

</script>

<template>
  <nav
    v-if="total > perPage"
    class="pagination-controls"
    aria-label="Pagination"
  >
    <span v-if="showSummary" class="pagination-summary">
      Showing {{ rangeStart }}–{{ rangeEnd }} of {{ total }}
    </span>

    <div class="pagination-actions">
      <button
        type="button"
        :disabled="page <= 1"
        aria-label="Previous page"
        @click="goToPage(page - 1)"
      >
        Previous
      </button>

      <span aria-live="polite">Page {{ page }} of {{ pageCount }}</span>

      <button
        type="button"
        :disabled="page >= pageCount"
        aria-label="Next page"
        @click="goToPage(page + 1)"
      >
        Next
      </button>
    </div>
  </nav>
</template>

<style scoped>
.pagination-controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 16px;
  border-top: 1px solid #e8edf3;
  color: #667085;
  font-size: 13px;
}

.pagination-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.pagination-actions button {
  border: 1px solid #d0d5dd;
  border-radius: 6px;
  background: #fff;
  color: #344054;
  padding: 6px 10px;
  cursor: pointer;
}

.pagination-actions button:disabled {
  cursor: not-allowed;
  opacity: 0.5;
}

@media (max-width: 560px) {
  .pagination-controls {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>
