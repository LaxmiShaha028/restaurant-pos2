<template>
  <main class="landing-admin" :class="{ 'public-landing': isPublicPage }" :style="themeStyle">
    <header v-if="!isPublicPage" class="page-head">
      <div>
        <p class="eyebrow">WEBSITE</p>
        <h1>Landing Page</h1>
        <p>Edit your restaurant page and preview the changes here.</p>
      </div>
      <div class="page-actions">
        <a
          v-if="publicPageUrl"
          class="view-page-button"
          :href="publicPageUrl"
          target="_blank"
          rel="noopener noreferrer"
        >
          <i class="bi bi-box-arrow-up-right"></i> View landing page
        </a>
        <button class="save-button" type="button" :disabled="isSaving" @click="save">
          <i class="bi bi-check2"></i> {{ isSaving ? 'Saving…' : 'Save changes' }}
        </button>
      </div>
    </header>

    <div v-if="saved && !isPublicPage" class="saved-message" role="status">
      Landing page settings saved.
    </div>
    <div v-if="errorMessage && !isPublicPage" class="saved-message save-error" role="alert">
      {{ errorMessage }}
    </div>
    <div v-if="isPublicPage && isLoadingPublic" class="public-state">Loading restaurant page…</div>
    <div v-else-if="isPublicPage && errorMessage" class="public-state public-error">{{ errorMessage }}</div>

    <div v-if="!isPublicPage || (!isLoadingPublic && !errorMessage)" class="editor-layout" :class="{ 'with-settings': !isPublicPage }">
      <section v-if="!isPublicPage" class="settings-card">
        <div class="card-heading">
          <span class="icon"><i class="bi bi-sliders"></i></span>
          <div><h2>Page Settings</h2><p>Changes appear in the preview as you type.</p></div>
        </div>

        <div v-for="group in fieldGroups" :key="group.title" class="settings-group">
          <h3>{{ group.title }}</h3>
          <label v-for="field in group.fields" :key="field.key">
            {{ field.label }}
            <input v-if="field.type === 'color'" v-model="form[field.key]" type="color" class="color-input" />
            <textarea v-else-if="field.type === 'area'" v-model="form[field.key]" rows="3" maxlength="400"></textarea>
            <input v-else v-model="form[field.key]" maxlength="160" />
          </label>
        </div>
        <!-- <div v-if="publicPageUrl" class="public-url-box"><strong>Public landing page</strong><a :href="publicPageUrl" target="_blank" rel="noopener noreferrer">{{ publicPageUrl }}</a></div> -->
      </section>

      <section class="preview-wrap">
        <div v-if="!isPublicPage" class="preview-label"><i class="bi bi-eye"></i> LIVE PREVIEW</div>
        <article v-if="!showOfferPage" class="site-preview home-preview" :style="themeStyle">
          <nav><strong>{{ form.name || 'SOPNO' }}</strong><div><a href="#" class="active" @click.prevent="showOfferPage = false">{{ form.navHome }}</a><a href="#menu">{{ form.navMenu }}</a><a href="#happy-hours" @click.prevent="showOfferPage = true">{{ form.navHappy }}</a><a href="#rewards">{{ form.navRewards }}</a><a href="#reserve">{{ form.navReserve }}</a><a href="#contact">{{ form.navContact }}</a></div></nav>
          <section class="home-hero">
            <div class="home-copy"><span class="home-kicker">{{ t(form.homeKicker) }}</span><h2>{{ t(form.homeTitle1) }}<br><em>{{ t(form.homeTitle2) }}</em></h2><p>{{ t(form.homeIntro) }}</p><a class="primary-cta" href="#reserve">{{ form.primaryButton }}</a><a class="secondary-cta" href="#menu">{{ form.secondaryButton }}</a></div>
            <div class="food-art"><img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=1000&q=85" alt="A platter of grilled food served at the restaurant" /></div>
          </section>
          <section id="happy-hours" class="home-happy"><div><h3>{{ t(form.happyBandTitle) }}</h3><div v-if="promotionsLoaded" class="happy-pills"><template v-if="activePromotions.length"><span v-for="offer in activePromotions.slice(0, 2)" :key="offer.id">{{ offerDiscount(offer) }}<small>{{ offer.name }}</small></span><span>{{ promotionSchedule }}<small>Offer schedule</small></span></template><span v-else>No active offers<small>Check back soon</small></span></div><div v-else class="happy-pills"><span>{{ form.pill1Title }}<small>{{ form.pill1Sub }}</small></span><span>{{ form.pill2Title }}<small>{{ form.pill2Sub }}</small></span><span>{{ form.hours }}<small>{{ form.pill3Sub }}</small></span></div></div><button type="button" @click="showOfferPage = true">{{ form.seeOfferButton }}</button></section>
          <section id="menu" class="kitchen"><h3>{{ t(form.kitchenTitle) }}</h3><div class="kitchen-grid"><div><b>01</b><strong>{{ form.kitchen1Title }}</strong><p>{{ form.kitchen1Text }}</p></div><div><b>02</b><strong>{{ form.kitchen2Title }}</strong><p>{{ form.kitchen2Text }}</p></div><div><b>03</b><strong>{{ form.kitchen3Title }}</strong><p>{{ form.kitchen3Text }}</p></div><div><b>04</b><strong>{{ form.kitchen4Title }}</strong><p>{{ form.kitchen4Text }}</p></div></div></section>
          <section id="contact" class="home-about"><div><h3>{{ t(form.aboutTitle) }}</h3><p>{{ t(form.aboutText) }}</p></div><div class="about-links"><a href="#contact"><b>{{ form.aboutOpenLabel }}</b><small>{{ form.hours || '[OPENING HOURS]' }}</small></a><a href="#contact"><b>{{ form.aboutFindLabel }}</b><small>{{ form.address || 'YOUR ADDRESS' }}</small></a></div></section>
          <section id="reserve" class="reservation"><div><h3>{{ form.reserveTitle }}</h3><p>{{ form.reserveText }}</p></div><form @submit.prevent><label>NAME<input placeholder="Your name" /></label><label>MOBILE NUMBER<input placeholder="Mobile number" /></label><div class="form-row"><label>DATE<input type="text" placeholder="Select date" /></label><label>GUESTS<input type="text" placeholder="2 guests" /></label></div><button type="button">{{ form.reserveButton }}</button></form></section>
          <footer><strong>{{ form.name || 'SOPNO' }}</strong><span>HOTLINE: {{ form.phone || '+00 000 000 000' }}<br>ADDRESS: {{ form.address || 'YOUR ADDRESS' }}</span><span>OPEN: {{ form.hours || '[OPENING HOURS]' }}<br>{{ form.terms }}</span></footer>
        </article>

        <article v-else class="site-preview home-preview offer-preview" :style="themeStyle">
          <div class="promo-bar">{{ promotionsLoaded ? promotionBarText : (form.promoText || `${form.drinkOffer} · ${form.starterOffer} · ${form.hours}`) }}</div>
          <nav><strong><a href="#" @click.prevent="showOfferPage = false">{{ form.name || 'SOPNO' }}</a></strong><div><a href="#" @click.prevent="showOfferPage = false">{{ form.navHome }}</a><a href="#" class="active" @click.prevent="showOfferPage = true">{{ form.navHappy }}</a><a href="#rewards">{{ form.navRewards }}</a><a href="#reserve">{{ form.navReserve }}</a><a href="#contact">{{ form.navContact }}</a></div></nav>
          <section class="hero">
            <div class="hero-copy">
              <h2>{{ form.headline || 'happy hours' }}</h2>
              <b class="hero-hours">{{ promotionsLoaded ? promotionSchedule : `${form.heroDays}, ${form.hours || '16:00 TO 19:00'}` }}</b>
              <template v-if="promotionsLoaded"><span v-for="offer in activePromotions.slice(0, 2)" :key="offer.id" class="offer-pill">{{ offerDiscount(offer) }} · {{ offer.name }}</span><span v-if="!activePromotions.length" class="offer-pill">No active offers</span></template>
              <template v-else><span class="offer-pill">{{ form.starterOffer || '50% OFF STARTERS & SIDES' }}</span><span class="offer-pill">{{ form.drinkOffer || '30% OFF ALL DRINKS' }}</span></template>
              <p>{{ promotionsLoaded ? promotionDescription : (form.description || 'Half price starters and sides, and 30% off all drinks. The discount is applied automatically at the bill.') }}</p>
              <a href="#reserve">{{ form.offerButton }}</a>
            </div>
            <div class="food-art"><img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=1000&q=85" alt="A platter of grilled food served at the restaurant" /></div>
          </section>
          <section class="deals"><h3>{{ form.dealsTitle1 }} <em>{{ form.dealsTitle2 }}</em></h3><div v-if="promotionsLoaded" class="deal-grid"><div v-for="(offer, index) in activePromotions" :key="offer.id" class="deal-card" :class="{ featured: index === 0 }"><small>{{ offer.type === 'weekday_weekend' ? 'SCHEDULED DEAL' : 'ACTIVE OFFER' }}</small><strong>{{ offer.discount_type === 'percentage' ? `${Number(offer.discount_value)}%` : money(offer.discount_value) }}<i>OFF</i></strong><b>{{ offer.name }}</b><p>{{ promotionCardDescription(offer) }}</p></div><p v-if="!activePromotions.length" class="no-public-offers">No active promotions at this time.</p></div><div v-else class="deal-grid"><div class="deal-card featured"><small>{{ form.deal1Badge }}</small><strong>{{ form.deal1Big }}<i>{{ form.deal1Off }}</i></strong><b>{{ form.deal1Name }}</b><p>{{ form.deal1Text }}</p></div><div class="deal-card"><small>{{ form.deal2Badge }}</small><strong>{{ form.deal2Big }}<i>{{ form.deal2Off }}</i></strong><b>{{ form.deal2Name }}</b><p>{{ form.deal2Text }}</p></div></div><div class="deal-chips"><template v-if="promotionsLoaded"><span v-for="offer in activePromotions.slice(0, 2)" :key="offer.id">{{ offerSchedule(offer) }}</span><span>No code needed</span></template><template v-else><span>{{ t(form.chip1) }}</span><span>{{ form.chip2 }}</span></template></div></section>
          <section class="offer"><template v-if="promotionsLoaded"><div v-for="offer in activePromotions.slice(0, 2)" :key="offer.id"><strong>{{ offerDiscount(offer) }}</strong><span>{{ offer.name }}<template v-if="Number(offer.minimum_order_amount) > 0"> · Min. {{ money(offer.minimum_order_amount) }}</template></span></div><div><strong>{{ promotionSchedule }}</strong><span>Offer schedule</span></div></template><template v-else><div><strong>{{ form.offer1Title }}</strong><span>{{ form.offer1Sub }}</span></div><div><strong>{{ form.offer2Title }}</strong><span>{{ form.offer2Sub }}</span></div><div><strong>{{ form.hours || '16:00–19:00' }}</strong><span>{{ form.offer3Sub }}</span></div></template></section>
          <section id="rewards" class="rewards"><h3>{{ form.rewardsTitle }}</h3><div class="reward-grid"><div><b>{{ form.reward1Title }}</b><p>{{ form.reward1Text }}</p></div><div><b>{{ form.reward2Title }}</b><p>{{ form.reward2Text }}</p></div><div><b>{{ form.reward3Title }}</b><p>{{ form.reward3Text }}</p></div></div></section>
          <section id="reserve" class="reservation"><div><h3>{{ form.reserveTitle }}</h3><p>{{ form.reserveText }}</p></div><form @submit.prevent><label>NAME<input placeholder="Your name" /></label><label>MOBILE NUMBER<input placeholder="Mobile number" /></label><div class="form-row"><label>DATE<input type="text" placeholder="Select date" /></label><label>GUESTS<input type="text" placeholder="2 guests" /></label></div><label>{{ form.birthdayLabel }}<input placeholder="DD / MM" /></label><button type="button">{{ form.reserveButton }}</button></form></section>
          <footer><strong>{{ form.name || 'SOPNO' }}</strong><span>{{ form.address || 'ADDRESS YOUR ADDRESS' }}<br>{{ form.phone || 'PHONE: 000 000 0000' }}</span><span>OPEN {{ form.hours || 'EVERY DAY' }}<br>{{ form.terms }}</span></footer>
        </article>
      </section>
    </div>
  </main>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import systemSettingService from '@/services/systemSettingService'
import offerService from '@/services/offerService'

const defaults = {
  // Restaurant name is filled in automatically from the restaurant.
  name: 'SOPNO',

  // Colors
  color: '#ffc400',
  textColor: '#ffffff',
  darkTextColor: '#111111',
  backgroundColor: '#0b0b0b',
  surfaceColor: '#191919',

  // Contact & hours
  address: '',
  phone: '',
  hours: '16:00–19:00',
  terms: 'Terms apply.',

  // Menu bar
  navHome: 'Home',
  navMenu: 'Menu',
  navHappy: 'Happy hours',
  navRewards: 'Rewards',
  navReserve: 'Reserve',
  navContact: 'Contact',

  // Home: hero
  homeKicker: 'BURGERS · STARTERS · SIDES · DRINKS',
  homeTitle1: 'Good food.',
  homeTitle2: 'Good nights.',
  homeIntro: 'Welcome to {name}. Big plates to share, cold drinks, and a warm table waiting for you.',
  primaryButton: 'Reserve a table',
  secondaryButton: 'View menu',

  // Home: happy hours band
  happyBandTitle: 'HAPPY HOURS, EVERYDAY',
  pill1Title: 'HALF PRICE',
  pill1Sub: 'Starters and sides',
  pill2Title: '30% OFF',
  pill2Sub: 'All drinks',
  pill3Sub: 'Every day',
  seeOfferButton: 'SEE THE OFFER',

  // Home: kitchen
  kitchenTitle: 'From our kitchen',
  kitchen1Title: 'Burgers',
  kitchen1Text: 'Brioche buns, melted cheese, and sauces made in house.',
  kitchen2Title: 'Starters',
  kitchen2Text: 'Crispy bites made for sharing, with dips on the side.',
  kitchen3Title: 'Sides',
  kitchen3Text: 'Seasoned wedges and crunchy extras to round out the table.',
  kitchen4Title: 'Drinks',
  kitchen4Text: 'Classic cocktails and cold drinks, poured all evening.',

  // Home: about
  aboutTitle: 'About {name}',
  aboutText: '[YOUR STORY: two or three lines about your restaurant, your cooking, and what guests can expect.]',
  aboutOpenLabel: 'OPEN',
  aboutFindLabel: 'FIND US',

  // Reservation (both pages)
  reserveTitle: 'Reserve your table',
  reserveText: 'Tell us when you are coming. We will confirm by SMS.',
  reserveButton: 'CONFIRM RESERVATION',
  birthdayLabel: 'BIRTHDAY (OPTIONAL)',

  // Happy hours page: top
  promoText: '',
  headline: 'happy hours',
  heroDays: 'EVERYDAY',
  description: 'Half price starters and sides, and 30% off all drinks. The discount is applied automatically at the bill.',
  starterOffer: '50% OFF STARTERS & SIDES',
  drinkOffer: '30% OFF ALL DRINKS',
  offerButton: 'Reserve a table',

  // Happy hours page: deals
  dealsTitle1: "Today's",
  dealsTitle2: 'big deals',
  deal1Badge: 'BEST DEAL',
  deal1Big: '50%',
  deal1Off: 'OFF',
  deal1Name: 'STARTERS & SIDES',
  deal1Text: 'Half price on every starter and side. Applied automatically at the bill.',
  deal2Badge: 'ALL DRINKS',
  deal2Big: '30%',
  deal2Off: 'OFF',
  deal2Name: 'COCKTAILS & COLD DRINKS',
  deal2Text: '30% off every drink on the menu during happy hours.',
  chip1: 'Every day, {hours}',
  chip2: 'No code needed',

  // Happy hours page: yellow band
  offer1Title: 'HALF PRICE',
  offer1Sub: 'Starters and sides',
  offer2Title: '30% OFF',
  offer2Sub: 'All drinks',
  offer3Sub: 'Every day of the week',

  // Happy hours page: rewards
  rewardsTitle: 'Rewards for regulars',
  reward1Title: 'Birthday treat',
  reward1Text: 'Share your birthday and get a special offer during your birthday week.',
  reward2Title: 'Loyalty reward',
  reward2Text: 'Visit often and unlock a reward on every fifth visit.',
  reward3Title: 'First to know',
  reward3Text: 'Get new offers by SMS and email before everyone else.',
}

// Settings panel: only the important text and the colors are editable.
const fieldGroups = [
  {
    title: 'Colors',
    fields: [
      { key: 'color', label: 'Accent color (yellow)', type: 'color' },
      { key: 'textColor', label: 'Text color (white)', type: 'color' },
      { key: 'darkTextColor', label: 'Text on accent (black)', type: 'color' },
      { key: 'backgroundColor', label: 'Page background color', type: 'color' },
      { key: 'surfaceColor', label: 'Box / card color', type: 'color' },
    ],
  },
  {
    title: 'Contact',
    fields: [
      { key: 'address', label: 'Address' },
      { key: 'phone', label: 'Phone number' },
      { key: 'hours', label: 'Opening hours' },
    ],
  },
  {
    title: 'Home page',
    fields: [
      { key: 'homeTitle1', label: 'Main heading (line 1)' },
      { key: 'homeTitle2', label: 'Main heading (line 2)' },
      { key: 'homeIntro', label: 'Intro text', type: 'area' },
      { key: 'kitchenTitle', label: 'Menu section title' },
      { key: 'kitchen1Title', label: 'Food name 1' },
      { key: 'kitchen2Title', label: 'Food name 2' },
      { key: 'kitchen3Title', label: 'Food name 3' },
      { key: 'kitchen4Title', label: 'Food name 4' },
      { key: 'aboutTitle', label: 'About title' },
      { key: 'aboutText', label: 'About text', type: 'area' },
      { key: 'reserveTitle', label: 'Reservation title' },
    ],
  },
  {
    title: 'Happy hours page',
    fields: [
      { key: 'headline', label: 'Main heading' },
      { key: 'starterOffer', label: 'Starters offer' },
      { key: 'drinkOffer', label: 'Drinks offer' },
      { key: 'description', label: 'Details text', type: 'area' },
      { key: 'deal1Name', label: 'Deal 1 title' },
      { key: 'deal2Name', label: 'Deal 2 title' },
      { key: 'rewardsTitle', label: 'Rewards title' },
      { key: 'reward1Title', label: 'Reward 1 title' },
      { key: 'reward2Title', label: 'Reward 2 title' },
      { key: 'reward3Title', label: 'Reward 3 title' },
    ],
  },
]

const form = reactive({ ...defaults })
const activePromotions = ref([])
const promotionsLoaded = ref(false)
const saved = ref(false)
const showOfferPage = ref(false)
const isSaving = ref(false)
const isLoadingPublic = ref(false)
const errorMessage = ref('')
const route = useRoute()
const authStore = useAuthStore()
const isPublicPage = computed(() => route.meta.publicLanding === true)
const publicPageUrl = computed(() => {
  const tenantKey = String(authStore.tenantKey || '').trim()
  return tenantKey
    ? `${window.location.origin}/site/${encodeURIComponent(tenantKey)}`
    : ''
})

const themeStyle = computed(() => ({
  '--accent': form.color,
  '--page-bg': form.backgroundColor,
  '--surface-bg': form.surfaceColor,
  '--text': form.textColor,
  '--ink': form.darkTextColor,
}))

const promotionSchedule = computed(() => {
  if (!promotionsLoaded.value) {
    return `${form.heroDays}, ${form.hours || '16:00–19:00'}`
  }
  const schedules = [...new Set(activePromotions.value.map(offerSchedule))]
  return schedules.length ? schedules.join(' · ') : 'No active offer schedule'
})

const promotionBarText = computed(() => activePromotions.value.length
  ? activePromotions.value.slice(0, 2).map((offer) => `${offerDiscount(offer)} ${offer.name} · ${offerSchedule(offer)}`).join(' · ')
  : 'No active promotions currently')

const promotionDescription = computed(() => activePromotions.value.length
  ? activePromotions.value.slice(0, 2).map(promotionCardDescription).join(' ')
  : 'There are no active promotions at this time. Please check back soon.')

function money(value) {
  return `৳ ${Number(value || 0).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

function offerDiscount(offer) {
  return offer.discount_type === 'percentage'
    ? `${Number(offer.discount_value)}% OFF`
    : `${money(offer.discount_value)} OFF`
}

function offerSchedule(offer) {
  const days = Array.isArray(offer.days_of_week) ? offer.days_of_week : []
  const labels = { mon: 'Mon', tue: 'Tue', wed: 'Wed', thu: 'Thu', fri: 'Fri', sat: 'Sat', sun: 'Sun' }
  const dayText = !days.length || days.length === 7
    ? 'Every day'
    : days.map((day) => labels[day] || day).join(', ')
  const start = offer.start_time ? String(offer.start_time).slice(0, 5) : ''
  const end = offer.end_time ? String(offer.end_time).slice(0, 5) : ''
  return `${dayText}${start && end ? `, ${start}–${end}` : ', All day'}`
}

function promotionCardDescription(offer) {
  const minimum = Number(offer.minimum_order_amount) > 0
    ? ` Minimum order ${money(offer.minimum_order_amount)}.`
    : ''
  return `${offerDiscount(offer)} ${offer.name}. Automatically applied at checkout.${minimum} ${offerSchedule(offer)}.`
}

function setPromotions(rows) {
  const today = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Dhaka' }).format(new Date())
  activePromotions.value = (Array.isArray(rows) ? rows : []).filter((offer) => {
    if (!offer?.is_active && offer?.is_active !== undefined) return false
    if (!['automatic', 'weekday_weekend'].includes(offer.type)) return false
    const from = offer.valid_from ? String(offer.valid_from).slice(0, 10) : ''
    const until = offer.valid_until ? String(offer.valid_until).slice(0, 10) : ''
    return (!from || from <= today) && (!until || until >= today)
  })
  promotionsLoaded.value = true
}

async function loadAdminPromotions() {
  try {
    const response = await offerService.list()
    setPromotions(response?.data || [])
  } catch {
    promotionsLoaded.value = false
  }
}

// Replaces {name} and {hours} placeholders inside editable text.
function t(value) {
  return String(value ?? '')
    .split('{name}').join(form.name || 'SOPNO')
    .split('{hours}').join(form.hours || '16:00–19:00')
}

onMounted(async () => {
  try {
    if (isPublicPage.value) {
      isLoadingPublic.value = true
      const tenantKey = encodeURIComponent(String(route.params.tenantKey || ''))
      const response = await api.get(`/public/landing-page/${tenantKey}`)
      const data = response?.data?.data || {}
      const pageSettings = data.landing_page_settings || {}
      Object.assign(form, defaults, pageSettings)
      if (data.restaurant?.name) {
        form.name = data.restaurant.name
      }
      setPromotions(data.promotions || [])
      return
    }

    const response = await systemSettingService.getSettings()
    const serverSettings = response?.data?.landing_page_settings
    if (serverSettings && Object.keys(serverSettings).length) {
      Object.assign(form, defaults, serverSettings)
    }

    // The restaurant name comes from the restaurant itself, not from the landing page settings.
    const autoName = String(
      response?.data?.restaurant?.name ||
      response?.data?.restaurant_name ||
      authStore.restaurant?.name ||
      authStore.restaurantName ||
      authStore.tenantName ||
      authStore.user?.restaurant?.name ||
      '',
    ).trim()
    if (autoName) {
      form.name = autoName
    }
    await loadAdminPromotions()
  } catch (error) {
    errorMessage.value = isPublicPage.value
      ? error?.response?.data?.message || 'This restaurant page is unavailable.'
      : systemSettingService.getErrorMessage(
          error,
          'Could not load landing page settings from the server.',
        )
  } finally {
    isLoadingPublic.value = false
  }
})

async function save() {
  isSaving.value = true
  saved.value = false
  errorMessage.value = ''
  try {
    const settings = { ...form }
    await systemSettingService.updateSettings({
      landing_page_settings: settings,
    })
    saved.value = true
    window.setTimeout(() => { saved.value = false }, 2500)
  } catch (error) {
    errorMessage.value = systemSettingService.getErrorMessage(
      error,
      'Could not save landing page settings to the server.',
    )
  } finally {
    isSaving.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Anton&family=DM+Sans:wght@400;500;600;700&display=swap');

.landing-admin{min-height:100%;padding:24px;color:#172033}.page-head{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px}.eyebrow{margin:0 0 5px;color:#f97316;font-size:11px;font-weight:800;letter-spacing:.12em}.page-head h1{margin:0;font-size:24px;font-weight:800}.page-head p:last-child{margin:5px 0 0;color:#738096;font-size:13px}.save-button{border:0;border-radius:8px;background:#111827;color:#fff;padding:11px 16px;font-weight:700}.save-button i{color:#ffc400;margin-right:5px}.saved-message{padding:10px 13px;margin-bottom:14px;border:1px solid #bbf7d0;border-radius:8px;background:#f0fdf4;color:#15803d;font-size:13px}.editor-layout{display:grid;grid-template-columns:minmax(260px,340px) minmax(0,1fr);gap:18px;align-items:start}.settings-card,.preview-wrap{border:1px solid #e5eaf0;border-radius:12px;background:#fff;box-shadow:0 2px 8px #15223808}.settings-card{padding:18px}.card-heading{display:flex;gap:11px;align-items:center;margin-bottom:18px}.icon{width:38px;height:38px;display:grid;place-items:center;border-radius:10px;background:#fff5e8;color:#f97316}.card-heading h2{font-size:15px;margin:0;font-weight:800}.card-heading p{font-size:11px;color:#8792a4;margin:3px 0 0}.settings-card label{display:block;margin:0 0 13px;color:#475569;font-size:11px;font-weight:700}.settings-card input,.settings-card textarea{display:block;width:100%;margin-top:5px;padding:9px 10px;border:1px solid #dbe2eb;border-radius:7px;background:#fff;color:#172033;font:13px inherit;outline:none}.settings-card input:focus,.settings-card textarea:focus{border-color:#f59e0b;box-shadow:0 0 0 3px #f59e0b20}.settings-card .color-input{height:37px;padding:3px}.preview-wrap{overflow:hidden;padding:14px;background:#f4f6f8}.preview-label{margin:0 0 10px;color:#64748b;font-size:10px;font-weight:800;letter-spacing:.1em}.site-preview{max-width:1100px;margin:auto;overflow:hidden;background:#0b0b0b;color:#fff;font-family:Arial,sans-serif;font-size:12px}.promo-bar{padding:7px 4%;background:var(--accent);color:#111;text-align:center;font-size:9px;font-weight:900;letter-spacing:.04em}.site-preview nav{height:48px;display:flex;justify-content:space-between;align-items:center;padding:0 5%;border-bottom:1px solid #202020}.site-preview nav strong,.site-preview footer strong{color:var(--accent);font-size:11px;letter-spacing:.28em}.site-preview nav div{color:#ddd;font-size:9px}.hero{display:grid;grid-template-columns:1fr 1fr;gap:5%;align-items:center;padding:3% 5% 4%}.hero-copy{display:flex;align-items:flex-start;flex-direction:column}.hero h2{max-width:440px;margin:0 0 8px;color:var(--accent);font-size:clamp(38px,6vw,76px);line-height:.78;font-weight:950;letter-spacing:-.06em}.hero-hours{margin:9px 0 13px;font-size:10px;letter-spacing:.04em}.offer-pill{margin:2px 0;padding:7px 11px;border-radius:4px;background:var(--accent);color:#111;font-size:9px;font-weight:900}.hero p{max-width:330px;margin:8px 0;color:#c0c0c0;font-size:10px;line-height:1.5}.hero a{margin-top:3px;padding:9px 13px;border-radius:4px;background:var(--accent);color:#111;text-decoration:none;text-transform:uppercase;font-size:9px;font-weight:900}.food-art{min-height:210px;display:grid;place-items:center;border-radius:4px;background:radial-gradient(ellipse at 48% 50%,#ad6e2d 0,#633918 38%,#21170f 72%,#100e0c 100%);box-shadow:inset 0 0 35px #0008}.plate{width:min(84%,300px);aspect-ratio:1.35;display:flex;align-items:center;justify-content:center;gap:2px;border:9px solid #d6c4a4;border-radius:50%;background:radial-gradient(ellipse,#9c6027,#3e2919 70%);box-shadow:0 12px 30px #000a,inset 0 0 0 4px #735538}.plate span{font-size:clamp(22px,4vw,48px);filter:drop-shadow(0 3px 3px #111)}.deals,.rewards{padding:1% 5% 3%}.deals h3,.rewards h3{margin:0 0 11px;font-size:clamp(18px,2.5vw,27px);font-weight:900}.deals h3 em{color:var(--accent);font-style:normal}.deal-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.deal-card{min-height:120px;padding:12px;border:1px solid var(--accent);border-radius:9px}.deal-card.featured{background:var(--accent);color:#111}.deal-card small{display:inline-block;padding:3px 8px;border-radius:9px;background:#111;color:var(--accent);font-size:7px;font-weight:900}.deal-card:not(.featured) small{background:var(--accent);color:#111}.deal-card strong{display:block;margin-top:4px;font-size:clamp(35px,6vw,68px);line-height:.9;font-weight:950;letter-spacing:-.06em}.deal-card strong i{margin-left:6px;font-size:12px;font-style:normal;letter-spacing:0}.deal-card b{font-size:9px}.deal-card p{margin:4px 0 0;font-size:8px;opacity:.75}.offer{display:flex;justify-content:space-between;gap:8px;padding:13px 5%;background:var(--accent);color:#111}.offer div{display:flex;flex-direction:column;gap:3px}.offer strong{font-size:clamp(15px,2.7vw,26px);font-weight:950}.offer span{font-size:9px}.rewards{padding-top:20px}.reward-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:9px}.reward-grid div{padding:11px;border:1px solid #806312;border-radius:7px}.reward-grid b{font-size:9px}.reward-grid p{margin:5px 0 0;color:#bbb;font-size:8px;line-height:1.5}.reservation{display:grid;grid-template-columns:.8fr 1.2fr;gap:16px;margin:0 5% 24px;padding:16px;border-radius:8px;background:#171717}.reservation h3{margin:0 0 5px;color:var(--accent);font-size:clamp(17px,2.5vw,25px)}.reservation p{max-width:220px;margin:0;color:#aaa;font-size:9px;line-height:1.5}.reservation form{display:grid;gap:7px}.reservation label{display:block;color:#ccc;font-size:7px;font-weight:800;letter-spacing:.04em}.reservation input{display:block;width:100%;height:23px;margin-top:3px;padding:4px 7px;border:0;border-radius:3px;background:#333;color:#fff;font-size:9px}.form-row{display:grid;grid-template-columns:1fr 1fr;gap:7px}.reservation button{height:27px;border:0;border-radius:3px;background:var(--accent);color:#111;font-size:8px;font-weight:900}.site-preview footer{display:flex;justify-content:space-between;gap:10px;padding:12px 5%;border-top:1px solid #222}.site-preview footer span{color:#aaa;text-align:center;font-size:7px;line-height:1.5}.site-preview footer span:last-child{text-align:right}@media(max-width:850px){.editor-layout{grid-template-columns:1fr}.page-head{align-items:flex-start}.preview-wrap{padding:8px}}@media(max-width:560px){.landing-admin{padding:14px}.hero{gap:12px;padding:5% 4%}.hero h2{font-size:40px}.food-art{min-height:140px}.site-preview nav{padding:0 4%}.site-preview nav div{font-size:7px}.deal-grid{gap:6px}.deal-card{padding:8px;min-height:100px}.offer{padding:12px 4%}.reservation{grid-template-columns:1fr;margin:0 4% 18px}.reward-grid{gap:5px}.reward-grid div{padding:8px}.deals,.rewards{padding-right:4%;padding-left:4%}}
.food-art{height:clamp(130px,21vw,260px);min-height:0;overflow:hidden;border:1px solid #423325;border-radius:4px;background:#21170f;box-shadow:0 8px 22px #0008}.food-art img{display:block;width:100%;height:100%;object-fit:cover;object-position:center 54%}
.saved-message.save-error{border-color:#fecaca;background:#fef2f2;color:#b91c1c}.save-button:disabled{cursor:wait;opacity:.7}
.public-url-box{display:flex;flex-direction:column;gap:4px;margin-top:10px;padding:8px;border:1px solid #dbe2eb;border-radius:7px;background:#f8fafc;font-size:10px}.public-url-box a{overflow-wrap:anywhere;color:#2563eb}.public-landing{min-height:100vh;padding:0;background:var(--page-bg,#0b0b0b);color:#fff}.public-landing .editor-layout{display:block}.public-landing .preview-wrap{overflow:visible;padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}.public-landing .site-preview{width:100%;max-width:none;min-height:100vh}.public-state{display:grid;min-height:100vh;place-items:center;background:#0b0b0b;color:#fff;font:600 16px Arial,sans-serif}.public-error{padding:24px;color:#fecaca;text-align:center}.page-actions{display:flex;align-items:center;justify-content:flex-end;gap:10px}.view-page-button{display:inline-flex;align-items:center;gap:6px;padding:10px 13px;border:1px solid #d5dce6;border-radius:8px;background:#fff;color:#182235;text-decoration:none;font-size:12px;font-weight:700}.view-page-button:hover{border-color:#f59e0b;background:#fffbeb;color:#111827}@media(max-width:520px){.page-actions{width:100%;justify-content:flex-start}.view-page-button,.page-actions .save-button{flex:1;text-align:center;justify-content:center;padding:9px 7px}}
.hero{padding:clamp(10px,1.2vw,18px) 5% clamp(12px,1.3vw,20px)}.deals,.rewards{padding:clamp(8px,1vw,12px) 5% clamp(10px,1.1vw,14px)}.rewards{padding-top:clamp(12px,1.4vw,16px)}.reservation{gap:12px;margin-bottom:clamp(10px,1.2vw,14px);padding:clamp(10px,1vw,12px)}
.editor-layout{display:block}.settings-card{max-width:520px;margin:0 0 14px}.page-head{flex-wrap:wrap}.settings-toggle{margin-left:auto;border:1px solid #d7dee8;border-radius:8px;background:#fff;color:#1f2937;padding:10px 14px;font-size:13px;font-weight:700}.settings-toggle i{margin-right:5px;color:#f97316}
.site-preview nav div{display:flex;align-items:center;gap:14px}.site-preview nav a{color:#ddd;text-decoration:none}.site-preview nav a:hover{color:var(--accent)}.site-preview nav strong a{color:var(--accent);letter-spacing:inherit}
.site-preview{background:var(--page-bg,#0b0b0b)}.editor-layout.with-settings{display:grid;grid-template-columns:minmax(230px,290px) minmax(0,1fr);gap:12px;align-items:stretch}.editor-layout.with-settings .settings-card{position:static;max-width:none;max-height:none;overflow:visible;margin:0;padding:11px}.editor-layout.with-settings .settings-card .card-heading{gap:8px;margin-bottom:9px}.editor-layout.with-settings .settings-card .icon{width:30px;height:30px;flex:none}.editor-layout.with-settings .settings-card label{margin:0 0 6px;font-size:10px;line-height:1.15}.editor-layout.with-settings .settings-card input,.editor-layout.with-settings .settings-card textarea{margin-top:3px;padding:5px 7px;font-size:11px;line-height:1.2}.editor-layout.with-settings .settings-card input:not([type=color]){height:29px}.editor-layout.with-settings .settings-card textarea{height:56px;min-height:56px;resize:vertical}.editor-layout.with-settings .settings-card .color-input{width:100%;height:29px;padding:2px}.editor-layout.with-settings .preview-wrap{min-width:0;padding:9px}.editor-layout.with-settings .site-preview{width:100%;max-width:none}.site-preview .kitchen-grid>div,.site-preview .reward-grid>div,.site-preview .reservation,.site-preview .deal-card:not(.featured){background:var(--surface-bg,#191919)}@media(max-width:850px){.editor-layout.with-settings{grid-template-columns:1fr}.editor-layout.with-settings .settings-card{position:static;max-height:none;overflow:visible}}

/* settings groups */
.settings-group{margin:0 0 14px;padding-bottom:6px;border-bottom:1px solid #eef1f5}
.settings-group:last-child{border-bottom:0}
.settings-group>h3{margin:0 0 8px;color:#f97316;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}

/* ============================================================
   HOME PAGE — colors come from the settings:
   --accent (yellow), --text (white), --ink (black on accent),
   --page-bg (background), --surface-bg (boxes / cards).
   --px = "one pixel of the design". Smaller value = smaller page.
   ============================================================ */
.site-preview.home-preview{container-type:inline-size;background:var(--page-bg,#0a0a0a);color:var(--text,#fff);font-family:'DM Sans',Arial,sans-serif}
.site-preview.home-preview>*{--px:min(.067cqw,.98px);--pad:5.5cqw}
@container (max-width:700px){.site-preview.home-preview>*{--px:min(.15cqw,.9px)}}

/* nav */
.site-preview.home-preview nav{height:calc(var(--px)*85);padding:0 var(--pad);border:0;background:transparent}
.site-preview.home-preview nav strong{font-family:Anton,Impact,sans-serif;font-weight:400;font-size:calc(var(--px)*25);letter-spacing:.32em;color:var(--accent)}
.site-preview.home-preview nav div{gap:calc(var(--px)*26);font-size:calc(var(--px)*15)}
.site-preview.home-preview nav div a{padding:calc(var(--px)*5) 0;border-bottom:calc(var(--px)*2) solid transparent;color:var(--text);font-size:calc(var(--px)*15);font-weight:500}
.site-preview.home-preview nav div a:hover{color:var(--accent)}
.site-preview.home-preview nav div a.active{color:var(--accent);border-bottom-color:var(--accent)}

/* hero */
.site-preview.home-preview .home-hero{display:grid;grid-template-columns:1.08fr 1fr;gap:calc(var(--px)*40);align-items:center;padding:calc(var(--px)*40) var(--pad) calc(var(--px)*100)}
.site-preview.home-preview .home-kicker{display:block;font-family:Anton,Impact,sans-serif;font-weight:400;font-size:calc(var(--px)*22);letter-spacing:.14em;color:var(--accent)}
.site-preview.home-preview .home-copy h2{max-width:calc(var(--px)*520);margin:calc(var(--px)*6) 0 calc(var(--px)*30);font:400 calc(var(--px)*104)/1 Anton,Impact,sans-serif;letter-spacing:0;color:var(--text)}
.site-preview.home-preview .home-copy h2 em{color:var(--accent);font-style:normal}
.site-preview.home-preview .home-copy p{max-width:calc(var(--px)*470);margin:0 0 calc(var(--px)*34);font-size:calc(var(--px)*20);line-height:1.5;color:var(--text)}
.site-preview.home-preview .home-copy .primary-cta,.site-preview.home-preview .home-copy .secondary-cta{display:inline-flex;align-items:center;height:calc(var(--px)*56);margin:0 calc(var(--px)*14) calc(var(--px)*6) 0;padding:0 calc(var(--px)*32);border:calc(var(--px)*2) solid var(--accent);border-radius:calc(var(--px)*5);background:var(--accent);color:var(--ink);font-size:calc(var(--px)*16);font-weight:700;letter-spacing:.05em;text-transform:uppercase;text-decoration:none}
.site-preview.home-preview .home-copy .secondary-cta{background:transparent;border-color:var(--text);color:var(--text)}
.site-preview.home-preview .home-hero .food-art{height:auto;min-height:0;aspect-ratio:1.435;border:1px solid color-mix(in srgb,var(--text) 55%,transparent);border-radius:calc(var(--px)*8);background:#1a120b;box-shadow:none}
.site-preview.home-preview .home-hero .food-art img{object-position:center}

/* public page: hero fills the first screen, content centered, yellow band starts below the fold */
.public-landing .site-preview.home-preview .home-hero{min-height:calc(100vh - var(--px)*85);box-sizing:border-box;padding-top:calc(var(--px)*30);padding-bottom:calc(var(--px)*50)}

/* happy hours band */
.site-preview.home-preview .home-happy{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:calc(var(--px)*24);padding:calc(var(--px)*48) var(--pad) calc(var(--px)*52);background:var(--accent);color:var(--ink)}
.site-preview.home-preview .home-happy h3{margin:0 0 calc(var(--px)*18);font:400 calc(var(--px)*66)/1 Anton,Impact,sans-serif;letter-spacing:0;color:var(--ink)}
.site-preview.home-preview .happy-pills{display:flex;flex-wrap:wrap;gap:calc(var(--px)*12)}
.site-preview.home-preview .happy-pills span{display:flex;flex-direction:column;padding:calc(var(--px)*10) calc(var(--px)*20);border-radius:calc(var(--px)*9);background:var(--ink);color:var(--accent);font:400 calc(var(--px)*40)/1.1 Anton,Impact,sans-serif}
.site-preview.home-preview .happy-pills small{margin-top:calc(var(--px)*2);font:700 calc(var(--px)*15)/1.3 'DM Sans',Arial,sans-serif;color:var(--text)}
.site-preview.home-preview .home-happy button{height:calc(var(--px)*56);padding:0 calc(var(--px)*30);border:0;border-radius:calc(var(--px)*6);background:var(--ink);color:var(--accent);font:700 calc(var(--px)*16) 'DM Sans',Arial,sans-serif;letter-spacing:.05em;cursor:pointer}

/* from our kitchen */
.site-preview.home-preview .kitchen{padding:calc(var(--px)*110) var(--pad) calc(var(--px)*70)}
.site-preview.home-preview .kitchen h3{margin:0 0 calc(var(--px)*38);font:400 calc(var(--px)*60)/1.1 Anton,Impact,sans-serif;letter-spacing:0;color:var(--accent)}
.site-preview.home-preview .kitchen-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:calc(var(--px)*20)}
.site-preview.home-preview .kitchen-grid>div{min-height:calc(var(--px)*207);padding:calc(var(--px)*28) calc(var(--px)*30);border-radius:calc(var(--px)*14);background:var(--surface-bg,#1a1a1a)}
.site-preview.home-preview .kitchen-grid>div:last-child{grid-column:1/-1;min-height:calc(var(--px)*182)}
.site-preview.home-preview .kitchen-grid b{display:block;margin-bottom:calc(var(--px)*16);font:400 calc(var(--px)*48)/1 Anton,Impact,sans-serif;color:var(--accent)}
.site-preview.home-preview .kitchen-grid strong{display:block;margin:0 0 calc(var(--px)*8);font:600 calc(var(--px)*24)/1.2 'DM Sans',Arial,sans-serif;color:var(--text)}
.site-preview.home-preview .kitchen-grid p{margin:0;font-size:calc(var(--px)*16);line-height:1.6;color:var(--text);opacity:.85}

/* about */
.site-preview.home-preview .home-about{display:grid;grid-template-columns:1fr 1fr;gap:calc(var(--px)*40);align-items:center;padding:calc(var(--px)*70) var(--pad) calc(var(--px)*90)}
.site-preview.home-preview .home-about h3{margin:0 0 calc(var(--px)*20);font:400 calc(var(--px)*62)/1.1 Anton,Impact,sans-serif;letter-spacing:0;color:var(--accent)}
.site-preview.home-preview .home-about p{max-width:calc(var(--px)*520);margin:0;font-size:calc(var(--px)*18);line-height:1.7;color:var(--text)}
.site-preview.home-preview .about-links{display:flex;gap:calc(var(--px)*21)}
.site-preview.home-preview .about-links a{display:flex;flex:1;flex-direction:column;align-items:flex-start;gap:calc(var(--px)*12);min-width:0;min-height:calc(var(--px)*160);padding:calc(var(--px)*24) calc(var(--px)*26);border:calc(var(--px)*2) solid var(--accent);border-radius:calc(var(--px)*12);background:transparent;color:var(--accent);text-decoration:none}
.site-preview.home-preview .about-links b{font:400 calc(var(--px)*38)/1 Anton,Impact,sans-serif;color:var(--accent)}
.site-preview.home-preview .about-links small{font-size:calc(var(--px)*15);line-height:1.4;color:var(--text)}

/* reservation */
.site-preview.home-preview .reservation{display:grid;grid-template-columns:1fr 1fr;gap:0;margin:calc(var(--px)*40) var(--pad) calc(var(--px)*100);padding:calc(var(--px)*48);border-radius:calc(var(--px)*14);background:var(--surface-bg,#1a1a1a)}
.site-preview.home-preview .reservation h3{margin:calc(var(--px)*8) 0 calc(var(--px)*14);font:400 calc(var(--px)*58)/1.1 Anton,Impact,sans-serif;letter-spacing:0;color:var(--accent)}
.site-preview.home-preview .reservation p{max-width:calc(var(--px)*430);font-size:calc(var(--px)*17);line-height:1.5;color:var(--text);opacity:.85}
.site-preview.home-preview .reservation form{gap:calc(var(--px)*14)}
.site-preview.home-preview .reservation label{font:700 calc(var(--px)*14)/1 'DM Sans',Arial,sans-serif;letter-spacing:.06em;color:var(--text)}
.site-preview.home-preview .reservation input{height:calc(var(--px)*50);margin-top:calc(var(--px)*10);padding:0 calc(var(--px)*14);border-radius:calc(var(--px)*4);background:color-mix(in srgb,var(--text) 16%,var(--surface-bg,#1a1a1a));color:var(--text);font:400 calc(var(--px)*16) 'DM Sans',Arial,sans-serif}
.site-preview.home-preview .reservation input::placeholder{color:transparent}
.site-preview.home-preview .reservation .form-row{gap:calc(var(--px)*14)}
.site-preview.home-preview .reservation button{height:calc(var(--px)*52);margin-top:calc(var(--px)*2);border-radius:calc(var(--px)*4);background:var(--accent);color:var(--ink);font:700 calc(var(--px)*17) 'DM Sans',Arial,sans-serif;letter-spacing:.04em;text-transform:uppercase}

/* footer */
.site-preview.home-preview footer{display:flex;align-items:center;justify-content:space-between;gap:calc(var(--px)*24);margin:0 3.6cqw;padding:calc(var(--px)*56) 1.9cqw calc(var(--px)*64);border-top:1px solid color-mix(in srgb,var(--text) 18%,transparent)}
.site-preview.home-preview footer strong{font:400 calc(var(--px)*25)/1 Anton,Impact,sans-serif;letter-spacing:.32em;color:var(--accent)}
.site-preview.home-preview footer span,.site-preview.home-preview footer span:last-child{font-size:calc(var(--px)*15);line-height:1.9;text-align:left;color:var(--text);opacity:.85}

/* narrow preview / phones: stack columns */
@container (max-width:700px){
  .site-preview.home-preview .home-hero,.site-preview.home-preview .home-about,.site-preview.home-preview .reservation{grid-template-columns:1fr}
  .site-preview.home-preview .home-hero{padding-bottom:calc(var(--px)*60)}
  .site-preview.home-preview .home-happy{flex-direction:column;align-items:flex-start}
  .site-preview.home-preview .kitchen-grid{grid-template-columns:1fr}
  .site-preview.home-preview .reservation{row-gap:calc(var(--px)*24)}
  .site-preview.home-preview footer{flex-direction:column;align-items:flex-start}
}

/* ============================================================
   HAPPY HOURS (OFFER) PAGE
   Reuses the home page nav / reservation / footer styles,
   the same --px scale and the same color variables.
   ============================================================ */
.site-preview.offer-preview .promo-bar{padding:calc(var(--px)*13) var(--pad);background:var(--accent);color:var(--ink);text-align:center;font:400 calc(var(--px)*19)/1.2 Anton,Impact,sans-serif;letter-spacing:.14em}
.site-preview.offer-preview nav{height:calc(var(--px)*90)}

/* hero */
.site-preview.offer-preview .hero{display:grid;grid-template-columns:1fr 1fr;gap:calc(var(--px)*50);align-items:center;padding:calc(var(--px)*30) var(--pad) calc(var(--px)*70)}
.site-preview.offer-preview .hero-copy{display:flex;flex-direction:column;align-items:flex-start}
.site-preview.offer-preview .hero h2{max-width:none;margin:0 0 calc(var(--px)*24);font:400 calc(var(--px)*112)/.95 Anton,Impact,sans-serif;letter-spacing:0;color:var(--accent)}
.site-preview.offer-preview .hero-hours{display:block;margin:0 0 calc(var(--px)*22);font:400 calc(var(--px)*30)/1.1 Anton,Impact,sans-serif;letter-spacing:.02em;color:var(--text)}
.site-preview.offer-preview .offer-pill{display:inline-flex;align-items:center;margin:0 0 calc(var(--px)*12);padding:calc(var(--px)*10) calc(var(--px)*18);border:calc(var(--px)*2) solid var(--accent);border-radius:calc(var(--px)*8);background:var(--accent);color:var(--ink);font:400 calc(var(--px)*22)/1.1 Anton,Impact,sans-serif;letter-spacing:.04em}
.site-preview.offer-preview .offer-pill+.offer-pill{background:transparent;color:var(--accent)}
.site-preview.offer-preview .hero p{max-width:calc(var(--px)*500);margin:calc(var(--px)*8) 0 calc(var(--px)*26);font-size:calc(var(--px)*18);line-height:1.5;color:var(--text);opacity:.88}
.site-preview.offer-preview .hero-copy>a{display:inline-flex;align-items:center;height:calc(var(--px)*54);margin:0;padding:0 calc(var(--px)*30);border-radius:calc(var(--px)*5);background:var(--accent);color:var(--ink);font:700 calc(var(--px)*16) 'DM Sans',Arial,sans-serif;letter-spacing:.05em;text-transform:uppercase;text-decoration:none}
.site-preview.offer-preview .hero .food-art{height:auto;min-height:0;aspect-ratio:1.435;border:1px solid color-mix(in srgb,var(--text) 55%,transparent);border-radius:calc(var(--px)*8);background:#1a120b;box-shadow:none}
.site-preview.offer-preview .hero .food-art img{object-position:center}

/* today's big deals */
.site-preview.offer-preview .deals{padding:calc(var(--px)*90) var(--pad) calc(var(--px)*70)}
.site-preview.offer-preview .deals h3{margin:0 0 calc(var(--px)*30);font:400 calc(var(--px)*50)/1.1 Anton,Impact,sans-serif;letter-spacing:0;color:var(--text)}
.site-preview.offer-preview .deals h3 em{color:var(--accent);font-style:normal}
.site-preview.offer-preview .deal-grid{display:grid;grid-template-columns:1fr 1fr;gap:calc(var(--px)*24)}
.site-preview.offer-preview .no-public-offers{grid-column:1/-1;color:var(--text);opacity:.8}
.site-preview.offer-preview .deal-card{min-height:calc(var(--px)*250);padding:calc(var(--px)*28);border:calc(var(--px)*2) solid var(--accent);border-radius:calc(var(--px)*20);color:var(--text)}
.site-preview.offer-preview .deal-card.featured{background:var(--accent);color:var(--ink)}
.site-preview.offer-preview .deal-card:not(.featured){background:var(--surface-bg,#191919)}
.site-preview.offer-preview .deal-card small{display:inline-block;padding:calc(var(--px)*6) calc(var(--px)*13);border-radius:999px;background:var(--ink);color:var(--accent);font:700 calc(var(--px)*11)/1 'DM Sans',Arial,sans-serif;letter-spacing:.16em}
.site-preview.offer-preview .deal-card:not(.featured) small{background:var(--accent);color:var(--ink)}
.site-preview.offer-preview .deal-card strong{display:block;margin:calc(var(--px)*6) 0 calc(var(--px)*4);font:400 calc(var(--px)*100)/1 Anton,Impact,sans-serif;letter-spacing:0;color:inherit}
.site-preview.offer-preview .deal-card:not(.featured) strong{color:var(--accent)}
.site-preview.offer-preview .deal-card strong i{margin-left:calc(var(--px)*8);font:400 calc(var(--px)*34)/1 Anton,Impact,sans-serif;letter-spacing:0}
.site-preview.offer-preview .deal-card b{display:block;font:400 calc(var(--px)*26)/1.1 Anton,Impact,sans-serif;letter-spacing:.02em}
.site-preview.offer-preview .deal-card p{max-width:calc(var(--px)*420);margin:calc(var(--px)*8) 0 0;font-size:calc(var(--px)*14);line-height:1.45;opacity:1}
.site-preview.offer-preview .deal-chips{display:flex;flex-wrap:wrap;gap:calc(var(--px)*12);margin-top:calc(var(--px)*28)}
.site-preview.offer-preview .deal-chips span{padding:calc(var(--px)*11) calc(var(--px)*18);border-radius:999px;background:var(--surface-bg,#1a1a1a);color:var(--text);font-size:calc(var(--px)*15)}

/* yellow offer band */
.site-preview.offer-preview .offer{display:grid;grid-template-columns:repeat(3,1fr);gap:calc(var(--px)*24);padding:calc(var(--px)*52) var(--pad) calc(var(--px)*56);background:var(--accent);color:var(--ink)}
.site-preview.offer-preview .offer div{display:flex;flex-direction:column;gap:calc(var(--px)*6)}
.site-preview.offer-preview .offer strong{font:400 calc(var(--px)*70)/1 Anton,Impact,sans-serif;letter-spacing:0;color:var(--ink)}
.site-preview.offer-preview .offer span{font:600 calc(var(--px)*20)/1.3 'DM Sans',Arial,sans-serif;color:var(--ink)}

/* rewards */
.site-preview.offer-preview .rewards{padding:calc(var(--px)*100) var(--pad) calc(var(--px)*60)}
.site-preview.offer-preview .rewards h3{margin:0 0 calc(var(--px)*38);font:400 calc(var(--px)*62)/1.1 Anton,Impact,sans-serif;letter-spacing:0;color:var(--accent)}
.site-preview.offer-preview .reward-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:calc(var(--px)*20)}
.site-preview.offer-preview .reward-grid>div{min-height:calc(var(--px)*150);padding:calc(var(--px)*28) calc(var(--px)*30);border:calc(var(--px)*2) solid var(--accent);border-radius:calc(var(--px)*14);background:var(--surface-bg,#191919)}
.site-preview.offer-preview .reward-grid b{display:block;font:600 calc(var(--px)*22)/1.2 'DM Sans',Arial,sans-serif;color:var(--text)}
.site-preview.offer-preview .reward-grid p{margin:calc(var(--px)*10) 0 0;font-size:calc(var(--px)*16);line-height:1.6;color:var(--text);opacity:.85}

/* narrow preview / phones */
@container (max-width:700px){
  .site-preview.offer-preview .hero,.site-preview.offer-preview .deal-grid,.site-preview.offer-preview .offer,.site-preview.offer-preview .reward-grid{grid-template-columns:1fr}
  .site-preview.offer-preview .deal-card{min-height:0}
}
</style>
