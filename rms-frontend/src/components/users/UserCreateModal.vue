<template>

<div class="user-create-overlay">


<div class="user-create-modal">


<!-- Header -->

<div class="user-create-header">


<div>

<h3>
Add New User
</h3>

<p>
Create system user account
</p>

</div>



<button

class="create-close-btn"

@click="$emit('close')"

>
×
</button>


</div>







<form

class="user-create-body"

@submit.prevent="submit"

enctype="multipart/form-data"

>



<div
v-if="error"
class="create-error"
>

{{ error }}

</div>






<!-- Account -->

<div class="create-section">


<h4>
Account Information
</h4>



<div class="create-grid">



<div class="create-field">

<label>
Name
</label>


<input

v-model="form.name"

type="text"

/>

</div>





<div class="create-field">

<label>
Username
</label>


<input

v-model="form.username"

type="text"

/>

</div>






<div class="create-field">

<label>
Email
</label>


<input

v-model="form.email"

type="email"

/>

</div>







<div class="create-field">

<label>
Role
</label>


<select

v-model="form.role_id"

>


<option value="">
Select Role
</option>


<option

v-for="role in roles"

:key="role.id"

:value="role.id"

>

{{role.display_name}}

</option>


</select>


</div>




</div>


</div>









<!-- Password -->

<div class="create-section">


<h4>
Password
</h4>



<div class="create-grid">



<div class="create-field">

<label>
Password
</label>


<input

v-model="form.password"

type="password"

/>

</div>





<div class="create-field">

<label>
Confirm Password
</label>


<input

v-model="
form.password_confirmation
"

type="password"

/>

</div>



</div>


</div>









<!-- Employee -->

<div class="create-section">


<h4>
Employee Information (Optional)
</h4>



<div class="create-grid">



<div class="create-field">

<label>
Phone
</label>


<input

v-model="form.phone"

type="text"

/>


</div>





<div class="create-field">

<label>
Joining Date
</label>


<input

v-model="form.joining_date"

type="date"

/>


</div>







<div class="create-field">

<label>
Hourly Rate
</label>


<input

v-model="form.hourly_rate"

type="number"

/>


</div>






<div class="create-field">


<label>
Profile Image
</label>


<input

type="file"

accept="image/*"

@change="imageChange"

/>



</div>



</div>



</div>









<!-- Preview -->


<div

v-if="preview"

class="image-preview"

>


<img

:src="preview"

/>


</div>










<div class="create-footer">


<button

type="button"

class="cancel-create-btn"

@click="$emit('close')"

>

Cancel

</button>





<button

type="submit"

class="save-create-btn"

:disabled="saving"

>


{{

saving

?

'Saving...'

:

'Create User'

}}


</button>


</div>






</form>


</div>


</div>


</template>







<script setup>


import {

reactive,

ref,

onMounted,

watch

}

from 'vue'


import userService

from '@/services/userService'



const emit = defineEmits([

'close',

'created'

])

watch(

()=>true,

()=>{

    resetForm()

}

)


const roles =
ref([])


const saving =
ref(false)


const error =
ref('')


const preview =
ref(null)



const form =
reactive({


name:'',

username:'',

email:'',

password:'',

password_confirmation:'',

role_id:'',

phone:'',

joining_date:'',

hourly_rate:'',

profile_image:null,

is_active:true


})







function imageChange(e)

{

const file =
e.target.files[0]


if(file)

{

form.profile_image =
file


preview.value =
URL.createObjectURL(file)

}


}







async function loadRoles()

{

const response =
await userService.getRoles()


roles.value =
response.data.data

}







async function submit()

{


saving.value=true

error.value=''


try {


const payload =
new FormData()



Object.keys(form)
.forEach(key=>{


if(
    form[key] !== null
    &&
    form[key] !== ''
)

{


    if(key === 'is_active')
    {

        payload.append(
            key,
            form[key] ? 1 : 0
        )

    }

    else
    {

        payload.append(
            key,
            form[key]
        )

    }


}


})



await userService.createUser(
    payload
)


resetForm()


emit('created')


emit('close')


}

catch(err)

{


error.value =

err?.response?.data?.message

??

'Unable to create user.'


}


finally

{

saving.value=false

}


}







onMounted(()=>{

loadRoles()

})

function resetForm()
{

    form.name='';

    form.username='';

    form.email='';

    form.password='';

    form.password_confirmation='';

    form.role_id='';

    form.phone='';

    form.joining_date='';

    form.hourly_rate='';

    form.profile_image=null;

    form.is_active=true;


    preview.value=null;

}


</script>