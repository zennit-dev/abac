<?php

namespace zennit\ABAC\Services\Resolution;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use zennit\ABAC\Contracts\ResourceResolver;
use zennit\ABAC\Traits\AccessesAbacConfiguration;

class DefaultResourceResolver implements ResourceResolver
{
    use AccessesAbacConfiguration;

    /**
     * @param  array<string, string>  $patterns
     * @return Builder<Model>|null
     */
    public function resolve(Request $request, array $patterns): ?Builder
    {
        $path = trim($request->path(), '/');

        $boundResource = $this->resolveResourceFromRouteBinding($request, $path, $patterns);
        if (! is_null($boundResource)) {
            return $boundResource;
        }

        return $this->findMatchingResource($path, $patterns);
    }

    /**
     * @param  array<string, string>  $patterns
     * @return Builder<Model>|null
     */
    private function resolveResourceFromRouteBinding(Request $request, string $path, array $patterns): ?Builder
    {
        $expectedModelClass = $this->resolveModelClassFromPath($path, $patterns);
        $route = $request->route();

        if (is_null($route)) {
            return null;
        }

        foreach ($route->parameters() as $parameter) {
            if (! $parameter instanceof Model) {
                continue;
            }

            if (! is_null($expectedModelClass) && ! $parameter instanceof $expectedModelClass) {
                continue;
            }

            $modelKey = $parameter->getKey();
            $query = $parameter->newQuery();

            if (is_null($modelKey)) {
                return $query;
            }

            return $query->where($parameter->getQualifiedKeyName(), $modelKey);
        }

        return null;
    }

    /**
     * @param  array<string, string>  $patterns
     * @return Builder<Model>|null
     */
    private function findMatchingResource(string $path, array $patterns): ?Builder
    {
        foreach ($patterns as $pattern => $modelClassString) {
            if (preg_match("#^$pattern$#", $path, $matches) === 1) {
                /** @var Model $model */
                $model = new $modelClassString;
                $resource = $model->newQuery();
                $id = $this->extractResourceId($matches);

                if (is_null($id)) {
                    return $resource;
                }

                return $this->applyPrimaryKeyFilter($resource, $model, $id);
            }
        }

        return null;
    }

    /**
     * @param  array<string, string>  $patterns
     */
    private function resolveModelClassFromPath(string $path, array $patterns): ?string
    {
        foreach ($patterns as $pattern => $modelClassString) {
            if (preg_match("#^$pattern$#", $path) === 1) {
                return $modelClassString;
            }
        }

        return null;
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    private function applyPrimaryKeyFilter(Builder $query, Model $model, string $id): Builder
    {
        $keys = array_values(array_filter(
            $this->getPrimaryKeyCandidates($model),
            fn (string $key): bool => Schema::hasColumn($model->getTable(), $key)
        ));

        if ($keys === []) {
            $keys = [$model->getKeyName()];
        }

        return $query->where(function (Builder $scopedQuery) use ($model, $keys, $id) {
            foreach ($keys as $index => $key) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $scopedQuery->{$method}($model->qualifyColumn($key), $id);
            }
        });
    }

    /**
     * @param  array<int, string>  $matches
     */
    private function extractResourceId(array $matches): ?string
    {
        if (count($matches) < 2) {
            return null;
        }

        $id = trim((string) end($matches), '/');

        return $id === '' ? null : $id;
    }
}
