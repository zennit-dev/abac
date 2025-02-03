<?php

use zennit\ABAC\Http\Controllers\CollectionConditionController;
use zennit\ABAC\Http\Controllers\ConditionAttributeController;
use zennit\ABAC\Http\Controllers\PermissionController;
use zennit\ABAC\Http\Controllers\PolicyCollectionController;
use zennit\ABAC\Http\Controllers\PolicyController;
use zennit\ABAC\Http\Controllers\ResourceAttributeController;
use zennit\ABAC\Http\Controllers\UserAttributeController;

Route::middleware(['auth:sanctum', 'abac'])->group(function () {
    Route::apiResource('user-attributes', UserAttributeController::class);
    Route::apiResource('resource-attributes', ResourceAttributeController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::apiResource('permissions.policies', PolicyController::class);
    Route::apiResource('permissions.policies.collections', PolicyCollectionController::class);
    Route::apiResource('permissions.policies.collections.conditions', CollectionConditionController::class);
    Route::apiResource('permissions.policies.collections.conditions.attributes', ConditionAttributeController::class);
});
