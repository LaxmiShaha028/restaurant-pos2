<template>
  <Teleport to="body">
    <Transition name="inventory-modal">
      <div
        v-if="show"
        class="transfer-template-backdrop"
        @click.self="requestClose"
      >
        <section
          class="transfer-template-modal"
          role="dialog"
          aria-modal="true"
          aria-labelledby="transfer-template-title"
        >
          <header class="transfer-template-header">
            <div>
              <span class="daily-transfer-eyebrow">
                Reusable Presets
              </span>

              <h2 id="transfer-template-title">
                Daily Transfer Templates
              </h2>

              <p>
                Save normal warehouse → restaurant quantities.
                Templates never change historical stock transfers.
              </p>
            </div>

            <button
              type="button"
              class="daily-transfer-icon-btn"
              :disabled="saving"
              @click="requestClose"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </header>


          <div class="transfer-template-body">
            <div
              v-if="successMessage"
              class="daily-transfer-alert daily-transfer-alert-success"
            >
              <i class="bi bi-check-circle"></i>
              {{ successMessage }}
            </div>

            <div
              v-if="errorMessage"
              class="daily-transfer-alert daily-transfer-alert-danger"
            >
              <i class="bi bi-exclamation-triangle"></i>
              {{ errorMessage }}
            </div>


            <div class="transfer-template-layout">
              <aside class="transfer-template-list-panel">
                <div class="transfer-template-list-header">
                  <div>
                    <h3>
                      Saved Templates
                    </h3>

                    <small>
                      {{ templates.length }} total
                    </small>
                  </div>

                  <button
                    type="button"
                    class="daily-transfer-primary-btn compact"
                    :disabled="saving"
                    @click="startCreate"
                  >
                    <i class="bi bi-plus-lg"></i>
                    New
                  </button>
                </div>


                <div
                  v-if="loading"
                  class="daily-transfer-loading"
                >
                  <span class="spinner-border spinner-border-sm"></span>
                  Loading templates...
                </div>


                <div
                  v-else-if="templates.length === 0"
                  class="daily-transfer-empty compact"
                >
                  <i class="bi bi-bookmark"></i>
                  <span>
                    No template created yet.
                  </span>
                </div>


                <div
                  v-else
                  class="transfer-template-list"
                >
                  <article
                    v-for="template in templates"
                    :key="template.id"
                    class="transfer-template-list-item"
                    :class="{
                      active:
                        Number(editingId)
                        ===
                        Number(template.id),
                    }"
                  >
                    <button
                      type="button"
                      class="transfer-template-select"
                      :disabled="saving"
                      @click="startEdit(template)"
                    >
                      <div>
                        <strong>
                          {{ template.name }}
                        </strong>

                        <small>
                          {{ template.total_items || template.items?.length || 0 }}
                          items
                        </small>
                      </div>

                      <span
                        class="transfer-template-status"
                        :class="{
                          inactive:
                            !template.is_active,
                        }"
                      >
                        {{
                          template.is_active
                            ? 'Active'
                            : 'Inactive'
                        }}
                      </span>
                    </button>


                    <div class="transfer-template-row-actions">
                      <button
                        type="button"
                        :disabled="saving || deletingId === template.id"
                        :title="
                          template.is_active
                            ? 'Deactivate template'
                            : 'Activate template'
                        "
                        @click="toggleStatus(template)"
                      >
                        <i
                          class="bi"
                          :class="
                            template.is_active
                              ? 'bi-pause-circle'
                              : 'bi-play-circle'
                          "
                        ></i>
                      </button>

                      <button
                        type="button"
                        class="danger"
                        :disabled="saving || deletingId === template.id"
                        title="Delete template"
                        @click="deleteTemplate(template)"
                      >
                        <span
                          v-if="deletingId === template.id"
                          class="spinner-border spinner-border-sm"
                        ></span>

                        <i
                          v-else
                          class="bi bi-trash"
                        ></i>
                      </button>
                    </div>
                  </article>
                </div>
              </aside>


              <main class="transfer-template-form-panel">
                <div class="daily-transfer-section-heading">
                  <div>
                    <h3>
                      {{
                        editingId
                          ? 'Edit Template'
                          : 'Create Template'
                      }}
                    </h3>

                    <p>
                      Saved quantities are defaults only. Daily Transfer
                      will always check current warehouse availability.
                    </p>
                  </div>
                </div>


                <div class="daily-transfer-grid">
                  <div class="daily-transfer-field">
                    <label for="transfer-template-name">
                      Template Name
                      <span>*</span>
                    </label>

                    <input
                      id="transfer-template-name"
                      v-model.trim="form.name"
                      type="text"
                      maxlength="120"
                      :disabled="saving"
                      placeholder="Morning Kitchen Stock"
                    />
                  </div>

                  <label class="transfer-template-active-check">
                    <input
                      v-model="form.is_active"
                      type="checkbox"
                      :disabled="saving"
                    />

                    <span>
                      Active template
                    </span>
                  </label>
                </div>


                <div class="daily-transfer-field daily-transfer-field-full">
                  <label for="transfer-template-description">
                    Description
                  </label>

                  <textarea
                    id="transfer-template-description"
                    v-model.trim="form.description"
                    rows="2"
                    maxlength="500"
                    :disabled="saving"
                    placeholder="Optional description"
                  ></textarea>
                </div>


                <div class="transfer-template-items-heading">
                  <div>
                    <h4>
                      Template Items
                    </h4>

                    <small>
                      {{ form.items.length }} item(s)
                    </small>
                  </div>

                  <button
                    type="button"
                    class="daily-transfer-secondary-btn"
                    :disabled="saving || !canAddItem"
                    @click="addItem"
                  >
                    <i class="bi bi-plus-lg"></i>
                    Add Item
                  </button>
                </div>


                <div
                  v-if="materialsLoading"
                  class="daily-transfer-loading"
                >
                  <span class="spinner-border spinner-border-sm"></span>
                  Loading raw materials...
                </div>


                <div
                  v-else
                  class="transfer-template-form-items"
                >
                  <article
                    v-for="(item, index) in form.items"
                    :key="item.row_key"
                    class="transfer-template-form-item"
                  >
                    <div class="transfer-template-form-item-grid">
                      <div class="daily-transfer-field">
                        <label
                          :for="`template-material-${item.row_key}`"
                        >
                          Raw Material
                          <span>*</span>
                        </label>

                        <select
                          :id="`template-material-${item.row_key}`"
                          v-model="item.raw_material_id"
                          :disabled="saving"
                        >
                          <option value="">
                            Select material
                          </option>

                          <option
                            v-for="material in materials"
                            :key="material.id"
                            :value="String(material.id)"
                            :disabled="
                              selectedElsewhere(
                                material.id,
                                item.row_key
                              )
                            "
                          >
                            {{
                              material.material_name
                              ||
                              material.name
                            }}
                            ({{ material.base_unit || '' }})
                          </option>
                        </select>
                      </div>


                      <div class="daily-transfer-field">
                        <label
                          :for="`template-qty-${item.row_key}`"
                        >
                          Default Quantity
                          <span>*</span>
                        </label>

                        <input
                          :id="`template-qty-${item.row_key}`"
                          v-model="item.quantity"
                          type="text"
                          inputmode="decimal"
                          autocomplete="off"
                          :disabled="saving"
                          placeholder="0"
                          @input="sanitizeQuantity(item)"
                        />
                      </div>


                      <div class="daily-transfer-field">
                        <label
                          :for="`template-note-${item.row_key}`"
                        >
                          Item Note
                        </label>

                        <input
                          :id="`template-note-${item.row_key}`"
                          v-model.trim="item.notes"
                          type="text"
                          maxlength="500"
                          :disabled="saving"
                          placeholder="Optional"
                        />
                      </div>


                      <button
                        type="button"
                        class="daily-transfer-remove-btn"
                        :disabled="saving || form.items.length <= 1"
                        @click="removeItem(index)"
                      >
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>

                    <small
                      v-if="itemError(index)"
                      class="daily-transfer-field-error"
                    >
                      {{ itemError(index) }}
                    </small>
                  </article>
                </div>


                <div class="transfer-template-form-actions">
                  <button
                    type="button"
                    class="daily-transfer-secondary-btn"
                    :disabled="saving"
                    @click="resetForm"
                  >
                    Clear
                  </button>

                  <button
                    type="button"
                    class="daily-transfer-primary-btn"
                    :disabled="saving || !formReady"
                    @click="saveTemplate"
                  >
                    <span
                      v-if="saving"
                      class="spinner-border spinner-border-sm"
                    ></span>

                    <i
                      v-else
                      class="bi bi-bookmark-check"
                    ></i>

                    {{
                      saving
                        ? 'Saving...'
                        : editingId
                          ? 'Update Template'
                          : 'Save Template'
                    }}
                  </button>
                </div>
              </main>
            </div>
          </div>
        </section>
      </div>
    </Transition>
  </Teleport>
</template>


<script setup>
import {
  computed,
  reactive,
  ref,
  watch,
} from 'vue'

import inventoryService
  from '@/services/inventoryService'

import stockTransferTemplateService
  from '@/services/stockTransferTemplateService'


const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
})


const emit = defineEmits([
  'close',
  'changed',
])


const templates =
  ref([])

const materials =
  ref([])

const loading =
  ref(false)

const materialsLoading =
  ref(false)

const saving =
  ref(false)

const deletingId =
  ref(null)

const editingId =
  ref(null)

const errorMessage =
  ref('')

const successMessage =
  ref('')

const serverErrors =
  ref({})

let rowSequence =
  0


const form = reactive({
  name: '',
  description: '',
  is_active: true,
  items: [],
})


const canAddItem =
  computed(() => {

    return (
      materials.value.length > 0
      &&
      form.items.length
      <
      Math.min(
        200,
        materials.value.length,
      )
    )
  })


const formReady =
  computed(() => {

    if (
      !form.name.trim()
      ||
      form.items.length === 0
    ) {
      return false
    }

    const selected =
      new Set()

    for (
      const item
      of form.items
    ) {
      const id =
        Number(
          item.raw_material_id
        )

      const quantity =
        parseQuantity(
          item.quantity
        )

      if (
        !Number.isInteger(id)
        ||
        id <= 0
        ||
        selected.has(id)
        ||
        quantity === null
        ||
        quantity <= 0
      ) {
        return false
      }

      selected.add(id)
    }

    return true
  })


async function loadTemplates()
{
  loading.value = true
  errorMessage.value = ''

  try {
    const response =
      await stockTransferTemplateService
        .getTemplates()

    templates.value =
      Array.isArray(response?.data)
        ? response.data
        : []
  } catch (error) {
    templates.value = []

    errorMessage.value =
      stockTransferTemplateService
        .getErrorMessage(
          error,
          'Unable to load transfer templates.',
        )
  } finally {
    loading.value = false
  }
}


async function loadMaterials()
{
  materialsLoading.value = true

  try {
    const collected = []

    let page = 1
    let lastPage = 1

    do {
      const response =
        await inventoryService
          .getRawMaterials({
            is_active: 1,
            sort_by: 'material_name',
            sort_direction: 'asc',
            page,
            per_page: 100,
          })

      const rows =
        Array.isArray(response?.data)
          ? response.data
          : []

      collected.push(
        ...rows,
      )

      lastPage =
        Math.max(
          1,
          Number(
            response?.meta?.last_page
            || 1,
          ),
        )

      page += 1
    } while (
      page <= lastPage
    )

    const unique =
      new Map()

    collected.forEach(
      (material) => {
        const id =
          Number(
            material?.id
          )

        if (
          Number.isInteger(id)
          &&
          id > 0
        ) {
          unique.set(
            id,
            material,
          )
        }
      },
    )

    materials.value =
      Array.from(
        unique.values(),
      )
  } catch (error) {
    materials.value = []

    errorMessage.value =
      inventoryService
        .getInventoryErrorMessage(
          error,
          'Unable to load raw materials for templates.',
        )
  } finally {
    materialsLoading.value = false
  }
}


function startCreate()
{
  resetForm()
}


function startEdit(
  template,
)
{
  if (
    saving.value
  ) {
    return
  }

  editingId.value =
    Number(
      template?.id
      || 0,
    )
    ||
    null

  form.name =
    String(
      template?.name
      || '',
    )

  form.description =
    String(
      template?.description
      || '',
    )

  form.is_active =
    template?.is_active
    !==
    false

  const items =
    Array.isArray(template?.items)
      ? template.items
      : []

  form.items =
    items.map(
      (item) => ({
        row_key:
          `template-${++rowSequence}`,

        raw_material_id:
          String(
            item?.raw_material_id
            || '',
          ),

        quantity:
          quantityString(
            item?.quantity,
          ),

        notes:
          String(
            item?.notes
            || '',
          ),
      }),
    )

  if (
    form.items.length === 0
  ) {
    addItem()
  }

  serverErrors.value = {}
  errorMessage.value = ''
  successMessage.value = ''
}


function resetForm()
{
  editingId.value = null

  form.name = ''
  form.description = ''
  form.is_active = true
  form.items = []

  serverErrors.value = {}
  errorMessage.value = ''
  successMessage.value = ''

  addItem()
}


function addItem()
{
  if (
    !canAddItem.value
    &&
    form.items.length > 0
  ) {
    return
  }

  form.items.push({
    row_key:
      `template-${++rowSequence}`,

    raw_material_id:
      '',

    quantity:
      '',

    notes:
      '',
  })
}


function removeItem(
  index,
)
{
  if (
    saving.value
    ||
    form.items.length <= 1
  ) {
    return
  }

  form.items.splice(
    index,
    1,
  )
}


function selectedElsewhere(
  rawMaterialId,
  rowKey,
)
{
  const id =
    Number(
      rawMaterialId
    )

  return form.items.some(
    (item) =>
      item.row_key !== rowKey
      &&
      Number(
        item.raw_material_id
      )
      ===
      id,
  )
}


function sanitizeQuantity(
  item,
)
{
  let value =
    String(
      item?.quantity
      ??
      '',
    )

  value =
    value.replace(
      /[^\d.]/g,
      '',
    )

  const dot =
    value.indexOf('.')

  if (
    dot >= 0
  ) {
    value =
      value.slice(
        0,
        dot + 1,
      )
      +
      value
        .slice(
          dot + 1,
        )
        .replace(
          /\./g,
          '',
        )
        .slice(
          0,
          4,
        )
  }

  item.quantity = value
}


async function saveTemplate()
{
  if (
    saving.value
    ||
    !formReady.value
  ) {
    return
  }

  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''
  serverErrors.value = {}

  const payload = {
    name:
      form.name.trim(),

    description:
      form.description.trim()
      ||
      null,

    is_active:
      Boolean(
        form.is_active
      ),

    items:
      form.items.map(
        (item) => ({
          raw_material_id:
            Number(
              item.raw_material_id
            ),

          quantity:
            quantityString(
              item.quantity
            ),

          notes:
            item.notes.trim()
            ||
            null,
        }),
      ),
  }

  try {
    let response

    if (
      editingId.value
    ) {
      response =
        await stockTransferTemplateService
          .updateTemplate(
            editingId.value,
            payload,
          )
    } else {
      response =
        await stockTransferTemplateService
          .createTemplate(
            payload,
          )
    }

    successMessage.value =
      response?.message
      ||
      'Transfer template saved successfully.'

    await loadTemplates()

    const savedId =
      Number(
        response?.data?.id
        || 0,
      )

    if (
      savedId
    ) {
      const saved =
        templates.value.find(
          (template) =>
            Number(template.id)
            ===
            savedId,
        )

      if (
        saved
      ) {
        startEdit(saved)
      }
    }

    emit(
      'changed',
      response?.data
      ||
      null,
    )
  } catch (error) {
    serverErrors.value =
      stockTransferTemplateService
        .getValidationErrors(
          error,
        )

    errorMessage.value =
      stockTransferTemplateService
        .getErrorMessage(
          error,
          'Unable to save transfer template.',
        )
  } finally {
    saving.value = false
  }
}


async function toggleStatus(
  template,
)
{
  if (
    saving.value
    ||
    !template?.id
  ) {
    return
  }

  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const response =
      await stockTransferTemplateService
        .updateStatus(
          template.id,
          !template.is_active,
        )

    successMessage.value =
      response?.message
      ||
      'Template status updated.'

    await loadTemplates()

    if (
      Number(editingId.value)
      ===
      Number(template.id)
    ) {
      const updated =
        templates.value.find(
          (item) =>
            Number(item.id)
            ===
            Number(template.id),
        )

      if (
        updated
      ) {
        startEdit(updated)
      }
    }

    emit(
      'changed',
      response?.data
      ||
      null,
    )
  } catch (error) {
    errorMessage.value =
      stockTransferTemplateService
        .getErrorMessage(
          error,
          'Unable to update template status.',
        )
  } finally {
    saving.value = false
  }
}


async function deleteTemplate(
  template,
)
{
  if (
    saving.value
    ||
    deletingId.value
    ||
    !template?.id
  ) {
    return
  }

  const confirmed =
    window.confirm(
      `Delete the transfer template "${template.name}"? Historical stock transfers will not be affected.`,
    )

  if (
    !confirmed
  ) {
    return
  }

  deletingId.value =
    template.id

  errorMessage.value = ''
  successMessage.value = ''

  try {
    const response =
      await stockTransferTemplateService
        .deleteTemplate(
          template.id,
        )

    if (
      Number(editingId.value)
      ===
      Number(template.id)
    ) {
      resetForm()
    }

    successMessage.value =
      response?.message
      ||
      'Transfer template deleted.'

    await loadTemplates()

    emit(
      'changed',
      null,
    )
  } catch (error) {
    errorMessage.value =
      stockTransferTemplateService
        .getErrorMessage(
          error,
          'Unable to delete transfer template.',
        )
  } finally {
    deletingId.value = null
  }
}


function itemError(
  index,
)
{
  const keys = [
    `items.${index}.raw_material_id`,
    `items.${index}.quantity`,
    `items.${index}.notes`,
  ]

  for (
    const key
    of keys
  ) {
    const value =
      serverErrors.value?.[key]

    if (
      Array.isArray(value)
      &&
      value.length
    ) {
      return String(
        value[0]
      )
    }

    if (
      typeof value === 'string'
      &&
      value
    ) {
      return value
    }
  }

  return ''
}


function parseQuantity(
  value,
)
{
  const raw =
    String(
      value
      ??
      '',
    ).trim()

  if (
    !/^\d+(?:\.\d{0,4})?$/.test(
      raw,
    )
  ) {
    return null
  }

  const number =
    Number(raw)

  return Number.isFinite(number)
    ? number
    : null
}


function quantityString(
  value,
)
{
  const number =
    Number(value)

  if (
    !Number.isFinite(number)
  ) {
    return ''
  }

  return number
    .toFixed(4)
    .replace(
      /0+$/,
      '',
    )
    .replace(
      /\.$/,
      '',
    )
}


function requestClose()
{
  if (
    saving.value
  ) {
    return
  }

  emit('close')
}


watch(
  () => props.show,
  async (show) => {

    if (
      !show
    ) {
      return
    }

    resetForm()

    await Promise.all([
      loadTemplates(),
      loadMaterials(),
    ])
  },
)
</script>
