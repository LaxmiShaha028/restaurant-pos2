<template>
  <Teleport to="body">
    <div v-if="show && item" class="pos-modal-backdrop" @click.self="close">
      <form class="pos-modal pos-food-modal" @submit.prevent="submit">
        <header class="pos-modal-header">
          <div class="pos-modal-title">
            <span><i class="bi bi-file-earmark-text"></i></span>
            <div><p>Customize Item</p><h2>Food Details</h2></div>
          </div>
          <button type="button" class="pos-modal-close" @click="close"><i class="bi bi-x-lg"></i></button>
        </header>

        <div class="pos-food-modal-product">
          <div class="pos-food-modal-image">
            <img v-if="imageUrl" :src="imageUrl" :alt="item.menu_name" />
            <i v-else class="bi bi-cup-hot"></i>
          </div>
          <div>
            <h3>{{ item.menu_name }}</h3>
            <p>{{ posOrderService.money(basePrice) }}</p>
          </div>
        </div>

        <div class="pos-modal-body">
          <section v-if="variants.length" class="pos-option-section">
            <div class="pos-option-heading"><h3>Variant</h3><span>Required</span></div>
            <div class="pos-option-pills">
              <label
                v-for="variant in variants"
                :key="variant.id"
                class="pos-option-pill"
                :class="{ selected: String(selectedVariantId) === String(variant.id) }"
              >
                <input v-model="selectedVariantId" type="radio" name="pos-variant" :value="String(variant.id)" />
                <span>{{ variant.variant_name || variant.name || `Variant ${variant.id}` }}</span>
                <strong>{{ posOrderService.money(variant.price) }}</strong>
              </label>
            </div>
          </section>

          <section v-if="addons.length" class="pos-option-section">
            <div class="pos-option-heading"><h3>Add-ons</h3><span>Optional</span></div>
            <div class="pos-option-pills">
              <label
                v-for="addon in addons"
                :key="addon.id"
                class="pos-option-pill"
                :class="{ selected: selectedAddonIds.includes(String(addon.id)) }"
              >
                <input v-model="selectedAddonIds" type="checkbox" :value="String(addon.id)" />
                <span>{{ addon.add_on_name || addon.name || `Add-on ${addon.id}` }}</span>
                <strong>+{{ posOrderService.money(addon.price) }}</strong>
              </label>
            </div>
          </section>

          <section class="pos-option-section">
            <div class="pos-option-heading"><h3>Kitchen Note</h3><span>Optional</span></div>
            <textarea v-model.trim="kitchenNote" rows="3" placeholder="No onion, less spicy, allergy note..."></textarea>
          </section>

          <p v-if="validationError" class="pos-field-error">{{ validationError }}</p>
        </div>

        <footer class="pos-food-modal-footer">
          <div class="pos-quantity-control">
            <span>Qty</span>
            <button type="button" @click="decrease"><i class="bi bi-dash"></i></button>
            <strong>{{ quantity }}</strong>
            <button type="button" @click="quantity += 1"><i class="bi bi-plus"></i></button>
          </div>

          <button type="submit" class="pos-primary-button pos-add-item-button">
            <span>Total {{ posOrderService.money(lineTotal) }}</span>
            <span class="divider"></span>
            Add to Order
            <i class="bi bi-arrow-right"></i>
          </button>
        </footer>
      </form>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import posOrderService from '@/services/posOrderService'

const props = defineProps({
  show: { type: Boolean, default: false },
  item: { type: Object, default: null },
})

const emit = defineEmits(['close', 'add'])
const selectedVariantId = ref('')
const selectedAddonIds = ref([])
const quantity = ref(1)
const kitchenNote = ref('')
const validationError = ref('')

const variants = computed(() =>
  Array.isArray(props.item?.variants) ? props.item.variants : [],
)

const addons = computed(() => {
  const rows = props.item?.add_ons ?? props.item?.addOns ?? props.item?.addons ?? []
  return Array.isArray(rows) ? rows : []
})

const basePrice = computed(() => Number(props.item?.price ?? props.item?.base_price ?? 0) || 0)
const selectedVariant = computed(() => variants.value.find((v) => String(v.id) === String(selectedVariantId.value)) ?? null)
const selectedAddons = computed(() => addons.value.filter((a) => selectedAddonIds.value.includes(String(a.id))))
const unitPrice = computed(() => selectedVariant.value ? Number(selectedVariant.value?.price || 0) : basePrice.value)
const addonsPrice = computed(() => selectedAddons.value.reduce((sum, addon) => sum + Number(addon?.price || 0), 0))
const lineTotal = computed(() => Number(((unitPrice.value + addonsPrice.value) * quantity.value).toFixed(2)))
const imageUrl = computed(() => posOrderService.resolveMenuImage(props.item))

function reset() {
  selectedVariantId.value = ''
  selectedAddonIds.value = []
  quantity.value = 1
  kitchenNote.value = ''
  validationError.value = ''
}

function decrease() {
  quantity.value = Math.max(1, quantity.value - 1)
}

function close() {
  emit('close')
}

function submit() {
  validationError.value = ''

  if (variants.value.length && !selectedVariantId.value) {
    validationError.value = 'Please select a variant.'
    return
  }

  emit('add', {
    menu_item_id: Number(props.item.id),
    menu_item_variant_id: selectedVariantId.value ? Number(selectedVariantId.value) : null,
    addon_ids: selectedAddonIds.value.map(Number),
    quantity: quantity.value,
    kitchen_note: kitchenNote.value,
  })
}

watch(() => props.show, (show) => {
  if (show) reset()
})
</script>
