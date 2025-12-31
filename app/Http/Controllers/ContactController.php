<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Handle the incoming contact form submission.
     */
    public function send(Request $request)
    {
        // 1. Validation logic
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|min:3|max:255',
            'message' => 'required|string|min:10|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'email', 'subject', 'message']);

        try {
            // 2. Send email to the admin
            // We use the admin email from the .env or a default one
            $adminEmail = config('mail.from.address') ?? 'chzakaria037@gmail.com';
            
            Mail::to($adminEmail)->send(new ContactMessage($data));

            // Log the contact for record keeping
            Log::info("Nouveau message de contact reçu de " . $data['email']);

            return response()->json([
                'success' => true,
                'message' => 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.'
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi de l'email de contact : " . $e->getMessage());
            
            // Analyze common SMTP errors for better debugging (only in local dev)
            $errorMessage = "Désolé, une erreur technique s'est produite lors de l'envoi de votre message.";
            if (config('app.env') === 'local') {
                if (str_contains($e->getMessage(), '535')) {
                    $errorMessage = "Erreur d'authentification SMTP (Gmail). Vérifiez votre Mot de passe d'application.";
                }
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
