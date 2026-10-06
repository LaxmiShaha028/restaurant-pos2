<template>

<section class="users-page">

    <!-- Header -->
    <header class="users-header">
      <div class="header-left">
        <div class="header-icon">
          <i class="bi bi-people" aria-hidden="true"></i>
        </div>

        <div>
          <p class="users-page-eyebrow">
            Access Control
          </p>

          <h2>
            Users Management
          </h2>

          <p>
            Manage system users, roles and account status
          </p>
        </div>
      </div>

      <button
        type="button"
        class="add-user-btn"
        @click="openCreateUser"
      >
        <i class="bi bi-plus-lg"></i>
        <span>Add User</span>
      </button>
    </header>





    <!-- Error -->

    <div

        v-if="error"

        class="error-box"

    >

        <strong>
            Unable to load users
        </strong>


        <span>
            {{ error }}
        </span>


    </div>









    <!-- Main Card -->


    <div class="users-card">





        <div class="card-header">


            <div>

                <h3>
                    All Users
                </h3>


                <p>
                    View and manage registered system users
                </p>


            </div>




            <span class="count-badge">


                {{ pagination.total }}


                Users


            </span>


        </div>








        <div class="card-body">






            <!-- Filters -->


            <UserFilters

                v-model="filters"

                :roles="roles"

                @change="

                    pagination.current_page = 1;

                    loadUsers(1)

                "

            />









            <!-- Table -->


            <div class="table-container">


                <UserTable


                    :users="users"


                    :loading="loading"



                    @view="viewUser"


                    @edit="editUser"


                    @status="changeStatus"


                    @delete="deleteUser"



                />



            </div>









            <!-- Pagination -->


            <div

                v-if="pagination.total"

                class="pagination"

            >



                <div class="pagination-info">


                    Showing


                    <strong>
                        {{ pagination.from }}
                    </strong>


                    to


                    <strong>
                        {{ pagination.to }}
                    </strong>


                    of


                    <strong>
                        {{ pagination.total }}
                    </strong>


                    users



                </div>






                <div class="pagination-actions">



                    <button


                        :disabled="
                            pagination.current_page <= 1
                        "


                        @click="
                            changePage(
                                pagination.current_page - 1
                            )
                        "


                    >

                        Previous

                    </button>







                    <span>


                        Page

                        {{ pagination.current_page }}


                        /

                        {{ pagination.last_page }}


                    </span>








                    <button


                        :disabled="

                            pagination.current_page
                            >=
                            pagination.last_page

                        "



                        @click="

                            changePage(

                                pagination.current_page + 1

                            )

                        "


                    >

                        Next


                    </button>



                </div>



            </div>





        </div>



    </div>









    <!-- View Modal -->


    <UserViewModal


        v-if="showViewModal"



        :user="selectedUser"



        @close="closeView"



    />









    <!-- Edit Modal -->


    <UserEditModal


        v-if="showEditModal"



        :user="selectedEditUser"



        @close="closeEdit"



        @updated="

            loadUsers(
                pagination.current_page
            )

        "


    />









    <!-- Create Modal -->


    <UserCreateModal


        v-if="showCreateModal"



        @close="

            showCreateModal=false

        "



        @created="

            loadUsers(1)

        "


    />





</section>

</template>









<script setup>


import {

    ref,

    reactive,

    onMounted

}

from 'vue'



import UserTable

from '@/components/users/UserTable.vue'



import UserFilters

from '@/components/users/UserFilters.vue'



import UserViewModal

from '@/components/users/UserViewModal.vue'



import UserEditModal

from '@/components/users/UserEditModal.vue'



import UserCreateModal

from '@/components/users/UserCreateModal.vue'



import userService

from '@/services/userService'





import '@/assets/css/users/user-modal.css'

import '@/assets/css/users/user-edit-modal.css'

import '@/assets/css/users/user-create-modal.css'

import '@/assets/css/users/user-filter.css'


const users =
    ref([])



const roles =
    ref([])



const loading =
    ref(false)



const error =
    ref('')



const selectedUser =
    ref(null)



const selectedEditUser =
    ref(null)



const showViewModal =
    ref(false)



const showEditModal =
    ref(false)



const showCreateModal =
    ref(false)









const filters =
    reactive({

        search:'',

        role_id:'',

        status:'',

        per_page:10

    })









const pagination =
    reactive({

        current_page:1,

        last_page:1,

        from:0,

        to:0,

        total:0

    })









// let searchTimer = null







async function loadUsers(page = 1)

{


    loading.value = true;

    error.value = '';



    try {


        const response =

            await userService.getUsers({


                page,


                search:
                    filters.search,


                role_id:
                    filters.role_id,


                status:
                    filters.status,


                per_page:
                    filters.per_page


            })




        users.value =

            response.data.data





        Object.assign(

            pagination,

            {


                current_page:

                    response.data.meta.current_page,



                last_page:

                    response.data.meta.last_page,



                from:

                    response.data.meta.from,



                to:

                    response.data.meta.to,



                total:

                    response.data.meta.total


            }

        )



    }


    catch(err)


    {


        console.error(err)



        error.value =

            err?.response?.data?.message

            ??

            'Unable to load users.'



    }



    finally


    {

        loading.value=false

    }


}









// function searchUsers()

// {


//     clearTimeout(
//         searchTimer
//     )



//     searchTimer =

//         setTimeout(()=>{


//             pagination.current_page = 1


//             loadUsers(1)



//         },500)


// }









async function loadRoles()

{

    try {


        const response =

            await userService.getRoles()



        roles.value =

            response.data.data



    }


    catch(err)


    {

        console.error(err)

    }


}









function openCreateUser()

{

    showCreateModal.value = true

}








function viewUser(user)

{

    selectedUser.value = user


    showViewModal.value=true

}








function closeView()

{

    showViewModal.value=false


    selectedUser.value=null

}








function editUser(user)

{

    selectedEditUser.value=user


    showEditModal.value=true

}








function closeEdit()

{

    showEditModal.value=false


    selectedEditUser.value=null

}









function changePage(page)

{

    if(

        page < 1

        ||

        page > pagination.last_page

    )

    {

        return

    }


    loadUsers(page)

}









async function changeStatus(user)

{

    try {


        await userService.toggleStatus(

            user.id,

            !user.is_active

        )


        loadUsers(
            pagination.current_page
        )


    }


    catch(err)

    {

        console.error(err)

    }


}









async function deleteUser(user)

{

    try {


        await userService.deleteUser(

            user.id

        )


        loadUsers(1)


    }


    catch(err)

    {

        console.error(err)

    }


}








onMounted(()=>{


    loadUsers()


    loadRoles()


})



</script>








<style

scoped

src="@/components/users/user.css"

></style>
<style

scoped

src="@/assets/css/users/user-compact.css"

></style>