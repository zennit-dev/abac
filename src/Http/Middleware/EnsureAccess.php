<?php

namespace zennit\ABAC\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use zennit\ABAC\Contracts\ActorResolver;
use zennit\ABAC\Contracts\ContextEnricher;
use zennit\ABAC\Contracts\ResourceResolver;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\PolicyMethod;
use zennit\ABAC\Services\AbacAttributeLoader;
use zennit\ABAC\Services\AbacCacheManager;
use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Traits\AccessesAbacConfiguration;

/**
 * Class EnsureAccess
 *
 * Middleware implementation for ABAC permission checking.
 * Validates actor access to resources based on configured policies.
 */
readonly class EnsureAccess
{
    use AccessesAbacConfiguration;

    public function __construct(
        protected AbacService $abac,
        protected AbacCacheManager $cacheManager,
        protected AbacAttributeLoader $attributeLoader,
        protected ContextEnricher $contextEnricher,
        protected ResourceResolver $resourceResolver,
        protected ActorResolver $actorResolver,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Closure  $next  The next middleware in the pipeline
     * @return Response The HTTP response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (! $request->user()) {
                return $this->unauthorizedResponse();
            }

            if (! $this->checkAccess($request)) {
                return $this->unauthorizedResponse();
            }

            return $next($request);
        } catch (Throwable $e) {
            report($e);

            return response()->json(
                ['error' => 'ABAC evaluation failed.'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Return a standardized unauthorized response.
     * Creates a JSON response with error message for unauthorized access.
     *
     * @return Response The HTTP response with 401 status
     */
    private function unauthorizedResponse(): Response
    {
        return response()->json(
            ['error' => 'Unauthorized to access this route.'],
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * AbacCheck if the request has access
     *
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    private function checkAccess(Request $request): bool
    {
        // First check excluded routes
        if ($this->isExcludedRoute($request)) {
            return true;
        }

        // If not excluded, check ABAC permissions
        $method = $this->matchRequestOperation($request);

        if (! $method) {
            return true;
        }

        $resource = $this->defineResource($request);

        if (is_null($resource)) {
            return $this->shouldAllowIfUnmatchedRoute();
        }

        $actor = $this->defineActor($request);

        $context = new AccessContext(
            method: $method,
            resource: $resource,
            actor: $this->attributeLoader->loadAllActorAttributes($actor),
            environment: $request->toArray()
        );

        $context = $this->contextEnricher->enrich($context, $request);

        $context = $this->abac->evaluate($context);

        $request->attributes->set('abac', $context);

        return $context->can;
    }

    /**
     * AbacCheck if the current route is in the excluded routes list
     */
    private function isExcludedRoute(Request $request): bool
    {
        $currentPath = $request->path();
        $currentMethod = strtoupper($request->method());
        $excludedRoutes = $this->getExcludedRoutes();

        foreach ($excludedRoutes as $route) {
            // If route is string, exclude all methods
            if (is_string($route) && $this->matchPath($currentPath, $route)) {
                return true;
            }

            // If route is array with method and path
            if (! is_array($route) || ! isset($route['path'])) {
                continue;
            }

            if (! $this->matchPath($currentPath, $route['path'])) {
                continue;
            }

            // If method is not specified or is '*', exclude all methods
            if (! isset($route['method']) || $route['method'] === '*') {
                return true;
            }

            // Handle both string and array method definitions
            $methods = (array) $route['method'];
            if (in_array($currentMethod, array_map('strtoupper', $methods))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Match path against pattern, supporting wildcards
     */
    private function matchPath(string $path, string $pattern): bool
    {
        $path = trim($path, '/');
        $pattern = trim($pattern, '/');

        // Direct match check
        if ($path === $pattern) {
            return true;
        }

        // Wildcard check
        if (str_ends_with($pattern, '*')) {
            $basePattern = rtrim($pattern, '*');

            return str_starts_with($path, $basePattern);
        }

        return false;
    }

    /**
     * Match the request methods against PermissionOperations
     */
    private function matchRequestOperation(Request $request): ?PolicyMethod
    {
        return match (strtoupper($request->method())) {
            'GET', 'HEAD' => PolicyMethod::READ,
            'POST' => PolicyMethod::CREATE,
            'PUT', 'PATCH' => PolicyMethod::UPDATE,
            'DELETE' => PolicyMethod::DELETE,
            default => null,
        };
    }

    /**
     * Define the resource for permission checking.
     * Retrieves the resource from the request using the configured middleware resource patterns.
     *
     * @param  Request  $request  The incoming HTTP request
     * @return Builder<Model>|null The resource for permission checking
     */
    private function defineResource(Request $request): ?Builder
    {
        return $this->resourceResolver->resolve($request, $this->getResourcePatterns());
    }

    /**
     * Define the actor for permission checking.
     * Retrieves the actor from the request using the configured middleware actor method.
     *
     * @param  Request  $request  The incoming HTTP request
     */
    private function defineActor(Request $request): Model
    {
        return $this->actorResolver->resolve($request, $this->getActorMethod());
    }
}
