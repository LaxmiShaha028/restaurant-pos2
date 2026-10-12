<template>
  <header class="expense-page-header">
    <div class="expense-header-content">
      <!-- Left: Icon + Text -->
      <div class="expense-header-left">
        <div class="expense-header-icon">
          <i
            class="bi bi-wallet2"
            aria-hidden="true"
          ></i>
        </div>

        <div>
          <p class="expense-page-eyebrow">
            Financial Operations
          </p>

          <h1 class="expense-title">
            Expense Management
          </h1>

          <p class="expense-description">
            Manage restaurant expenses,
            payments and financial records.
          </p>
        </div>
      </div>

      <!-- Right: Actions -->
      <div class="expense-header-actions">
        <button
          type="button"
          class="expense-header-btn"
          @click="refresh"
        >
          <i class="bi bi-arrow-clockwise"></i>
        </button>

        <button
          type="button"
          class="expense-header-btn expense-add-btn"
          @click="addExpense"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Add Expense</span>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
/*
|--------------------------------------------------------------------------
| Events
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
  'refresh',
  'add',
])

function refresh() {
  emit('refresh')
}

function addExpense() {
  emit('add')
}
</script>

<style scoped>
</style>