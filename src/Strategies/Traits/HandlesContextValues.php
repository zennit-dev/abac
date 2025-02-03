<?php

namespace zennit\ABAC\Strategies\Traits;

use zennit\ABAC\DTO\AccessContext;

trait HandlesContextValues
{
    protected function resolveIfContextValue(mixed $value, AccessContext $context): mixed
    {
        if (is_string($value) && str_starts_with($value, '$')) {
            return $this->resolveContextValue($value, $context);
        }

        if (is_array($value)) {
            return array_map(fn ($v) => $this->resolveIfContextValue($v, $context), $value);
        }

        return $value;
    }

    protected function resolveContextValue(string $value, AccessContext $context): mixed
    {
        $parts = explode('.', substr($value, 1));

        return match ($parts[0]) {
            'subject' => $this->resolveSubjectValue($parts, $context),
            'resource' => $this->resolveResourceValue($parts, $context),
            'operation' => $context->operation,
            'context' => $this->resolveCustomContextValue($parts, $context),
            default => $value
        };
    }

    private function resolveSubjectValue(array $parts, AccessContext $context): mixed
    {
        if (count($parts) < 2) {
            return null;
        }

        array_shift($parts);
        $path = implode('.', $parts);

        return $this->getNestedValue($context->subject, $path);
    }

    private function resolveResourceValue(array $parts, AccessContext $context): mixed
    {
        if (count($parts) === 1) {
            return $context->resource;
        }

        array_shift($parts);
        $path = implode('.', $parts);

        return $this->getNestedValue($context->context['resource'] ?? [], $path);
    }

    private function resolveCustomContextValue(array $parts, AccessContext $context): mixed
    {
        if (!isset($parts[1])) {
            return null;
        }

        return $context->context[$parts[1]] ?? null;
    }

    /**
     * Get a nested value from an object or array using dot notation
     */
    private function getNestedValue(mixed $data, string $path): mixed
    {
        $keys = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            if (is_object($current)) {
                if (!property_exists($current, $key)) {
                    return null;
                }
                $current = $current->$key;
            } elseif (is_array($current)) {
                if (!array_key_exists($key, $current)) {
                    return null;
                }
                $current = $current[$key];
            } else {
                return null;
            }
        }

        return $current;
    }
}
