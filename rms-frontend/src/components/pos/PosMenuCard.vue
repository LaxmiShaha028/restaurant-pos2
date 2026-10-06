<template>
  <button
    type="button"
    class="pos-menu-card"
    :disabled="disabled"
    @click="emit('select', item)"
  >
    <div class="pos-menu-card-media">
      <img
        v-if="imageUrl"
        :src="imageUrl"
        :alt="item.menu_name"
        @error="imageFailed = true"
      />

      <div v-else class="pos-menu-card-placeholder">
        <i class="bi bi-cup-hot"></i>
      </div>

      <span v-if="categoryLabel" class="pos-menu-card-category">
        {{ categoryLabel }}
      </span>

      <span v-if="hasOptions" class="pos-menu-card-options">
        <i class="bi bi-sliders"></i>
        Options
      </span>
    </div>

    <div class="pos-menu-card-body">
      <h3>{{ item.menu_name || 'Menu Item' }}</h3>
      <p v-if="item.description">{{ item.description }}</p>

      <div class="pos-menu-card-footer">
        <strong>{{ priceLabel }}</strong>
        <span><i class="bi bi-plus-lg"></i></span>
      </div>
    </div>
  </button>
</template>

<script setup>
import { computed, ref } from 'vue'
import posOrderService from '@/services/posOrderService'

const props = defineProps({
  item: { type: Object, required: true },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['select'])
const imageFailed = ref(false)

const variants = computed(() =>
  Array.isArray(props.item?.variants) ? props.item.variants : [],
)

const addons = computed(() => {
  const rows =
    props.item?.add_ons ??
    props.item?.addOns ??
    props.item?.addons ??
    []

  return Array.isArray(rows) ? rows : []
})

const hasOptions = computed(
  () => variants.value.length > 0 || addons.value.length > 0,
)

const categoryLabel = computed(
  () =>
    props.item?.category?.category_name ??
    props.item?.category_name ??
    '',
)

const imageUrl = computed(() => {
  if (imageFailed.value) return ''
  return posOrderService.resolveMenuImage(props.item)
})

const priceLabel = computed(() => {
  const base = Number(
    props.item?.price ?? props.item?.base_price ?? 0,
  )

  const variantPrices = variants.value
    .map((variant) => Number(variant?.price))
    .filter(Number.isFinite)

  if (variantPrices.length) {
    return `From ${posOrderService.money(Math.min(...variantPrices))}`
  }

  return posOrderService.money(base)
})
</script>
