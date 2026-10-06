<?php

namespace App\Services;


use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SystemSetting;



class TrialBalancePdfService
{


    public function generate(
        array $data
    )
    {


        $setting =

            SystemSetting::on('tenant')
                ->first();



        $restaurantName =

            $setting?->restaurant_name

            ??

            'Restaurant Name';





        return Pdf::loadView(

            'pdf.trial-balance',

            [

                'data'=>$data,

                'restaurantName'=>$restaurantName

            ]

        )

        ->setPaper(

            'a4',

            'portrait'

        );


    }


}