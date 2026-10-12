<template>

<div class="user-filters">


    <!-- Search -->

    <div class="filter-search">


        <i class="bi bi-search"></i>


        <input

            v-model="localFilters.search"

            type="text"

            placeholder="Search users..."

            @input="emitFilters"

        />


    </div>






    <!-- Role -->

    <select

        v-model="localFilters.role_id"

        @change="emitFilters"

        class="filter-select"

    >

        <option value="">
            All Roles
        </option>


        <option

            v-for="role in roles"

            :key="role.id"

            :value="role.id"

        >

            {{
                role.display_name
                ??
                role.name
            }}

        </option>


    </select>







    <!-- Status -->

    <select

        v-model="localFilters.status"

        @change="emitFilters"

        class="filter-select"

    >

        <option value="">
            All Status
        </option>


        <option value="active">
            Active
        </option>


        <option value="blocked">
            Blocked
        </option>


    </select>








    <!-- Per Page -->

    <select

        v-model="localFilters.per_page"

        @change="emitFilters"

        class="filter-select"

    >


        <option value="10">
            10 / page
        </option>


        <option value="25">
            25 / page
        </option>


        <option value="50">
            50 / page
        </option>


        <option value="100">
            100 / page
        </option>


    </select>








    <!-- Reset -->

    <button

        class="reset-filter-btn"

        @click="reset"

    >

        Reset

    </button>



</div>


</template>







<script setup>


import {

    reactive,

    watch

}

from 'vue'





const props = defineProps({

    roles:{

        type:Array,

        default:()=>[]

    },


    modelValue:{

        type:Object,

        required:true

    }


})





const emit = defineEmits([

    'update:modelValue',

    'change'

])






const localFilters =
    reactive({

        search:
            props.modelValue.search
            ??
            '',


        role_id:
            props.modelValue.role_id
            ??
            '',


        status:
            props.modelValue.status
            ??
            '',


        per_page:
            props.modelValue.per_page
            ??
            10


    })








function emitFilters()
{

    emit(
        'update:modelValue',
        {
            ...localFilters
        }
    )


    emit(
        'change'
    )

}








function reset()
{

    localFilters.search=''

    localFilters.role_id=''

    localFilters.status=''

    localFilters.per_page=10


    emitFilters()

}







watch(

    ()=>props.modelValue,

    (value)=>{


        Object.assign(

            localFilters,

            value

        )


    },


    {
        deep:true
    }

)

</script>