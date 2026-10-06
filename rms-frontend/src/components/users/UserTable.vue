<template>

<div class="user-table-wrapper">



    <!-- Loading -->

    <div

        v-if="loading"

        class="table-state"

    >

        <div class="loader"></div>

        Loading users...


    </div>





    <!-- Empty -->

    <div

        v-else-if="!users.length"

        class="table-state"

    >

        No users found.


    </div>







    <!-- Table -->


    <table

        v-else

        class="user-table"

    >



        <thead>


            <tr>


                <th>
                    Profile
                </th>



                <th>
                    Name
                </th>



                <th>
                    Username
                </th>



                <th>
                    Role
                </th>



                <th>
                    Status
                </th>



                <th>
                    Last Login
                </th>



                <th>
                    Action
                </th>



            </tr>


        </thead>







        <tbody>


            <tr

                v-for="user in users"

                :key="user.id"

            >






                <!-- Profile -->


                <td>


                    <img

                        v-if="
                            user.employee?.profile_image
                        "

                        :src="
                            user.employee.profile_image
                        "

                        class="user-avatar"

                    />





                    <div

                        v-else

                        class="user-avatar-placeholder"

                    >

                        {{


                            getInitial(
                                user.name
                            )


                        }}


                    </div>



                </td>









                <!-- Name -->


                <td>


                    <div class="user-name">


                        {{ user.name }}


                    </div>



                    <div class="user-email">


                        {{ user.email }}


                    </div>


                </td>









                <!-- Username -->


                <td>


                    <span class="username">


                        @{{ user.username }}


                    </span>


                </td>









                <!-- Role -->


                <td>



                    <span class="role-badge">


                        {{

                            user.role?.display_name

                            ??

                            user.role?.name

                            ??

                            '-'

                        }}



                    </span>



                </td>









                <!-- Status -->


                <td>



                    <span

                        :class="

                            user.is_active

                            ?

                            'status-active'

                            :

                            'status-blocked'

                        "

                    >



                        {{

                            user.is_active

                            ?

                            'Active'

                            :

                            'Blocked'

                        }}



                    </span>




                </td>









                <!-- Last Login -->


                <td>



                    <span class="date-text">


                        {{

                            formatDate(
                                user.last_login_at
                            )

                        }}



                    </span>



                </td>









                <!-- Action -->


                <td>



                    <div class="user-actions">





                        <button

                            class="view-btn"

                            @click="$emit(
                                'view',
                                user
                            )"

                        >

                            View

                        </button>







                        <button

                            class="edit-btn"

                            @click="$emit(
                                'edit',
                                user
                            )"

                        >

                            Edit

                        </button>







                        <button

                            class="status-btn"

                            @click="$emit(
                                'status',
                                user
                            )"

                        >


                            {{

                                user.is_active

                                ?

                                'Block'

                                :

                                'Unblock'

                            }}


                        </button>







                        <button

                            class="delete-btn"

                            @click="$emit(
                                'delete',
                                user
                            )"

                        >

                            Delete

                        </button>





                    </div>



                </td>





            </tr>



        </tbody>




    </table>




</div>


</template>









<script setup>


defineProps({


    users:

    {

        type:Array,

        default:()=>[]

    },



    loading:

    {

        type:Boolean,

        default:false

    }


});








function getInitial(name)

{

    if(!name)

    {

        return 'U';

    }



    return name

        .charAt(0)

        .toUpperCase();


}








function formatDate(date)

{

    if(!date)

    {

        return '-';

    }



    return new Date(date)

        .toLocaleDateString();


}

</script>
<style
  scoped
  src="@/components/users/user.css"
></style>
<style

scoped

src="@/assets/css/users/user-compact.css"

></style>