<?php
/**
 * CityZen - DemandeInterventionModel
 * Propriétés uniquement. Logique métier → InterventionService ou contrôleur.
 */

require_once APP_PATH . 'core/Model.php';

class DemandeInterventionModel extends Model
{
    protected string $table = 'demandes_intervention';
    protected array $attributes = [];

    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function fill(array $data): void
    {
        $this->attributes = $data;
    }
}
