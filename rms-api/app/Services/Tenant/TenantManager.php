<?php

namespace App\Services\Tenant;

use App\Models\Central\Restaurant;

class TenantManager
{
    private ?Restaurant $restaurant = null;

    public function set(
        Restaurant $restaurant
    ): void {
        $this->restaurant =
            $restaurant;
    }

    public function get(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function id(): ?int
    {
        return $this->restaurant?->id;
    }

    public function key(): ?string
    {
        return $this->restaurant?->tenant_key;
    }

    public function domain(): ?string
    {
        return $this->restaurant
            ?->domains()
            ->where('is_primary', true)
            ->value('domain');
    }

    public function clear(): void
    {
        $this->restaurant = null;
    }

    public function check(): bool
    {
        return $this->restaurant !== null;
    }
}