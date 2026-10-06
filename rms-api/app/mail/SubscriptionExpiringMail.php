<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiringMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public array $mailData
    ) {
    }

    public function build(): static
    {
        $hours = (int) ($this->mailData['reminder_hours'] ?? 0);
        $restaurantName = (string) ($this->mailData['restaurant_name'] ?? 'Restaurant');

        return $this
            ->subject(
                $hours === 24
                    ? "Important: {$restaurantName} subscription expires in 24 hours"
                    : "{$restaurantName} subscription expires in 48 hours"
            )
            ->view('emails.subscriptions.expiring')
            ->with([
                'mailData' => $this->mailData,
            ]);
    }
}
