<?php

namespace zennit\ABAC\Http\Controllers\Core;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Http\Requests\Core\IndexRequest;
use zennit\ABAC\Traits\AbacHasConfigurations;

abstract class Controller
{
    use AbacHasConfigurations;
    use AuthorizesRequests;

    /**
     * Display a listing of the resource, paginated.
     */
    protected function paginate(IndexRequest $request, array $data): array
    {
        $perPage = $request->input('perPage');
        $page = $request->input('page');
        $collection = collect($data);

        $paginator = new LengthAwarePaginator(
            $collection->forPage($page, $perPage),
            $collection->count(),
            $perPage,
            $page,
            ['path' => $request->url()]
        );

        return [
            'items' => $paginator->items(),
            'pagination' => [
                'firstPage' => 1,
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'firstPageUrl' => $paginator->url(1),
                'lastPageUrl' => $paginator->url($paginator->lastPage()),
                'perPage' => $paginator->perPage(),
                'nextPageUrl' => $paginator->nextPageUrl(),
                'prevPageUrl' => $paginator->previousPageUrl(),
                'total' => $paginator->total(),
                'hasMorePages' => $paginator->hasPages(),
            ],
        ];
    }

    /**
     * Format error response
     */
    protected function sendErrorResponse(Throwable $e): JsonResponse
    {
        report($e);

        if ($e instanceof QueryException) {
            return $this->handleDatabaseError($e);
        }

        return $this->handleHttpError($e);
    }

    private function handleDatabaseError(QueryException $e): JsonResponse
    {
        // MySQL error codes
        switch ($e->errorInfo[1]) {
            case 1062:  // Duplicate entry
                $statusCode = 422;
                $message = 'This record already exists.';
                $error = 'DUPLICATE_ENTRY';
                break;

            case 1451:  // Cannot delete or update a parent row (foreign key constraint)
                $statusCode = 409;
                $message = 'This record cannot be deleted because it is referenced by other records.';
                $error = 'FOREIGN_KEY_CONSTRAINT';
                break;

            case 1452:  // Cannot add or update a child row (foreign key constraint)
                $statusCode = 422;
                $message = 'Invalid reference to a related record.';
                $error = 'INVALID_FOREIGN_KEY';
                break;

            case 1264:  // Out of range value
                $statusCode = 422;
                $message = 'Value is out of allowed range.';
                $error = 'OUT_OF_RANGE';
                break;

            case 1146:  // Table doesn't exist
            case 1049:  // Unknown database
                $statusCode = 500;
                $message = 'Database configuration error.';
                $error = 'DATABASE_CONFIG_ERROR';
                break;
            default:
                $statusCode = 500;
                $message = 'Database error occurred.';
                $error = 'DATABASE_ERROR';
                break;
        }

        $response = [
            'error' => $error,
            'message' => $message,
            'code' => $statusCode,
        ];

        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
                'sql_error_code' => $e->errorInfo[1],
            ];
        }

        return response()->json($response, $statusCode);
    }

    private function handleHttpError(Throwable $e): JsonResponse
    {
        $statusCode = match (get_class($e)) {
            BadRequestException::class => 400,
            ModelNotFoundException::class => 404,
            ValidationException::class => 422,
            HttpExceptionInterface::class => $e->getStatusCode(),
            default => 500,
        };

        $response = [
            'error' => 'There was an error, please try again later or contact support if the problem persists.',
            'message' => $e->getMessage(),
            'code' => $statusCode,
        ];

        if ($e instanceof ValidationException) {
            $response['errors'] = $e->errors();
        }

        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
            ];
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Check if the current user has permission to view the resource.
     *
     * This method evaluates the user's permissions against the specified resource and context.
     * It uses the ABAC (Attribute-Based Access Control) policy evaluation to determine if the user
     * has the necessary permissions to access the resource.
     *
     * @param  IndexRequest  $request  The request object containing the user's request data.
     * @param  string  $resource  The fully qualified class name of the resource to check (e.g., User::class).
     * @param  array  $context  Additional context data to pass to the policy evaluation. This array should contain
     *                          data related to the resource being checked (e.g., [1, 2, 3] for resource IDs).
     *
     * @throws InvalidArgumentException If the provided arguments are invalid.
     * @throws \zennit\ABAC\Exceptions\ValidationException If the policy evaluation fails due to validation errors.
     * @return array The matched policies that the user has permission to access.
     */
    protected function evaluateIndex(IndexRequest $request, string $resource, array $context): array
    {
        $context = new AccessContext(
            $resource,
            PermissionOperations::INDEX->value,
            $request->{$this->getSubjectMethod()},
            $context,
        );

        return abacPolicy()->evaluate($context)->matched;
    }

    protected function failPolicy(bool $granted): void
    {
        abort_if(!$granted, 403, 'Unauthorized action.');
    }
}
