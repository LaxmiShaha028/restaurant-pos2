<template>
  <div ref="pickerRoot" class="reservation-filter-date-picker">
    <button
      :id="id"
      type="button"
      class="reservation-filter-date-trigger"
      :aria-expanded="open"
      aria-haspopup="dialog"
      @click="togglePicker"
      @keydown.esc="closePicker"
    >
      <span :class="{ 'is-placeholder': !modelValue }">{{ displayValue || 'mm/dd/yyyy' }}</span>
      <i class="bi bi-calendar3" aria-hidden="true"></i>
    </button>

    <Transition name="reservation-filter-date-menu">
      <div v-if="open" class="reservation-filter-date-menu" role="dialog" :aria-label="`Choose ${label}`">
        <div class="reservation-filter-date-heading">
          <strong>{{ monthLabel }}</strong>
          <div>
            <button type="button" aria-label="Previous month" @click="moveMonth(-1)"><i class="bi bi-chevron-left"></i></button>
            <button type="button" aria-label="Next month" @click="moveMonth(1)"><i class="bi bi-chevron-right"></i></button>
          </div>
        </div>
        <div class="reservation-filter-date-weekdays" aria-hidden="true">
          <span v-for="weekday in weekdays" :key="weekday">{{ weekday }}</span>
        </div>
        <div class="reservation-filter-date-days" role="grid" :aria-label="monthLabel">
          <button
            v-for="day in days"
            :key="day.key"
            type="button"
            role="gridcell"
            :class="{ 'is-outside': !day.isCurrentMonth, 'is-selected': day.key === modelValue, 'is-today': day.key === todayKey }"
            :aria-label="day.ariaLabel"
            :aria-selected="day.key === modelValue"
            :disabled="day.isBeforeMin"
            @click="selectDate(day.key)"
          >{{ day.day }}</button>
        </div>
        <div class="reservation-filter-date-footer">
          <button type="button" @click="clearDate">Clear</button>
          <button type="button" :disabled="min && todayKey < min" @click="selectDate(todayKey)">Today</button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  min: { type: String, default: '' },
  id: { type: String, required: true },
  label: { type: String, required: true },
})

const emit = defineEmits(['update:modelValue'])
const pickerRoot = ref(null)
const open = ref(false)
const month = ref(monthStart(props.modelValue ? parseDate(props.modelValue) : new Date()))
const weekdays = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']

const todayKey = localDateKey(new Date())
const displayValue = computed(() => {
  if (!props.modelValue) return ''
  const [year, monthNumber, day] = props.modelValue.split('-')
  return `${monthNumber}/${day}/${year}`
})
const monthLabel = computed(() => month.value.toLocaleDateString(undefined, { month: 'long', year: 'numeric' }))
const days = computed(() => {
  const first = month.value
  const start = new Date(first.getFullYear(), first.getMonth(), 1 - first.getDay())
  return Array.from({ length: 42 }, (_, index) => {
    const date = new Date(start.getFullYear(), start.getMonth(), start.getDate() + index)
    return {
      key: localDateKey(date),
      day: date.getDate(),
      isCurrentMonth: date.getMonth() === first.getMonth(),
      isBeforeMin: Boolean(props.min && localDateKey(date) < props.min),
      ariaLabel: date.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }),
    }
  })
})

watch(() => props.modelValue, (value) => {
  if (value) month.value = monthStart(parseDate(value))
})

function parseDate(value) {
  const [year, monthNumber, day] = String(value).slice(0, 10).split('-').map(Number)
  return year && monthNumber && day ? new Date(year, monthNumber - 1, day) : new Date()
}

function monthStart(date) {
  return new Date(date.getFullYear(), date.getMonth(), 1)
}

function localDateKey(date) {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

function togglePicker() {
  if (!open.value) month.value = monthStart(props.modelValue ? parseDate(props.modelValue) : new Date())
  open.value = !open.value
}

function closePicker() {
  open.value = false
}

function moveMonth(offset) {
  month.value = new Date(month.value.getFullYear(), month.value.getMonth() + offset, 1)
}

function selectDate(value) {
  emit('update:modelValue', value)
  closePicker()
}

function clearDate() {
  emit('update:modelValue', '')
  closePicker()
}

function handleDocumentClick(event) {
  if (!pickerRoot.value?.contains(event.target)) closePicker()
}

onMounted(() => document.addEventListener('click', handleDocumentClick))
onBeforeUnmount(() => document.removeEventListener('click', handleDocumentClick))
</script>

<style scoped>
.reservation-filter-date-picker { position: relative; width: 100%; }
.reservation-filter-date-trigger {
  display: flex;
  width: 100%;
  min-height: 28px;
  height: 28px;
  align-items: center;
  justify-content: space-between;
  gap: 6px;
  padding: 0 7px;
  border: 1px solid #e5e7eb;
  border-radius: 5px;
  background: #fff;
  color: #172033;
  font: inherit;
  font-size: 12px;
  text-align: left;
  cursor: pointer;
}
.reservation-filter-date-trigger:hover,
.reservation-filter-date-trigger[aria-expanded="true"] { border-color: #fb923c; }
.reservation-filter-date-trigger:focus-visible { outline: none; border-color: #f97316; box-shadow: 0 0 0 3px rgba(249, 115, 22, .12); }
.reservation-filter-date-trigger .is-placeholder { color: #667085; }
.reservation-filter-date-trigger i { color: #ea580c; }
.reservation-filter-date-menu {
  position: absolute;
  z-index: 1200;
  top: calc(100% + 5px);
  left: 0;
  width: 276px;
  padding: 11px;
  border: 1px solid #f1e3d5;
  border-radius: 10px;
  background: #fff;
  box-shadow: 0 14px 30px rgba(23, 32, 51, .16);
}
.reservation-filter-date-heading, .reservation-filter-date-heading > div, .reservation-filter-date-footer { display: flex; align-items: center; justify-content: space-between; }
.reservation-filter-date-heading { margin-bottom: 9px; color: #172033; font-size: 12px; }
.reservation-filter-date-heading > div { gap: 4px; }
.reservation-filter-date-heading button, .reservation-filter-date-footer button {
  display: grid;
  min-width: 27px;
  height: 27px;
  place-items: center;
  padding: 0 6px;
  border: 0;
  border-radius: 6px;
  background: transparent;
  color: #c2410c;
  font: inherit;
  cursor: pointer;
}
.reservation-filter-date-heading button:hover, .reservation-filter-date-footer button:hover { background: #fff1e6; }
.reservation-filter-date-weekdays, .reservation-filter-date-days { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); text-align: center; }
.reservation-filter-date-weekdays { margin-bottom: 4px; color: #8491a3; font-size: 10px; font-weight: 700; }
.reservation-filter-date-days button {
  display: grid;
  height: 30px;
  place-items: center;
  border: 0;
  border-radius: 6px;
  background: transparent;
  color: #334155;
  font: inherit;
  font-size: 11px;
  cursor: pointer;
}
.reservation-filter-date-days button:hover, .reservation-filter-date-days button.is-today:not(.is-selected) { background: #fff1e6; color: #c2410c; }
.reservation-filter-date-days button.is-outside { color: #a7b0bd; }
.reservation-filter-date-days button.is-selected { background: #f97316; color: #fff; font-weight: 700; }
.reservation-filter-date-days button:disabled { background: transparent; color: #cbd5e1; cursor: not-allowed; }
.reservation-filter-date-footer button:disabled { color: #cbd5e1; cursor: not-allowed; }
.reservation-filter-date-footer { margin-top: 7px; padding-top: 7px; border-top: 1px solid #f1e3d5; }
.reservation-filter-date-footer button { color: #c2410c; font-size: 11px; }
.reservation-filter-date-menu-enter-active, .reservation-filter-date-menu-leave-active { transition: opacity .12s ease, transform .12s ease; }
.reservation-filter-date-menu-enter-from, .reservation-filter-date-menu-leave-to { opacity: 0; transform: translateY(-3px); }
</style>
