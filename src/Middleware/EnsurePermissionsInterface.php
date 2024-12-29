<?php

namespace zennit\ABAC\Middleware;

use Closure;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface EnsurePermissionsInterface
 * 
 * Defines the contract for ABAC permission middleware implementations.
 * Handles authorization checks for incoming requests based on configured subjects.
 */
interface EnsurePermissionsInterface
{
	/**
	 * Handle an incoming request.
	 * Validates permissions for the current user against the requested resource.
	 *
	 * @param  Request  $request  The incoming HTTP request
	 * @param  Closure  $next     The next middleware in the pipeline
	 * @return Response          The HTTP response
	 */
	public function handle(Request $request, Closure $next): Response;

	/**
	 * Define the subject for permission checking.
	 * Retrieves the subject (usually a user or profile) from the request using configured method.
	 *
	 * @param  Request     $request       The incoming HTTP request
	 * @return object|null                The subject for permission checking, or null if not found
	 * @throws RuntimeException          When the configured subject method doesn't exist
	 */
	public function defineSubject(Request $request): ?object;
}