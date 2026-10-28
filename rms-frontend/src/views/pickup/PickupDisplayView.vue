<template>
  <main class="pickup-display">
    <header class="brand-header" :aria-label="`${restaurantName} pickup display`">
      <div class="brand-mark"><span>{{ restaurantMonogram }}</span></div>
      <div class="brand-copy"><strong>{{ restaurantName }}</strong><span>KITCHEN &amp; GRILL</span></div>
    </header>
    <button v-if="audioNeedsEnable" type="button" class="pickup-enable-audio" @click="enableDisplayAudio">
      <i class="bi bi-volume-up-fill"></i> Enable announcement audio
    </button>

    <div v-if="newlyReadyTokens.length" class="pickup-ready-alert" role="status" aria-live="assertive">
      <i class="bi bi-bell-fill"></i>
      <span><strong>{{ newlyReadyTokens.join(', ') }}</strong> {{ newlyReadyTokens.length === 1 ? 'is' : 'are' }} ready for pickup</span>
    </div>
    <div v-if="errorMessage" class="pickup-error" role="alert">{{ errorMessage }}</div>

    <section class="pickup-columns" aria-label="Takeaway order status and menu">
      <article class="pickup-column preparing-column">
        <header class="pickup-column-heading">
          <div><h2>Preparing</h2></div>
          <strong>{{ preparingOrders.length }}</strong>
        </header>
        <div v-if="!preparingOrders.length" class="pickup-empty">No orders preparing</div>
        <div v-else ref="preparingOrderList" class="pickup-order-grid" :style="gridStyle(preparingOrders.length)">
          <article v-for="order in preparingOrders" :key="order.id" class="pickup-order-card" :class="{ 'is-delayed': isDelayed(order) }">
            <strong class="pickup-token" :style="tokenStyle(preparingOrders.length)">{{ order.token }}</strong>
            <div class="order-meta"><span>{{ (order.items || []).reduce((sum, item) => sum + Number(item.quantity || 0), 0) }} items</span><span>Placed {{ new Date(order.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span></div>
            <div
              class="preparation-progress"
              role="progressbar"
              aria-label="Preparation progress"
              :aria-valuenow="preparationProgress(order)"
              aria-valuemin="0"
              aria-valuemax="100"
            ><span :style="{ width: `${preparationProgress(order)}%` }"></span></div>
            <div class="progress-meta"><span>{{ preparationProgress(order) }}%</span><span>{{ estimatedWait(order) }} min left</span></div>
          </article>
        </div>
      </article>

      <article class="pickup-column ready-column">
        <header class="pickup-column-heading">
          <div><h2>Ready</h2></div>
          <strong>{{ readyOrders.length }}</strong>
        </header>
        <article v-if="pinnedOrder" class="pickup-order-card ready-order-card pickup-pinned-card is-just-ready" aria-live="assertive">
          <strong class="pickup-token">{{ pinnedOrder.token }}</strong>
          <span class="pickup-ready-label">Ready for collection</span>
        </article>
        <div v-if="!readyOrders.length" class="pickup-empty">No orders ready yet</div>
        <div v-else ref="readyOrderList" class="pickup-order-grid" :style="gridStyle(readyOrders.length)">
          <article
            v-for="order in readyOrders.filter((item) => String(item.id) !== pinnedOrderId)"
            :key="order.id"
            class="pickup-order-card ready-order-card"
            :class="{ 'is-just-ready': String(order.id) === justReadyOrderId }"
          >
            <strong class="pickup-token" :style="tokenStyle(readyOrders.length)">{{ order.token }}</strong>
            <span class="pickup-ready-label">Ready for collection</span>
          </article>
        </div>
      </article>
      <article class="menu-column">
        <header class="menu-heading"><h2>Menu</h2><button type="button" aria-label="Back" title="Back" @click="goBack">‹</button></header>
        <div ref="menuList" class="menu-list">
          <section v-for="group in menuGroups" :key="group.name" class="menu-category">
            <h3>{{ group.name }}</h3>
            <article v-for="item in group.items" :key="item.id" class="menu-card">
              <img class="menu-photo" :src="foodImage(item)" :alt="item.menu_name" loading="lazy">
              <div class="menu-info"><h4>{{ item.menu_name }} <span v-if="item.is_featured">Popular</span></h4><p>{{ item.description || item.ingredients || group.name }}</p></div>
              <strong>৳{{ Number(item.price || 0).toLocaleString('en-BD') }}</strong>
            </article>
          </section>
          <div v-if="!menuGroups.length" class="menu-empty">Menu items will appear here</div>
        </div>
      </article>
    </section>
    <!-- <footer class="pickup-footer">Please show your token at the pickup counter.</footer> -->
  </main>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import orderService from '@/services/orderService'
import { useMenuManagementStore } from '@/stores/menuManagement'
import { useSystemSettingStore } from '@/stores/systemSetting'
import { resolveMediaUrl } from '@/utils/mediaUrl'

const menuStore = useMenuManagementStore()
const settingsStore = useSystemSettingStore()
const orders = ref([])
const pickupMenuItems = ref([])
const errorMessage = ref('')
const newlyReadyTokens = ref([])
const soundEnabled = ref(localStorage.getItem('pickup_display_sound') === 'true')
const audioNeedsEnable = ref(false)
const audioContext = ref(null)
const preparingOrderList = ref(null)
const readyOrderList = ref(null)
const menuList = ref(null)
const justReadyOrderId = ref(null)
const pinnedOrderId = ref(null)
let pollTimer = null
let alertTimer = null
let highlightTimer = null
let pinnedOrderTimer = null
let autoScrollTimer = null
let hasInitialBoard = false
let previousReadyIds = new Set()
let lastProcessedCalloutId = null
let pendingAudioOrders = []
const autoScrollStates = new WeakMap()

const preparingOrders = computed(() => orders.value.filter((order) => order.status === 'preparing'))
const readyOrders = computed(() => orders.value.filter((order) => order.status === 'ready'))
const pinnedOrder = computed(() => readyOrders.value.find((order) => String(order.id) === pinnedOrderId.value) || null)
const restaurantName = computed(() => settingsStore.restaurantName || 'Restaurant')
const restaurantMonogram = computed(() => restaurantName.value.trim().split(/\s+/).slice(0, 2).map((word) => word[0]).join('').toUpperCase())
const menuGroups = computed(() => {
  const groups = new Map()
  pickupMenuItems.value.filter((item) => item.is_available !== false).forEach((item) => {
    const name = item.category?.category_name || 'Menu'
    if (!groups.has(name)) groups.set(name, [])
    groups.get(name).push(item)
  })
  return [...groups].map(([name, items]) => ({ name, items }))
})

const foodPhoto = (filename) => `https://images.unsplash.com/${filename}?auto=format&fit=crop&w=420&q=80`
function foodImage(item) {
  const savedImage = resolveMediaUrl(item.image_url || item.image_path)
  if (savedImage) return savedImage

  const name = String(item.menu_name || '').toLowerCase()
  if (name.includes('beef tehari') || name.includes('beef set meal')) return 'https://recipes.milon.im/assets/images/recipes/beef-tehari.jpg'
  if (name.includes('chicken set meal')) return 'https://prcdn.freetls.fastly.net/release_image/167099/7/167099-7-e0574fd57dd7af399c8fd6ae6e9f44d42-733x572.jpg?auto=webp&fit=bounds&w=420'
  if (name.includes('student combo')) return foodPhoto('photo-1692737348416-0a4fa4cf1523')
  if (name.includes('burger') && name.includes('beef')) return foodPhoto('photo-1598182198871-d3f4ab4fd181')
  if (name.includes('burger')) return foodPhoto('photo-1692737348416-0a4fa4cf1523')
  if (name.includes('pizza')) return foodPhoto('photo-1604917869287-3ae73c77e227')
  if (name.includes('biryani') || name.includes('tehari') || name.includes('rice')) return 'https://media-assets.swiggy.com/swiggy/image/upload/fl_lossy,f_auto,q_auto,w_420/RX_THUMBNAIL/IMAGES/VENDOR/2026/2/20/07926d90-610f-40f9-9944-304274e9fcf5_533361.jpg'
  if (name.includes('fries')) return foodPhoto('photo-1688978181542-87a886a16fbe')
  if (name.includes('wings')) return foodPhoto('photo-1624153064067-566cae78993d')
  if (name.includes('coffee')) return foodPhoto('photo-1581996323441-538096e854b9')
  if (name.includes('drink') || name.includes('juice')) return foodPhoto('photo-1513558161293-cdaf765edfd47')
  if (name.includes('brownie')) return foodPhoto('photo-1606313564200-e75d5e30476c')
  if (name.includes('ice cream')) return foodPhoto('photo-1501446529957-6226bd447c46')
  return foodPhoto('photo-1547592180-85f173990554')
}

async function loadMenuItems() {
  try {
    pickupMenuItems.value = await menuStore.fetchAllMenuItems()
  } catch {
    pickupMenuItems.value = []
  }
}

function pinOrder(id) {
  pinnedOrderId.value = String(id)
  clearTimeout(pinnedOrderTimer)
  pinnedOrderTimer = setTimeout(() => { pinnedOrderId.value = null }, 6000)
}

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
    const callout = response?.callout
    let calledOrderId = null
    if (callout?.id && callout.id !== lastProcessedCalloutId) {
      lastProcessedCalloutId = callout.id
      const token = String(callout.token ?? '').trim()
      if (token) {
        if (callout.order_id != null) {
          calledOrderId = String(callout.order_id)
          pinOrder(callout.order_id)
          justReadyOrderId.value = String(callout.order_id)
          clearTimeout(highlightTimer)
          highlightTimer = setTimeout(() => { justReadyOrderId.value = null }, 60000)
        }
        pendingAudioOrders = [{ token }]
        void playChime(true).then((played) => {
          if (!played) {
            audioNeedsEnable.value = true
            return
          }
          pendingAudioOrders = []
          audioNeedsEnable.value = false
          announceReadyOrders([{ token }], true)
        })
      }
    }
    const nextReady = nextOrders.filter((order) => order.status === 'ready')
    const nextReadyIds = new Set(nextReady.map((order) => String(order.id)))
    if (hasInitialBoard) {
      const newReady = nextReady.filter((order) =>
        !previousReadyIds.has(String(order.id)) && String(order.id) !== calledOrderId,
      )
      if (newReady.length) {
        const newestOrderId = String(newReady[newReady.length - 1].id)
        pinOrder(newestOrderId)
        justReadyOrderId.value = newestOrderId
        clearTimeout(highlightTimer)
        highlightTimer = setTimeout(() => { justReadyOrderId.value = null }, 60000)
        newlyReadyTokens.value = newReady.map((order) => order.token)
        clearTimeout(alertTimer)
        alertTimer = setTimeout(() => { newlyReadyTokens.value = [] }, 12000)
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

async function resumeAudioContext(force = false) {
  if (!soundEnabled.value && !force) return
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

async function enableDisplayAudio() {
  const queuedOrders = pendingAudioOrders
  pendingAudioOrders = []
  if (queuedOrders.length) announceReadyOrders(queuedOrders, true)
  await resumeAudioContext(true)
  const audioReady = audioContext.value?.state === 'running'
  audioNeedsEnable.value = !audioReady
  if (audioReady && queuedOrders.length) void playChime(true)
}

function handleDisplayStorage(event) {
  if (event.key !== 'pickup_display_sound' && event.key !== null) return
  soundEnabled.value = event.newValue === 'true'
  if (soundEnabled.value) void resumeAudioContext()
}

function goBack() {
  window.history.back()
}

function autoScrollList(list) {
  if (!list || list.scrollHeight <= list.clientHeight + 1) return

  const state = autoScrollStates.get(list) || { direction: 1, pause: 0 }
  autoScrollStates.set(list, state)

  const maxScroll = list.scrollHeight - list.clientHeight
  if (state.pause > 0) {
    state.pause -= 1
    return
  }

  list.scrollTop += state.direction * 0.45
  if (list.scrollTop >= maxScroll) {
    list.scrollTop = maxScroll
    state.direction = -1
    state.pause = 35
  } else if (list.scrollTop <= 0) {
    list.scrollTop = 0
    state.direction = 1
    state.pause = 35
  }
}

async function playChime(force = false) {
  if (!soundEnabled.value && !force) return false
  await resumeAudioContext(force)
  const context = audioContext.value
  if (!context || context.state !== 'running') return false
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
  return true
}

function announceReadyOrders(readyOrders, force = false) {
  const speech = window.speechSynthesis
  const Utterance = window.SpeechSynthesisUtterance
  if ((!soundEnabled.value && !force) || !speech || typeof Utterance !== 'function') return

  const englishVoices = speech.getVoices().filter((voice) => voice.lang?.toLowerCase().startsWith('en'))
  const preferredVoice = englishVoices
    .map((voice) => {
      const name = voice.name.toLowerCase()
      let score = voice.lang?.toLowerCase() === 'en-us' ? 20 : 0
      if (/natural|neural|online|premium|enhanced/.test(name)) score += 100
      if (!voice.localService) score += 30
      if (/google|microsoft|aria|jenny|ava|samantha|zira/.test(name)) score += 15
      if (voice.default) score += 2
      return { voice, score }
    })
    .sort((first, second) => second.score - first.score)[0]?.voice

  readyOrders.forEach((order) => {
    const token = String(order.token ?? '').trim()
    if (!token) return

    const announcement = new Utterance(`Order ${token} is ready for pickup.`)
    if (preferredVoice) {
      announcement.voice = preferredVoice
      announcement.lang = preferredVoice.lang
    } else {
      announcement.lang = 'en-US'
    }
    announcement.rate = 0.96
    announcement.pitch = 1
    announcement.volume = 0.9
    speech.speak(announcement)
  })
}

onMounted(() => {
  void resumeAudioContext()
  void loadMenuItems()
  void settingsStore.fetchSettings()
  loadBoard()
  pollTimer = setInterval(loadBoard, 5000)
  autoScrollTimer = setInterval(() => {
    autoScrollList(preparingOrderList.value)
    autoScrollList(readyOrderList.value)
    autoScrollList(menuList.value)
  }, 40)
  document.addEventListener('pointerdown', handleDisplayInteraction)
  document.addEventListener('keydown', handleDisplayInteraction)
  window.addEventListener('storage', handleDisplayStorage)
})

onBeforeUnmount(() => {
  clearInterval(pollTimer)
  clearInterval(autoScrollTimer)
  clearTimeout(alertTimer)
  clearTimeout(highlightTimer)
  clearTimeout(pinnedOrderTimer)
  document.removeEventListener('pointerdown', handleDisplayInteraction)
  document.removeEventListener('keydown', handleDisplayInteraction)
  window.removeEventListener('storage', handleDisplayStorage)
  window.speechSynthesis?.cancel?.()
  audioContext.value?.close?.()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap');
.pickup-display{--bg:#0b0c0e;--panel:#131417;--card:#1a1c20;--line:#25282d;--cream:#f3e6d6;--gold:#c9a24d;--amber:#e8a22c;--green:#588157;--red:#971821;box-sizing:border-box;display:flex;width:100%;height:100vh;height:100dvh;min-height:0;flex-direction:column;gap:clamp(16px,2vh,28px);overflow:hidden;padding:clamp(22px,2vw,42px);background:var(--bg);color:var(--cream);font-family:Inter,Arial,sans-serif}
.brand-header{display:flex;flex:none;align-items:center;gap:clamp(14px,1.2vw,22px);min-height:clamp(54px,7vh,82px)}.brand-mark{display:grid;width:clamp(52px,4.2vw,74px);aspect-ratio:1;place-items:center;border:1px solid var(--gold);outline:1px solid var(--gold);outline-offset:-6px;color:var(--gold);font:700 clamp(17px,1.5vw,25px) 'Playfair Display',Georgia,serif}.brand-copy{display:flex;flex-direction:column;gap:3px}.brand-copy strong{color:var(--cream);font:600 clamp(26px,2.2vw,40px)/1 'Playfair Display',Georgia,serif}.brand-copy span{color:var(--gold);font-size:clamp(10px,.8vw,14px);letter-spacing:.3em}
.pickup-enable-audio{position:fixed;z-index:6;top:12px;right:12px;display:flex;align-items:center;gap:8px;padding:10px 14px;border:1px solid var(--gold);border-radius:0;background:var(--panel);color:var(--cream);font-size:14px;font-weight:700;cursor:pointer}.pickup-enable-audio:hover{background:var(--card)}
.pickup-ready-alert{display:flex;flex:none;align-items:center;gap:12px;padding:10px 16px;border:1px solid var(--green);background:var(--panel);color:var(--cream);font-size:clamp(14px,1vw,20px)}.pickup-ready-alert>i{color:var(--green);font-size:22px}.pickup-error{flex:none;padding:10px 14px;border:1px solid var(--red);background:var(--panel);color:var(--cream);font-size:14px}
.pickup-columns{display:grid;min-height:0;flex:1;grid-template-columns:minmax(0,1fr) minmax(0,1fr) minmax(0,1.36fr);gap:clamp(14px,1.5vw,28px)}.pickup-column,.menu-column{display:flex;min-width:0;min-height:0;flex-direction:column;overflow:hidden;border:1px solid var(--line);background:var(--panel)}
.pickup-column-heading,.menu-heading{display:flex;flex:none;min-height:clamp(54px,7vh,74px);align-items:center;gap:12px;padding:0 clamp(16px,1.3vw,24px);background:var(--amber);color:#17130a}.ready-column .pickup-column-heading{background:var(--green);color:#fff}.pickup-column-heading h2,.menu-heading h2{margin:0;font:600 clamp(20px,1.4vw,28px)/1.1 'Playfair Display',Georgia,serif}.pickup-column-heading>strong{display:inline-flex;min-width:44px;justify-content:center;margin-left:auto;border:1px solid currentColor;padding:5px 10px;font:600 clamp(15px,1vw,20px) 'Playfair Display',Georgia,serif}
.pickup-empty{display:grid;min-height:0;flex:1;place-items:center;color:#9a938b;font-size:clamp(14px,1.1vw,20px)}.pickup-order-grid{display:flex;min-height:0;flex:1;flex-direction:column;gap:clamp(9px,.9vw,15px);overflow-x:hidden;overflow-y:auto;padding:clamp(10px,.8vw,16px);overscroll-behavior:contain;scrollbar-width:none;-ms-overflow-style:none}.pickup-order-grid::-webkit-scrollbar{display:none;width:0;height:0}.pickup-order-card{container-type:inline-size;display:flex;min-width:0;min-height:clamp(104px,13vh,150px);flex:0 0 auto;flex-direction:column;justify-content:center;overflow:hidden;padding:clamp(12px,1vw,20px);border:1px solid var(--line);border-left:5px solid var(--amber);background:var(--card)}.ready-order-card{border-left-color:var(--green)}.pickup-order-card.is-delayed{border-left-color:#e3a700}.ready-order-card.is-just-ready{animation:none}.pickup-pinned-card{border-left-width:5px}
.pickup-token{display:block;overflow:hidden;color:var(--cream);font:600 clamp(29px,3vw,54px)/1 'Playfair Display',Georgia,serif;letter-spacing:.01em;text-overflow:ellipsis;white-space:nowrap}.order-meta,.progress-meta{display:flex;justify-content:space-between;gap:8px;color:#aaa7a2;font-size:clamp(11px,.85vw,15px)}.order-meta{margin-top:clamp(8px,1vh,14px)}.preparation-progress{height:6px;flex:none;margin-top:clamp(9px,1vh,14px);overflow:hidden;background:#303237}.preparation-progress span{display:block;height:100%;background:var(--amber);transition:width .35s ease}.progress-meta{margin-top:5px}.pickup-ready-label{display:inline-flex;align-items:center;align-self:flex-start;margin-top:clamp(10px,1.2vh,16px);padding:7px 12px;background:var(--green);color:#fff;font-size:clamp(10px,.8vw,14px);font-weight:800;letter-spacing:.12em;text-transform:uppercase}
.menu-heading{justify-content:space-between;background:var(--red);color:var(--cream);border-bottom:1px solid var(--line)}.menu-heading button{border:0;background:transparent;color:var(--cream);opacity:.1;font:400 36px/1 Inter,Arial,sans-serif;cursor:pointer}.menu-heading button:hover,.menu-heading button:focus-visible{opacity:.45}.menu-list{min-height:0;flex:1;overflow-y:auto;padding:clamp(10px,1vw,18px);scrollbar-width:none;-ms-overflow-style:none}.menu-list::-webkit-scrollbar{display:none}.menu-category{margin-bottom:clamp(16px,2vh,26px)}.menu-category h3{margin:0 0 10px;padding-bottom:9px;border-bottom:1px solid var(--line);color:var(--gold);font:700 clamp(12px,.9vw,16px) Inter,Arial,sans-serif;letter-spacing:.22em;text-transform:uppercase}.menu-card{display:flex;min-height:clamp(84px,12vh,132px);align-items:center;gap:clamp(12px,1.2vw,22px);margin-bottom:10px;padding:clamp(10px,.9vw,16px);border:1px solid var(--line);background:var(--card)}.menu-icon{display:grid;width:clamp(62px,6vw,100px);aspect-ratio:1;flex:none;place-items:center;border:1px solid var(--line);background:#0b0c0e;color:var(--gold);font-size:clamp(25px,2.2vw,40px)}.menu-info{min-width:0;flex:1}.menu-info h4{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin:0;color:var(--cream);font:600 clamp(17px,1.35vw,25px)/1.2 'Playfair Display',Georgia,serif}.menu-info h4 span{padding:3px 7px;border:1px solid var(--gold);color:var(--gold);font:700 clamp(9px,.65vw,12px) Inter,Arial,sans-serif;letter-spacing:.15em;text-transform:uppercase}.menu-info p{overflow:hidden;margin:5px 0 0;color:#9b9da2;font-size:clamp(12px,.95vw,17px);text-overflow:ellipsis;white-space:nowrap}.menu-card>strong{flex:none;color:var(--gold);font:600 clamp(17px,1.4vw,26px) 'Playfair Display',Georgia,serif;white-space:nowrap}
@media(min-width:1600px){.pickup-display{font-size:1.12em}.pickup-token{font-size:clamp(38px,3.2vw,72px)}.menu-icon{font-size:clamp(34px,2.5vw,50px)}.menu-info h4{font-size:clamp(23px,1.5vw,32px)}.menu-info p,.order-meta,.progress-meta{font-size:clamp(16px,1vw,21px)}.menu-card>strong{font-size:clamp(24px,1.6vw,34px)}}
@media(max-width:900px){.pickup-display{padding:16px;gap:12px}.pickup-columns{grid-template-columns:1fr 1fr;grid-template-rows:minmax(0,1fr) minmax(0,1fr)}.menu-column{grid-column:1/-1}.menu-list{display:grid;grid-template-columns:1fr 1fr;gap:12px}.menu-category{margin:0}.menu-card{min-height:80px}}
@media(prefers-reduced-motion:reduce){.preparation-progress span{transition:none}}
.preparing-column .pickup-column-heading{color:#17130a}
.preparing-column .pickup-column-heading>strong{background:transparent;color:#17130a;border-color:#17130a}
.menu-heading button{position:fixed;z-index:8;top:8px;right:12px;display:grid;width:28px;height:28px;place-items:center;opacity:.1}
.menu-heading button:hover,.menu-heading button:focus-visible{opacity:.1;outline:none}
.menu-photo{display:block;width:clamp(62px,6vw,100px);aspect-ratio:1;flex:none;border:1px solid var(--line);object-fit:cover;background:#0b0c0e}
.menu-empty{display:grid;min-height:100%;place-items:center;color:#aaa7a2;font-size:clamp(14px,1vw,18px)}
</style>
