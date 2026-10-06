<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TestAttendanceRequest;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class TestAttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService
    ) {
    }

    public function create(
        TestAttendanceRequest $request
    ): JsonResponse {

        $data = $request->validated();

        $attendanceDate =
            Carbon::parse(
                $data['attendance_date']
            )->startOfDay();

        /*
        |----------------------------------------------------------------------
        | First ensure schedule-generated attendance exists
        |----------------------------------------------------------------------
        */

        $this->attendanceService->syncForDate(
            $attendanceDate,
            $request->user()
        );

        $attendance =
            Attendance::query()
                ->where(
                    'employee_id',
                    $data['employee_id']
                )
                ->whereDate(
                    'attendance_date',
                    $attendanceDate
                )
                ->firstOrFail();

        /*
        |----------------------------------------------------------------------
        | Apply controlled test timestamps
        |----------------------------------------------------------------------
        */

        $attendance->update([
            'check_in_at' =>
                ! empty($data['check_in_at'])
                    ? Carbon::parse(
                        $data['check_in_at']
                    )
                    : null,

            'check_out_at' =>
                ! empty($data['check_out_at'])
                    ? Carbon::parse(
                        $data['check_out_at']
                    )
                    : null,

            'status' =>
                ! empty($data['check_out_at'])
                    ? Attendance::STATUS_COMPLETED
                    : (
                        ! empty($data['check_in_at'])
                            ? Attendance::STATUS_PRESENT
                            : Attendance::STATUS_SCHEDULED
                    ),

            'is_manual' =>
                true,

            'auto_checked_out' =>
                false,

            'auto_checkout_reason' =>
                null,

            'updated_by' =>
                $request->user()?->id,
        ]);

        /*
        |----------------------------------------------------------------------
        | Recalculate attendance
        |----------------------------------------------------------------------
        */

        // $reflection =
        //     new \ReflectionClass(
        //         $this->attendanceService
        //     );

        // $method =
        //     $reflection->getMethod(
        //         'recalculateAttendance'
        //     );

        // $method->setAccessible(true);

        // $method->invoke(
        //     $this->attendanceService,
        //     $attendance->fresh(),
        //     $request->user()
        // );
        $attendance =
    $this->attendanceService
        ->recalculateForTesting(
            $attendance->fresh(),
            $request->user()
        );

        $attendance->load([
            'employee.user.role',
            'breaks',
            'shiftSchedule',
            'shiftScheduleOverride',
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Test attendance created successfully.',
            'data' =>
                new \App\Http\Resources\AttendanceResource(
                    $attendance
                ),
        ]);
    }
}