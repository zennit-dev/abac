<?php

namespace zennit\ABAC\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\EmptyObject;
use zennit\ABAC\Enums\PolicyMethod;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Services\AbacAttributeLoader;
use zennit\ABAC\Services\AbacCacheManager;
use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Traits\AbacHasConfigurations;

/**
 * Class EnsurePermissions
 *
 * Middleware implementation for ABAC permission checking.
 * Validates user access to resources based on configured policies and subjects.
 */
readonly class EnsureAccess
{
    use AbacHasConfigurations;

    public function __construct(
        protected AbacService $abac,
        protected AbacCacheManager $cacheManager,
        protected AbacAttributeLoader $attributeLoader
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware in the pipeline
     *
     * @return Response The HTTP response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (!$request->user()) {
                return $this->unauthorizedResponse();
            }

            if (!$this->checkAccess($request)) {
                return $this->unauthorizedResponse();
            }

            return $next($request);
        } catch (Throwable $e) {
            report($e);

            return $this->unauthorizedResponse();
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
     * @throws InvalidArgumentException
     * @throws ValidationException
     * @throws UnsupportedOperatorException
     */
    private function checkAccess(Request $request): bool
    {
        // First check excluded routes
        if ($this->isExcludedRoute($request)) {
            return true;
        }

        // If not excluded, check ABAC permissions
        $method = $this->matchRequestOperation($request);

        $context = new AccessContext(
            method:       $method,
            subject:      $this->attributeLoader->loadAllSubjectAttributes($this->defineSubject($request)),
            object:       $this->attributeLoader->loadAllObjectAttributes($this->defineObject($request)),
            object_type:  get_class($this->defineObject($request)),
            subject_type: get_class($this->defineSubject($request)),
        );

        return $this->abac->can($context);
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
            if (!is_array($route) && !isset($route['path'])) {
                return false;
            }

            if (!$this->matchPath($currentPath, $route['path'])) {
                continue;
            }

            // If method is not specified or is '*', exclude all methods
            if (!isset($route['method']) || $route['method'] === '*') {
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
    private function matchRequestOperation(Request $request): ?string
    {
        return match (strtoupper($request->method())) {
            'GET', 'HEAD' => PolicyMethod::READ->value,
            'POST' => PolicyMethod::CREATE->value,
            'PUT', 'PATCH' => PolicyMethod::UPDATE->value,
            'DELETE' => PolicyMethod::DELETE->value,
            default => null,
        };
    }

    /**
     * Define the subject for permission checking.
     * Retrieves the subject from the request using the method configured in abac.middleware.path_resources
     *
     * @param Request $request The incoming HTTP request
     *
     * @return object The subject for permission checking
     */
    private function defineSubject(Request $request): object
    {
        $path = trim($request->path(), '/');
        $patterns = $this->getPathPatterns();

        return $this->findMatchingSubject($path, $patterns);
    }

    /**
     * Find the matching model class for a given path and handle different ID types
     */
    private function findMatchingSubject(string $path, array $patterns): object
    {
        foreach ($patterns as $pattern => $modelClass) {
            $escapedPattern = preg_quote($pattern, '#');
            if (preg_match("#$escapedPattern#", $path)) {
                $pathParts = explode('/', $path);
                $id = end($pathParts);

                if (empty($id)) {
                    continue;
                }

                if ($this->isValidUuid($id)) {
                    return $modelClass::where('id', $id)->first();
                }

                if (is_numeric($id)) {
                    return $modelClass::find($id);
                }

                if (method_exists($modelClass, 'findBySlug')) {
                    return $modelClass::findBySlug($id);
                }
            }
        }

        return new EmptyObject();
    }

    /**
     * Check if a string is a valid UUID
     */
    private function isValidUuid(string $uuid): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid) === 1;
    }

    /**
     * Define the object for permission checking.
     * Retrieves the object from the request using the method configured in abac.middleware.object_method.
     *
     * @param Request $request The incoming HTTP request
     *
     * @throws RuntimeException When the configured object method doesn't exist
     * @return object The object for permission checking
     */
    private function defineObject(Request $request): object
    {
        $method = $this->getObjectMethod();

        if (!is_callable([$request, $method])) {
            throw new RuntimeException("Object method '$method' is not callable on request");
        }

        $object = $request->$method();

        if (is_null($object)) {
            throw new RuntimeException("Object method '$method' returned null");
        }

        return $object;
    }
}
