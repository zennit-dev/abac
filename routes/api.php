<?php

use zennit\ABAC\Http\Controllers\CollectionConditionController;
use zennit\ABAC\Http\Controllers\ConditionAttributeController;
use zennit\ABAC\Http\Controllers\PermissionController;
use zennit\ABAC\Http\Controllers\PolicyCollectionController;
use zennit\ABAC\Http\Controllers\PolicyController;
use zennit\ABAC\Http\Controllers\ResourceAttributeController;
use zennit\ABAC\Http\Controllers\UserAttributeController;

Route::apiResource('user-attributes', UserAttributeController::class)
    ->middleware('abac');

Route::apiResource('resource-attributes', ResourceAttributeController::class)
    ->middleware('abac');

Route::apiResource('permissions', PermissionController::class)
    ->middleware('abac');

Route::apiResource('permissions.policies', PolicyController::class)
    ->middleware('abac');

Route::apiResource('permissions.policies.collections', PolicyCollectionController::class)
    ->middleware('abac');

Route::apiResource('permissions.policies.collections.conditions', CollectionConditionController::class)
    ->middleware('abac');

Route::apiResource('permissions.policies.collections.conditions.attributes', ConditionAttributeController::class)
    ->middleware('abac');
