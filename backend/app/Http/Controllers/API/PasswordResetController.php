<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /**
     * Send password reset code via email or SMS
     */
    public function sendResetCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact' => ['required', 'string'], // Email ou téléphone
            'method' => ['required', 'in:email,sms'], // Méthode de vérification
        ]);

        $contact = trim($validated['contact']);
        $method = $validated['method'];

        // Chercher l'utilisateur par email ou téléphone
        $user = User::where(function ($query) use ($contact) {
            $query->where('email', $contact)
                ->orWhere('phone', $contact);
        })
            ->where('is_active', true)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'contact' => ['Aucun utilisateur trouvé avec cette information.'],
            ]);
        }

        // Générer un code à 6 chiffres
        $resetCode = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Stocker le code avec expiration (15 minutes)
        $user->update([
            'reset_code' => Hash::make($resetCode),
            'reset_code_expires_at' => now()->addMinutes(15),
        ]);

        // Envoyer le code selon la méthode choisie
        if ($method === 'email') {
            $this->sendEmailCode($user->email, $resetCode);
            $maskedContact = $this->maskEmail($user->email);
        } else {
            $this->sendSMSCode($user->phone, $resetCode);
            $maskedContact = $this->maskPhone($user->phone);
        }

        return response()->json([
            'message' => 'Code de vérification envoyé avec succès',
            'method' => $method,
            'masked_contact' => $maskedContact,
            'expires_in' => 15, // minutes
        ]);
    }

    /**
     * Verify reset code and allow password reset
     */
    public function verifyResetCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $contact = trim($validated['contact']);
        $code = $validated['code'];

        // Chercher l'utilisateur
        $user = User::where(function ($query) use ($contact) {
            $query->where('email', $contact)
                ->orWhere('phone', $contact);
        })
            ->where('is_active', true)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'contact' => ['Utilisateur non trouvé.'],
            ]);
        }

        // Vérifier l'expiration du code
        if (!$user->reset_code_expires_at || now()->isAfter($user->reset_code_expires_at)) {
            throw ValidationException::withMessages([
                'code' => ['Le code de vérification a expiré.'],
            ]);
        }

        // Vérifier le code
        if (!Hash::check($code, $user->reset_code)) {
            throw ValidationException::withMessages([
                'code' => ['Code de vérification invalide.'],
            ]);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($validated['new_password']),
            'reset_code' => null,
            'reset_code_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès',
        ]);
    }

    /**
     * Send email with reset code
     */
    private function sendEmailCode(string $email, string $code): void
    {
        // Log le code pour le développement
        Log::info("Code de réinitialisation envoyé par email", [
            'email' => $email,
            'code' => $code,
        ]);

        // Envoyer l'email réel
        try {
            Mail::to($email)->send(new \App\Mail\PasswordResetCodeMail($code, $email));
            Log::info("Email de réinitialisation envoyé avec succès", ['email' => $email]);
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi de l'email de réinitialisation", [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            // On ne lance pas d'exception pour ne pas bloquer le processus
        }
    }

    /**
     * Send SMS with reset code
     */
    private function sendSMSCode(string $phone, string $code): void
    {
        // Pour l'instant, on log le code (à remplacer par un vrai service SMS)
        Log::info("Code de réinitialisation envoyé par SMS", [
            'phone' => $phone,
            'code' => $code,
        ]);

        // TODO: Implémenter l'envoi de SMS réel (Twilio, etc.)
    }

    /**
     * Mask email for privacy
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1];

        $maskedName = substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
        return $maskedName . '@' . $domain;
    }

    /**
     * Mask phone for privacy
     */
    private function maskPhone(string $phone): string
    {
        return substr($phone, 0, 2) . str_repeat('*', strlen($phone) - 4) . substr($phone, -2);
    }
}
