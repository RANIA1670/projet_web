<?php

declare(strict_types=1);

namespace App\Helpers;

final class ValidationHelper
{
    public static function validateFullName(string $value): array
    {
        if ($value === '') {
            return ['valid' => false, 'error' => 'Le nom complet est obligatoire.'];
        }
        
        if (!preg_match('/^[\p{L}\p{M}][\p{L}\p{M}\s\'\-]{1,119}$/u', $value)) {
            return ['valid' => false, 'error' => 'Nom invalide (lettres, espaces, tiret, apostrophe).'];
        }
        
        return ['valid' => true, 'error' => ''];
    }
    
    public static function validateEmail(string $value): array
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL) || mb_strlen($value) > 190) {
            return ['valid' => false, 'error' => 'Email invalide.'];
        }
        
        return ['valid' => true, 'error' => ''];
    }
    
    public static function validatePassword(string $value): array
    {
        if (mb_strlen($value) < 8) {
            return ['valid' => false, 'error' => 'Le mot de passe doit contenir au moins 8 caractères.'];
        }
        
        if (!preg_match('/[A-Za-z]/', $value) || !preg_match('/\d/', $value)) {
            return ['valid' => false, 'error' => 'Le mot de passe doit contenir au moins 1 lettre et 1 chiffre.'];
        }
        
        return ['valid' => true, 'error' => ''];
    }
    
    public static function validateUsername(string $value): array
    {
        if ($value === '') {
            return ['valid' => false, 'error' => 'Le nom d\'utilisateur est obligatoire.'];
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]{3,32}$/', $value)) {
            return ['valid' => false, 'error' => 'Nom d\'utilisateur invalide (3-32, lettres/chiffres/underscore).'];
        }
        
        return ['valid' => true, 'error' => ''];
    }
    
    public static function validateBirthDate(string $value): array
    {
        if ($value === '') {
            return ['valid' => true, 'error' => ''];
        }
        
        $dateParts = explode('-', $value);
        if (count($dateParts) !== 3 || !checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[0])) {
            return ['valid' => false, 'error' => 'Date de naissance invalide.'];
        }
        
        try {
            $date = new \DateTime($value);
            $now = new \DateTime();
            $minDate = new \DateTime('1900-01-01');
            
            if ($date > $now) {
                return ['valid' => false, 'error' => 'La date de naissance ne peut pas être dans le futur.'];
            }
            
            if ($date < $minDate) {
                return ['valid' => false, 'error' => 'La date de naissance est trop ancienne.'];
            }
        } catch (\Exception $e) {
            return ['valid' => false, 'error' => 'Date de naissance invalide.'];
        }
        
        return ['valid' => true, 'error' => ''];
    }
    
    public static function validatePostalCode(string $value): array
    {
        if ($value === '') {
            return ['valid' => true, 'error' => ''];
        }
        
        if (!preg_match('/^[0-9]{5}$/', $value) && !preg_match('/^[A-Z]{1,2}[0-9R][0-9A-Z]? [0-9][A-Z]{2}$/i', $value)) {
            return ['valid' => false, 'error' => 'Code postal invalide (format français ou britannique).'];
        }
        
        return ['valid' => true, 'error' => ''];
    }
    
    public static function validateCity(string $value): array
    {
        if ($value === '') {
            return ['valid' => true, 'error' => ''];
        }
        
        if (!preg_match('/^[\p{L}\p{M}][\p{L}\p{M}\s\'\-]{1,119}$/u', $value)) {
            return ['valid' => false, 'error' => 'Nom de ville invalide.'];
        }
        
        return ['valid' => true, 'error' => ''];
    }
    
    public static function validatePhone(string $value): array
    {
        if ($value === '') {
            return ['valid' => true, 'error' => ''];
        }
        
        $phoneClean = preg_replace('/[\s\-\.\(\)]+/', '', $value);
        if (!preg_match('/^\+?[0-9]{8,15}$/', $phoneClean)) {
            return ['valid' => false, 'error' => 'Numero de telephone invalide.'];
        }
        
        return ['valid' => true, 'error' => ''];
    }
    
    public static function validatePasswordMatch(string $password, string $confirm): array
    {
        if ($confirm === '') {
            return ['valid' => false, 'error' => 'La confirmation du mot de passe est obligatoire.'];
        }
        
        if ($password !== $confirm) {
            return ['valid' => false, 'error' => 'Les mots de passe ne correspondent pas.'];
        }
        
        return ['valid' => true, 'error' => ''];
    }
    
    public static function sanitizeString(string $value): string
    {
        return trim($value);
    }
    
    public static function validateRequired(string $value, string $fieldName): array
    {
        if ($value === '') {
            return ['valid' => false, 'error' => "Le champ {$fieldName} est obligatoire."];
        }
        
        return ['valid' => true, 'error' => ''];
    }
}
