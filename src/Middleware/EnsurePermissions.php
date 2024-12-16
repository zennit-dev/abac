<?php

namespace zennit\ABAC\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use zennit\ABAC\Contracts\AbacServiceInterface;
use zennit\ABAC\DTO\AccessContext;

readonly class EnsurePermissions
{
    public function __construct(
        private AbacServiceInterface $abac
    ) {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthorized, you need to sign in'], 401);
        }

        $context = new AccessContext(
            subject: $request->user(),
            resource: $this->getResourceFromPath($request->path()),
            operation: $request->method(),
            resourceIds: []
        );

        if (!$this->abac->evaluate($context)->granted) {
            return response()->json(['error' => 'Unauthorized to perform this action.'], 401);
        }

        return $next($request);
    }

    /**
     * Extract the resource name from the path.
     * Examples:
     * - /api/v1/posts -> posts
     * - /api/v1/organizations/1/projects/2/task-lists/3/tasks -> tasks
     * - /api/v1/organizations/1/projects -> projects
     */
    private function getResourceFromPath(string $path): string
    {
        // Remove API version prefix if exists
        $segments = explode('/', trim($path, '/'));

        // Skip api/v1 or similar prefixes
        if (in_array($segments[0], ['api', 'v1', 'v2'])) {
            array_shift($segments); // Remove 'api'
            if (!empty($segments) && in_array($segments[0], ['v1', 'v2'])) {
                array_shift($segments); // Remove version
            }
        }

        // For paths like 'organizations/1/projects/2/task-lists/3/tasks'
        // We want to get the last resource name (tasks)
        $lastResource = '';
        foreach ($segments as $i => $segment) {
            // Skip ID segments (usually numeric or UUID)
            if ($this->looksLikeId($segment)) {
                continue;
            }
            $lastResource = $segment;
        }

        return $lastResource;
    }

    /**
     * Check if a segment looks like an ID
     */
    private function looksLikeId(string $segment): bool
    {
        // Check for numeric IDs
        if (is_numeric($segment)) {
            return true;
        }

        // Check for UUID format
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $segment)) {
            return true;
        }

        return false;
    }
}
