<template>
  <main class="pickup-display">
    <div class="pickup-utility-controls">
      <button type="button" class="pickup-utility-button pickup-back-button" aria-label="Back to orders" title="Back to orders" @click="goBack">&lt;</button>
    </div>

    <div v-if="newlyReadyTokens.length" class="pickup-ready-alert" role="status" aria-live="assertive">
      <i class="bi bi-bell-fill"></i>
      <span><strong>{{ newlyReadyTokens.join(', ') }}</strong> {{ newlyReadyTokens.length === 1 ? 'is' : 'are' }} ready for pickup</span>
    </div>
    <div v-if="errorMessage" class="pickup-error" role="alert">{{ errorMessage }}</div>

    <section class="pickup-columns" aria-label="Takeaway order status">
      <article class="pickup-column preparing-column">
        <header class="pickup-column-heading">
          <span class="pickup-status-icon"><i class="bi bi-fire"></i></span>
          <div><h2>NOW COOKING</h2><p>In Kitchen &middot; Orders being prepared</p></div>
          <strong>{{ preparingOrders.length }}</strong>
        </header>
        <div v-if="!preparingOrders.length" class="pickup-empty">No orders preparing</div>
        <div v-else ref="preparingOrderList" class="pickup-order-grid" :style="gridStyle(preparingOrders.length)">
          <article v-for="order in preparingOrders" :key="order.id" class="pickup-order-card" :class="{ 'is-delayed': isDelayed(order) }">
            <strong class="pickup-token" :style="tokenStyle(preparingOrders.length)">{{ order.token }}</strong>
            <span class="pickup-wait" :class="{ 'is-delayed-label': isDelayed(order) }">
              <i :class="isDelayed(order) ? 'bi bi-exclamation-triangle-fill' : 'bi bi-clock'"></i>
              {{ isDelayed(order) ? 'Delayed' : `Est. ${estimatedWait(order)} min` }}
            </span>
            <p class="order-details">{{ itemSummary(order) }}</p>
            <div
              class="preparation-progress"
              role="progressbar"
              aria-label="Preparation progress"
              :aria-valuenow="preparationProgress(order)"
              aria-valuemin="0"
              aria-valuemax="100"
            >
              <span :style="{ width: `${preparationProgress(order)}%` }"></span>
            </div>
          </article>
        </div>
      </article>

      <article class="pickup-column ready-column">
        <header class="pickup-column-heading">
          <span class="pickup-status-icon"><i class="bi bi-check2-circle"></i></span>
          <div><h2>READY FOR PICKUP</h2><p>Please proceed to the counter when your number is called</p></div>
          <strong>{{ readyOrders.length }}</strong>
        </header>
        <div v-if="!readyOrders.length" class="pickup-empty">No orders ready yet</div>
        <div v-else ref="readyOrderList" class="pickup-order-grid" :style="gridStyle(readyOrders.length)">
          <article
            v-for="order in readyOrders"
            :key="order.id"
            class="pickup-order-card ready-order-card"
            :class="{ 'is-just-ready': String(order.id) === justReadyOrderId }"
          >
            <strong class="pickup-token" :style="tokenStyle(readyOrders.length)">{{ order.token }}</strong>
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
import { useRouter } from 'vue-router'
import orderService from '@/services/orderService'

const router = useRouter()
const orders = ref([])
const errorMessage = ref('')
const newlyReadyTokens = ref([])
const soundEnabled = ref(localStorage.getItem('pickup_display_sound') === 'true')
const audioContext = ref(null)
const preparingOrderList = ref(null)
const readyOrderList = ref(null)
const justReadyOrderId = ref(null)
let pollTimer = null
let alertTimer = null
let autoScrollTimer = null
let hasInitialBoard = false
let previousReadyIds = new Set()
const autoScrollStates = new WeakMap()

const preparingOrders = computed(() => orders.value.filter((order) => order.status === 'preparing'))
const readyOrders = computed(() => orders.value.filter((order) => order.status === 'ready'))

function ageInMinutes(order) {
  const createdAt = Date.parse(order.created_at || '')
  return Number.isFinite(createdAt) ? Math.max(0, Math.floor((Date.now() - createdAt) / 60000)) : 0
}

function estimatedWait(order) {
  return Math.max(1, 15 - ageInMinutes(order))
}

function preparationProgress(order) {
  return Math.min(100, Math.round((ageInMinutes(order) / 15) * 100))
}

function isDelayed(order) {
  return ageInMinutes(order) >= 20
}

function itemSummary(order) {
  return (order.items || []).map((item) => `${item.name}${item.variant ? ` · ${item.variant}` : ''} ×${item.quantity}`).join('  •  ')
}

function gridStyle(count) {
  const columns = Math.min(count, Math.max(1, Math.ceil(Math.sqrt(count * 1.6))))
  return {
    '--card-columns': columns,
    gridTemplateRows: count <= 4 ? 'minmax(210px, min(24vh, 260px))' : undefined,
  }
}

function tokenStyle(count) {
  return { fontSize: count <= 4 ? 'clamp(26px, 2.2vw, 42px)' : 'clamp(18px, 18cqi, 42px)' }
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
        justReadyOrderId.value = String(newReady[newReady.length - 1].id)
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

async function resumeAudioContext() {
  if (!soundEnabled.value) return
  try {
    const AudioContextClass = window.AudioContext || window.webkitAudioContext
    if (!AudioContextClass) return
    if (!audioContext.value) audioContext.value = new AudioContextClass()
    if (audioContext.value.state !== 'running') await audioContext.value.resume()
  } catch {
    // The browser may require a user gesture on this display before audio can play.
  }
}

function handleDisplayInteraction() {
  void resumeAudioContext()
}

function syncSoundPreference(event) {
  if (event.key !== 'pickup_display_sound' && event.key !== null) return
  soundEnabled.value = event.newValue === 'true'
  if (soundEnabled.value) void resumeAudioContext()
}

function goBack() {
  router.push({ name: 'order-management' })
}

function autoScrollList(list) {
  if (!list || list.scrollHeight <= list.clientHeight + 1) return

  const state = autoScrollStates.get(list) || { started: false, reachedTop: false }
  autoScrollStates.set(list, state)

  const maxScroll = list.scrollHeight - list.clientHeight
  if (!state.started) {
    list.scrollTop = maxScroll
    state.started = true
    return
  }

  if (state.reachedTop) {
    list.scrollTop = maxScroll
    state.reachedTop = false
    return
  }

  list.scrollTop = Math.max(list.scrollTop - 1.1, 0)
  if (list.scrollTop <= 0) {
    state.reachedTop = true
  }
}

async function playChime() {
  if (!soundEnabled.value) return
  await resumeAudioContext()
  const context = audioContext.value
  if (!context || context.state !== 'running') return
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
  void resumeAudioContext()
  loadBoard()
  pollTimer = setInterval(loadBoard, 5000)
  autoScrollTimer = setInterval(() => {
    autoScrollList(preparingOrderList.value)
    autoScrollList(readyOrderList.value)
  }, 40)
  document.addEventListener('pointerdown', handleDisplayInteraction)
  document.addEventListener('keydown', handleDisplayInteraction)
  window.addEventListener('storage', syncSoundPreference)
})

onBeforeUnmount(() => {
  clearInterval(pollTimer)
  clearInterval(autoScrollTimer)
  clearTimeout(alertTimer)
  document.removeEventListener('pointerdown', handleDisplayInteraction)
  document.removeEventListener('keydown', handleDisplayInteraction)
  window.removeEventListener('storage', syncSoundPreference)
  audioContext.value?.close?.()
})
</script>

<style scoped>
.pickup-display{box-sizing:border-box;display:flex;width:100%;height:100vh;min-height:0;flex-direction:column;gap:clamp(10px,1.4vh,20px);overflow:hidden;padding:clamp(12px,2vw,30px);background:#f5f3ef;color:#101a32}
.pickup-utility-controls{position:fixed;z-index:5;top:max(8px,env(safe-area-inset-top));left:max(8px,env(safe-area-inset-left));display:flex;align-items:center;opacity:.32;transition:opacity .18s ease}
.pickup-utility-controls:hover,.pickup-utility-controls:focus-within{opacity:.9}
.pickup-utility-button{display:grid;width:27px;height:27px;place-items:center;padding:0;border:0;border-radius:50%;background:#fff;color:#49536a;font-size:15px;cursor:pointer}
.pickup-utility-button:hover,.pickup-utility-button:focus-visible{background:#fff0df;color:#ed6500;outline:2px solid #ed6500;outline-offset:2px}
.pickup-back-button{font-family:Arial,sans-serif;font-size:19px;font-weight:400;line-height:1}
.pickup-ready-alert{display:flex;flex:none;align-items:center;gap:12px;padding:clamp(9px,1vw,16px) 20px;border:1px solid #a6e4c4;border-radius:12px;background:#eafaf1;color:#087a49;font-size:clamp(14px,1.5vw,20px)}.pickup-ready-alert>i{font-size:22px}
.pickup-error{flex:none;padding:10px 14px;border-radius:9px;background:#fff0ee;color:#bd2b20;font-size:14px}
.pickup-columns{display:grid;min-height:0;flex:1;grid-template-columns:repeat(2,minmax(0,1fr));gap:clamp(10px,1.4vw,20px)}.pickup-column{display:flex;min-width:0;min-height:0;flex-direction:column;overflow:hidden;padding:clamp(14px,1.7vw,24px);background:#fff;border:1px solid #e9edf4;border-radius:22px;box-shadow:0 12px 30px #1b24300d}.ready-column{background:#f1fbf7;border-color:#d7eee5}
.pickup-column-heading{display:flex;flex:none;align-items:center;gap:clamp(8px,1vw,13px);padding-bottom:clamp(8px,1.2vh,17px);border-bottom:1px solid #f0e7dc}.pickup-status-icon{display:grid;width:clamp(34px,4vw,48px);height:clamp(34px,4vw,48px);flex:none;place-items:center;border-radius:13px;background:#fff1e2;color:#f36b00;font-size:clamp(17px,2vw,22px)}.ready-column .pickup-status-icon{background:#e6f8ee;color:#07834e}.pickup-column-heading h2{margin:0;font-size:clamp(16px,2vw,23px);font-weight:800}.pickup-column-heading p{margin:3px 0 0;color:#78829c;font-size:clamp(10px,1vw,13px)}.pickup-column-heading>strong{display:grid;width:clamp(30px,3.2vw,38px);height:clamp(30px,3.2vw,38px);flex:none;place-items:center;margin-left:auto;border-radius:50%;background:#fff2e2;color:#e96400;font-size:clamp(14px,1.5vw,18px)}.ready-column .pickup-column-heading>strong{background:#e6f8ee;color:#07834e}
.pickup-column-heading h2{text-transform:uppercase;letter-spacing:.025em}.pickup-column-heading>strong{display:inline-flex;width:auto;height:auto;min-width:28px;justify-content:center;padding:5px 9px;border-radius:999px;font-size:14px}.ready-column .pickup-column-heading{border-bottom-color:#dcefe7}
.pickup-empty{display:grid;min-height:0;flex:1;place-items:center;color:#8991a4;font-size:clamp(14px,1.5vw,20px)}.pickup-order-grid{display:flex;min-height:0;flex:1;flex-direction:column;gap:clamp(9px,1vw,14px);overflow-x:hidden;overflow-y:auto;padding:clamp(7px,1vh,14px) 4px 4px 0;overscroll-behavior:contain;scrollbar-width:none;-ms-overflow-style:none}.pickup-order-grid::-webkit-scrollbar{display:none;width:0;height:0}.pickup-order-card{container-type:inline-size;display:flex;min-width:0;min-height:118px;flex:0 0 auto;flex-direction:column;justify-content:center;overflow:hidden;padding:clamp(10px,1vw,16px);border:1px solid #e5eaf3;border-left:5px solid #f47709;border-radius:16px;background:#fff;box-shadow:0 3px 9px #1b24300a}.ready-order-card{border-color:#b7e8ce;border-left-color:#15955b;background:#fff}.pickup-order-card.is-delayed{border-color:#efc078;border-left-color:#dc5b22;background:#fffaf1}.ready-order-card.is-just-ready{z-index:1;transform-origin:center;animation:just-ready-pulse 1.45s ease-in-out infinite}.preparation-progress{height:5px;flex:none;margin-top:10px;overflow:hidden;border-radius:999px;background:#edf1f7}.preparation-progress span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#f6a300,#ffc14a);transition:width .35s ease}.pickup-token{display:block;overflow:hidden;color:#101a32;font-size:clamp(20px,20cqi,64px);font-weight:900;letter-spacing:.04em;line-height:1.05;text-overflow:ellipsis;white-space:nowrap}.pickup-wait{display:inline-flex;align-self:flex-start;align-items:center;gap:5px;margin-top:clamp(3px,.5vh,8px);color:#64748b;font-size:clamp(9px,.9vw,13px);font-weight:700}.pickup-wait.is-delayed-label{color:#b54708}.order-details{display:-webkit-box;overflow:hidden;margin:clamp(4px,.7vh,10px) 0 0;color:#57627c;font-size:clamp(10px,1.1vw,15px);line-height:1.3;-webkit-box-orient:vertical;-webkit-line-clamp:2}.pickup-ready-label{display:inline-flex;align-items:center;gap:5px;margin-top:clamp(4px,.7vh,10px);padding:5px 8px;align-self:flex-start;border-radius:99px;background:#dff6e9;color:#07834e;font-size:clamp(9px,.9vw,12px);font-weight:900;letter-spacing:.04em}.pickup-footer{flex:none;padding:2px;text-align:center;color:#78829c;font-size:clamp(10px,.95vw,13px);font-weight:600}
@keyframes just-ready-pulse{0%,100%{transform:scale(1);box-shadow:0 3px 9px #1b24300a}50%{transform:scale(1.025);box-shadow:0 12px 26px #00a8782e}}
@media(prefers-reduced-motion:reduce){.ready-order-card.is-just-ready{animation:none}}
@media(max-width:760px){.pickup-display{height:auto;min-height:100dvh;overflow-y:auto;padding:12px 8px;gap:8px}.pickup-columns{flex:none;min-height:0;grid-template-columns:minmax(0,1fr);gap:10px}.pickup-column{height:55dvh;min-height:260px;overflow:hidden;padding:12px;border-radius:18px}.pickup-column-heading{gap:9px;padding-bottom:9px}.pickup-column-heading h2{font-size:16px}.pickup-column-heading p{display:block;font-size:10px}.pickup-order-grid{flex:1;min-height:0;grid-template-columns:none;grid-auto-rows:auto;gap:8px;overflow-x:hidden;overflow-y:auto}.pickup-order-card{min-height:104px;padding:8px;border-left-width:3px;border-radius:12px}.pickup-token{font-size:clamp(18px,20cqi,32px)}.order-details{font-size:10px}}
</style>
