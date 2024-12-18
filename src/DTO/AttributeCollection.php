<?php

namespace zennit\ABAC\DTO;

class AttributeCollection
{
    private array $attributes = [];

    public function __construct(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (is_array($value) && isset($value['attribute_name'])) {
                $this->attributes[$value['attribute_name']] = $value['attribute_value'];
            } else {
                $this->attributes[$key] = $value;
            }
        }
    }

    public function get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function all(): array
    {
        return $this->attributes;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function hash(): string
    {
        return md5(serialize($this->attributes));
    }
}
