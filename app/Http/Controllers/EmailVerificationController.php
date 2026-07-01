<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmailVerificationController extends Controller
{
    /**
     * Verificar si un email es válido usando Abstract API
     */
    public function verifyEmail(Request $request)
    {
        try {
            // Validación más flexible
            $email = $request->input('email');
            
            if (!$email) {
                return response()->json([
                    'is_valid' => false,
                    'message' => 'El email es requerido.',
                ], 400);
            }

            // Validación básica de formato
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'is_valid' => false,
                    'message' => 'El formato del correo no es válido.',
                ], 400);
            }

            $apiKey = env('ABSTRACT_API_KEY');

            if (!$apiKey) {
                // Si no hay API key, simplemente hacer validación local
                return response()->json([
                    'is_valid' => true,
                    'is_valid_format' => true,
                    'is_smtp_valid' => true,
                    'is_catchall_email' => false,
                    'is_disposable_email' => false,
                    'quality_score' => 1,
                    'message' => 'Correo válido (validación local)',
                ]);
            }

            // Llamar a Abstract API para verificar el email
            $response = Http::timeout(10)->get('https://emailvalidation.abstractapi.com/v1/', [
                'api_key' => $apiKey,
                'email' => $email,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $isValid = ($data['is_valid_format']['value'] ?? false) && 
                          ($data['is_smtp_valid']['value'] ?? false) &&
                          !($data['is_disposable_email']['value'] ?? false);

                return response()->json([
                    'is_valid_format' => $data['is_valid_format']['value'] ?? false,
                    'is_smtp_valid' => $data['is_smtp_valid']['value'] ?? false,
                    'is_catchall_email' => $data['is_catchall_email']['value'] ?? false,
                    'is_disposable_email' => $data['is_disposable_email']['value'] ?? false,
                    'quality_score' => $data['quality_score']['value'] ?? 0,
                    'is_valid' => $isValid,
                    'message' => $this->getEmailMessage($data),
                ]);
            }

            return response()->json([
                'is_valid' => false,
                'message' => 'No se pudo verificar el email. Intenta de nuevo.',
            ], 400);
        } catch (\Exception $e) {
            \Log::error('Email verification error: ' . $e->getMessage());
            
            return response()->json([
                'is_valid' => false,
                'message' => 'Error al verificar el email. Por favor, intenta de nuevo.',
            ], 400);
        }
    }

    /**
     * Generar un mensaje basado en los resultados de validación
     */
    private function getEmailMessage($data)
    {
        // Si el email no es válido en formato
        if (!($data['is_valid_format']['value'] ?? false)) {
            return 'El formato del correo no es válido.';
        }

        // Si es un email desechable
        if ($data['is_disposable_email']['value'] ?? false) {
            return 'No se permiten correos desechables.';
        }

        // Si es un catchall (puede que no sea válido)
        if ($data['is_catchall_email']['value'] ?? false) {
            return 'Este correo pertenece a un servidor catchall.';
        }

        // Si SMTP es inválido
        if (!($data['is_smtp_valid']['value'] ?? false)) {
            return 'El correo no existe o no es válido.';
        }

        // Si llegamos aquí, el email es válido
        return 'El correo es válido.';
    }
}
