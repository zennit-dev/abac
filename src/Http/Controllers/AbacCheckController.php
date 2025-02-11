<?php

namespace zennit\ABAC\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use zennit\ABAC\Http\Requests\AbacCheckRequest;
use zennit\ABAC\Http\Services\AbacCheckService;

class AbacCheckController extends Controller
{
    public function __construct(protected AbacCheckService $service)
    {
    }

    public function index(Request $request, int $chain): JsonResponse
    {
        try {
            $matched = [];

            return response()->json($this->paginate($request, $matched));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function store(AbacCheckRequest $request, $chain): JsonResponse
    {
        try {
            return response()->json($this->service->store($request->validated(), $chain));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function show(int $check): JsonResponse
    {
        try {
            return response()->json($this->service->show($check));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function update(AbacCheckRequest $request, int $check): JsonResponse
    {
        try {
            return response()->json($this->service->update($request->validated(), $check));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function destroy(int $check): JsonResponse
    {
        try {
            $this->service->destroy($check);

            return response()->json([], 204);
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }
}
