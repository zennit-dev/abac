<?php

namespace zennit\ABAC\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use zennit\ABAC\Http\Controllers\Core\Controller;
use zennit\ABAC\Http\Requests\Core\IndexRequest;
use zennit\ABAC\Http\Requests\PolicyRequest;
use zennit\ABAC\Http\Services\PolicyService;
use zennit\ABAC\Models\Policy;

class PolicyController extends Controller
{
    public function __construct(protected PolicyService $service)
    {
    }

    public function index(IndexRequest $request, int $permission): JsonResponse
    {
        try {
            $matched = $this->evaluateIndex($request, Policy::class, $this->service->index($permission));

            return response()->json($this->paginate($request, $matched));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function store(PolicyRequest $request, $permission): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('create', [Policy::class]));
            $chain = (bool) $request->query('chain', false);

            return response()->json($this->service->store($request->validated(), $permission, $chain));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function show(Request $request, int $policy): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('read', [Policy::class, $policy]));

            return response()->json($this->service->show($policy));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function update(PolicyRequest $request, int $policy): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('update', [Policy::class, $policy]));

            return response()->json($this->service->update($request->validated(), $policy));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function destroy(Request $request, int $policy): JsonResponse
    {
        try {
            $this->failPolicy($request->{$this->getSubjectMethod()}->can('delete', [Policy::class, $policy]));
            $this->service->destroy($policy);

            return response()->json([], 204);
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }
}
