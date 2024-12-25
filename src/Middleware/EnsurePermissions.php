<?php

namespace zennit\ABAC\Middleware;

use Closure;
use Illuminate\Http\Request;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Services\ZennitAbacService;
use zennit\ABAC\Traits\ZennitAbacHasConfigurations;

class EnsurePermissions
{
    use ZennitAbacHasConfigurations;

    public function __construct(
        protected ZennitAbacService $abac
    ) {
    }

    /**
     * Handle an incoming request.
     * Validates permissions for the current user against the requested resource.
     *
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware in the pipeline
     * @return Response The HTTP response
     * @throws ValidationException If context validation fails
     * @throws InvalidArgumentException If cache operations fail
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return $this->unauthorizedResponse('Unauthorized, you need to sign in');
        }

        $context = new AccessContext(
            resource:    $this->getResourceFromPath($request->path()),
	        operation:   strtolower($request->method()),
	        subject:     $request->user(),
	        context: []
        );

        if (!$this->abac->can($context)) {
            return $this->unauthorizedResponse('Unauthorized to access this route');
        }

        return $next($request);
    }

    /**
     * Return a standardized unauthorized response.
     * Creates a JSON response with error message for unauthorized access.
     *
     * @param string $message The error message to return
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
     * @param string $path The request path
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
     * @param string $string The string to check
     * @return bool True if the string is a valid UUID
     */
    private function isUuid(string $string): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            strtolower($string)
        ) === 1;
    }
}
