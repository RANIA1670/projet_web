<?php
// ================================================
//  FICHIER  : controllers/Validator.php
//  RÔLE     : Classe utilitaire de validation PHP
//             Toute validation se fait ICI (pas HTML5)
// ================================================

class Validator
{
    private array $erreurs = [];

    // ---- Nettoyer une donnée (trim + enlever balises HTML) ----
    public static function nettoyer(string $val): string
    {
        return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
    }

    // ---- Vérifier qu'un champ n'est pas vide ----
    public function requis(string $valeur, string $champ): self
    {
        if (trim($valeur) === '') {
            $this->erreurs[] = "Le champ « $champ » est obligatoire.";
        }
        return $this; // Permet le chaînage : $v->requis()->email()
    }

    // ---- Vérifier la longueur minimale ----
    public function minLen(string $valeur, int $min, string $champ): self
    {
        if (strlen(trim($valeur)) > 0 && strlen(trim($valeur)) < $min) {
            $this->erreurs[] = "Le champ « $champ » doit contenir au moins $min caractères.";
        }
        return $this;
    }

    // ---- Vérifier qu'un email est valide (filter_var — PAS HTML5) ----
    public function email(string $valeur, string $champ = 'Email'): self
    {
        if (trim($valeur) !== '' && !filter_var($valeur, FILTER_VALIDATE_EMAIL)) {
            $this->erreurs[] = "Le champ « $champ » doit être un email valide (ex : nom@domaine.com).";
        }
        return $this;
    }

    // ---- Vérifier qu'une date est valide ----
    public function date(string $valeur, string $champ = 'Date'): self
    {
        if (trim($valeur) !== '') {
            $d = DateTime::createFromFormat('Y-m-d', $valeur);
            if (!$d || $d->format('Y-m-d') !== $valeur) {
                $this->erreurs[] = "Le champ « $champ » doit être une date valide.";
            }
        }
        return $this;
    }

    // ---- Vérifier qu'une valeur est un entier positif ----
    public function entierPositif(mixed $valeur, string $champ): self
    {
        if (!filter_var($valeur, FILTER_VALIDATE_INT) || (int)$valeur <= 0) {
            $this->erreurs[] = "Le champ « $champ » est invalide.";
        }
        return $this;
    }

    // ---- Vérifier le format téléphone (ex: +216 71 000 000) ----
    public function telephone(string $valeur, string $champ = 'Téléphone'): self
    {
        if (trim($valeur) !== '' && !preg_match('/^[+\d\s\-().]{7,20}$/', $valeur)) {
            $this->erreurs[] = "Le champ « $champ » doit être un numéro de téléphone valide.";
        }
        return $this;
    }

    // ---- Y a-t-il des erreurs ? ----
    public function aDesErreurs(): bool
    {
        return !empty($this->erreurs);
    }

    // ---- Récupérer la liste des erreurs ----
    public function getErreurs(): array
    {
        return $this->erreurs;
    }
}
