<?php

use zennit\ABAC\Http\Controllers\AbacChainController;
use zennit\ABAC\Http\Controllers\AbacCheckController;
use zennit\ABAC\Http\Controllers\AbacObjectAdditionalAttributesController;
use zennit\ABAC\Http\Controllers\AbacPolicyController;
use zennit\ABAC\Http\Controllers\AbacSubjectAdditionalAttributeController;

Route::apiResource('object-attributes', AbacObjectAdditionalAttributesController::class);
Route::apiResource('subject-attributes', AbacSubjectAdditionalAttributeController::class);
Route::apiResource('policies', AbacPolicyController::class);
Route::apiResource('policies.chains', AbacChainController::class);
Route::apiResource('policies.chains.checks', AbacCheckController::class);
