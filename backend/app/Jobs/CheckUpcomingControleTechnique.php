<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ControleTechnique;
use App\Models\Vehicle;
use App\Models\User;
use App\Notifications\ControleTechniqueReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CheckUpcomingControleTechnique implements ShouldQueue
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
        $dans30Jours = $now->copy()->addDays(30);

        $cts = ControleTechnique::whereBetween('prochain_controle', [$now, $dans30Jours])->get();

        foreach ($cts as $ct) {
            $vehicle = Vehicle::find($ct->vehicle_id);
            if (!$vehicle) continue;
            $user = User::find($vehicle->user_id);
            if (!$user || !$user->email) continue;

            $user->notify(new ControleTechniqueReminderNotification(
                $vehicle->immatriculation ?? $vehicle->id,
                $ct->prochain_controle,
                $user->email
            ));

            Log::info("Relance CT envoyée à {$user->email} pour véhicule {$vehicle->immatriculation}");
        }
    }
}
