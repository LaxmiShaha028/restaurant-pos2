<template>

<div class="audit-modal-overlay">


<div class="audit-modal">



<!-- Header -->

<div class="audit-modal-header">


<div>

<h3>
Audit Details
</h3>


<p>
Complete activity information
</p>


</div>




<button

class="audit-close-btn"

@click="$emit('close')"

>

×


</button>


</div>









<!-- Body -->


<div class="audit-modal-body">





<!-- User -->

<div class="audit-section">


<h4>
Performed By
</h4>



<div class="user-card">


<div class="user-avatar">


{{

getInitial(

log?.user?.name

)

}}


</div>



<div>


<strong>

{{

log?.user?.name

??

'System'

}}

</strong>



<span>

{{

log?.user?.email

??

'-'

}}

</span>



</div>


</div>


</div>









<!-- Activity -->


<div class="audit-section">


<h4>
Activity
</h4>



<div class="activity-grid">



<div>

<label>
Module
</label>


<span class="module-badge">

{{log.module}}

</span>

</div>





<div>

<label>
Action
</label>


<span

class="action-badge"

>

{{log.action}}

</span>


</div>






<div>

<label>
Date
</label>


<p>

{{formatDate(log.created_at)}}

</p>

</div>





<div>

<label>
IP Address
</label>


<p>

{{log.ip_address ?? '-'}}

</p>

</div>


</div>


</div>









<!-- Description -->


<div class="audit-section">


<h4>
Description
</h4>


<p class="description">

{{

log.description

??

'-'

}}


</p>


</div>









<!-- Changes -->

<div
  v-if="log.old_values || log.new_values"
  class="audit-section"
>
  <h4>Data Changes</h4>

  <div v-if="changeRows.length" class="change-table">
    <div class="change-row change-row-heading" role="row">
      <span>Field</span>
      <span>Before</span>
      <span>After</span>
    </div>

    <div
      v-for="row in changeRows"
      :key="row.key"
      class="change-row"
      role="row"
    >
      <strong class="change-field">{{ row.label }}</strong>
      <span class="change-value change-before">
        <small>Before</small>
        <span>{{ row.before }}</span>
      </span>
      <span class="change-value change-after">
        <small>After</small>
        <span>{{ row.after }}</span>
      </span>
    </div>
  </div>

  <p v-else class="change-empty">No field-level changes were recorded.</p>
</div>


<!-- Device -->
<div class="audit-section">
  <h4>Device Information</h4>
  <div v-if="log?.user_agent" class="device-info-grid">
    <div class="device-info-item"><label>Browser</label><strong>{{ deviceInfo.browser }}</strong></div>
    <div class="device-info-item"><label>Operating System</label><strong>{{ deviceInfo.os }}</strong></div>
    <div class="device-info-item"><label>Device Type</label><strong>{{ deviceInfo.device }}</strong></div>
  </div>
  <p v-else class="device-unavailable">Device information is not available.</p>
  <details v-if="log?.user_agent" class="device-technical-details">
    <summary>Show technical details</summary>
    <code>{{ log.user_agent }}</code>
  </details>
</div>






</div>









<!-- Footer -->


<div class="audit-modal-footer">


<button

class="close-footer-btn"

@click="$emit('close')"

>

Close

</button>


</div>







</div>

</div>

</template>








<script setup>
import { computed } from 'vue'

const props = defineProps({

log:{

type:Object,

default:null

}

})







function getInitial(name)

{

if(!name)

return 'S'


return name

.charAt(0)

.toUpperCase()


}







function parseAuditData(data) {
  if (data === null || data === undefined || data === '') {
    return {}
  }

  if (typeof data === 'string') {
    try {
      return JSON.parse(data)
    } catch {
      return { details: data }
    }
  }

  return data
}

function flattenAuditData(value, prefix = '') {
  if (value && typeof value === 'object' && !Array.isArray(value)) {
    const entries = Object.entries(value)

    if (!entries.length && prefix) {
      return new Map([[prefix, 'No details']])
    }

    return entries.reduce((result, [key, nestedValue]) => {
      const field = prefix ? `${prefix}.${key}` : key
      for (const [nestedField, nestedEntry] of flattenAuditData(nestedValue, field)) {
        result.set(nestedField, nestedEntry)
      }
      return result
    }, new Map())
  }

  return new Map([[prefix || 'details', value]])
}

function humanizeField(field) {
  const knownLabels = {
    is_active: 'Account Status',
    is_available: 'Availability',
    is_featured: 'Featured',
    is_verified: 'Verified',
    user_id: 'User ID',
    raw_material_id: 'Raw Material ID',
    menu_item_id: 'Menu Item ID',
    add_on_id: 'Add-on ID',
    created_at: 'Created',
    updated_at: 'Last Updated',
    deleted_at: 'Deleted',
    ip_address: 'IP Address',
    user_agent: 'Device Information',
  }

  return String(field)
    .split('.')
    .map((part) => {
      if (knownLabels[part]) return knownLabels[part]

      return part
        .replace(/([a-z0-9])([A-Z])/g, '$1 $2')
        .replace(/[_-]+/g, ' ')
        .replace(/\bid\b/gi, 'ID')
        .replace(/\b\w/g, (letter) => letter.toUpperCase())
    })
    .join(' › ')
}

function formatAuditValue(value, field = '') {
  if (value === null || value === undefined) return 'Not set'

  if (typeof value === 'boolean') {
    if (field === 'is_active') return value ? 'Active' : 'Inactive'
    if (field === 'is_available') return value ? 'Available' : 'Unavailable'
    if (field === 'is_featured') return value ? 'Featured' : 'Not featured'
    if (field === 'is_verified') return value ? 'Verified' : 'Not verified'
    return value ? 'Yes' : 'No'
  }

  if (Array.isArray(value)) {
    if (!value.length) return 'None'
    return value.map((item) => formatAuditValue(item)).join(', ')
  }

  if (typeof value === 'object') {
    const pairs = Object.entries(value)
    if (!pairs.length) return 'No details'
    return pairs
      .map(([key, item]) => `${humanizeField(key)}: ${formatAuditValue(item, key)}`)
      .join('; ')
  }

  if (typeof value === 'string') {
    const text = value.trim()
    if (!text) return 'Empty'

    if (/(?:_at|_date|_time)$/i.test(field) && /^\d{4}-\d{2}-\d{2}(?:T|\s)/.test(text)) {
      const date = new Date(text)
      if (!Number.isNaN(date.getTime())) {
        const options = /T|\d{2}:\d{2}/.test(text) ? { dateStyle: 'medium', timeStyle: 'short' } : { dateStyle: 'medium' }
        return new Intl.DateTimeFormat(undefined, options).format(date)
      }
    }
    if (/^(true|false)$/i.test(text)) {
      return formatAuditValue(text.toLowerCase() === 'true', field)
    }

    if (/^[a-z][a-z0-9_-]*$/i.test(text) && /[_-]/.test(text)) {
      return humanizeField(text)
    }

    return text
  }

  return String(value)
}

function parseUserAgent(userAgent) {
  if (!userAgent) return { browser: 'Unknown browser', os: 'Unknown operating system', device: 'Unknown device' }
  let browser = 'Unknown browser'
  let match = userAgent.match(/(?:Edg|Edge)\/([\d.]+)/i)
  if (match) browser = 'Microsoft Edge ' + match[1]
  else if ((match = userAgent.match(/OPR\/([\d.]+)/i))) browser = 'Opera ' + match[1]
  else if ((match = userAgent.match(/Chrome\/([\d.]+)/i))) browser = 'Google Chrome ' + match[1]
  else if ((match = userAgent.match(/Firefox\/([\d.]+)/i))) browser = 'Mozilla Firefox ' + match[1]
  else if ((match = userAgent.match(/(?:MSIE |rv:)([\d.]+).*?(?:Trident|like Gecko)/i))) browser = 'Internet Explorer ' + match[1]
  else if ((match = userAgent.match(/Version\/([\d.]+).*Safari/i))) browser = 'Apple Safari ' + match[1]
  let os = 'Unknown operating system', osMatch
  if ((osMatch = userAgent.match(/Windows NT ([\d.]+)/i))) {
    const versions = { '10.0': '10/11', '6.3': '8.1', '6.2': '8', '6.1': '7' }
    os = 'Windows ' + (versions[osMatch[1]] || osMatch[1])
  } else if ((osMatch = userAgent.match(/Android(?:\s([\d.]+))?/i))) os = 'Android' + (osMatch[1] ? ' ' + osMatch[1] : '')
  else if ((osMatch = userAgent.match(/(?:iPhone OS|CPU OS) ([\d_]+)/i))) os = 'iOS ' + osMatch[1].replace(/_/g, '.')
  else if ((osMatch = userAgent.match(/Mac OS X ([\d_]+)/i))) os = 'macOS ' + osMatch[1].replace(/_/g, '.')
  else if (/Linux/i.test(userAgent)) os = 'Linux'
  let device = 'Desktop computer'
  if (/iPad|Tablet/i.test(userAgent)) device = 'Tablet'
  else if (/iPhone|iPod|Mobile/i.test(userAgent)) device = 'Mobile phone'
  else if (/Android/i.test(userAgent)) device = 'Android device'
  return { browser, os, device }
}
const deviceInfo = computed(() => parseUserAgent(props.log?.user_agent))
const changeRows = computed(() => {
  const before = flattenAuditData(parseAuditData(props.log?.old_values))
  const after = flattenAuditData(parseAuditData(props.log?.new_values))
  const fields = new Set([...before.keys(), ...after.keys()])

  return [...fields].map((key) => ({
    key,
    label: humanizeField(key),
    before: before.has(key) ? formatAuditValue(before.get(key), key.split('.').at(-1)) : '—',
    after: after.has(key) ? formatAuditValue(after.get(key), key.split('.').at(-1)) : '—',
  }))
})


function formatDate(date)

{

if(!date)

return '-'


return new Date(date)

.toLocaleString()


}



</script>