<template>
  <main class="pickup-display" :class="{ 'is-fullscreen': isFullscreen }">
    <header class="pickup-header">
      <div class="brand-block">
        <img v-if="restaurantLogo" class="restaurant-logo" :src="restaurantLogo" :alt="`${restaurantName} logo`" />
        <span v-else class="restaurant-logo-fallback"><i class="bi bi-shop"></i></span>
        <div>
          <p class="pickup-eyebrow">{{ restaurantName || 'TAKEAWAY ORDERS' }}</p>
          <h1>Order Pickup</h1>
          <p class="pickup-subtitle">Watch your token. We’ll show it here when it’s ready.</p>
        </div>
      </div>
      <div class="pickup-header-actions">
        <span class="pickup-live"><i></i> Live updates</span>
        <button type="button" class="control-button" :aria-pressed="soundEnabled" @click="toggleSound">
          <i :class="soundEnabled ? 'bi bi-volume-up' : 'bi bi-volume-mute'"></i>
          Sound {{ soundEnabled ? 'On' : 'Off' }}
        </button>
        <button type="button" class="control-button" @click="toggleFullscreen">
          <i :class="isFullscreen ? 'bi bi-fullscreen-exit' : 'bi bi-fullscreen'"></i>
          {{ isFullscreen ? 'Exit full screen' : 'Full screen' }}
        </button>
      </div>
    </header>

    <div v-if="newlyReadyTokens.length" class="pickup-ready-alert" role="status" aria-live="assertive">
      <i class="bi bi-bell-fill"></i>
      <span><strong>{{ newlyReadyTokens.join(', ') }}</strong> {{ newlyReadyTokens.length === 1 ? 'is' : 'are' }} ready for pickup</span>
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
        <div v-else class="pickup-order-grid" :style="gridStyle(preparingOrders.length)">
          <article v-for="order in preparingOrders" :key="order.id" class="pickup-order-card" :class="{ 'is-delayed': isDelayed(order) }">
            <strong class="pickup-token">{{ order.token }}</strong>
            <span class="pickup-wait" :class="{ 'is-delayed-label': isDelayed(order) }">
              <i :class="isDelayed(order) ? 'bi bi-exclamation-triangle-fill' : 'bi bi-clock'"></i>
              {{ isDelayed(order) ? 'Delayed' : `Est. ${estimatedWait(order)} min` }}
            </span>
            <p class="order-details">{{ itemSummary(order) }}</p>
          </article>
        </div>
      </article>

      <article class="pickup-column ready-column">
        <header class="pickup-column-heading">
          <span class="pickup-status-icon"><i class="bi bi-check2-circle"></i></span>
          <div><h2>Ready for Pickup</h2><p>Please collect at the counter</p></div>
          <strong>{{ readyOrders.length }}</strong>
        </header>
        <div v-if="!readyOrders.length" class="pickup-empty">No orders ready yet</div>
        <div v-else class="pickup-order-grid" :style="gridStyle(readyOrders.length)">
          <article v-for="order in readyOrders" :key="order.id" class="pickup-order-card ready-order-card">
            <strong class="pickup-token">{{ order.token }}</strong>
            <span class="pickup-ready-label"><i class="bi bi-bell-fill"></i> READY</span>
            <p class="order-details">{{ itemSummary(order) }}</p>
          </article>
        </div>
      </article>
    </section>
    <footer class="pickup-footer">Please show your token at the pickup counter.</footer>
  </main>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import orderService from '@/services/orderService'
import { useSystemSettingStore } from '@/stores/systemSetting'

const settingsStore = useSystemSettingStore()
const orders = ref([])
const errorMessage = ref('')
const newlyReadyTokens = ref([])
const soundEnabled = ref(false)
const isFullscreen = ref(false)
const audioContext = ref(null)
let pollTimer = null
let alertTimer = null
let hasInitialBoard = false
let previousReadyIds = new Set()

const restaurantName = computed(() => settingsStore.restaurantName)
const restaurantLogo = computed(() => settingsStore.restaurantLogo)
const preparingOrders = computed(() => orders.value.filter((order) => order.status === 'preparing'))
const readyOrders = computed(() => orders.value.filter((order) => order.status === 'ready'))

function ageInMinutes(order) {
  const createdAt = Date.parse(order.created_at || '')
  return Number.isFinite(createdAt) ? Math.max(0, Math.floor((Date.now() - createdAt) / 60000)) : 0
}

function estimatedWait(order) {
  return Math.max(1, 15 - ageInMinutes(order))
}

function isDelayed(order) {
  return ageInMinutes(order) >= 20
}

function itemSummary(order) {
  return (order.items || []).map((item) => `${item.name}${item.variant ? ` · ${item.variant}` : ''} ×${item.quantity}`).join('  •  ')
}

function gridStyle(count) {
  const columns = Math.max(1, Math.ceil(Math.sqrt(count * 1.6)))
  return { '--card-columns': columns }
}

async function loadBoard() {
  try {
    const response = await orderService.getPickupBoard()
    const nextOrders = Array.isArray(response?.data) ? response.data : []
    const nextReady = nextOrders.filter((order) => order.status === 'ready')
    const nextReadyIds = new Set(nextReady.map((order) => String(order.id)))
    if (hasInitialBoard) {
      const newReady = nextReady.filter((order) => !previousReadyIds.has(String(order.id)))
      if (newReady.length) {
        newlyReadyTokens.value = newReady.map((order) => order.token)
        clearTimeout(alertTimer)
        alertTimer = setTimeout(() => { newlyReadyTokens.value = [] }, 12000)
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

async function toggleSound() {
  if (soundEnabled.value) {
    soundEnabled.value = false
    localStorage.setItem('pickup_display_sound', 'false')
    return
  }
  try {
    const AudioContextClass = window.AudioContext || window.webkitAudioContext
    if (!AudioContextClass) return
    if (!audioContext.value) audioContext.value = new AudioContextClass()
    await audioContext.value.resume()
    soundEnabled.value = true
    localStorage.setItem('pickup_display_sound', 'true')
    playChime()
  } catch {
    soundEnabled.value = false
  }
}

async function toggleFullscreen() {
  try {
    if (document.fullscreenElement) await document.exitFullscreen()
    else await document.documentElement.requestFullscreen()
  } catch {
    errorMessage.value = 'Full-screen mode is unavailable in this browser.'
  }
}

function syncFullscreenState() {
  isFullscreen.value = Boolean(document.fullscreenElement)
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
  settingsStore.fetchSettings()
  loadBoard()
  pollTimer = setInterval(loadBoard, 5000)
  document.addEventListener('fullscreenchange', syncFullscreenState)
})

onBeforeUnmount(() => {
  clearInterval(pollTimer)
  clearTimeout(alertTimer)
  document.removeEventListener('fullscreenchange', syncFullscreenState)
  audioContext.value?.close?.()
})
</script>

<style scoped>
.pickup-display{box-sizing:border-box;display:flex;width:100%;height:100vh;min-height:0;flex-direction:column;gap:clamp(10px,1.4vh,20px);overflow:hidden;padding:clamp(12px,2vw,30px);background:#f5f3ef;color:#101a32}
.pickup-header{display:flex;flex:none;align-items:center;justify-content:space-between;gap:20px;padding:clamp(12px,1.8vw,26px) clamp(14px,2vw,30px);background:#fff;border:1px solid #eee0cf;border-radius:18px;box-shadow:0 4px 18px #1b24300a}
.brand-block{display:flex;min-width:0;align-items:center;gap:clamp(10px,1.5vw,22px)}.restaurant-logo,.restaurant-logo-fallback{display:grid;width:clamp(48px,6vw,84px);height:clamp(48px,6vw,84px);flex:none;place-items:center;border-radius:14px;object-fit:contain;background:#fff7ec;color:#f36b00;font-size:32px}.brand-block>div{min-width:0}
.pickup-eyebrow{margin:0 0 5px;color:#f36b00;font-size:clamp(10px,1vw,14px);font-weight:800;letter-spacing:.09em}.pickup-header h1{margin:0;font-size:clamp(22px,2.8vw,40px);font-weight:800}.pickup-subtitle{margin:4px 0 0;color:#75809b;font-size:clamp(11px,1vw,15px)}
.pickup-header-actions{display:flex;flex:none;align-items:center;gap:clamp(7px,1vw,14px)}.pickup-live{color:#3a765e;font-size:clamp(10px,.95vw,13px);font-weight:700;white-space:nowrap}.pickup-live i{display:inline-block;width:9px;height:9px;margin-right:6px;border-radius:50%;background:#18a76d;box-shadow:0 0 0 4px #e4f8ed}
.control-button{display:flex;min-height:38px;align-items:center;gap:7px;padding:8px 12px;border:1px solid #e8d9c6;border-radius:9px;background:#fff;color:#ed6500;font-size:clamp(10px,.95vw,13px);font-weight:700;white-space:nowrap}.control-button i{font-size:16px}
.pickup-ready-alert{display:flex;flex:none;align-items:center;gap:12px;padding:clamp(9px,1vw,16px) 20px;border:1px solid #a6e4c4;border-radius:12px;background:#eafaf1;color:#087a49;font-size:clamp(14px,1.5vw,20px)}.pickup-ready-alert>i{font-size:22px}
.pickup-error{flex:none;padding:10px 14px;border-radius:9px;background:#fff0ee;color:#bd2b20;font-size:14px}
.pickup-columns{display:grid;min-height:0;flex:1;grid-template-columns:repeat(2,minmax(0,1fr));gap:clamp(10px,1.4vw,20px)}.pickup-column{display:flex;min-width:0;min-height:0;flex-direction:column;overflow:hidden;padding:clamp(10px,1.5vw,22px);background:#fff;border:1px solid #eee0cf;border-radius:18px;box-shadow:0 4px 18px #1b24300a}
.pickup-column-heading{display:flex;flex:none;align-items:center;gap:clamp(8px,1vw,13px);padding-bottom:clamp(8px,1.2vh,17px);border-bottom:1px solid #f0e7dc}.pickup-status-icon{display:grid;width:clamp(34px,4vw,48px);height:clamp(34px,4vw,48px);flex:none;place-items:center;border-radius:13px;background:#fff1e2;color:#f36b00;font-size:clamp(17px,2vw,22px)}.ready-column .pickup-status-icon{background:#e6f8ee;color:#07834e}.pickup-column-heading h2{margin:0;font-size:clamp(16px,2vw,23px);font-weight:800}.pickup-column-heading p{margin:3px 0 0;color:#78829c;font-size:clamp(10px,1vw,13px)}.pickup-column-heading>strong{display:grid;width:clamp(30px,3.2vw,38px);height:clamp(30px,3.2vw,38px);flex:none;place-items:center;margin-left:auto;border-radius:50%;background:#fff2e2;color:#e96400;font-size:clamp(14px,1.5vw,18px)}.ready-column .pickup-column-heading>strong{background:#e6f8ee;color:#07834e}
.pickup-empty{display:grid;min-height:0;flex:1;place-items:center;color:#8991a4;font-size:clamp(14px,1.5vw,20px)}.pickup-order-grid{display:grid;min-height:0;flex:1;grid-template-columns:repeat(var(--card-columns),minmax(0,1fr));grid-auto-rows:minmax(0,1fr);gap:clamp(6px,.8vw,12px);overflow:hidden;padding-top:clamp(7px,1vh,14px)}.pickup-order-card{display:flex;min-width:0;min-height:0;flex-direction:column;justify-content:center;overflow:hidden;padding:clamp(7px,1vw,16px);border:1px solid #eee0cf;border-left:5px solid #f47709;border-radius:13px;background:#fffdfa}.ready-order-card{border-color:#b7e8ce;border-left-color:#15955b;background:#f7fff9}.pickup-order-card.is-delayed{border-color:#efc078;border-left-color:#dc5b22;background:#fff8ed}.pickup-token{display:block;overflow:hidden;color:#101a32;font-size:clamp(24px,4vw,64px);font-weight:900;letter-spacing:.04em;line-height:1.05;text-overflow:ellipsis;white-space:nowrap}.pickup-wait{display:inline-flex;align-self:flex-start;align-items:center;gap:5px;margin-top:clamp(3px,.5vh,8px);color:#64748b;font-size:clamp(9px,.9vw,13px);font-weight:700}.pickup-wait.is-delayed-label{color:#b54708}.order-details{display:-webkit-box;overflow:hidden;margin:clamp(4px,.7vh,10px) 0 0;color:#57627c;font-size:clamp(10px,1.1vw,15px);line-height:1.3;-webkit-box-orient:vertical;-webkit-line-clamp:2}.pickup-ready-label{display:inline-flex;align-items:center;gap:5px;margin-top:clamp(4px,.7vh,10px);padding:5px 8px;align-self:flex-start;border-radius:99px;background:#dff6e9;color:#07834e;font-size:clamp(9px,.9vw,12px);font-weight:900;letter-spacing:.04em}.pickup-footer{flex:none;padding:2px;text-align:center;color:#78829c;font-size:clamp(10px,.95vw,13px);font-weight:600}
@media(max-width:760px){.pickup-display{height:100dvh;padding:8px;gap:7px}.pickup-header{align-items:flex-start;padding:10px;gap:8px}.brand-block{gap:8px}.restaurant-logo,.restaurant-logo-fallback{width:38px;height:38px;border-radius:9px;font-size:21px}.pickup-header-actions{flex-wrap:wrap;justify-content:flex-end;gap:5px}.pickup-live{display:none}.control-button{min-height:32px;padding:6px 8px}.pickup-columns{gap:7px}.pickup-column{padding:8px;border-radius:12px}.pickup-column-heading{gap:6px}.pickup-column-heading p{display:none}.pickup-order-grid{grid-template-columns:repeat(min(var(--card-columns),2),minmax(0,1fr));gap:5px}.pickup-order-card{padding:6px;border-left-width:3px;border-radius:8px}.pickup-token{font-size:clamp(20px,6vw,38px)}.order-details{font-size:9px}}
</style>
