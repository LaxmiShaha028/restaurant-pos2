<template>
  <main class="landing-admin" :class="{ 'public-landing': isPublicPage }">
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
        <label>Restaurant name<input v-model="form.name" maxlength="60" /></label>
        <label>Happy hours headline<input v-model="form.headline" maxlength="100" /></label>
        <label>Happy hours details<textarea v-model="form.description" rows="2" maxlength="240"></textarea></label>
        <label>Address<input v-model="form.address" maxlength="140" /></label>
        <label>Phone number<input v-model="form.phone" maxlength="40" /></label>
        <label>Opening hours<input v-model="form.hours" maxlength="80" /></label>
        <label>Half price starters and sides<input v-model="form.starterOffer" maxlength="30" /></label>
        <label>All drinks offer<input v-model="form.drinkOffer" maxlength="30" /></label>
        <label>Accent color<input v-model="form.color" type="color" class="color-input" /></label>
        <label>Background color<input v-model="form.backgroundColor" type="color" class="color-input" /></label>
        <label>Section / card color<input v-model="form.surfaceColor" type="color" class="color-input" /></label>
        <div v-if="publicPageUrl" class="public-url-box"><strong>Public landing page</strong><a :href="publicPageUrl" target="_blank" rel="noopener noreferrer">{{ publicPageUrl }}</a></div>
      </section>

      <section class="preview-wrap">
        <div v-if="!isPublicPage" class="preview-label"><i class="bi bi-eye"></i> LIVE PREVIEW</div>
        <article v-if="!showOfferPage" class="site-preview home-preview" :style="{ '--accent': form.color, '--page-bg': form.backgroundColor, '--surface-bg': form.surfaceColor }">
          <nav><strong>{{ form.name || 'SOPNO' }}</strong><div><a href="#" @click.prevent="showOfferPage = false">Home</a><a href="#menu">Menu</a><a href="#happy-hours" @click.prevent="showOfferPage = true">Happy hours</a><a href="#rewards">Rewards</a><a href="#reserve">Reserve</a><a href="#contact">Contact</a></div></nav>
          <section class="home-hero">
            <div class="home-copy"><span class="home-kicker">BURGERS · STARTERS · SIDES · DRINKS</span><h2>Good food.<br><em>Good nights.</em></h2><p>Welcome to {{ form.name || 'SOPNO' }}. Big plates to share, cold drinks, and a warm table waiting for you.</p><a class="primary-cta" href="#reserve">Reserve a table</a><a class="secondary-cta" href="#menu">View menu</a></div>
            <div class="food-art"><img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=1000&q=85" alt="A platter of grilled food served at the restaurant" /></div>
          </section>
          <section id="happy-hours" class="home-happy"><div><h3>HAPPY HOURS, EVERYDAY</h3><div class="happy-pills"><span>HALF PRICE<small>Starters and sides</small></span><span>30% OFF<small>All drinks</small></span><span>{{ form.hours }}<small>Every day of the week</small></span></div></div><button type="button" @click="showOfferPage = true">SEE THE OFFER</button></section>
          <section id="menu" class="kitchen"><h3>From our kitchen</h3><div class="kitchen-grid"><div><b>01</b><strong>Burgers</strong><p>Brioches buns, melted cheese, and sauces made in house.</p></div><div><b>02</b><strong>Starters</strong><p>Our plates made for sharing with dips on the side.</p></div><div><b>03</b><strong>Sides</strong><p>Seasoned wedges and crunchy extras to round out the table.</p></div><div><b>04</b><strong>Drinks</strong><p>Classic cocktails and cold drinks, poured all evening.</p></div></div></section>
          <section id="contact" class="home-about"><div><h3>About {{ form.name || 'SOPNO' }}</h3><p>YOUR STORY: Two or three lines about your restaurant, your cooking, and what guests can expect.</p></div><div class="about-links"><a href="#contact"><b>OPEN</b><small>{{ form.hours }}</small></a><a href="#contact"><b>FIND US</b><small>{{ form.address || 'YOUR ADDRESS' }}</small></a></div></section>
          <section id="reserve" class="reservation"><div><h3>Reserve your table</h3><p>Tell us when you are coming. We will confirm by SMS.</p></div><form @submit.prevent><label>NAME<input placeholder="Your name" /></label><label>MOBILE NUMBER<input placeholder="Mobile number" /></label><div class="form-row"><label>DATE<input type="text" placeholder="Select date" /></label><label>GUESTS<input type="text" placeholder="2 guests" /></label></div><button type="button">CONFIRM RESERVATION</button></form></section>
          <footer><strong>{{ form.name || 'SOPNO' }}</strong><span>{{ form.address || 'ADDRESS YOUR ADDRESS' }}<br>{{ form.phone || 'PHONE: 000 000 0000' }}</span><span>OPEN {{ form.hours || 'EVERY DAY' }}<br>Terms apply</span></footer>
        </article>

        <article v-else class="site-preview offer-preview" :style="{ '--accent': form.color, '--page-bg': form.backgroundColor, '--surface-bg': form.surfaceColor }">
          <div class="promo-bar">35% OFF ALL DRINKS · {{ form.starterOffer }} · {{ form.hours }}</div>
          <nav><strong><a href="#" @click.prevent="showOfferPage = false">{{ form.name || 'SOPNO' }}</a></strong><div><a href="#" @click.prevent="showOfferPage = false">Home</a><a href="#" @click.prevent="showOfferPage = false">Menu</a><a href="#" @click.prevent="showOfferPage = true">Happy hours</a><a href="#" @click.prevent="showOfferPage = false">Rewards</a><a href="#reserve">Reserve</a><a href="#" @click.prevent="showOfferPage = false">Contact</a></div></nav>
          <section class="hero">
            <div class="hero-copy">
              <h2>{{ form.headline || 'happy hours' }}</h2>
              <b class="hero-hours">EVERYDAY, {{ form.hours || '16:00 TO 19:00' }}</b>
              <span class="offer-pill">{{ form.starterOffer || '50% OFF STARTERS & SIDES' }}</span>
              <span class="offer-pill">{{ form.drinkOffer || '30% OFF ALL DRINKS' }}</span>
              <p>{{ form.description || 'Half price starters and sides, and 30% off all drinks. The discount is applied automatically at the bill.' }}</p>
              <a href="#reserve">Reserve a table</a>
            </div>
            <div class="food-art"><img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=1000&q=85" alt="A platter of grilled food served at the restaurant" /></div>
          </section>
          <section class="deals"><h3>Today's <em>big deals</em></h3><div class="deal-grid"><div class="deal-card featured"><small>BEST DEAL</small><strong>50%<i>OFF</i></strong><b>STARTERS & SIDES</b><p>Half price on every starter and side.</p></div><div class="deal-card"><small>ALL EVENING</small><strong>30%<i>OFF</i></strong><b>COCKTAILS & COLD DRINKS</b><p>30% off every drink during happy hours.</p></div></div></section>
          <section class="offer"><div><strong>HALF PRICE</strong><span>Starters and sides</span></div><div><strong>30% OFF</strong><span>All drinks</span></div><div><strong>{{ form.hours || '16:00–19:00' }}</strong><span>Every day of the week</span></div></section>
          <section class="rewards"><h3>Rewards for regulars</h3><div class="reward-grid"><div><b>Birthday treat</b><p>Share your birthday and get a special offer during your birthday week.</p></div><div><b>Loyalty reward</b><p>Visit often and unlock a reward on every fifth visit.</p></div><div><b>First to know</b><p>Get news about new dishes and special offers.</p></div></div></section>
          <section id="reserve" class="reservation"><div><h3>Reserve your table</h3><p>Tell us when you are coming. We will confirm by SMS.</p></div><form @submit.prevent><label>NAME<input placeholder="Your name" /></label><label>MOBILE NUMBER<input placeholder="Mobile number" /></label><div class="form-row"><label>DATE<input type="text" placeholder="Select date" /></label><label>GUESTS<input type="text" placeholder="2 guests" /></label></div><label>BIRTHDAY (OPTIONAL)<input placeholder="DD / MM" /></label><button type="button">CONFIRM RESERVATION</button></form></section>
          <footer><strong>{{ form.name || 'SOPNO' }}</strong><span>{{ form.address || 'ADDRESS YOUR ADDRESS' }}<br>{{ form.phone || 'PHONE: 000 000 0000' }}</span><span>OPEN {{ form.hours || 'EVERY DAY' }}<br>Terms apply</span></footer>
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

const defaults = {
  name: 'SOPNO',
  headline: 'happy hours',
  description: 'Half price starters and sides, and 30% off all drinks. The discount is applied automatically at the bill.',
  address: '',
  phone: '',
  hours: '16:00–19:00',
  starterOffer: '50% OFF STARTERS & SIDES',
  drinkOffer: '30% OFF ALL DRINKS',
  color: '#ffc400',
  backgroundColor: '#0b0b0b',
  surfaceColor: '#191919',
}

const form = reactive({ ...defaults })
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

onMounted(async () => {
  try {
    if (isPublicPage.value) {
      isLoadingPublic.value = true
      const tenantKey = encodeURIComponent(String(route.params.tenantKey || ''))
      const response = await api.get(`/public/landing-page/${tenantKey}`)
      const data = response?.data?.data || {}
      const pageSettings = data.landing_page_settings || {}
      Object.assign(form, defaults, pageSettings)
      if (!pageSettings.name && data.restaurant?.name) {
        form.name = data.restaurant.name
      }
      return
    }

    const response = await systemSettingService.getSettings()
    const serverSettings = response?.data?.landing_page_settings
    if (serverSettings && Object.keys(serverSettings).length) {
      Object.assign(form, defaults, serverSettings)
    }
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
.landing-admin{min-height:100%;padding:24px;color:#172033}.page-head{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px}.eyebrow{margin:0 0 5px;color:#f97316;font-size:11px;font-weight:800;letter-spacing:.12em}.page-head h1{margin:0;font-size:24px;font-weight:800}.page-head p:last-child{margin:5px 0 0;color:#738096;font-size:13px}.save-button{border:0;border-radius:8px;background:#111827;color:#fff;padding:11px 16px;font-weight:700}.save-button i{color:#ffc400;margin-right:5px}.saved-message{padding:10px 13px;margin-bottom:14px;border:1px solid #bbf7d0;border-radius:8px;background:#f0fdf4;color:#15803d;font-size:13px}.editor-layout{display:grid;grid-template-columns:minmax(260px,340px) minmax(0,1fr);gap:18px;align-items:start}.settings-card,.preview-wrap{border:1px solid #e5eaf0;border-radius:12px;background:#fff;box-shadow:0 2px 8px #15223808}.settings-card{padding:18px}.card-heading{display:flex;gap:11px;align-items:center;margin-bottom:18px}.icon{width:38px;height:38px;display:grid;place-items:center;border-radius:10px;background:#fff5e8;color:#f97316}.card-heading h2{font-size:15px;margin:0;font-weight:800}.card-heading p{font-size:11px;color:#8792a4;margin:3px 0 0}.settings-card label{display:block;margin:0 0 13px;color:#475569;font-size:11px;font-weight:700}.settings-card input,.settings-card textarea{display:block;width:100%;margin-top:5px;padding:9px 10px;border:1px solid #dbe2eb;border-radius:7px;background:#fff;color:#172033;font:13px inherit;outline:none}.settings-card input:focus,.settings-card textarea:focus{border-color:#f59e0b;box-shadow:0 0 0 3px #f59e0b20}.settings-card .color-input{height:37px;padding:3px}.preview-wrap{overflow:hidden;padding:14px;background:#f4f6f8}.preview-label{margin:0 0 10px;color:#64748b;font-size:10px;font-weight:800;letter-spacing:.1em}.site-preview{max-width:1100px;margin:auto;overflow:hidden;background:#0b0b0b;color:#fff;font-family:Arial,sans-serif;font-size:12px}.promo-bar{padding:7px 4%;background:var(--accent);color:#111;text-align:center;font-size:9px;font-weight:900;letter-spacing:.04em}.site-preview nav{height:48px;display:flex;justify-content:space-between;align-items:center;padding:0 5%;border-bottom:1px solid #202020}.site-preview nav strong,.site-preview footer strong{color:var(--accent);font-size:11px;letter-spacing:.28em}.site-preview nav div{color:#ddd;font-size:9px}.hero{display:grid;grid-template-columns:1fr 1fr;gap:5%;align-items:center;padding:3% 5% 4%}.hero-copy{display:flex;align-items:flex-start;flex-direction:column}.hero h2{max-width:440px;margin:0 0 8px;color:var(--accent);font-size:clamp(38px,6vw,76px);line-height:.78;font-weight:950;letter-spacing:-.06em}.hero-hours{margin:9px 0 13px;font-size:10px;letter-spacing:.04em}.offer-pill{margin:2px 0;padding:7px 11px;border-radius:4px;background:var(--accent);color:#111;font-size:9px;font-weight:900}.hero p{max-width:330px;margin:8px 0;color:#c0c0c0;font-size:10px;line-height:1.5}.hero a{margin-top:3px;padding:9px 13px;border-radius:4px;background:var(--accent);color:#111;text-decoration:none;text-transform:uppercase;font-size:9px;font-weight:900}.food-art{min-height:210px;display:grid;place-items:center;border-radius:4px;background:radial-gradient(ellipse at 48% 50%,#ad6e2d 0,#633918 38%,#21170f 72%,#100e0c 100%);box-shadow:inset 0 0 35px #0008}.plate{width:min(84%,300px);aspect-ratio:1.35;display:flex;align-items:center;justify-content:center;gap:2px;border:9px solid #d6c4a4;border-radius:50%;background:radial-gradient(ellipse,#9c6027,#3e2919 70%);box-shadow:0 12px 30px #000a,inset 0 0 0 4px #735538}.plate span{font-size:clamp(22px,4vw,48px);filter:drop-shadow(0 3px 3px #111)}.deals,.rewards{padding:1% 5% 3%}.deals h3,.rewards h3{margin:0 0 11px;font-size:clamp(18px,2.5vw,27px);font-weight:900}.deals h3 em{color:var(--accent);font-style:normal}.deal-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.deal-card{min-height:120px;padding:12px;border:1px solid var(--accent);border-radius:9px}.deal-card.featured{background:var(--accent);color:#111}.deal-card small{display:inline-block;padding:3px 8px;border-radius:9px;background:#111;color:var(--accent);font-size:7px;font-weight:900}.deal-card:not(.featured) small{background:var(--accent);color:#111}.deal-card strong{display:block;margin-top:4px;font-size:clamp(35px,6vw,68px);line-height:.9;font-weight:950;letter-spacing:-.06em}.deal-card strong i{margin-left:6px;font-size:12px;font-style:normal;letter-spacing:0}.deal-card b{font-size:9px}.deal-card p{margin:4px 0 0;font-size:8px;opacity:.75}.offer{display:flex;justify-content:space-between;gap:8px;padding:13px 5%;background:var(--accent);color:#111}.offer div{display:flex;flex-direction:column;gap:3px}.offer strong{font-size:clamp(15px,2.7vw,26px);font-weight:950}.offer span{font-size:9px}.rewards{padding-top:20px}.reward-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:9px}.reward-grid div{padding:11px;border:1px solid #806312;border-radius:7px}.reward-grid b{font-size:9px}.reward-grid p{margin:5px 0 0;color:#bbb;font-size:8px;line-height:1.5}.reservation{display:grid;grid-template-columns:.8fr 1.2fr;gap:16px;margin:0 5% 24px;padding:16px;border-radius:8px;background:#171717}.reservation h3{margin:0 0 5px;color:var(--accent);font-size:clamp(17px,2.5vw,25px)}.reservation p{max-width:220px;margin:0;color:#aaa;font-size:9px;line-height:1.5}.reservation form{display:grid;gap:7px}.reservation label{display:block;color:#ccc;font-size:7px;font-weight:800;letter-spacing:.04em}.reservation input{display:block;width:100%;height:23px;margin-top:3px;padding:4px 7px;border:0;border-radius:3px;background:#333;color:#fff;font-size:9px}.form-row{display:grid;grid-template-columns:1fr 1fr;gap:7px}.reservation button{height:27px;border:0;border-radius:3px;background:var(--accent);color:#111;font-size:8px;font-weight:900}.site-preview footer{display:flex;justify-content:space-between;gap:10px;padding:12px 5%;border-top:1px solid #222}.site-preview footer span{color:#aaa;text-align:center;font-size:7px;line-height:1.5}.site-preview footer span:last-child{text-align:right}@media(max-width:850px){.editor-layout{grid-template-columns:1fr}.page-head{align-items:flex-start}.preview-wrap{padding:8px}}@media(max-width:560px){.landing-admin{padding:14px}.hero{gap:12px;padding:5% 4%}.hero h2{font-size:40px}.food-art{min-height:140px}.site-preview nav{padding:0 4%}.site-preview nav div{font-size:7px}.deal-grid{gap:6px}.deal-card{padding:8px;min-height:100px}.offer{padding:12px 4%}.reservation{grid-template-columns:1fr;margin:0 4% 18px}.reward-grid{gap:5px}.reward-grid div{padding:8px}.deals,.rewards{padding-right:4%;padding-left:4%}}
.food-art{height:clamp(130px,21vw,260px);min-height:0;overflow:hidden;border:1px solid #423325;border-radius:4px;background:#21170f;box-shadow:0 8px 22px #0008}.food-art img{display:block;width:100%;height:100%;object-fit:cover;object-position:center 54%}
.saved-message.save-error{border-color:#fecaca;background:#fef2f2;color:#b91c1c}.save-button:disabled{cursor:wait;opacity:.7}
.public-url-box{display:flex;flex-direction:column;gap:4px;margin-top:10px;padding:8px;border:1px solid #dbe2eb;border-radius:7px;background:#f8fafc;font-size:10px}.public-url-box a{overflow-wrap:anywhere;color:#2563eb}.public-landing{min-height:100vh;padding:0;background:var(--page-bg,#0b0b0b);color:#fff}.public-landing .editor-layout{display:block}.public-landing .preview-wrap{overflow:visible;padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}.public-landing .site-preview{width:100%;max-width:none;min-height:100vh}.public-state{display:grid;min-height:100vh;place-items:center;background:#0b0b0b;color:#fff;font:600 16px Arial,sans-serif}.public-error{padding:24px;color:#fecaca;text-align:center}.page-actions{display:flex;align-items:center;justify-content:flex-end;gap:10px}.view-page-button{display:inline-flex;align-items:center;gap:6px;padding:10px 13px;border:1px solid #d5dce6;border-radius:8px;background:#fff;color:#182235;text-decoration:none;font-size:12px;font-weight:700}.view-page-button:hover{border-color:#f59e0b;background:#fffbeb;color:#111827}@media(max-width:520px){.page-actions{width:100%;justify-content:flex-start}.view-page-button,.page-actions .save-button{flex:1;text-align:center;justify-content:center;padding:9px 7px}}
.home-preview .home-hero{padding:clamp(12px,1.5vw,22px) 5%}.hero{padding:clamp(10px,1.2vw,18px) 5% clamp(12px,1.3vw,20px)}.deals,.rewards{padding:clamp(8px,1vw,12px) 5% clamp(10px,1.1vw,14px)}.rewards{padding-top:clamp(12px,1.4vw,16px)}.kitchen,.home-about{padding-top:clamp(10px,1vw,12px);padding-bottom:clamp(10px,1vw,12px)}.reservation{gap:12px;margin-bottom:clamp(10px,1.2vw,14px);padding:clamp(10px,1vw,12px)}.home-preview nav{height:36px}
.editor-layout{display:block}.settings-card{max-width:520px;margin:0 0 14px}.page-head{flex-wrap:wrap}.settings-toggle{margin-left:auto;border:1px solid #d7dee8;border-radius:8px;background:#fff;color:#1f2937;padding:10px 14px;font-size:13px;font-weight:700}.settings-toggle i{margin-right:5px;color:#f97316}
.site-preview nav div{display:flex;align-items:center;gap:14px}.site-preview nav a{color:#ddd;text-decoration:none}.site-preview nav a:hover{color:var(--accent)}.site-preview nav strong a{color:var(--accent);letter-spacing:inherit}.home-preview{padding-top:0}.home-preview nav{height:40px}.home-preview .home-hero{display:grid;grid-template-columns:1fr 1fr;gap:5%;align-items:center;padding:5% 5%}.home-copy{display:block}.home-kicker{color:var(--accent);font-size:9px;font-weight:900;letter-spacing:.13em}.home-copy h2{margin:10px 0 8px;color:#f4f0e8;font-size:clamp(34px,5vw,68px);line-height:.82;font-weight:950;letter-spacing:-.06em}.home-copy h2 em{color:var(--accent);font-style:normal}.home-copy p{max-width:320px;color:#bcbcbc;font-size:9px;line-height:1.55}.home-copy .primary-cta,.home-copy .secondary-cta{display:inline-block;margin:4px 5px 0 0;padding:8px 10px;border:1px solid var(--accent);border-radius:3px;background:var(--accent);color:#111;text-decoration:none;text-transform:uppercase;font-size:8px;font-weight:900}.home-copy .secondary-cta{background:transparent;color:#fff;border-color:#aaa}.home-preview .food-art{height:clamp(115px,17vw,210px)}.home-happy{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 5%;background:var(--accent);color:#111}.home-happy h3{margin:0 0 6px;font-size:clamp(15px,2vw,22px);font-weight:950}.happy-pills{display:flex;gap:6px}.happy-pills span{display:flex;flex-direction:column;padding:5px 8px;border-radius:3px;background:#111;color:var(--accent);font-size:10px;font-weight:900}.happy-pills small{color:#fff;font-size:7px;font-weight:500}.home-happy button{flex-shrink:0;padding:8px 10px;border:0;border-radius:3px;background:#111;color:var(--accent);font-size:8px;font-weight:900;cursor:pointer}.kitchen,.home-about{padding:16px 5%}.kitchen h3,.home-about h3{margin:0 0 10px;color:var(--accent);font-size:19px;font-weight:950}.kitchen-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:7px}.kitchen-grid>div{min-height:61px;padding:9px;border-radius:5px;background:#191919}.kitchen-grid>div:last-child{grid-column:1/-1}.kitchen-grid b{display:block;color:var(--accent);font-size:16px}.kitchen-grid strong{display:block;margin:2px 0;font-size:9px}.kitchen-grid p{margin:0;color:#aaa;font-size:7px;line-height:1.4}.home-about{display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:center}.home-about p{max-width:300px;margin:0;color:#aaa;font-size:8px;line-height:1.5}.about-links{display:flex;gap:8px}.about-links a{display:flex;min-width:90px;flex:1;flex-direction:column;gap:4px;padding:11px;border:1px solid var(--accent);border-radius:6px;color:var(--accent);text-decoration:none}.about-links small{color:#aaa;font-size:7px}.home-preview .reservation{margin-bottom:16px}.home-preview footer{padding-top:9px;padding-bottom:9px}@media(max-width:560px){.home-preview nav div{gap:6px}.home-preview nav div a{font-size:7px}.home-preview .home-hero{gap:10px;padding:5% 4%}.home-copy h2{font-size:36px}.home-copy p{font-size:8px}.home-preview .food-art{height:100px}.home-happy{padding:10px 4%}.home-happy h3{font-size:13px}.happy-pills{gap:3px}.happy-pills span{padding:4px;font-size:8px}.happy-pills small{font-size:6px}.home-happy button{padding:7px 6px;font-size:7px}.kitchen,.home-about{padding-right:4%;padding-left:4%}.home-about{grid-template-columns:1fr 1fr;gap:7px}.about-links{gap:4px}.about-links a{min-width:0;padding:7px}.kitchen-grid>div{padding:7px}}
.site-preview{background:var(--page-bg,#0b0b0b)}.editor-layout.with-settings{display:grid;grid-template-columns:minmax(230px,290px) minmax(0,1fr);gap:12px;align-items:start}.editor-layout.with-settings .settings-card{position:sticky;top:12px;max-width:none;max-height:none;overflow:visible;margin:0;padding:11px}.editor-layout.with-settings .settings-card .card-heading{gap:8px;margin-bottom:9px}.editor-layout.with-settings .settings-card .icon{width:30px;height:30px}.editor-layout.with-settings .settings-card label{margin:0 0 6px;font-size:10px;line-height:1.15}.editor-layout.with-settings .settings-card input,.editor-layout.with-settings .settings-card textarea{margin-top:3px;padding:5px 7px;font-size:11px;line-height:1.2}.editor-layout.with-settings .settings-card input:not([type=color]){height:29px}.editor-layout.with-settings .settings-card textarea{height:42px;min-height:42px;resize:none}.editor-layout.with-settings .settings-card .color-input{width:100%;height:29px;padding:2px}.editor-layout.with-settings .preview-wrap{min-width:0;padding:9px}.editor-layout.with-settings .site-preview{width:100%;max-width:none}.site-preview .kitchen-grid>div,.site-preview .reward-grid>div,.site-preview .reservation,.site-preview .deal-card:not(.featured){background:var(--surface-bg,#191919)}@media(max-width:850px){.editor-layout.with-settings{grid-template-columns:1fr}.editor-layout.with-settings .settings-card{position:static}}
</style>
