<?php

namespace zennit\ABAC\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use zennit\ABAC\Http\Requests\AbacSubjectAdditionalAttributeRequest;
use zennit\ABAC\Http\Services\AbacSubjectAdditionalAttributeService;

class AbacSubjectAdditionalAttributeController extends Controller
{
    public function __construct(protected AbacSubjectAdditionalAttributeService $service)
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

    public function store(AbacSubjectAdditionalAttributeRequest $request): JsonResponse
    {
        try {
            return response()->json($this->service->store($request->validated()));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function show(int $subject_attribute): JsonResponse
    {
        try {
            return response()->json($this->service->show($subject_attribute));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function update(AbacSubjectAdditionalAttributeRequest $request, int $subject_attribute): JsonResponse
    {
        try {
            return response()->json($this->service->update($request->validated(), $subject_attribute));
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }

    public function destroy(int $subject_attribute): JsonResponse
    {
        try {
            $this->service->destroy($subject_attribute);

            return response()->json([], 204);
        } catch (Throwable $e) {
            return $this->sendErrorResponse($e);
        }
    }
}
