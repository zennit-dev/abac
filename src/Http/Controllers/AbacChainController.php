<?php

namespace zennit\ABAC\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use zennit\ABAC\Http\Requests\AbacChainRequest;
use zennit\ABAC\Http\Services\AbacChainService;

class AbacChainController extends Controller
{
    public function __construct(protected AbacChainService $service)
    {
    }

    public function index(Request $request, int $policy): JsonResponse
    {
        try {
            $matched = [];

            return response()->json($this->paginate($request, $matched));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function store(AbacChainRequest $request, $policy): JsonResponse
    {
        try {
            return response()->json($this->service->store($request->validated(), $policy, $request->has('checks')));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function show(int $chain): JsonResponse
    {
        try {
            return response()->json($this->service->show($chain));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function update(AbacChainRequest $request, int $chain): JsonResponse
    {
        try {
            return response()->json($this->service->update($request->validated(), $chain));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function destroy(int $chain): JsonResponse
    {
        try {
            $this->service->destroy($chain);

            return response()->json([], 204);
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }
}
