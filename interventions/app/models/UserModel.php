<?php
/**
 * CityZen - UserModel
 * Propriétés uniquement. Logique métier → UserService.
 */

require_once APP_PATH . 'core/Model.php';

class UserModel extends Model
{
    protected string $table = 'users';
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
