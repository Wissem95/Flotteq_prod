<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckIncompleteProfile
{
    /**
     * Handle an incoming request.
     *
     * Ce middleware vérifie si le profil utilisateur est incomplet
     * et ajoute une alerte dans la réponse si c'est le cas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Vérifier si l'utilisateur est authentifié
        $user = Auth::user();
        if (!$user) {
            return $response;
        }

        // Vérifier si le profil est incomplet
        if ($user->hasIncompleteProfile()) {
            // Log pour le debug
            Log::info("Profil incomplet détecté pour l'utilisateur {$user->id}", [
                'user_id' => $user->id,
                'email' => $user->email,
                'missing_fields' => $user->getMissingProfileFields()
            ]);

            // Ajouter les informations de profil incomplet à la réponse
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $data = $response->getData(true);

                // Ajouter les alertes de profil incomplet
                $data['profile_alert'] = [
                    'type' => 'profile_incomplete',
                    'message' => 'Votre profil est incomplet. Veuillez compléter vos informations personnelles.',
                    'missing_fields' => $user->getMissingProfileFields(),
                    'action_url' => '/profile',
                    'priority' => 'high'
                ];

                $response->setData($data);
            }
        }

        return $response;
    }
}
