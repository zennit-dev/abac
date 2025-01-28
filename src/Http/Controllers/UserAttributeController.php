<?php

namespace zennit\ABAC\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use zennit\ABAC\Http\Controllers\Core\Controller;
use zennit\ABAC\Http\Requests\Core\IndexRequest;
use zennit\ABAC\Http\Requests\UserAttributeRequest;
use zennit\ABAC\Http\Services\UserAttributeService;
use zennit\ABAC\Models\UserAttribute;

class UserAttributeController extends Controller
{
    public function __construct(protected UserAttributeService $service)
    {
    }

    public function index(IndexRequest $request): JsonResponse
    {
        try {
            $matched = $this->evaluateIndex($request, UserAttribute::class, $this->service->index());

            return response()->json($this->paginate($request, $matched));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function store(UserAttributeRequest $request): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('create', [UserAttribute::class]));

            return response()->json($this->service->store($request->validated()));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function show(Request $request, int $user_attribute): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('read', [UserAttribute::class, $user_attribute]));

            return response()->json($this->service->show($user_attribute));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function update(UserAttributeRequest $request, int $user_attribute): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('update', [UserAttribute::class, $user_attribute]));

            return response()->json($this->service->update($request->validated(), $user_attribute));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function destroy(Request $request, int $user_attribute): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('delete', [UserAttribute::class, $user_attribute]));
            $this->service->destroy($user_attribute);

            return response()->json([], 204);
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }
}
