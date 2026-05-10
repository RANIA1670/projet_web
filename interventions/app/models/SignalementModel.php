<?php
/**
 * CityZen - SignalementModel
 * Propriétés uniquement. Logique métier → SignalementService.
 */

require_once APP_PATH . 'core/Model.php';

class SignalementModel extends Model
{
    protected string $table = 'interv_signalements';
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
