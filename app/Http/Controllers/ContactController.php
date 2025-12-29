<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Handle the incoming contact form submission.
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'email', 'subject', 'message']);

        try {
            // Send email to the admin
            Mail::to('chzakaria037@gmail.com')->send(new \App\Mail\ContactAdminMail($data));

            // Send confirmation email to the user
            Mail::to($data['email'])->send(new \App\Mail\ContactConfirmationMail($data['name']));

            return response()->json([
                'success' => true,
                'message' => 'Votre message a été envoyé avec succès !'
            ]);
        } catch (\Exception $e) {
            $errorMessage = "Une erreur technique est survenue.";
            $debugMessage = $e->getMessage();

            // Analyze common SMTP errors
            if (str_contains($debugMessage, '535') || str_contains($debugMessage, 'Username and Password not accepted')) {
                $errorMessage = "Erreur d'authentification Gmail. Vérifiez que vous utilisez bien un Mot de passe d'application (16 caractères) et non votre mot de passe habituel.";
            } elseif (str_contains($debugMessage, 'Connection could not be established')) {
                $errorMessage = "Impossible de se connecter au serveur Gmail. Vérifiez le port (465=SSL) et votre connexion internet.";
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'debug' => $debugMessage
            ], 500);
        }
    }
}
