<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | PDF Export
    |--------------------------------------------------------------------------
    */

    public function pdf(
    Request $request,
    string $type
) {

    $data =
        $this->reportService->getExportData(
            $type,
            $request->all()
        );


    /*
    |--------------------------------------------------------------------------
    | Attendance Monthly Matrix PDF
    |--------------------------------------------------------------------------
    */

    if ($type === 'attendance') {

    $pdf = Pdf::loadView(
        'reports.attendance-matrix-pdf',
        $data
    );

    $pdf->setPaper(
        'a4',
        'landscape'
    );

    return $pdf->download(
        'attendance-report.pdf'
    );
}


    /*
    |--------------------------------------------------------------------------
    | Existing Reports
    |--------------------------------------------------------------------------
    */

    $pdf =
        Pdf::loadView(
            'reports.pdf',
            [
                'type' => $type,
                'data' => $data,
            ]
        )
        ->setPaper(
            'a4',
            'landscape'
        );


    return $pdf->download(
        $type . '-report.pdf'
    );
}


    /*
    |--------------------------------------------------------------------------
    | CSV Export
    |--------------------------------------------------------------------------
    */

    /*
|--------------------------------------------------------------------------
| CSV Export
|--------------------------------------------------------------------------
*/

public function csv(
    Request $request,
    string $type
): StreamedResponse {

    /*
    |--------------------------------------------------------------------------
    | Attendance CSV
    |--------------------------------------------------------------------------
    |
    | Attendance now uses a monthly matrix structure,
    | so it needs a dedicated CSV export flow.
    |
    */

    if ($type === 'attendance') {

        $rows =
            $this->reportService
                ->exportAttendanceCsv(
                    $request->all()
                );


        return response()->streamDownload(

            function () use ($rows) {

                $handle =
                    fopen(
                        'php://output',
                        'w'
                    );


                /*
                |--------------------------------------------------------------------------
                | UTF-8 BOM
                |--------------------------------------------------------------------------
                */

                fprintf(
                    $handle,
                    chr(0xEF)
                    . chr(0xBB)
                    . chr(0xBF)
                );


                /*
                |--------------------------------------------------------------------------
                | CSV Rows
                |--------------------------------------------------------------------------
                */

                foreach (
                    $rows as $row
                ) {

                    fputcsv(
                        $handle,
                        $row
                    );

                }


                fclose(
                    $handle
                );

            },

            'attendance-report.csv',

            [

                'Content-Type' =>
                    'text/csv; charset=UTF-8',

                'Content-Disposition' =>
                    'attachment; filename="attendance-report.csv"',

                'Cache-Control' =>
                    'no-cache, no-store, must-revalidate',

                'Pragma' =>
                    'no-cache',

            ]

        );

    }


    /*
    |--------------------------------------------------------------------------
    | Existing CSV Reports
    |--------------------------------------------------------------------------
    */

    $data =
        $this->reportService->getExportData(
            $type,
            $request->all()
        );


    return response()->streamDownload(

        function () use ($data) {

            $handle =
                fopen(
                    'php://output',
                    'w'
                );


            /*
            |--------------------------------------------------------------------------
            | UTF-8 BOM
            |--------------------------------------------------------------------------
            */

            fprintf(
                $handle,
                chr(0xEF)
                . chr(0xBB)
                . chr(0xBF)
            );


            /*
            |--------------------------------------------------------------------------
            | Data
            |--------------------------------------------------------------------------
            */

            if (!empty($data)) {

                fputcsv(

                    $handle,

                    array_keys(
                        $data[0]
                    )

                );


                foreach (
                    $data as $row
                ) {

                    fputcsv(
                        $handle,
                        $row
                    );

                }

            } else {

                fputcsv(

                    $handle,

                    [
                        'No Data Found'
                    ]

                );

            }


            fclose(
                $handle
            );

        },

        $type . '-report.csv',

        [

            'Content-Type' =>
                'text/csv; charset=UTF-8',

            'Content-Disposition' =>
                'attachment; filename="' .
                $type .
                '-report.csv"',

            'Cache-Control' =>
                'no-cache, no-store, must-revalidate',

            'Pragma' =>
                'no-cache',

        ]

    );
}
}