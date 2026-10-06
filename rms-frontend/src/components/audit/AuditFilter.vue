<template>


<div class="audit-filter">


<input

v-model="local.search"

placeholder="Search activity..."

@input="emitChange"

/>




<select

v-model="local.module"

@change="emitChange"

>


<option value="">
All Modules
</option>


<option value="users">
Users
</option>


<option value="employees">
Employees
</option>


<option value="orders">
Orders
</option>


</select>





<select

v-model="local.action"

@change="emitChange"

>


<option value="">
All Actions
</option>


<option value="created">
Created
</option>


<option value="updated">
Updated
</option>


<option value="deleted">
Deleted
</option>


<option value="blocked">
Blocked
</option>


<option value="unblocked">
Unblocked
</option>


</select>



</div>


</template>



<script setup>


import {

reactive,

watch

}

from 'vue'



const props = defineProps({

modelValue:{

type:Object,

default:()=>({})

}

})



const emit = defineEmits([

'update:modelValue',

'change'

])




const local = reactive({

...props.modelValue

})





watch(

local,

()=>{


emit(

'update:modelValue',

local

)


},

{

deep:true

}

)





function emitChange()

{

emit('change')

}



</script>