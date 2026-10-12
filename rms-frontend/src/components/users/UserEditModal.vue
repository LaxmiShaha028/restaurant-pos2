<template>

<div class="user-edit-overlay">


    <div class="user-edit-modal">


        <!-- Header -->

        <div class="user-edit-header">


            <div>

                <h3>
                    Edit User
                </h3>


                <p>
                    Update account information
                </p>

            </div>




            <button

                class="edit-close-btn"

                @click="$emit('close')"

            >

                ×

            </button>


        </div>







        <!-- Body -->


        <form

            class="user-edit-body"

            @submit.prevent="submit"

        >



            <!-- Error -->

            <div

                v-if="error"

                class="edit-error"

            >

                {{ error }}

            </div>







            <!-- Account Section -->


            <div class="edit-section">


                <h4>
                    Account Information
                </h4>



                <div class="edit-grid">





                    <div class="edit-field">


                        <label>
                            Name
                        </label>


                        <input

                            v-model="form.name"

                            type="text"

                        />


                    </div>







                    <div class="edit-field">


                        <label>
                            Username
                        </label>


                        <input

                            v-model="form.username"

                            type="text"

                        />


                    </div>








                    <div class="edit-field">


                        <label>
                            Email
                        </label>


                        <input

                            v-model="form.email"

                            type="email"

                        />


                    </div>







                    <div class="edit-field">


                        <label>
                            Role
                        </label>



                        <select

                            v-model="form.role_id"

                        >


                            <option

                                v-for="role in roles"

                                :key="role.id"

                                :value="role.id"

                            >

                                {{ role.display_name }}

                            </option>


                        </select>


                    </div>






                </div>


            </div>









            <!-- Password -->


            <div class="edit-section">


                <h4>
                    Change Password
                </h4>



                <div class="edit-grid">



                    <div class="edit-field">


                        <label>
                            New Password
                        </label>


                        <input

                            v-model="form.password"

                            type="password"

                            placeholder="Leave empty to keep old password"

                        />


                    </div>





                    <div class="edit-field">


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

            <div

                v-if="user?.employee"

                class="edit-section"


            >


                <h4>
                    Employee Information
                </h4>




                <div class="edit-grid">





                    <div class="edit-field">


                        <label>
                            Phone
                        </label>


                        <input

                            v-model="form.phone"

                            type="text"

                        />


                    </div>







                    <div class="edit-field">


                        <label>
                            Hourly Rate
                        </label>


                        <input

                            v-model="form.hourly_rate"

                            type="number"

                        />


                    </div>







                </div>



            </div>








            <!-- Status -->


            <div class="edit-section">


                <h4>
                    Account Status
                </h4>



                <label class="switch-row">


                    <input

                        v-model="form.is_active"

                        type="checkbox"

                    />


                    <span>

                        Active Account

                    </span>


                </label>



            </div>







            <!-- Footer -->


            <div class="user-edit-footer">


                <button

                    type="button"

                    class="cancel-edit-btn"

                    @click="$emit('close')"

                >

                    Cancel

                </button>





                <button

                    type="submit"

                    class="save-edit-btn"

                    :disabled="saving"

                >

                    <span

                        v-if="saving"

                        class="btn-loader"

                    ></span>



                    {{

                        saving

                        ?

                        'Saving...'

                        :

                        'Save Changes'

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

    watch,

    onMounted

}

from 'vue'



import userService

from '@/services/userService'





const props = defineProps({

    user:
    {
        type:Object,
        default:null
    }

})





const emit = defineEmits([

    'close',

    'updated'

])







const saving =
    ref(false)



const error =
    ref('')



const roles =
    ref([])








const form = reactive({

    name:'',

    username:'',

    email:'',

    role_id:'',

    password:'',

    password_confirmation:'',

    phone:'',

    hourly_rate:'',

    is_active:true

})







function fillForm()

{

    if(!props.user)
    {
        return
    }


    form.name =
        props.user.name ?? ''


    form.username =
        props.user.username ?? ''


    form.email =
        props.user.email ?? ''


    form.role_id =
        props.user.role?.id ?? ''


    form.phone =
        props.user.employee?.phone ?? ''


    form.hourly_rate =
        props.user.employee?.hourly_rate ?? ''


    form.is_active =
        props.user.is_active



}







async function loadRoles()

{

    try {


        const response =

            await userService.getRoles()



        roles.value =

            response?.data?.data
            ??
            response?.data
            ??
            []

    }


    catch(error)

    {

        console.error(error)

    }


}







async function submit()

{

    saving.value=true;

    error.value='';



    try {


        await userService.updateUser(

            props.user.id,

            form

        )



        emit('updated')

        emit('close')


    }


    catch(err)

    {


        error.value =

            err?.response?.data?.message

            ??

            'Unable to update user.'


    }


    finally

    {

        saving.value=false

    }


}








watch(

    ()=>props.user,

    ()=>{

        fillForm()

    },

    {
        immediate:true
    }

)







onMounted(()=>{

    loadRoles()

})


</script>