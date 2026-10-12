<template>

<div
  v-if="show"
  class="expense-modal-overlay"
>

  <div class="expense-modal">

    <div class="expense-modal-header">

      <h3>
        <i class="bi bi-wallet2"></i>

        {{
          isEditMode
            ? 'Edit Expense'
            : 'Add Expense'
        }}
      </h3>

      <button
        type="button"
        class="close-btn"
        @click="close"
      >
        <i class="bi bi-x-lg"></i>
      </button>

    </div>


    <form
      @submit.prevent="submit"
    >

      <div class="expense-form-grid">

        <!-- Expense Date -->

        <div class="form-group">

          <label>
            Expense Date
          </label>

          <input
            type="date"
            v-model="form.expense_date"
            required
          />

        </div>


        <!-- Category -->

        <div class="form-group">

          <div class="expense-category-label-row">

            <label>
              Category
            </label>

            <button
              type="button"
              class="expense-category-add-btn"
              @click="toggleCategoryCreator"
            >
              <i
                :class="
                  showCategoryCreator
                    ? 'bi bi-x-lg'
                    : 'bi bi-plus-lg'
                "
              ></i>

              {{
                showCategoryCreator
                  ? 'Close'
                  : 'Add Category'
              }}
            </button>

          </div>


          <select
            v-model="form.expense_category_id"
            required
          >

            <option value="">
              {{
                categories.length
                  ? 'Select Category'
                  : 'No categories available'
              }}
            </option>

            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </option>

          </select>


          <small
            v-if="categories.length === 0"
            class="expense-category-help"
          >
            No expense category exists yet.
            Use “Add Category” above to create the first one.
          </small>


          <!-- Quick Add Category -->

          <div
            v-if="showCategoryCreator"
            class="expense-category-quick-add"
          >

            <div class="expense-category-quick-title">
              Quick Add Category
            </div>


            <input
              type="text"
              v-model="categoryForm.name"
              maxlength="150"
              placeholder="e.g. Electricity Bill"
              @keydown.enter.prevent="createCategory"
            />


            <textarea
              v-model="categoryForm.description"
              rows="2"
              maxlength="2000"
              placeholder="Description (optional)"
            ></textarea>


            <p
              v-if="displayCategoryError"
              class="expense-category-error"
            >
              {{ displayCategoryError }}
            </p>


            <button
              type="button"
              class="btn-primary expense-category-save-btn"
              :disabled="categorySaving"
              @click="createCategory"
            >

              <span
                v-if="categorySaving"
                class="spinner-border spinner-border-sm"
              ></span>

              {{
                categorySaving
                  ? 'Adding...'
                  : 'Add & Select Category'
              }}

            </button>

          </div>

        </div>


        <!-- Amount -->

        <div class="form-group">

          <label>
            Amount
          </label>

          <input
            type="number"
            step="0.01"
            min="0.01"
            v-model="form.amount"
            placeholder="Enter amount"
            required
          />

        </div>


        <!-- Payment Method -->

        <div class="form-group">

          <label>
            Payment Method
          </label>

          <select
            v-model="form.payment_method"
            required
          >

            <option value="">
              Select Method
            </option>

            <option
              v-for="method in paymentMethods"
              :key="method.value"
              :value="method.value"
            >
              {{ method.label }}
            </option>

          </select>

        </div>


        <!-- Paid By -->

        <div class="form-group">

          <label>
            Paid By
          </label>

          <input
            type="text"
            :value="paidByName"
            readonly
            class="readonly-input"
          />

          <small>
            Automatically assigned user
          </small>

        </div>

      </div>


      <!-- Notes -->

      <div class="form-group">

        <label>
          Notes
        </label>

        <textarea
          v-model="form.notes"
          rows="3"
          placeholder="Expense notes..."
        ></textarea>

      </div>


      <!-- Buttons -->

      <div class="expense-form-actions">

        <button
          type="button"
          class="btn-secondary"
          @click="close"
        >
          Cancel
        </button>

        <button
          type="submit"
          class="btn-primary"
          :disabled="loading || categorySaving"
        >

          <span
            v-if="loading"
            class="spinner-border spinner-border-sm"
          ></span>

          {{
            loading
              ? 'Saving...'
              : (
                  isEditMode
                    ? 'Update Expense'
                    : 'Save Expense'
                )
          }}

        </button>

      </div>

    </form>

  </div>

</div>

</template>


<script setup>

import {
  computed,
  reactive,
  ref,
  watch,
} from 'vue'


const props = defineProps({

  show:{
    type:Boolean,
    default:false
  },

  expense:{
    type:Object,
    default:null
  },

  categories:{
    type:Array,
    default:()=>[]
  },

  paymentMethods:{
    type:Array,
    default:()=>[]
  },

  loading:{
    type:Boolean,
    default:false
  },

  categorySaving:{
    type:Boolean,
    default:false
  },

  categoryError:{
    type:String,
    default:''
  },

  autoSelectCategoryId:{
    type:[
      Number,
      String,
    ],
    default:null
  }

})


const emit = defineEmits([
  'close',
  'submit',
  'create-category',
])


const form = reactive({

  expense_category_id:'',

  expense_date:'',

  amount:'',

  payment_method:'',

  notes:''

})


const showCategoryCreator =
  ref(false)


const localCategoryError =
  ref('')


const categoryForm =
  reactive({

    name:'',

    description:''

  })


const isEditMode = computed(()=>{

  return Boolean(
    props.expense
  )

})


const paidByName = computed(()=>{

  return (
    props.expense?.paid_by_name
    ||
    'Current User'
  )

})


const displayCategoryError =
  computed(()=>{

    return (
      localCategoryError.value
      ||
      props.categoryError
      ||
      ''
    )

  })


watch(

  ()=>props.expense,

  (expense)=>{

    if(expense){

      form.expense_category_id =
        expense.category?.id
        ??
        expense.expense_category_id
        ??
        ''

      form.expense_date =
        expense.expense_date
          ? expense.expense_date.substring(
              0,
              10
            )
          : ''

      form.amount =
        expense.amount
        ??
        ''

      form.payment_method =
        expense.payment_method
        ??
        ''

      form.notes =
        expense.notes
        ??
        ''

    }
    else{

      resetForm()

    }

  },

  {
    immediate:true
  }

)


watch(

  ()=>props.autoSelectCategoryId,

  (categoryId)=>{

    if(
      categoryId === null
      ||
      categoryId === ''
    ){
      return
    }

    form.expense_category_id =
      categoryId

    showCategoryCreator.value =
      false

    resetCategoryForm()

  }

)


watch(

  ()=>props.show,

  (isVisible)=>{

    if(!isVisible){

      showCategoryCreator.value =
        false

      resetCategoryForm()

    }

  }

)


function toggleCategoryCreator(){

  showCategoryCreator.value =
    !showCategoryCreator.value

  localCategoryError.value =
    ''

}


function createCategory(){

  const name =
    categoryForm
      .name
      .trim()

  if(!name){

    localCategoryError.value =
      'Category name is required.'

    return
  }

  localCategoryError.value =
    ''

  emit(
    'create-category',
    {
      name,
      description:
        categoryForm
          .description
          .trim()
        ||
        null,
    }
  )

}


function submit(){

  emit(
    'submit',
    {
      ...form
    }
  )

}


function close(){

  emit(
    'close'
  )

}


function resetForm(){

  form.expense_category_id=''

  form.expense_date=''

  form.amount=''

  form.payment_method=''

  form.notes=''

}


function resetCategoryForm(){

  categoryForm.name=''

  categoryForm.description=''

  localCategoryError.value=''

}

</script>


<style scoped>

.expense-category-label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.expense-category-add-btn {
  border: 0;
  background: transparent;
  padding: 0;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

.expense-category-add-btn i {
  margin-right: 4px;
}

.expense-category-help {
  display: block;
  margin-top: 6px;
}

.expense-category-quick-add {
  margin-top: 12px;
  padding: 12px;
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 8px;
}

.expense-category-quick-title {
  margin-bottom: 10px;
  font-size: 14px;
  font-weight: 700;
}

.expense-category-quick-add input,
.expense-category-quick-add textarea {
  width: 100%;
  margin-bottom: 8px;
}

.expense-category-error {
  margin: 2px 0 8px;
  font-size: 13px;
  color: #b42318;
}

.expense-category-save-btn {
  width: 100%;
}

</style>
