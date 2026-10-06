<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminUserResource;
use App\Models\User;
use App\Models\Role;
use App\Http\Requests\Api\UpdateUserRequest;
use App\Services\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Api\CreateUserRequest;
use App\Traits\HasAuditLog;


class AdminUserController extends Controller
{

    use HasAuditLog;
public function __construct(

    private readonly UserManagementService $service

)
{
}





    /*
    |--------------------------------------------------------------------------
    | User List
    |--------------------------------------------------------------------------
    */

    public function index(
    Request $request
): JsonResponse {


    $users =

        $this->service->getUsers(
            $request->all()
        );



    return response()->json([


        'success'=>true,


        'message'=>

            'Users retrieved successfully.',



        'data'=>

            AdminUserResource::collection(
                $users
            ),



        'meta'=>[


            'current_page'=>

                $users->currentPage(),



            'last_page'=>

                $users->lastPage(),



            'per_page'=>

                $users->perPage(),



            'from'=>

                $users->firstItem(),



            'to'=>

                $users->lastItem(),



            'total'=>

                $users->total(),



        ],



    ]);

}
    /*
|--------------------------------------------------------------------------
| Create User
|--------------------------------------------------------------------------
*/

public function store(
    CreateUserRequest $request
): JsonResponse {


    $user =

        $this->service->createUser(

            $request->validated()

        );



    $this->audit(

        'users',

        'created',

        $user,

        null,

        $user->toArray(),

        'Created new user: '.$user->name

    );



    return response()->json([


        'success'=>true,


        'message'=>
            'User created successfully.',



        'data'=>

            new AdminUserResource(
                $user
            ),


    ],201);


}




    /*
    |--------------------------------------------------------------------------
    | View User
    |--------------------------------------------------------------------------
    */

    public function show(
        User $user
    ): JsonResponse {


        return response()->json([

            'success'=>true,


            'data'=>
                new AdminUserResource(
                    $this->service->findUser($user)
                ),

        ]);

    }

    /*
|--------------------------------------------------------------------------
| Roles Dropdown
|--------------------------------------------------------------------------
*/

public function roles(): JsonResponse
{

    $roles = Role::query()

        ->where(
            'is_active',
            true
        )

        ->orderBy(
            'display_name'
        )

        ->get([
            'id',
            'name',
            'display_name',
        ]);


    return response()->json([

        'success'=>true,

        'data'=>$roles

    ]);

}





    /*
    |--------------------------------------------------------------------------
    | Update User
    |--------------------------------------------------------------------------
    */

    public function update(
    UpdateUserRequest $request,
    User $user
): JsonResponse {


    /*
    |--------------------------------------------------------------------------
    | Store Old Data
    |--------------------------------------------------------------------------
    */


    $oldData = [

        'name' =>
            $user->name,


        'username' =>
            $user->username,


        'email' =>
            $user->email,


        'role_id' =>
            $user->role_id,


        'role' =>
            $user->role?->display_name,


        'is_active' =>
            $user->is_active,

    ];





    /*
    |--------------------------------------------------------------------------
    | Update User
    |--------------------------------------------------------------------------
    */


    $updated =

        $this->service->updateUser(

            $user,

            $request->validated()

        );







    /*
    |--------------------------------------------------------------------------
    | New Data
    |--------------------------------------------------------------------------
    */


    $newData = [

        'name' =>
            $updated->name,


        'username' =>
            $updated->username,


        'email' =>
            $updated->email,


        'role_id' =>
            $updated->role_id,


        'role' =>
            $updated->role?->display_name,


        'is_active' =>
            $updated->is_active,

    ];








    /*
    |--------------------------------------------------------------------------
    | Create Audit Log
    |--------------------------------------------------------------------------
    */


    $this->audit(

        'users',

        'updated',

        $updated,

        $oldData,

        $newData,

        'Updated user: '.$updated->name

    );







    return response()->json([


        'success'=>true,


        'message'=>

            'User updated successfully.',



        'data'=>

            new AdminUserResource(

                $updated

            ),


    ]);

}





    /*
    |--------------------------------------------------------------------------
    | Block / Unblock
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(
    Request $request,
    User $user
): JsonResponse {


    /*
    |--------------------------------------------------------------------------
    | Old Status
    |--------------------------------------------------------------------------
    */


    $oldData = [

        'is_active' =>
            $user->is_active,

        'status' =>
            $user->is_active
            ?
            'Active'
            :
            'Blocked',

    ];





    /*
    |--------------------------------------------------------------------------
    | Update Status
    |--------------------------------------------------------------------------
    */


    $updated =

        $this->service->toggleStatus(

            $user,

            (bool)
            $request->boolean(
                'is_active'
            )

        );







    /*
    |--------------------------------------------------------------------------
    | New Status
    |--------------------------------------------------------------------------
    */


    $newData = [

        'is_active' =>
            $updated->is_active,


        'status' =>
            $updated->is_active
            ?
            'Active'
            :
            'Blocked',

    ];









    /*
    |--------------------------------------------------------------------------
    | Audit
    |--------------------------------------------------------------------------
    */


    $action =

        $updated->is_active

        ?

        'unblocked'

        :

        'blocked';





    $description =

        $updated->is_active

        ?

        'Unblocked user: '.$updated->name

        :

        'Blocked user: '.$updated->name;







    $this->audit(

        'users',

        $action,

        $updated,

        $oldData,

        $newData,

        $description

    );








    return response()->json([


        'success'=>true,


        'message'=>

            'User status updated.',



        'data'=>

            new AdminUserResource(

                $updated

            ),


    ]);


}





    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(
    User $user
): JsonResponse {


    /*
    |--------------------------------------------------------------------------
    | Store Before Delete
    |--------------------------------------------------------------------------
    */


    $oldData = [

        'id' =>
            $user->id,


        'name' =>
            $user->name,


        'username' =>
            $user->username,


        'email' =>
            $user->email,


        'role' =>
            $user->role?->display_name,


    ];








    /*
    |--------------------------------------------------------------------------
    | Delete User
    |--------------------------------------------------------------------------
    */


    $this->service->deleteUser(

        $user

    );








    /*
    |--------------------------------------------------------------------------
    | Audit Log
    |--------------------------------------------------------------------------
    */


    $this->audit(

        'users',

        'deleted',

        $user,

        $oldData,

        null,

        'Deleted user: '.$user->name

    );







    return response()->json([


        'success'=>true,


        'message'=>

            'User deleted successfully.',


    ]);

}
}