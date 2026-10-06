<?php

namespace App\Http\Resources;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;



class AuditLogResource extends JsonResource
{


    public function toArray(
        Request $request
    ): array
    {


        return [


            'id'=>
                $this->id,


            'user'=>

                $this->whenLoaded(

                    'user',

                    function(){

                        return [

                            'id'=>
                                $this->user->id,


                            'name'=>
                                $this->user->name,


                            'username'=>
                                $this->user->username,


                            'email'=>
                                $this->user->email,

                        ];

                    }

                ),




            'module'=>
                $this->module,



            'action'=>
                $this->action,



            'description'=>
                $this->description,



            'model_type'=>
                $this->model_type,



            'model_id'=>
                $this->model_id,



            'old_values'=>
                $this->old_values,



            'new_values'=>
                $this->new_values,



            'ip_address'=>
                $this->ip_address,



            'user_agent'=>
                $this->user_agent,



            'created_at'=>
                $this->created_at?->toISOString(),


        ];

    }


}