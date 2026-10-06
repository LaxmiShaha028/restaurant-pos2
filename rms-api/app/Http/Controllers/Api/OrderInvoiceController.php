<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;

use App\Models\Order;

use App\Models\SystemSetting;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Http\Request;

use Symfony\Component\HttpFoundation\Response;



class OrderInvoiceController extends Controller
{


    /*
    |--------------------------------------------------------------------------
    | Download Customer Invoice
    |--------------------------------------------------------------------------
    */


    public function download(
        Request $request,
        Order $order
    ): Response {



        /*
        |--------------------------------------------------------------------------
        | Load Invoice Relations
        |--------------------------------------------------------------------------
        */


        $order->load([


            'customer',


            'primaryTable',


            'tables',


            'items.variant',


            'items.addons',


            'payments.receiver',


            'creator',


        ]);







        /*
        |--------------------------------------------------------------------------
        | Load System Settings
        |--------------------------------------------------------------------------
        */


        $settings =

            SystemSetting::query()

                ->firstOrCreate(

                    [
                        'id' => 1,
                    ],


                    [

                        'restaurant_name' =>

                            config(

                                'app.name',

                                'Restaurant Management System'

                            ),


                        'vat_rate' => 0,


                        'vat_type' => 'percentage',


                        'service_charge_rate' => 0,


                        'service_charge_type' => 'percentage',


                        'invoice_currency' => 'BDT',


                        'invoice_show_logo' => true,


                        'invoice_show_company_name' => true,


                        'invoice_show_branch_address' => true,


                        'invoice_show_token_number' => true,


                        'invoice_show_table_number' => true,


                    ]

                );









        /*
        |--------------------------------------------------------------------------
        | Prepare Invoice Items
        |--------------------------------------------------------------------------
        */


        $invoiceItems =


            $order->items

                ->map(

                    function ($item): array {


                        $quantity = max(

                            1,

                            (int)

                            $item->quantity

                        );



                        $unitPrice =

                            (float)

                            $item->unit_price;



                        $baseTotal =

                            $unitPrice *

                            $quantity;



                        $addonTotal =

                            (float)

                            $item->addon_total;



                        $lineTotal =

                            (float)

                            $item->line_total;




                        if (

                            $lineTotal <= 0

                            &&

                            (

                                $baseTotal > 0

                                ||

                                $addonTotal > 0

                            )

                        ) {


                            $lineTotal =

                                $baseTotal

                                +

                                $addonTotal;


                        }






                        return [



                            'id' =>

                                (int)

                                $item->id,



                            'name' =>

                                $item->item_name

                                ?:

                                'Menu Item',



                            'variant' =>

                                $item->variant_name,



                            'quantity' =>

                                $quantity,



                            'unit_price' =>

                                $unitPrice,



                            'base_total' =>

                                $baseTotal,



                            'addon_total' =>

                                $addonTotal,



                            'line_total' =>

                                $lineTotal,



                            'kitchen_note' =>

                                $item->kitchen_note,




                            'addons' =>


                                $item->addons

                                    ->map(

                                        function ($addon): array {


                                            return [


                                                'name' =>

                                                    $addon->addon_name,



                                                'quantity' =>

                                                    max(

                                                        1,

                                                        (int)

                                                        $addon->quantity

                                                    ),



                                                'unit_price' =>

                                                    (float)

                                                    $addon->unit_price,



                                                'total_price' =>

                                                    (float)

                                                    $addon->total_price,



                                            ];


                                        }

                                    )

                                    ->values()

                                    ->all(),


                        ];



                    }

                )

                ->values();
        /*
        |--------------------------------------------------------------------------
        | Resolve Tables
        |--------------------------------------------------------------------------
        */


        $mergedTables =


            $order->tables

                ->filter(

                    static fn ($table): bool =>

                        ! (bool)

                        (

                            $table->pivot?->is_primary

                            ??

                            false

                        )

                )

                ->pluck('table_name')

                ->filter()

                ->values()

                ->all();








        /*
        |--------------------------------------------------------------------------
        | Customer Information
        |--------------------------------------------------------------------------
        */


        $customer = [


            'name' =>

                $order->customer_name

                ?:

                $order->customer?->name

                ?:

                'Walk-in Customer',



            'phone' =>

                $order->customer_phone

                ?:

                $order->customer?->phone,



            'email' =>

                $order->customer_email

                ?:

                $order->customer?->email,



        ];








        /*
        |--------------------------------------------------------------------------
        | Financial Summary
        |--------------------------------------------------------------------------
        */


        $financialSummary = [


            'subtotal' =>

                (float)

                $order->subtotal,



            'discount' =>

                (float)

                $order->discount_amount,



            'tax' =>

                (float)

                $order->tax_amount,



            'service_charge' =>

                (float)

                $order->service_charge,



            'total' =>

                (float)

                $order->total_amount,



            'paid' =>

                (float)

                $order->paid_amount,



            'due' =>

                (float)

                $order->due_amount,



        ];








        /*
        |--------------------------------------------------------------------------
        | Currency Setup
        |--------------------------------------------------------------------------
        */


        $invoiceCurrency =

            strtoupper(

                (string)

                (

                    $settings->invoice_currency

                    ?:

                    'BDT'

                )

            );




        $currencySymbols = [


            'BDT' => '৳',

            'USD' => '$',

            'EUR' => '€',

            'GBP' => '£',

            'INR' => '₹',

            'SAR' => '﷼',

            'AED' => 'د.إ',


        ];




        $currencySymbol =


            $currencySymbols[$invoiceCurrency]

            ??

            $invoiceCurrency;









        /*
        |--------------------------------------------------------------------------
        | Restaurant Logo
        |--------------------------------------------------------------------------
        */


        $restaurantLogoUrl =


            $this->resolvePdfImage(

                $settings->restaurant_logo

            );









        /*
        |--------------------------------------------------------------------------
        | Invoice Settings Payload
        |--------------------------------------------------------------------------
        */


        $invoiceSettings = [



            'restaurant_name' =>


                $settings->restaurant_name

                ?:

                config(

                    'app.name',

                    'Restaurant Management System'

                ),





            'restaurant_logo' =>


                $restaurantLogoUrl,





            'invoice_subtitle' =>


                $settings->invoice_subtitle

                ?:

                null,





            'branch_address' =>


                $settings->branch_address

                ?:

                null,





            'bin_number' =>


                $settings->bin_number

                ?:

                null,





            'currency' =>


                $invoiceCurrency,





            'currency_symbol' =>


                $currencySymbol,







            'show_logo' =>


                (int)

                $settings->invoice_show_logo

                === 1,





            'show_company_name' =>


                (int)

                $settings->invoice_show_company_name

                === 1,





            'show_branch_address' =>


                (int)

                $settings->invoice_show_branch_address

                === 1,





            'show_token_number' =>


                (int)

                $settings->invoice_show_token_number

                === 1,





            'show_table_number' =>


                (int)

                $settings->invoice_show_table_number

                === 1,



        ];
        /*
        |--------------------------------------------------------------------------
        | Build Invoice Data
        |--------------------------------------------------------------------------
        */


        $invoiceData = [


            'order' =>

                $order,



            'items' =>

                $invoiceItems,



            'customer' =>

                $customer,



            'financial' =>

                $financialSummary,



            'invoiceSettings' =>

                $invoiceSettings,



            'primaryTable' =>

                $order->primaryTable,



            'mergedTables' =>

                $mergedTables,



            'payments' =>

                $order->payments,



            'generatedAt' =>

                now(),



        ];








        /*
        |--------------------------------------------------------------------------
        | Safe File Name
        |--------------------------------------------------------------------------
        */


        $safeOrderNumber = preg_replace(

            '/[^A-Za-z0-9\-_]/',

            '-',

            (string)

            $order->order_number

        );



        $fileName =


            'invoice-'

            .

            (

                $safeOrderNumber

                ?:

                $order->id

            )

            .

            '.pdf';









        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */


        $pdf = Pdf::loadView(
            'order-invoice',
            $invoiceData
        )
        ->setPaper(

            [0, 0, 226.77, 500],

            'portrait'

        );





        return $pdf->download(

            $fileName

        );


    }








    /*
    |--------------------------------------------------------------------------
    | Resolve PDF Image
    |--------------------------------------------------------------------------
    |
    | DomPDF webp issue handle করার জন্য
    |
    */


    private function resolvePdfImage(?string $path): ?string

    {


        if(empty($path)){


            return null;


        }




        $relativePath = ltrim(

            $path,

            '/'

        );





        if(

            str_starts_with(

                $relativePath,

                'storage/'

            )

        ){


            $relativePath = substr(

                $relativePath,

                8

            );


        }







        $fullPath = storage_path(

            'app/public/' .

            $relativePath

        );






        if(!file_exists($fullPath)){


            return null;


        }







        $extension = strtolower(

            pathinfo(

                $fullPath,

                PATHINFO_EXTENSION

            )

        );







        /*
        |--------------------------------------------------------------------------
        | Convert WebP
        |--------------------------------------------------------------------------
        */


        if($extension === 'webp'){



            if(function_exists('imagecreatefromwebp')){



                $image = imagecreatefromwebp(

                    $fullPath

                );



                ob_start();



                imagepng($image);



                $png = ob_get_clean();



                imagedestroy($image);





                return

                    'data:image/png;base64,'

                    .

                    base64_encode($png);



            }




            return null;



        }







        $mime = mime_content_type(

            $fullPath

        )

        ?:

        'image/png';







        return

            'data:' .

            $mime .

            ';base64,' .

            base64_encode(

                file_get_contents(

                    $fullPath

                )

            );



    }



}