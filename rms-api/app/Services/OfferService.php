<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Offer;
use App\Models\Order;
use Carbon\CarbonImmutable;

class OfferService
{
    /** @return array{offer: Offer, discount_amount: float}|null */
    public function bestForOrder(
        float $subtotal,
        ?Customer $customer,
        ?int $excludeOrderId = null
    ): ?array {
        $today = now()->toDateString();
        $time = now()->format('H:i:s');
        $weekday = strtolower(now()->format('D'));

        $offers = Offer::query()
            ->where('is_active', true)
            ->where(function ($query) use ($today): void {
                $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today);
            })
            ->where(function ($query) use ($today): void {
                $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', $today);
            })
            ->orderByDesc('discount_value')
            ->lockForUpdate()
            ->get();

        $eligible = $offers->filter(function (Offer $offer) use (
            $subtotal,
            $customer,
            $today,
            $time,
            $weekday,
            $excludeOrderId
        ): bool {
            if ((float) $offer->minimum_order_amount > $subtotal) {
                return false;
            }

            if ($offer->days_of_week && ! in_array($weekday, $offer->days_of_week, true)) {
                return false;
            }

            if ($offer->start_time && $offer->end_time) {
                $start = (string) $offer->start_time;
                $end = (string) $offer->end_time;
                $withinTime = $end < $start
                    ? ($time >= $start || $time <= $end)
                    : ($time >= $start && $time <= $end);
                if (! $withinTime) {
                    return false;
                }
            }

            if ($offer->type === 'birthday' && ! $this->isBirthdayWindow($customer, (int) $offer->birthday_window_days)) {
                return false;
            }

            if ($offer->type === 'loyalty') {
                if (! $customer || $customer->visitCount() < (int) $offer->minimum_customer_orders) {
                    return false;
                }
            }

            $usageQuery = Order::query()
                ->where('offer_id', $offer->id)
                ->where('status', '!=', Order::STATUS_CANCELED);

            if ($excludeOrderId) {
                $usageQuery->where('id', '!=', $excludeOrderId);
            }

            if ($offer->usage_limit !== null && (clone $usageQuery)->count() >= (int) $offer->usage_limit) {
                return false;
            }

            if ($offer->per_customer_limit !== null && ! $customer) {
                return false;
            }

            if ($offer->per_customer_limit !== null) {
                $customerUses = (clone $usageQuery)
                    ->where('customer_id', $customer->id)
                    ->count();
                if ($customerUses >= (int) $offer->per_customer_limit) {
                    return false;
                }
            }

            return true;
        });

        return $eligible
            ->map(function (Offer $offer) use ($subtotal): array {
                $discount = $offer->discount_type === 'percentage'
                    ? $subtotal * (float) $offer->discount_value / 100
                    : (float) $offer->discount_value;

                return [
                    'offer' => $offer,
                    'discount_amount' => round(min($subtotal, max(0, $discount)), 2),
                ];
            })
            ->sortByDesc('discount_amount')
            ->first();
    }

    private function isBirthdayWindow(?Customer $customer, int $windowDays): bool
    {
        if (! $customer?->date_of_birth) {
            return false;
        }

        $today = CarbonImmutable::today();
        $birth = CarbonImmutable::parse($customer->date_of_birth);
        $birthdayMonthEnd = CarbonImmutable::create($today->year, $birth->month, 1)->endOfMonth()->day;
        $birthdayThisYear = $today->setDate($today->year, $birth->month, min($birth->day, $birthdayMonthEnd));
        $dates = [
            $birthdayThisYear,
            $birthdayThisYear->subYear(),
            $birthdayThisYear->addYear(),
        ];

        return collect($dates)->contains(
            fn (CarbonImmutable $date): bool => $date->diffInDays($today) <= $windowDays
        );
    }
}
