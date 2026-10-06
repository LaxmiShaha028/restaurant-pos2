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

v-if="

log.old_values

||

log.new_values

"

class="audit-section"

>


<h4>
Data Changes
</h4>





<div class="changes-grid">





<div class="change-box old">


<h5>
Before
</h5>



<pre>

{{

formatJson(

log.old_values

)

}}


</pre>


</div>







<div class="change-box new">


<h5>
After
</h5>



<pre>

{{

formatJson(

log.new_values

)

}}


</pre>


</div>






</div>


</div>









<!-- Browser -->


<div class="audit-section">


<h4>
Device Information
</h4>


<p>

{{

log.user_agent

??

'-'

}}


</p>


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







function formatJson(data)

{

if(!data)

return '-'


return JSON.stringify(

data,

null,

4

)

}







function formatDate(date)

{

if(!date)

return '-'


return new Date(date)

.toLocaleString()


}



</script>