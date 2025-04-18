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
            return response()->json($this->paginate($request, $request->abac()->query->get()->toArray()));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function store(AbacObjectAdditionalAttributesRequest $request): JsonResponse
    {
        try {
            return response()->json($this->service->store($request->validated()), 201);
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

            return response()->json(['message' => 'Object Attribute deleted successfully.'], 204);
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }
}
