<template>

<div class="user-modal-overlay">


    <div class="user-view-modal">



        <!-- Header -->

        <div class="user-modal-header">


            <h3>
                User Details
            </h3>



            <button

                class="close-btn"

                @click="$emit('close')"

            >

                ×

            </button>


        </div>






        <!-- Body -->


        <div

            v-if="user"

            class="user-modal-body"

        >



            <!-- Profile -->


            <div class="profile-section">


                <img

v-if="profileImage"

:src="profileImage"

class="large-profile-image"

/>



                <div

                    v-else

                    class="large-avatar"

                >

                    {{
                        getInitial(
                            user.name
                        )
                    }}

                </div>





                <h2>

                    {{ user.name }}

                </h2>



                <p>

                    {{ user.email }}

                </p>


                <span>

                    @{{ user.username }}

                </span>


            </div>









            <!-- Account Information -->


            <div class="info-card">


                <h4>
                    Account Information
                </h4>



                <div class="info-grid">



                    <div>

                        <label>
                            Role
                        </label>

                        <strong>

                            {{
                                user.role?.display_name
                                ??
                                user.role?.name
                                ??
                                '-'
                            }}

                        </strong>

                    </div>





                    <div>

                        <label>
                            Status
                        </label>


                        <strong

                            :class="
                                user.is_active
                                ?
                                'text-active'
                                :
                                'text-blocked'
                            "

                        >

                            {{
                                user.is_active
                                ?
                                'Active'
                                :
                                'Blocked'
                            }}

                        </strong>


                    </div>






                    <div>

                        <label>
                            Last Login
                        </label>


                        <strong>

                            {{
                                formatDate(
                                    user.last_login_at
                                )
                            }}

                        </strong>


                    </div>






                    <div>

                        <label>
                            Created
                        </label>


                        <strong>

                            {{
                                formatDate(
                                    user.created_at
                                )
                            }}

                        </strong>


                    </div>



                </div>


            </div>









            <!-- Employee Information -->


            <div

                v-if="user.employee"

                class="info-card"


            >


                <h4>
                    Employee Information
                </h4>



                <div class="info-grid">



                    <div>

                        <label>
                            Phone
                        </label>


                        <strong>

                            {{
                                user.employee.phone
                                ??
                                '-'
                            }}

                        </strong>


                    </div>






                    <div>

                        <label>
                            Joining Date
                        </label>


                        <strong>

                            {{
                                formatDate(
                                    user.employee.joining_date
                                )
                            }}

                        </strong>


                    </div>






                    <div>

                        <label>
                            Hourly Rate
                        </label>


                        <strong>

                            {{
                                user.employee.hourly_rate
                                ??
                                0
                            }}

                            /hour

                        </strong>


                    </div>






                    <div>

                        <label>
                            Current Status
                        </label>


                        <strong>

                            {{
                                user.employee.current_status
                                ??
                                '-'
                            }}

                        </strong>


                    </div>



                </div>


            </div>









            <!-- Permissions -->


            <div

                v-if="
                    user.permissions?.length
                "

                class="info-card"


            >


                <h4>
                    Permissions
                </h4>




                <div class="permission-list">


                    <span

                        v-for="
                            permission in user.permissions
                        "

                        :key="permission"

                    >

                        {{ permission }}


                    </span>


                </div>



            </div>





        </div>






        <!-- Footer -->


        <div class="user-modal-footer">


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

import {
    computed
} from 'vue'


const props = defineProps({

    user:
    {
        type:Object,
        default:null
    }

})



const profileImage = computed(()=>{

    const path =
        props.user
            ?.employee
            ?.profile_image


    if(!path)
    {
        return null
    }


    if(path.startsWith('http'))
    {
        return path
    }


    return `/storage/${path}`

})




function getInitial(name)
{

    if(!name)
    {
        return 'U'
    }


    return name
        .charAt(0)
        .toUpperCase()

}





function formatDate(date)
{

    if(!date)
    {
        return '-'
    }


    return new Date(date)
        .toLocaleDateString()

}


</script>