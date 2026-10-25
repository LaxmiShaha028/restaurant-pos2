<template>
  <div ref="selectRoot" class="order-filter-select">
    <button
      type="button"
      class="order-filter-select-trigger"
      :aria-label="label"
      :aria-expanded="open"
      aria-haspopup="listbox"
      :disabled="disabled"
      @click="toggleMenu"
      @keydown.esc="closeMenu"
    >
      <span>{{ selectedLabel }}</span>
      <i class="bi bi-chevron-down" aria-hidden="true"></i>
    </button>

    <Transition name="order-filter-select-menu">
      <div v-if="open" class="order-filter-select-menu" role="listbox" :aria-label="label">
        <button
          type="button"
          class="order-filter-select-option"
          :class="{ 'is-selected': modelValue === '' }"
          role="option"
          :aria-selected="modelValue === ''"
          @click="select('')"
        >
          <span>{{ placeholder }}</span>
          <i v-if="modelValue === ''" class="bi bi-check2" aria-hidden="true"></i>
        </button>
        <button
          v-for="option in options"
          :key="option.value"
          type="button"
          class="order-filter-select-option"
          :class="{ 'is-selected': String(modelValue) === String(option.value) }"
          role="option"
          :aria-selected="String(modelValue) === String(option.value)"
          @click="select(option.value)"
        >
          <span>{{ option.label }}</span>
          <i v-if="String(modelValue) === String(option.value)" class="bi bi-check2" aria-hidden="true"></i>
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  options: { type: Array, default: () => [] },
  placeholder: { type: String, required: true },
  label: { type: String, required: true },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])
const selectRoot = ref(null)
const open = ref(false)
const selectedLabel = computed(() => {
  if (props.modelValue === '' || props.modelValue == null) return props.placeholder
  return props.options.find(option => String(option.value) === String(props.modelValue))?.label || props.placeholder
})

function toggleMenu() {
  open.value = !open.value
}

function closeMenu() {
  open.value = false
}

function select(value) {
  emit('update:modelValue', value)
  closeMenu()
}

function handleDocumentClick(event) {
  if (!selectRoot.value?.contains(event.target)) closeMenu()
}

onMounted(() => document.addEventListener('click', handleDocumentClick))
onBeforeUnmount(() => document.removeEventListener('click', handleDocumentClick))
</script>

<style scoped>
.order-filter-select { position: relative; width: 100%; min-width: 0; }
.order-filter-select-trigger {
  display: flex;
  width: 100%;
  min-height: 28px;
  height: 28px;
  align-items: center;
  justify-content: space-between;
  gap: 7px;
  padding: 3px 9px;
  border: 1px solid #dfe5ec;
  border-radius: 5px;
  background: #fff;
  color: #172033;
  font: inherit;
  font-size: 12px;
  text-align: left;
  cursor: pointer;
  transition: border-color .15s ease, box-shadow .15s ease;
}
.order-filter-select-trigger > span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.order-filter-select-trigger > i { flex: 0 0 auto; color: #ea580c; }
.order-filter-select-trigger:hover,
.order-filter-select-trigger[aria-expanded="true"] { border-color: #fb923c; }
.order-filter-select-trigger:focus-visible { outline: none; border-color: #f97316; box-shadow: 0 0 0 .2rem rgba(249, 115, 22, .14); }
.order-filter-select.invalid .order-filter-select-trigger { border-color: #dc2626; }
.order-filter-select-trigger:disabled { cursor: not-allowed; background: #f9fafb; color: #98a2b3; }
.order-filter-select-menu {
  position: absolute;
  z-index: 1200;
  top: calc(100% + 5px);
  left: 0;
  width: 100%;
  max-height: 240px;
  overflow-y: auto;
  padding: 5px;
  border: 1px solid #f1e3d5;
  border-radius: 9px;
  background: #fff;
  box-shadow: 0 12px 28px rgba(23, 32, 51, .16);
}
.order-filter-select-option {
  display: flex;
  width: 100%;
  min-height: 30px;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 5px 8px;
  border: 0;
  border-radius: 6px;
  background: transparent;
  color: #334155;
  font: inherit;
  font-size: 12px;
  text-align: left;
  cursor: pointer;
}
.order-filter-select-option:hover,
.order-filter-select-option.is-selected { background: #fff1e6; color: #c2410c; }
.order-filter-select-option > i { color: #f97316; }
.order-filter-select-menu-enter-active,
.order-filter-select-menu-leave-active { transition: opacity .12s ease, transform .12s ease; }
.order-filter-select-menu-enter-from,
.order-filter-select-menu-leave-to { opacity: 0; transform: translateY(-3px); }
</style>
