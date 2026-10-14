<template>
  <div ref="pickerRoot" class="reservation-time-picker">
    <button
      :id="id"
      type="button"
      class="reservation-time-trigger"
      :class="{ 'reservation-input-error': invalid }"
      :disabled="disabled"
      :aria-expanded="open"
      aria-haspopup="dialog"
      @click="togglePicker"
      @keydown.esc="closePicker"
    >
      <span :class="{ 'is-placeholder': !modelValue }">
        {{ displayValue || placeholder }}
      </span>
      <i class="bi bi-clock" aria-hidden="true"></i>
    </button>

    <Transition name="reservation-time-menu">
      <div
        v-if="open"
        class="reservation-time-menu"
        role="dialog"
        aria-label="Choose time"
      >
        <div class="reservation-time-menu-heading">Select time</div>

        <div class="reservation-time-columns">
          <div class="reservation-time-column">
            <span class="reservation-time-column-label">Hour</span>
            <div class="reservation-time-options" role="listbox" aria-label="Hour">
              <button
                v-for="hour in hours"
                :key="hour"
                type="button"
                role="option"
                :aria-selected="draftHour === hour"
                :class="{ 'is-selected': draftHour === hour }"
                @click="choose('hour', hour)"
              >{{ hour }}</button>
            </div>
          </div>

          <div class="reservation-time-column">
            <span class="reservation-time-column-label">Minute</span>
            <div class="reservation-time-options" role="listbox" aria-label="Minute">
              <button
                v-for="minute in minutes"
                :key="minute"
                type="button"
                role="option"
                :aria-selected="draftMinute === minute"
                :class="{ 'is-selected': draftMinute === minute }"
                @click="choose('minute', minute)"
              >{{ minute }}</button>
            </div>
          </div>

          <div class="reservation-time-column reservation-time-period-column">
            <span class="reservation-time-column-label">Period</span>
            <div class="reservation-time-options" role="listbox" aria-label="AM or PM">
              <button
                v-for="period in periods"
                :key="period"
                type="button"
                role="option"
                :aria-selected="draftPeriod === period"
                :class="{ 'is-selected': draftPeriod === period }"
                @click="choose('period', period)"
              >{{ period }}</button>
            </div>
          </div>
        </div>

        <div class="reservation-time-menu-footer">
          <button type="button" class="time-cancel" @click="closePicker">Cancel</button>
          <button
            type="button"
            class="time-done"
            :disabled="!hasDraftSelection"
            @click="confirmTime"
          >Done</button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  id: { type: String, default: undefined },
  placeholder: { type: String, default: 'Select a time' },
  disabled: { type: Boolean, default: false },
  invalid: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'change'])

const hours = Array.from({ length: 12 }, (_, index) => String(index + 1).padStart(2, '0'))
const minutes = Array.from({ length: 60 }, (_, index) => String(index).padStart(2, '0'))
const periods = ['AM', 'PM']
const pickerRoot = ref(null)
const open = ref(false)
const draftHour = ref('12')
const draftMinute = ref('00')
const draftPeriod = ref('AM')
const hasDraftSelection = ref(false)

const displayValue = computed(() => {
  if (!props.modelValue) return ''
  const [rawHour, rawMinute] = props.modelValue.split(':')
  const hour = Number(rawHour)
  if (!Number.isFinite(hour) || !rawMinute) return ''
  const period = hour >= 12 ? 'PM' : 'AM'
  const displayHour = String(hour % 12 || 12).padStart(2, '0')
  return `${displayHour}:${rawMinute.slice(0, 2)} ${period}`
})

function loadDraft() {
  const [rawHour, rawMinute] = (props.modelValue || '').split(':')
  const hour = Number(rawHour)
  if (rawHour && Number.isFinite(hour)) {
    draftHour.value = String(hour % 12 || 12).padStart(2, '0')
    draftMinute.value = (rawMinute || '00').slice(0, 2).padStart(2, '0')
    draftPeriod.value = hour >= 12 ? 'PM' : 'AM'
  } else {
    draftHour.value = '12'
    draftMinute.value = '00'
    draftPeriod.value = 'AM'
  }
  hasDraftSelection.value = false
}

function togglePicker() {
  if (props.disabled) return
  if (!open.value) loadDraft()
  open.value = !open.value
}

function choose(part, value) {
  if (part === 'hour') draftHour.value = value
  if (part === 'minute') draftMinute.value = value
  if (part === 'period') draftPeriod.value = value
  hasDraftSelection.value = true
}

function confirmTime() {
  if (!hasDraftSelection.value) return
  let hour = Number(draftHour.value) % 12
  if (draftPeriod.value === 'PM') hour += 12
  const value = `${String(hour).padStart(2, '0')}:${draftMinute.value}`
  emit('update:modelValue', value)
  emit('change')
  closePicker()
}

function closePicker() {
  open.value = false
}

function handleOutsideClick(event) {
  if (open.value && !pickerRoot.value?.contains(event.target)) closePicker()
}

onMounted(() => document.addEventListener('pointerdown', handleOutsideClick))
onBeforeUnmount(() => document.removeEventListener('pointerdown', handleOutsideClick))
</script>

<style scoped>
.reservation-time-picker {
  position: relative;
  width: 100%;
}

.reservation-time-trigger {
  display: flex;
  width: 100%;
  min-height: 30px;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 5px 8px;
  border: 1px solid #d0d5dd;
  border-radius: 4px;
  outline: none;
  background: #fff;
  color: #172033;
  font: inherit;
  font-size: 12px;
  text-align: left;
  transition: border-color .15s ease, box-shadow .15s ease;
  cursor: pointer;
}

.reservation-time-trigger:hover,
.reservation-time-trigger[aria-expanded="true"] {
  border-color: #fdba74;
}

.reservation-time-trigger:focus-visible {
  border-color: #f97316;
  box-shadow: 0 0 0 3px rgba(249, 115, 22, .13);
}

.reservation-time-trigger.reservation-input-error {
  border-color: #d92d20;
}

.reservation-time-trigger .is-placeholder {
  color: #98a2b3;
}

.reservation-time-trigger > i {
  color: #475569;
}

.reservation-time-trigger:disabled {
  background: #f9fafb;
  color: #98a2b3;
  cursor: not-allowed;
}

.reservation-time-menu {
  position: absolute;
  z-index: 1200;
  top: calc(100% + 6px);
  left: 0;
  width: 282px;
  padding: 10px;
  border: 1px solid #eee3da;
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 16px 36px rgba(23, 32, 51, .16);
}

.reservation-time-menu-heading {
  margin: 0 0 8px;
  color: #172033;
  font-size: 12px;
  font-weight: 700;
}

.reservation-time-columns {
  display: grid;
  grid-template-columns: 1fr 1fr .86fr;
  gap: 6px;
}

.reservation-time-column-label {
  display: block;
  padding: 4px 0 6px;
  color: #7c8a9d;
  font-size: 9px;
  font-weight: 700;
  text-align: center;
  text-transform: uppercase;
}

.reservation-time-options {
  display: flex;
  max-height: 168px;
  flex-direction: column;
  gap: 2px;
  overflow-y: auto;
  padding: 2px;
  border: 1px solid #f1eee9;
  border-radius: 8px;
  scrollbar-color: #fdba74 #fff7ed;
  scrollbar-width: thin;
}

.reservation-time-options button {
  min-height: 29px;
  flex: 0 0 29px;
  border: 1px solid transparent;
  border-radius: 6px;
  background: transparent;
  color: #334155;
  font: inherit;
  font-size: 11px;
  cursor: pointer;
}

.reservation-time-options button:hover {
  border-color: #fed7aa;
  background: #fff7ed;
  color: #c2410c;
}

.reservation-time-options button.is-selected {
  border-color: #f97316;
  background: #f97316;
  color: #fff;
  font-weight: 700;
}

.reservation-time-menu-footer {
  display: flex;
  justify-content: flex-end;
  gap: 6px;
  margin-top: 9px;
  padding-top: 8px;
  border-top: 1px solid #f1eee9;
}

.reservation-time-menu-footer button {
  min-height: 28px;
  padding: 0 9px;
  border: 1px solid #eee3da;
  border-radius: 7px;
  background: #fff;
  color: #475569;
  font: inherit;
  font-size: 10px;
  font-weight: 600;
  cursor: pointer;
}

.reservation-time-menu-footer .time-done {
  border-color: #f97316;
  background: #f97316;
  color: #fff;
}

.reservation-time-menu-footer .time-done:disabled {
  border-color: #fed7aa;
  background: #fed7aa;
  cursor: not-allowed;
}

.reservation-time-menu-enter-active,
.reservation-time-menu-leave-active {
  transform-origin: top left;
  transition: opacity .14s ease, transform .14s ease;
}

.reservation-time-menu-enter-from,
.reservation-time-menu-leave-to {
  opacity: 0;
  transform: translateY(-4px) scale(.98);
}

@media (max-width: 400px) {
  .reservation-time-menu {
    width: min(282px, calc(100vw - 44px));
  }
}
</style>
