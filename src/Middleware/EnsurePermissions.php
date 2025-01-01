<?php

namespace zennit\ABAC\Middleware;

use Closure;
use Illuminate\Http\Request;
use Log;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Services\ZennitAbacCacheManager;
use zennit\ABAC\Services\ZennitAbacService;
use zennit\ABAC\Traits\ZennitAbacHasConfigurations;

/**
 * Class EnsurePermissions
 *
 * Middleware implementation for ABAC permission checking.
 * Validates user access to resources based on configured policies and subjects.
 */
readonly class EnsurePermissions implements EnsurePermissionsInterface
{
    use ZennitAbacHasConfigurations;

    public function __construct(
        protected ZennitAbacService $abac,
        protected ZennitAbacCacheManager $cacheManager,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Closure  $next  The next middleware in the pipeline
     *
     * @throws InvalidArgumentException If cache operations fail
     * @throws ValidationException If context validation fails
     * @return Response The HTTP response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return $this->unauthorizedResponse('Unauthorized, you need to sign in');
        }

        $currentPath = $request->path();
        $excludedRoutes = $this->getExcludedRoutes();

        Log::debug('🚀 Request Check:', [
            'current_path' => $currentPath,
            'method' => $request->method(),
            'excluded_routes' => $excludedRoutes,
        ]);

        // Check each excluded route
        foreach ($excludedRoutes as $pattern) {
            $isMatch = $this->matchPath($currentPath, $pattern);
            Log::debug('🔍 Route Check:', [
                'pattern' => $pattern,
                'path' => $currentPath,
                'is_match' => $isMatch,
            ]);

            if ($isMatch) {
                Log::debug('✅ Route excluded, allowing access');

                return $next($request);
            }
        }

        Log::debug('❌ Route not excluded, checking permissions');

        try {
            $cacheKey = $this->buildCacheKey($request);
            $hasAccess = $this->cacheManager->remember(
                $cacheKey,
                fn () => $this->checkAccess($request)
            );

            if (!$hasAccess) {
                return $this->unauthorizedResponse('Unauthorized to access this route');
            }

            return $next($request);
        } catch (InvalidArgumentException $e) {
            report($e);
            if (!$this->checkAccess($request)) {
                return $this->unauthorizedResponse('Unauthorized to access this route');
            }

            return $next($request);
        }
    }

    /**
     * Build a unique cache key for the request
     */
    private function buildCacheKey(Request $request): string
    {
        $user = $request->user();
        $path = $request->path();
        $method = $request->method();

        return "permission_check:$user->id:$path:$method";
    }

    /**
     * Check if the request has access
     *
     * @throws InvalidArgumentException
     * @throws ValidationException
     */
    private function checkAccess(Request $request): bool
    {
        // First check excluded routes
        if ($this->isExcludedRoute($request)) {
            return true;
        }

        // If not excluded, check ABAC permissions
        $context = new AccessContext(
            resource: $this->getResourceFromPath($request->path()),
            operation: strtolower($request->method()),
            subject: $this->defineSubject($request),
            context: []
        );

        return $this->abac->can($context);
    }

    /**
     * Define the subject for permission checking.
     * Retrieves the subject from the request using the method configured in zennit_abac.middleware.subject_method.
     *
     * @param  Request  $request  The incoming HTTP request
     *
     * @throws RuntimeException When the configured subject method doesn't exist
     * @return object|null The subject for permission checking
     */
    public function defineSubject(Request $request): ?object
    {
        $method = $this->getSubjectMethod();

        if (!method_exists($request, $method)) {
            throw new RuntimeException("Subject method '$method' does not exist on Request object");
        }

        return $request->$method();
    }

    /**
     * Return a standardized unauthorized response.
     * Creates a JSON response with error message for unauthorized access.
     *
     * @param  string  $message  The error message to return
     *
     * @return Response The HTTP response with 401 status
     */
    private function unauthorizedResponse(string $message): Response
    {
        return response()->json(
            ['error' => $message],
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Extract the resource name from the request path.
     * Handles API versioning and removes IDs from the path.
     *
     * @param  string  $path  The request path
     *
     * @return string The extracted resource name
     */
    private function getResourceFromPath(string $path): string
    {
        // Remove API version prefix if exists
        $segments = explode('/', trim($path, '/'));

        // Skip api/v1 or similar prefixes
        if (isset($segments[0]) && $segments[0] === 'api') {
            array_shift($segments);
        }
        if (!empty($segments) && str_starts_with($segments[0], 'v')) {
            array_shift($segments);
        }

        // Get the last non-empty segment that's not an ID
        $segments = array_filter(
            $segments,
            fn ($segment) => !empty($segment) && !is_numeric($segment) && !$this->isUuid($segment)
        );

        return !empty($segments) ? end($segments) : '';
    }

    /**
     * Check if a string matches UUID format.
     * Used to identify and remove UUIDs from resource paths.
     *
     * @param  string  $string  The string to check
     *
     * @return bool True if the string is a valid UUID
     */
    private function isUuid(string $string): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            strtolower($string)
        ) === 1;
    }

    /**
     * Check if the current route is in the excluded routes list
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
            if (is_array($route) && isset($route['path'])) {
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
}
