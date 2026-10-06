<template>

  <section class="expense-management-page">

    <ExpensePageHeader
      @refresh="loadAllData"
      @add="openAddExpense"
    />


    <ExpenseSummaryCards
      :summary="summary"
      :loading="summaryLoading"
    />


    <ExpenseFilters
      v-model:filters="filters"
      :categories="categories"
      :payment-methods="paymentMethods"
      :loading="loading"
      @apply="applyFilters"
      @clear="clearFilters"
      @print="printReport"
    />


    <ExpenseTable
      :expenses="expenses"
      :loading="loading"
      :meta="meta"
      @delete="handleDelete"
      @edit="openEditExpense"
      @page-change="changePage"
    />


    <ExpenseFormModal
      :show="showExpenseModal"
      :expense="editingExpense"
      :categories="categories"
      :payment-methods="paymentMethods"
      :loading="savingExpense"
      :category-saving="categorySaving"
      :category-error="categoryError"
      :auto-select-category-id="autoSelectCategoryId"
      @close="closeExpenseModal"
      @submit="saveExpense"
      @create-category="createExpenseCategory"
    />

  </section>

</template>


<script setup>

import {
  nextTick,
  onMounted,
  reactive,
  ref,
} from 'vue'


import expenseService
  from '@/services/expenseService'


import ExpensePageHeader
  from '@/components/expenses/ExpensePageHeader.vue'

import ExpenseSummaryCards
  from '@/components/expenses/ExpenseSummaryCards.vue'

import ExpenseFilters
  from '@/components/expenses/ExpenseFilters.vue'

import ExpenseTable
  from '@/components/expenses/ExpenseTable.vue'

import ExpenseFormModal
  from '@/components/expenses/ExpenseFormModal.vue'


const expenses =
  ref([])

const categories =
  ref([])

const paymentMethods =
  ref([])

const summary =
  ref({})

const meta =
  ref({})

const loading =
  ref(false)

const summaryLoading =
  ref(false)

const errorMessage =
  ref('')

const deleteLoadingId =
  ref(null)

const showExpenseModal =
  ref(false)

const editingExpense =
  ref(null)

const savingExpense =
  ref(false)


/*
|--------------------------------------------------------------------------
| Quick Category State
|--------------------------------------------------------------------------
*/

const categorySaving =
  ref(false)

const categoryError =
  ref('')

const autoSelectCategoryId =
  ref(null)


const filters =
  reactive({

    date_from:'',

    date_to:'',

    category_id:'',

    payment_method:'',

    page:1,

    per_page:10,

  })


async function loadAllData(){

  await Promise.all([

    loadOptions(),

    loadExpenses(),

    loadSummary(),

  ])

}


async function loadOptions(){

  try{

    const response =
      await expenseService
        .getOptions()

    categories.value =
      response
        ?.data
        ?.categories
        ||
        []

    paymentMethods.value =
      response
        ?.data
        ?.payment_methods
        ||
        []

  }
  catch(error){

    console.error(error)

  }

}


function openEditExpense(expense){

  editingExpense.value =
    expense

  categoryError.value =
    ''

  autoSelectCategoryId.value =
    null

  showExpenseModal.value =
    true

}


async function loadExpenses(){

  loading.value =
    true

  try{

    const response =
      await expenseService
        .getExpenses(
          filters
        )

    expenses.value =
      response.data

    meta.value =
      response.meta

  }
  catch(error){

    errorMessage.value =
      expenseService
        .getErrorMessage(
          error,
          'Unable to load expenses.'
        )

  }
  finally{

    loading.value =
      false

  }

}


async function loadSummary(){

  summaryLoading.value =
    true

  try{

    const response =
      await expenseService
        .getSummary(
          filters
        )

    summary.value =
      response.data

  }
  catch(error){

    console.error(error)

  }
  finally{

    summaryLoading.value =
      false

  }

}


async function applyFilters(){

  filters.page =
    1

  await Promise.all([

    loadExpenses(),

    loadSummary(),

  ])

}


async function clearFilters(){

  filters.date_from =
    ''

  filters.date_to =
    ''

  filters.category_id =
    ''

  filters.payment_method =
    ''

  filters.page =
    1

  await applyFilters()

}


async function changePage(page){

  filters.page =
    page

  await loadExpenses()

}


async function handleDelete(expense){

  const confirmed =
    window.confirm(
      'Delete this expense?'
    )

  if(!confirmed){

    return

  }

  deleteLoadingId.value =
    expense.id

  try{

    await expenseService
      .deleteExpense(
        expense.id
      )

    await loadAllData()

  }
  catch(error){

    errorMessage.value =
      expenseService
        .getErrorMessage(
          error,
          'Delete failed.'
        )

  }
  finally{

    deleteLoadingId.value =
      null

  }

}


function openAddExpense(){

  /*
  |--------------------------------------------------------------------------
  | Important
  |--------------------------------------------------------------------------
  |
  | Always clear editingExpense before opening Add mode.
  | This prevents a previously edited expense from accidentally reopening
  | the modal in edit mode.
  |
  */

  editingExpense.value =
    null

  categoryError.value =
    ''

  autoSelectCategoryId.value =
    null

  showExpenseModal.value =
    true

}


function closeExpenseModal(){

  showExpenseModal.value =
    false

  editingExpense.value =
    null

  categoryError.value =
    ''

  autoSelectCategoryId.value =
    null

}


/*
|--------------------------------------------------------------------------
| Create / Reactivate Expense Category
|--------------------------------------------------------------------------
*/

async function createExpenseCategory(
  payload
){

  categorySaving.value =
    true

  categoryError.value =
    ''

  try{

    const response =
      await expenseService
        .createCategory(
          payload
        )

    const category =
      response
        ?.data

    /*
    |--------------------------------------------------------------------------
    | Reload canonical category list from backend
    |--------------------------------------------------------------------------
    */

    await loadOptions()

    /*
    |--------------------------------------------------------------------------
    | Force watcher to fire even if the same restored category id is selected.
    |--------------------------------------------------------------------------
    */

    autoSelectCategoryId.value =
      null

    await nextTick()

    autoSelectCategoryId.value =
      category?.id
      ??
      null

  }
  catch(error){

    categoryError.value =
      expenseService
        .getErrorMessage(
          error,
          'Unable to create expense category.'
        )

  }
  finally{

    categorySaving.value =
      false

  }

}


async function saveExpense(payload){

  savingExpense.value =
    true

  try{

    if(editingExpense.value){

      await expenseService
        .updateExpense(
          editingExpense.value.id,
          payload
        )

    }
    else{

      await expenseService
        .createExpense(
          payload
        )

    }

    closeExpenseModal()

    await loadAllData()

  }
  catch(error){

    errorMessage.value =
      expenseService
        .getErrorMessage(
          error,
          editingExpense.value
            ? 'Unable to update expense.'
            : 'Unable to create expense.'
        )

  }
  finally{

    savingExpense.value =
      false

  }

}


function printReport(){

  window.print()

}


onMounted(
  loadAllData
)

</script>


<style>

@import '@/assets/css/expenses/expense-header.css';

@import '@/assets/css/expenses/expense-summary.css';

@import '@/assets/css/expenses/expense-filter.css';

@import '@/assets/css/expenses/expense-table.css';

@import '@/assets/css/expenses/expense-modal.css';

@import '@/assets/css/expenses/expenses-compact.css';

</style>
