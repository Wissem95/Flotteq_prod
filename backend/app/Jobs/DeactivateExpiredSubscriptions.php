<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\UserSubscription;
use App\Models\User;
use App\Notifications\SubscriptionExpiredNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DeactivateExpiredSubscriptions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = Carbon::now();
        $expired = UserSubscription::where('end_date', '<', $now)
            ->where('is_active', true)
            ->get();

        foreach ($expired as $subscription) {
            $subscription->is_active = false;
            $subscription->save();

            $user = User::find($subscription->user_id);
            if ($user && $user->email) {
                $user->notify(new SubscriptionExpiredNotification());
                Log::info("Notification d'expiration envoyée à {$user->email} (abonnement #{$subscription->id})");
            }
        }
    }
}
