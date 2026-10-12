<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class UserManagementService
{


    /*
    |--------------------------------------------------------------------------
    | User List
    |--------------------------------------------------------------------------
    */

    public function getUsers(
    array $filters = []
): LengthAwarePaginator {


    return User::query()

        /*
        |--------------------------------------------------------------------------
        | Load Relations
        |--------------------------------------------------------------------------
        */

        ->with([
            'role',
            'employee',
        ])


        /*
        |--------------------------------------------------------------------------
        | Search Filter
        |--------------------------------------------------------------------------
        */

        ->when(
    $filters['search'] ?? null,

    function($query,$search){


        $query->where(function($q) use ($search){


            $q->where(
                'name',
                'like',
                "%{$search}%"
            )


            ->orWhere(
                'username',
                'like',
                "%{$search}%"
            )


            ->orWhere(
                'email',
                'like',
                "%{$search}%"
            )


            ->orWhereHas(
                'role',
                function($roleQuery) use ($search){

                    $roleQuery->where(
                        'display_name',
                        'like',
                        "%{$search}%"
                    );

                }
            );


        });


    }
)



        /*
        |--------------------------------------------------------------------------
        | Role Filter
        |--------------------------------------------------------------------------
        */

        ->when(
    $filters['role'] ?? null,
    function ($query, $role) {


        $query->whereHas(
            'role',
            function($q) use ($role){

                $q->where(
                    'name',
                    $role
                );

            }
        );


    }
)



        /*
        |--------------------------------------------------------------------------
        | Account Status Filter
        |--------------------------------------------------------------------------
        */

        ->when(
            $filters['status'] ?? null,
            function ($query, $status) {


                if (
                    $status === 'active'
                ) {


                    $query->where(
                        'is_active',
                        true
                    );


                }



                if(
    $status === 'blocked'
)
{

    $query->where(
        'is_active',
        false
    );

}



if(
    $status === 'active'
)
{

    $query->where(
        'is_active',
        true
    );

}


            }
        )



        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        ->latest()



        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        ->paginate(

            $filters['per_page']
            ??
            10

        );

}

/*
|--------------------------------------------------------------------------
| Create User
|--------------------------------------------------------------------------
*/

public function createUser(
    array $data
): User {


    return DB::transaction(

        function () use ($data) {


            /*
            |--------------------------------------------------------------------------
            | Create User Account
            |--------------------------------------------------------------------------
            */


            $user = User::create([


                'role_id'=>
                    $data['role_id'],


                'name'=>
                    $data['name'],


                'username'=>
                    $data['username'],


                'email'=>
                    $data['email'],


                'password'=>
                    Hash::make(
                        $data['password']
                    ),



                'is_active'=>

                    array_key_exists(
                        'is_active',
                        $data
                    )

                    ?

                    (bool)
                    $data['is_active']

                    :

                    true,


            ]);





            /*
            |--------------------------------------------------------------------------
            | Create Employee Profile (Optional)
            |--------------------------------------------------------------------------
            */


            if(

                isset(
                    $data['joining_date']
                )

            ){


                $profileImage = null;


                if(

                    isset(
                        $data['profile_image']
                    )

                ){

                    $profileImage =

                        $data['profile_image']
                            ->store(
                                'employees',
                                'public'
                            );

                }





                Employee::create([


                    'user_id'=>
                        $user->id,


                    'phone'=>
                        $data['phone']
                        ??
                        null,



                    'profile_image'=>
                        $profileImage,



                    'joining_date'=>
                        $data['joining_date'],



                    'hourly_rate'=>
                        $data['hourly_rate']
                        ??
                        0,



                    'current_status'=>
                        Employee::STATUS_NONE,



                    'status_updated_at'=>
                        now(),


                ]);


            }





            return $user->fresh([

                'role',

                'employee',

            ]);

        }

    );


}



    /*
    |--------------------------------------------------------------------------
    | Single User
    |--------------------------------------------------------------------------
    */

    public function findUser(
        User $user
    ): User {


        return $user->load([
            'role',
            'employee',
        ]);

    }





    /*
    |--------------------------------------------------------------------------
    | Update User
    |--------------------------------------------------------------------------
    */

    public function updateUser(
    User $user,
    array $data
): User {


    return DB::transaction(

        function () use (
            $user,
            $data
        ) {


            /*
            |--------------------------------------------------------------------------
            | Protect Admin Role
            |--------------------------------------------------------------------------
            */

            if (

                $user->hasRole('admin')

                &&

                array_key_exists(
                    'role_id',
                    $data
                )

            ) {


                $newRole =
                    \App\Models\Role::find(
                        $data['role_id']
                    );


                if (

                    $newRole

                    &&

                    $newRole->name !== 'admin'

                ) {


                    throw ValidationException::withMessages([

                        'role_id' =>

                            'Admin role cannot be downgraded.',

                    ]);

                }

            }





            /*
            |--------------------------------------------------------------------------
            | Update User Account
            |--------------------------------------------------------------------------
            */


            $userData = [];




            foreach (

                [

                    'name',

                    'username',

                    'email',

                    'role_id',

                ]

                as $field

            ) {


                if (

                    array_key_exists(
                        $field,
                        $data
                    )

                ) {


                    $userData[$field] =

                        $data[$field];


                }


            }






            /*
            |--------------------------------------------------------------------------
            | Update Active / Block Status
            |--------------------------------------------------------------------------
            */


            if (

                array_key_exists(
                    'is_active',
                    $data
                )

            ) {


                $isActive =

                    (bool)
                    $data['is_active'];



                $userData['is_active'] =

                    $isActive;



                $userData['blocked_at'] =

                    $isActive

                        ? null

                        : now();



                if ($isActive) {


                    $userData['failed_login_attempts'] =

                        0;


                }


            }







            /*
            |--------------------------------------------------------------------------
            | Save User
            |--------------------------------------------------------------------------
            */


            if (

                count(
                    $userData
                ) > 0

            ) {


                $user->update(
                    $userData
                );


            }







            /*
            |--------------------------------------------------------------------------
            | Return Updated User
            |--------------------------------------------------------------------------
            */


            return $user->fresh([

                'role',

                'employee',

            ]);


        }

    );


}





    /*
    |--------------------------------------------------------------------------
    | Toggle Status
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(
        User $user,
        bool $status
    ): User {


        $user->update([

            'is_active' =>
                $status,


            'blocked_at' =>
                $status
                ?
                null
                :
                now(),

        ]);


        return $user->fresh([
            'role',
            'employee',
        ]);

    }





    /*
    |--------------------------------------------------------------------------
    | Soft Delete
    |--------------------------------------------------------------------------
    */

    public function deleteUser(
        User $user
    ): bool {


        if(
            $user->hasRole('admin')
        ){

            throw ValidationException::withMessages([

                'user' =>
                    'Admin user cannot be deleted.',

            ]);

        }


        return (bool)
            $user->delete();

    }


}