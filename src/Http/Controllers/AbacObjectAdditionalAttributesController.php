<?php

namespace zennit\ABAC\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use zennit\ABAC\Http\Requests\AbacObjectAdditionalAttributesRequest;
use zennit\ABAC\Http\Services\AbacObjectAdditionalAttributesService;

class AbacObjectAdditionalAttributesController extends Controller
{
    public function __construct(protected AbacObjectAdditionalAttributesService $service)
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

    public function store(AbacObjectAdditionalAttributesRequest $request): JsonResponse
    {
        try {
            return response()->json($this->service->store($request->validated()));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function show(int $object_attribute): JsonResponse
    {
        try {
            return response()->json($this->service->show($object_attribute));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function update(AbacObjectAdditionalAttributesRequest $request, int $object_attribute): JsonResponse
    {
        try {
            return response()->json($this->service->update($request->validated(), $object_attribute));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function destroy(int $object_attribute): JsonResponse
    {
        try {
            $this->service->destroy($object_attribute);

            return response()->json([], 204);
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }
}
