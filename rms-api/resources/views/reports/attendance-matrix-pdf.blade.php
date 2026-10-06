<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Attendance Sheet - {{ $month }}
    </title>


    <style>

        @page {

            size: A4 landscape;

            margin:
                14px
                12px
                14px
                12px;

        }


        * {

            box-sizing: border-box;

        }


        body {

            margin: 0;

            padding: 0;

            font-family:
                DejaVu Sans,
                Arial,
                sans-serif;

            font-size: 8px;

            color: #111827;

        }


        /*
        |--------------------------------------------------------------------------
        | Page Header
        |--------------------------------------------------------------------------
        */

        .page-header {

            position: relative;

            width: 100%;

            min-height: 32px;

            margin-bottom: 10px;

        }


        /*
        |--------------------------------------------------------------------------
        | Title
        |--------------------------------------------------------------------------
        */

        .page-title {

            width: 100%;

            text-align: center;

            margin: 0;

            padding: 0;

            font-size: 15px;

            font-weight: 700;

            letter-spacing: 0.4px;

        }


        /*
        |--------------------------------------------------------------------------
        | Month
        |--------------------------------------------------------------------------
        */

        .page-month {

            position: absolute;

            top: 2px;

            right: 0;

            font-size: 8px;

            font-weight: 700;

            white-space: nowrap;

        }


        /*
        |--------------------------------------------------------------------------
        | Attendance Table
        |--------------------------------------------------------------------------
        */

        .attendance-table {

            width: 100%;

            border-collapse: collapse;

            table-layout: fixed;

        }


        .attendance-table th,
        .attendance-table td {

            border: 1px solid #7f7f7f;

            padding: 0;

            text-align: center;

            vertical-align: middle;

            height: 16px;

        }


        /*
        |--------------------------------------------------------------------------
        | Table Header
        |--------------------------------------------------------------------------
        */

        .attendance-table thead th {

            background: #f2f2f2;

            font-weight: 700;

            font-size: 7px;

            height: 17px;

        }


        /*
        |--------------------------------------------------------------------------
        | SL Column
        |--------------------------------------------------------------------------
        */

        .sl-column {

            width: 28px;

        }


        /*
        |--------------------------------------------------------------------------
        | Name Column
        |--------------------------------------------------------------------------
        |
        | Increased width so employee names are clearly visible.
        |
        */

        .name-column {

            width: 235px;

            text-align: left !important;

            padding-left: 6px !important;

        }


        /*
        |--------------------------------------------------------------------------
        | Day Columns
        |--------------------------------------------------------------------------
        */

        .day-column {

            width: auto;

            font-size: 7px;

        }


        /*
        |--------------------------------------------------------------------------
        | Employee Serial
        |--------------------------------------------------------------------------
        */

        .employee-sl {

            font-size: 7px;

            font-weight: 600;

        }


        /*
        |--------------------------------------------------------------------------
        | Employee Name
        |--------------------------------------------------------------------------
        */

        .employee-name {

            text-align: left !important;

            padding-left: 6px !important;

            padding-right: 5px !important;

            font-size: 8px;

            font-weight: 600;

            white-space: nowrap;

            overflow: hidden;

        }


        /*
        |--------------------------------------------------------------------------
        | Present
        |--------------------------------------------------------------------------
        */

        .status-present {

            font-weight: 700;

        }


        /*
        |--------------------------------------------------------------------------
        | Absent
        |--------------------------------------------------------------------------
        */

        .status-absent {

            font-weight: 700;

        }


        /*
        |--------------------------------------------------------------------------
        | Footer / Legend
        |--------------------------------------------------------------------------
        */

        .footer {

            margin-top: 8px;

            text-align: left;

            font-size: 7px;

        }


        .footer strong {

            font-weight: 700;

        }


    </style>

</head>


<body>


    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <div class="page-header">


        <!-- CENTER TITLE -->

        <h1 class="page-title">

            ATTENDANCE SHEET

        </h1>


        <!-- RIGHT SIDE MONTH -->

        <div class="page-month">

            Month:
            {{ $month }}

        </div>


    </div>


    <!-- =========================================================
         ATTENDANCE MATRIX
    ========================================================== -->

    <table class="attendance-table">


        <thead>

            <tr>


                <!-- SL -->

                <th class="sl-column">

                    SL

                </th>


                <!-- NAME -->

                <th class="name-column">

                    NAME

                </th>


                <!-- DAYS -->

                @for(
                    $day = 1;
                    $day <= $days_in_month;
                    $day++
                )

                    <th class="day-column">

                        {{ $day }}

                    </th>

                @endfor


            </tr>

        </thead>


        <tbody>


            @forelse(
                $rows as $row
            )


                <tr>


                    <!-- =================================================
                         SERIAL
                    ================================================== -->

                    <td class="employee-sl">

                        {{ $row['sl'] }}

                    </td>


                    <!-- =================================================
                         EMPLOYEE NAME
                    ================================================== -->

                    <td class="employee-name">

                        {{ $row['employee_name'] }}

                    </td>


                    <!-- =================================================
                         DAILY STATUS
                    ================================================== -->

                    @for(
                        $day = 1;
                        $day <= $days_in_month;
                        $day++
                    )


                        @php

                            $status =
                                $row['days'][$day]
                                ?? '';

                        @endphp


                        <td
                            class="
                                {{
                                    $status === 'P'
                                        ? 'status-present'
                                        : (
                                            $status === 'A'
                                                ? 'status-absent'
                                                : ''
                                        )
                                }}
                            "
                        >

                            {{ $status }}

                        </td>


                    @endfor


                </tr>


            @empty


                <tr>

                    <td
                        colspan="{{ 2 + $days_in_month }}"
                        style="
                            padding: 20px;
                            text-align: center;
                        "
                    >

                        No employees found.

                    </td>

                </tr>


            @endforelse


        </tbody>


    </table>


    <!-- =========================================================
         LEGEND
    ========================================================== -->

    <div class="footer">

        <strong>P</strong>
        = Present

        &nbsp;&nbsp;&nbsp;&nbsp;

        <strong>A</strong>
        = Absent

    </div>


</body>

</html>