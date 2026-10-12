<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'blocked_at' => $this->blocked_at?->toISOString(),
            'last_login_at' => $this->last_login_at?->toISOString(),
            'last_logout_at' => $this->last_logout_at?->toISOString(),

            'role' => $this->whenLoaded('role', function (): array {
                return [
                    'id' => $this->role->id,
                    'name' => $this->role->name,
                    'display_name' => $this->role->display_name,
                ];
            }),

            'permissions' => $this->when(
                $this->relationLoaded('role')
                && $this->role?->relationLoaded('permissions'),
                fn (): array => $this->role->permissions
                    ->pluck('name')
                    ->values()
                    ->all()
            ),
            'employee' => $this->whenLoaded(
    'employee',
    function (): ?array {

        if (! $this->employee) {
            return null;
        }


        return [

            'id' =>
                $this->employee->id,


            'phone' =>
                $this->employee->phone,


            'profile_image' =>
                $this->employee->profile_image
                    ? asset(
                        'storage/' .
                        $this->employee->profile_image
                    )
                    : null,


            'joining_date' =>
                $this->employee->joining_date?->format(
                    'Y-m-d'
                ),


            'hourly_rate' =>
                $this->employee->hourly_rate,


            'current_status' =>
                $this->employee->current_status,


        ];

    }
),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}