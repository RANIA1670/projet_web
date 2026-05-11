<?php
/**
 * FormValidator — Validateur centralisé côté serveur
 *
 * Usage:
 *   $v = new FormValidator();
 *   $v->required('title', $title)
 *     ->minLength('title', $title, 5)
 *     ->maxLength('title', $title, 150)
 *     ->noScript('title', $title);
 *
 *   if ($v->fails()) {
 *       $errors = $v->getErrors(); // ['title' => 'message']
 *   }
 */
class FormValidator
{
    /** @var array<string,string>  champ => premier message d'erreur */
    private array $errors = [];

    // ─── Règles ──────────────────────────────────────────────────────────────

    /**
     * Champ obligatoire (non vide après trim)
     */
    public function required(string $field, string $value, string $label = ''): static
    {
        if (trim($value) === '') {
            $this->addError($field, ($label ?: $field) . ' est obligatoire.');
        }
        return $this;
    }

    /**
     * Longueur minimale
     */
    public function minLength(string $field, string $value, int $min, string $label = ''): static
    {
        if (!$this->hasError($field) && mb_strlen(trim($value)) < $min) {
            $this->addError($field, ($label ?: $field) . " doit contenir au moins {$min} caractères.");
        }
        return $this;
    }

    /**
     * Longueur maximale
     */
    public function maxLength(string $field, string $value, int $max, string $label = ''): static
    {
        if (!$this->hasError($field) && mb_strlen(trim($value)) > $max) {
            $this->addError($field, ($label ?: $field) . " ne peut pas dépasser {$max} caractères.");
        }
        return $this;
    }

    /**
     * Interdit les balises HTML / scripts (XSS basique)
     */
    public function noScript(string $field, string $value, string $label = ''): static
    {
        if (!$this->hasError($field) && $value !== strip_tags($value)) {
            $this->addError($field, ($label ?: $field) . ' ne doit pas contenir de balises HTML.');
        }
        return $this;
    }

    /**
     * Pas de mots interdits (spam / vulgarités basiques)
     */
    public function noBannedWords(string $field, string $value, string $label = ''): static
    {
        $banned = ['spam', 'promo', 'solde', 'acheter', 'http://', 'https://', 'www.'];
        $lower  = mb_strtolower($value);
        foreach ($banned as $word) {
            if (!$this->hasError($field) && str_contains($lower, $word)) {
                $this->addError($field, ($label ?: $field) . ' contient un mot non autorisé : "' . $word . '".');
                break;
            }
        }
        return $this;
    }

    /**
     * Pas de répétition excessive d'un même caractère (ex. "aaaaaa")
     */
    public function noExcessiveRepeat(string $field, string $value, int $limit = 6, string $label = ''): static
    {
        if (!$this->hasError($field) && preg_match('/(.)\1{' . ($limit - 1) . ',}/u', $value)) {
            $this->addError($field, ($label ?: $field) . ' contient une répétition excessive de caractères.');
        }
        return $this;
    }

    /**
     * Pas que des espaces / chiffres / ponctuation (contenu non significatif)
     */
    public function hasMeaningfulContent(string $field, string $value, string $label = ''): static
    {
        if (!$this->hasError($field) && !preg_match('/\p{L}/u', $value)) {
            $this->addError($field, ($label ?: $field) . ' doit contenir au moins quelques lettres.');
        }
        return $this;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function addError(string $field, string $message): void
    {
        // Un seul message par champ
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /** @return array<string,string> */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Premier message d'erreur global (pour affichage simple)
     */
    public function firstError(): string
    {
        return reset($this->errors) ?: '';
    }

    /**
     * Erreur d'un champ spécifique
     */
    public function errorFor(string $field): string
    {
        return $this->errors[$field] ?? '';
    }
}
