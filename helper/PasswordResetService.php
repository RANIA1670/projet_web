<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Helpers\MailService;

class PasswordResetService
{
    private MailService $mailService;
    
    public function __construct(?MailService $mailService = null)
    {
        $this->mailService = $mailService ?? new MailService();
    }
    
    public function sendResetCodeByEmail(string $email, string $code, string $username): array
    {
        try {
            $subject = 'CityZen - Code de réinitialisation de mot de passe';
            $message = $this->getEmailTemplate($username, $code);
            
            $result = $this->mailService->send($email, $subject, $message);
            
            if ($result['ok']) {
                return ['ok' => true, 'message' => 'Code envoyé par email avec succès.'];
            }
            
            return ['ok' => false, 'error' => $result['error'] ?? 'Erreur lors de l\'envoi de l\'email.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'Erreur: ' . $e->getMessage()];
        }
    }
    
    public function sendResetCodeBySMS(string $phone, string $code): array
    {
        try {
            // Pour les SMS, nous utiliserons un service simulé (à configurer avec un vrai service SMS)
            $message = "CityZen: Votre code de reinitialisation est: {$code}. Valide 15 minutes.";
            
            // Simulation d'envoi SMS (remplacer par un vrai service SMS comme Twilio, etc.)
            $smsResult = $this->sendSMS($phone, $message);
            
            if ($smsResult['ok']) {
                return ['ok' => true, 'message' => 'Code envoyé par SMS avec succès.'];
            }
            
            return ['ok' => false, 'error' => $smsResult['error'] ?? 'Erreur lors de l\'envoi du SMS.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'Erreur: ' . $e->getMessage()];
        }
    }
    
    private function getEmailTemplate(string $username, string $code): string
    {
        return "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .code { background: #e9ecef; padding: 15px; font-size: 24px; font-weight: bold; text-align: center; margin: 20px 0; border-radius: 5px; }
        .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🏛️ CityZen</h1>
            <h2>Réinitialisation de mot de passe</h2>
        </div>
        <div class='content'>
            <p>Bonjour <strong>{$username}</strong>,</p>
            <p>Vous avez demandé la réinitialisation de votre mot de passe CityZen.</p>
            <p>Votre code de vérification est:</p>
            <div class='code'>{$code}</div>
            <p><strong>Ce code est valide pendant 15 minutes.</strong></p>
            <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
        </div>
        <div class='footer'>
            <p>Cet email a été envoyé automatiquement. Merci de ne pas répondre.</p>
            <p>© 2024 CityZen - Service Citoyen</p>
        </div>
    </div>
</body>
</html>";
    }
    
    private function sendSMS(string $phone, string $message): array
    {
        // Simulation d'envoi SMS
        // Dans un environnement de production, intégrer un vrai service SMS comme:
        // - Twilio
        // - OVH SMS
        // - Orange SMS API
        // - etc.
        
        // Validation du numéro de téléphone
        if (!$this->validatePhoneNumber($phone)) {
            return ['ok' => false, 'error' => 'Numéro de téléphone invalide.'];
        }
        
        // Logique d'envoi SMS simulée
        error_log("SMS envoyé à {$phone}: {$message}");
        
        // Simulation de succès
        return ['ok' => true];
    }
    
    private function validatePhoneNumber(string $phone): bool
    {
        // Validation simple du format de numéro de téléphone
        // Accepte les formats: +33612345678, 0612345678, etc.
        return preg_match('/^(\+?\d{1,3}[-\s]?)?\d{9,15}$/', $phone);
    }
}
