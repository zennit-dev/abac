<?php

namespace zennit\ABAC\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;
use zennit\ABAC\Http\Requests\AbacPolicyRequest;
use zennit\ABAC\Http\Services\AbacPolicyService;

class AbacPolicyController extends Controller
{
    public function __construct(protected AbacPolicyService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $matched = [];

            return response()->json($this->paginate($request, $matched));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function store(AbacPolicyRequest $request): JsonResponse
    {
        try {
            $response = DB::transaction(function () use ($request) {
                return $this->service->store($request->validated());
            });

            return response()->json($response, 201);
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function show(int $policy): JsonResponse
    {
        try {
            return response()->json($this->service->show($policy));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function update(AbacPolicyRequest $request, int $policy): JsonResponse
    {
        try {
            return response()->json($this->service->update($request->validated(), $policy));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function destroy(int $policy): JsonResponse
    {
        try {
            $this->service->destroy($policy);

            return response()->json([], 204);
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }
}
