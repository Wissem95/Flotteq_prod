<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VerificationController extends Controller
{
    /**
     * Send verification code to user's email or phone
     */
    public function sendVerificationCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact' => ['required', 'string'], // Email ou téléphone
            'method' => ['required', 'in:email,sms'], // Méthode de vérification
        ]);

        $contact = trim($validated['contact']);
        $method = $validated['method'];

        // Vérifier si l'email/téléphone existe déjà
        $existingUser = User::where(function ($query) use ($contact) {
            $query->where('email', $contact)
                ->orWhere('phone', $contact);
        })->first();

        if ($existingUser) {
            throw ValidationException::withMessages([
                'contact' => ['Cette adresse email ou ce numéro de téléphone est déjà utilisé.'],
            ]);
        }

        // Générer un code à 6 chiffres
        $verificationCode = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Stocker temporairement les informations de vérification
        $verificationData = [
            'contact' => $contact,
            'method' => $method,
            'code' => Hash::make($verificationCode),
            'expires_at' => now()->addMinutes(15),
        ];

        // Stocker en session ou cache (ici on utilise la session)
        session(['verification_data' => $verificationData]);

        // Envoyer le code selon la méthode choisie
        if ($method === 'email') {
            $this->sendEmailCode($contact, $verificationCode);
            $maskedContact = $this->maskEmail($contact);
        } else {
            $this->sendSMSCode($contact, $verificationCode);
            $maskedContact = $this->maskPhone($contact);
        }

        return response()->json([
            'message' => 'Code de vérification envoyé avec succès',
            'method' => $method,
            'masked_contact' => $maskedContact,
            'expires_in' => 15, // minutes
        ]);
    }

    /**
     * Verify the code sent to user
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $code = $validated['code'];
        $verificationData = session('verification_data');

        if (!$verificationData) {
            throw ValidationException::withMessages([
                'code' => ['Aucune vérification en cours. Veuillez recommencer.'],
            ]);
        }

        // Vérifier l'expiration
        if (now()->isAfter($verificationData['expires_at'])) {
            session()->forget('verification_data');
            throw ValidationException::withMessages([
                'code' => ['Le code de vérification a expiré.'],
            ]);
        }

        // Vérifier le code
        if (!Hash::check($code, $verificationData['code'])) {
            throw ValidationException::withMessages([
                'code' => ['Code de vérification invalide.'],
            ]);
        }

        // Marquer comme vérifié
        session(['verification_verified' => true]);

        return response()->json([
            'message' => 'Code vérifié avec succès',
            'verified' => true,
        ]);
    }

    /**
     * Check if current session has verified contact
     */
    public function checkVerificationStatus(Request $request): JsonResponse
    {
        $verificationData = session('verification_data');
        $isVerified = session('verification_verified', false);

        if (!$verificationData) {
            return response()->json([
                'verified' => false,
                'message' => 'Aucune vérification en cours',
            ]);
        }

        return response()->json([
            'verified' => $isVerified,
            'contact' => $verificationData['contact'],
            'method' => $verificationData['method'],
            'expires_at' => $verificationData['expires_at'],
        ]);
    }

    /**
     * Send email with verification code
     */
    private function sendEmailCode(string $email, string $code): void
    {
        // Pour l'instant, on log le code (à remplacer par un vrai service email)
        \Log::info("Code de vérification envoyé par email", [
            'email' => $email,
            'code' => $code,
        ]);

        // TODO: Implémenter l'envoi d'email réel
        // Mail::to($email)->send(new VerificationCodeMail($code));
    }

    /**
     * Send SMS with verification code
     */
    private function sendSMSCode(string $phone, string $code): void
    {
        // Pour l'instant, on log le code (à remplacer par un vrai service SMS)
        \Log::info("Code de vérification envoyé par SMS", [
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
