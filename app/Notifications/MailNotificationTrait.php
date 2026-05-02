<?php

namespace App\Notifications;

use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\ThrottlesExceptionsWithRedis;
use Throwable;

trait MailNotificationTrait {
    public int $tries = 5;
    public array $backoff = [600, 1200, 2400, 4800, 9600];

    public function middleware(): array {
        return [
            (new RateLimited('mails'))->releaseAfter(3600),
            (new ThrottlesExceptionsWithRedis(5, 600))->by('mail')->backoff(5),
        ];
    }

    public function via($notifiable): array {
        return ['mail'];
    }

    /**
     * Determine the notification's delivery delay.
     *
     * @return array<string, \Illuminate\Support\Carbon>
     */
    public function withDelay(object $notifiable): array {
        return [
            'mail' => now()->addMinutes(5),
        ];
    }

    public function failed(Throwable $exception): void {
        // Report the exception if the job fails after all attempts
        report($exception);
    }
}
