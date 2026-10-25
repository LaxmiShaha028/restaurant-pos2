<template>
  <main class="pickup-display">
    <header class="pickup-header">
      <div>
        <p class="pickup-eyebrow">TAKEAWAY ORDERS</p>
        <h1>Order Pickup</h1>
        <p class="pickup-subtitle">Watch your token. We’ll show it here when it’s ready.</p>
      </div>
      <div class="pickup-header-actions">
        <span class="pickup-live"><i></i> Live updates</span>
        <button type="button" class="pickup-sound-button" @click="enableSound">
          <i :class="soundEnabled ? 'bi bi-volume-up' : 'bi bi-volume-mute'"></i>
          {{ soundEnabled ? 'Sound on' : 'Enable sound' }}
        </button>
      </div>
    </header>

    <div v-if="newlyReadyToken" class="pickup-ready-alert" role="status" aria-live="assertive">
      <i class="bi bi-bell-fill"></i>
      <span><strong>{{ newlyReadyToken }}</strong> is ready for pickup</span>
      <button type="button" aria-label="Dismiss notification" @click="newlyReadyToken = ''">×</button>
    </div>

    <div v-if="errorMessage" class="pickup-error" role="alert">{{ errorMessage }}</div>

    <section class="pickup-columns" aria-label="Takeaway order status">
      <article class="pickup-column preparing-column">
        <header class="pickup-column-heading">
          <span class="pickup-status-icon"><i class="bi bi-fire"></i></span>
          <div><h2>Preparing</h2><p>We’re making your order</p></div>
          <strong>{{ preparingOrders.length }}</strong>
        </header>
        <div v-if="!preparingOrders.length" class="pickup-empty">No orders preparing</div>
        <div v-for="order in preparingOrders" :key="order.id" class="pickup-order-card">
          <strong class="pickup-token">{{ order.token }}</strong>
          <ul><li v-for="(item, index) in order.items" :key="`${order.id}-${index}`">
            {{ item.name }}<span v-if="item.variant"> · {{ item.variant }}</span><b>×{{ item.quantity }}</b>
          </li></ul>
        </div>
      </article>

      <article class="pickup-column ready-column">
        <header class="pickup-column-heading">
          <span class="pickup-status-icon"><i class="bi bi-check2-circle"></i></span>
          <div><h2>Ready for Pickup</h2><p>Please collect at the counter</p></div>
          <strong>{{ readyOrders.length }}</strong>
        </header>
        <div v-if="!readyOrders.length" class="pickup-empty">No orders ready yet</div>
        <div v-for="order in readyOrders" :key="order.id" class="pickup-order-card ready-order-card">
          <strong class="pickup-token">{{ order.token }}</strong>
          <span class="pickup-ready-label"><i class="bi bi-bell-fill"></i> READY</span>
          <ul><li v-for="(item, index) in order.items" :key="`${order.id}-${index}`">
            {{ item.name }}<span v-if="item.variant"> · {{ item.variant }}</span><b>×{{ item.quantity }}</b>
          </li></ul>
        </div>
      </article>
    </section>
    <footer class="pickup-footer">Please show your token at the pickup counter.</footer>
  </main>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import orderService from '@/services/orderService'

const orders = ref([])
const errorMessage = ref('')
const newlyReadyToken = ref('')
const soundEnabled = ref(false)
const audioContext = ref(null)
let pollTimer = null
let alertTimer = null
let hasInitialBoard = false
let previousReadyIds = new Set()

const preparingOrders = computed(() => orders.value.filter((order) => order.status === 'preparing'))
const readyOrders = computed(() => orders.value.filter((order) => order.status === 'ready'))

async function loadBoard() {
  try {
    const response = await orderService.getPickupBoard()
    const nextOrders = Array.isArray(response?.data) ? response.data : []
    const nextReady = nextOrders.filter((order) => order.status === 'ready')
    const nextReadyIds = new Set(nextReady.map((order) => String(order.id)))

    if (hasInitialBoard) {
      const newlyReady = nextReady.find((order) => !previousReadyIds.has(String(order.id)))
      if (newlyReady) {
        newlyReadyToken.value = newlyReady.token
        clearTimeout(alertTimer)
        alertTimer = setTimeout(() => { newlyReadyToken.value = '' }, 12000)
        playChime()
      }
    }

    previousReadyIds = nextReadyIds
    hasInitialBoard = true
    orders.value = nextOrders
    errorMessage.value = ''
  } catch (error) {
    errorMessage.value = orderService.getOrderErrorMessage(error, 'Pickup display could not load orders.')
  }
}

async function enableSound() {
  try {
    const AudioContextClass = window.AudioContext || window.webkitAudioContext
    if (!AudioContextClass) return
    if (!audioContext.value) audioContext.value = new AudioContextClass()
    await audioContext.value.resume()
    soundEnabled.value = true
  } catch {
    soundEnabled.value = false
  }
}

function playChime() {
  const context = audioContext.value
  if (!soundEnabled.value || !context) return
  const oscillator = context.createOscillator()
  const gain = context.createGain()
  oscillator.type = 'sine'
  oscillator.frequency.setValueAtTime(880, context.currentTime)
  oscillator.frequency.setValueAtTime(1174, context.currentTime + 0.18)
  gain.gain.setValueAtTime(0.0001, context.currentTime)
  gain.gain.exponentialRampToValueAtTime(0.18, context.currentTime + 0.03)
  gain.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + 0.55)
  oscillator.connect(gain)
  gain.connect(context.destination)
  oscillator.start()
  oscillator.stop(context.currentTime + 0.56)
}

onMounted(() => {
  loadBoard()
  pollTimer = setInterval(loadBoard, 5000)
})

onBeforeUnmount(() => {
  clearInterval(pollTimer)
  clearTimeout(alertTimer)
  audioContext.value?.close?.()
})
</script>

<style scoped>
.pickup-display{min-height:100vh;padding:clamp(18px,3vw,42px);background:#f5f3ef;color:#101a32}
.pickup-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:26px 30px;background:#fff;border:1px solid #eee0cf;border-radius:18px;box-shadow:0 4px 18px #1b24300a}
.pickup-eyebrow{margin:0 0 5px;color:#f36b00;font-size:12px;font-weight:800;letter-spacing:.09em}
.pickup-header h1{margin:0;font-size:clamp(26px,3vw,40px);font-weight:800}
.pickup-subtitle{margin:6px 0 0;color:#75809b}
.pickup-header-actions{display:flex;align-items:center;gap:14px}.pickup-live{color:#3a765e;font-size:13px;font-weight:700}.pickup-live i{display:inline-block;width:9px;height:9px;margin-right:6px;border-radius:50%;background:#18a76d;box-shadow:0 0 0 4px #e4f8ed}
.pickup-sound-button{padding:10px 15px;border:1px solid #e8d9c6;border-radius:9px;background:#fff;color:#ed6500;font-weight:700}.pickup-sound-button i{margin-right:6px}
.pickup-ready-alert{display:flex;align-items:center;gap:12px;margin:18px 0;padding:16px 20px;border:1px solid #a6e4c4;border-radius:12px;background:#eafaf1;color:#087a49;font-size:18px}.pickup-ready-alert>i{font-size:22px}.pickup-ready-alert button{margin-left:auto;border:0;background:transparent;color:inherit;font-size:24px}
.pickup-error{margin:14px 0;padding:12px 16px;border-radius:9px;background:#fff0ee;color:#bd2b20}
.pickup-columns{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;margin-top:20px}.pickup-column{min-height:58vh;padding:22px;background:#fff;border:1px solid #eee0cf;border-radius:18px;box-shadow:0 4px 18px #1b24300a}
.pickup-column-heading{display:flex;align-items:center;gap:13px;padding-bottom:17px;border-bottom:1px solid #f0e7dc}.pickup-status-icon{display:grid;place-items:center;width:48px;height:48px;border-radius:13px;background:#fff1e2;color:#f36b00;font-size:22px}.ready-column .pickup-status-icon{background:#e6f8ee;color:#07834e}.pickup-column-heading h2{margin:0;font-size:23px;font-weight:800}.pickup-column-heading p{margin:3px 0 0;color:#78829c;font-size:13px}.pickup-column-heading>strong{display:grid;place-items:center;width:38px;height:38px;margin-left:auto;border-radius:50%;background:#fff2e2;color:#e96400;font-size:18px}.ready-column .pickup-column-heading>strong{background:#e6f8ee;color:#07834e}
.pickup-empty{display:grid;min-height:250px;place-items:center;color:#8991a4}.pickup-order-card{position:relative;margin-top:14px;padding:18px 20px;border:1px solid #eee0cf;border-left:5px solid #f47709;border-radius:13px;background:#fffdfa}.ready-order-card{border-color:#b7e8ce;border-left-color:#15955b;background:#f7fff9}.pickup-token{display:block;color:#101a32;font-size:clamp(27px,3vw,40px);font-weight:900;letter-spacing:.04em}.pickup-order-card ul{display:grid;gap:6px;margin:12px 0 0;padding:0;list-style:none;color:#57627c}.pickup-order-card li{display:flex;gap:5px;align-items:center}.pickup-order-card li b{margin-left:auto;color:#101a32}.pickup-ready-label{position:absolute;top:20px;right:20px;padding:7px 10px;border-radius:99px;background:#dff6e9;color:#07834e;font-size:12px;font-weight:900;letter-spacing:.04em}.pickup-footer{padding:19px;text-align:center;color:#78829c;font-weight:600}
@media(max-width:760px){.pickup-header{align-items:flex-start;flex-direction:column}.pickup-header-actions{width:100%;justify-content:space-between}.pickup-columns{grid-template-columns:1fr}.pickup-column{min-height:38vh}.pickup-empty{min-height:140px}}
</style>
