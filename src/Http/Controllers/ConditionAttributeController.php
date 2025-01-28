<?php

namespace zennit\ABAC\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use zennit\ABAC\Http\Controllers\Core\Controller;
use zennit\ABAC\Http\Requests\ConditionAttributeRequest;
use zennit\ABAC\Http\Requests\Core\IndexRequest;
use zennit\ABAC\Http\Services\ConditionAttributeService;
use zennit\ABAC\Models\ConditionAttribute;

class ConditionAttributeController extends Controller
{
    public function __construct(protected ConditionAttributeService $service)
    {
    }

    public function index(IndexRequest $request, int $condition): JsonResponse
    {
        try {
            $matched = $this->evaluateIndex($request, ConditionAttribute::class, $this->service->index($condition));

            return response()->json($this->paginate($request, $matched));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function store(ConditionAttributeRequest $request, $condition): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('create', [ConditionAttribute::class]));

            return response()->json($this->service->store($request->validated(), $condition));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function show(Request $request, int $attribute): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('read', [ConditionAttribute::class, $attribute]));

            return response()->json($this->service->show($attribute));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function update(ConditionAttributeRequest $request, int $attribute): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('update', [ConditionAttribute::class, $attribute]));

            return response()->json($this->service->update($request->validated(), $attribute));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function destroy(Request $request, int $attribute): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('delete', [ConditionAttribute::class, $attribute]));
            $this->service->destroy($attribute);

            return response()->json([], 204);
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }
}
