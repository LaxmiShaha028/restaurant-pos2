<template>
  <button
    type="button"
    class="pos-table-card"
    :class="{ selected, primary }"
    @click="emit('toggle', table)"
  >
    <span class="pos-table-card-check">
      <i :class="selected ? 'bi bi-check-lg' : 'bi bi-plus-lg'"></i>
    </span>

    <div class="pos-table-card-icon">
      <i class="bi bi-grid-3x3-gap-fill"></i>
    </div>

    <div class="pos-table-card-copy">
      <div class="pos-table-card-title-row">
        <h3>{{ table.table_name || `Table ${table.id}` }}</h3>
      </div>

      <p>{{ table.section || 'Restaurant Floor' }}</p>

      <div class="pos-table-card-meta">
        <span>
          <i class="bi bi-people"></i>
          {{ table.capacity || 0 }} seats
        </span>

        <span class="available">
          <i class="bi bi-check-circle-fill"></i>
          Available
        </span>
      </div>
    </div>
  </button>
</template>

<script setup>
defineProps({
  table: { type: Object, required: true },
  selected: { type: Boolean, default: false },
  primary: { type: Boolean, default: false },
})

const emit = defineEmits(['toggle'])
</script>
