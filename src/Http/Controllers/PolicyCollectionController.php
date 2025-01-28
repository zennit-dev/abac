<?php

namespace zennit\ABAC\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use zennit\ABAC\Http\Controllers\Core\Controller;
use zennit\ABAC\Http\Requests\Core\IndexRequest;
use zennit\ABAC\Http\Requests\PolicyCollectionRequest;
use zennit\ABAC\Http\Services\PolicyCollectionService;
use zennit\ABAC\Models\PolicyCollection;

class PolicyCollectionController extends Controller
{
    public function __construct(protected PolicyCollectionService $service)
    {
    }

    public function index(IndexRequest $request, int $policy): JsonResponse
    {
        try {
            $matched = $this->evaluateIndex($request, PolicyCollection::class, $this->service->index($policy));

            return response()->json($this->paginate($request, $matched));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function store(PolicyCollectionRequest $request, $policy): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('create', [PolicyCollection::class]));
            $chain = (bool) $request->query('chain', false);

            return response()->json($this->service->store($request->validated(), $policy, $chain));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function show(Request $request, int $collection): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('read', [PolicyCollection::class, $collection]));

            return response()->json($this->service->show($collection));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function update(PolicyCollectionRequest $request, int $collection): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('update', [PolicyCollection::class, $collection]));

            return response()->json($this->service->update($request->validated(), $collection));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function destroy(Request $request, int $collection): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('delete', [PolicyCollection::class, $collection]));
            $this->service->destroy($collection);

            return response()->json([], 204);
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }
}
