<?php
/**
 * CityZen - Contact Model
 */

require_once APP_PATH . 'core/Model.php';

class ContactModel extends Model
{
    protected string $table = 'interv_contacts';
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
