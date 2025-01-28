<?php

namespace zennit\ABAC\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use zennit\ABAC\Http\Controllers\Core\Controller;
use zennit\ABAC\Http\Requests\Core\IndexRequest;
use zennit\ABAC\Http\Requests\PermissionRequest;
use zennit\ABAC\Http\Services\PermissionService;
use zennit\ABAC\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(protected PermissionService $service)
    {
    }

    public function index(IndexRequest $request): JsonResponse
    {
        try {
            $matched = $this->evaluateIndex($request, Permission::class, $this->service->index());

            return response()->json($this->paginate($request, $matched));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function store(PermissionRequest $request): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('create', [Permission::class]));
            $chain = (bool) $request->query('chain', false);

            return response()->json($this->service->store($request->validated(), $chain));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function show(Request $request, int $permission): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('read', [Permission::class, $permission]));

            return response()->json($this->service->show($permission));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function update(PermissionRequest $request, int $permission): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('update', [Permission::class, $permission]));

            return response()->json($this->service->update($request->validated(), $permission));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function destroy(Request $request, int $permission): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('delete', [Permission::class, $permission]));
            $this->service->destroy($permission);

            return response()->json([], 204);
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }
}
