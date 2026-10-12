<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SystemSetting;


class ProfitLossPdfService
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

            'pdf.profit-loss',

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