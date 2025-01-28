<?php

namespace zennit\ABAC\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use zennit\ABAC\Http\Controllers\Core\Controller;
use zennit\ABAC\Http\Requests\Core\IndexRequest;
use zennit\ABAC\Http\Requests\ResourceAttributeRequest;
use zennit\ABAC\Http\Services\ResourceAttributeService;
use zennit\ABAC\Models\ResourceAttribute;

class ResourceAttributeController extends Controller
{
    public function __construct(protected ResourceAttributeService $service)
    {
    }

    public function index(IndexRequest $request): JsonResponse
    {
        try {
            $matched = $this->evaluateIndex($request, ResourceAttribute::class, $this->service->index());

            return response()->json($this->paginate($request, $matched));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function store(ResourceAttributeRequest $request): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('create', [ResourceAttribute::class]));

            return response()->json($this->service->store($request->validated()));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function show(Request $request, int $user_attribute): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('read', [ResourceAttribute::class, $user_attribute]));

            return response()->json($this->service->show($user_attribute));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function update(ResourceAttributeRequest $request, int $user_attribute): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('update', [ResourceAttribute::class, $user_attribute]));

            return response()->json($this->service->update($request->validated(), $user_attribute));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function destroy(Request $request, int $user_attribute): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('delete', [ResourceAttribute::class, $user_attribute]));
            $this->service->destroy($user_attribute);

            return response()->json([], 204);
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }
}
