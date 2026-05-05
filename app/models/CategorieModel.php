<?php
/**
 * CityZen - CategorieModel
 * Propriétés uniquement. Logique métier → CategorieService.
 */

require_once APP_PATH . 'core/Model.php';

class CategorieModel extends Model
{
    protected string $table = 'categories';
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
