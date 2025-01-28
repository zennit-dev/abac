<?php

namespace zennit\ABAC\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use zennit\ABAC\Http\Controllers\Core\Controller;
use zennit\ABAC\Http\Requests\CollectionConditionRequest;
use zennit\ABAC\Http\Requests\Core\IndexRequest;
use zennit\ABAC\Http\Services\CollectionConditionService;
use zennit\ABAC\Models\CollectionCondition;

class CollectionConditionController extends Controller
{
    public function __construct(protected CollectionConditionService $service)
    {
    }

    public function index(IndexRequest $request, int $policy): JsonResponse
    {
        try {
            $matched = $this->evaluateIndex($request, CollectionCondition::class, $this->service->index($policy));

            return response()->json($this->paginate($request, $matched));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function store(CollectionConditionRequest $request, $policy): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('create', [CollectionCondition::class]));
            $chain = (bool) $request->query('chain', false);

            return response()->json($this->service->store($request->validated(), $policy, $chain));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function show(Request $request, int $collection): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('read', [CollectionCondition::class, $collection]));

            return response()->json($this->service->show($collection));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function update(CollectionConditionRequest $request, int $collection): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('update', [CollectionCondition::class, $collection]));

            return response()->json($this->service->update($request->validated(), $collection));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function destroy(Request $request, int $collection): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('delete', [CollectionCondition::class, $collection]));
            $this->service->destroy($collection);

            return response()->json([], 204);
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }
}
