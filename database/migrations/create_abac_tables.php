<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use zennit\ABAC\Enums\PolicyOperators;
use zennit\ABAC\Services\ConfigurationService;

return new class () extends Migration
{
    public function up(): void
    {
        $config = app(ConfigurationService::class);
        $userAttributesTable = $config->getUserAttributesTable();

        Schema::create($userAttributesTable['name'], function (Blueprint $table) use ($userAttributesTable) {
            $table->id();
            $table->timestamps();
            $table->string($userAttributesTable['subject_type_column']);
            $table->unsignedBigInteger($userAttributesTable['subject_id_column']);
            $table->string($userAttributesTable['attribute_name_column']);
            $table->string($userAttributesTable['attribute_value_column']);

            $table->index([$userAttributesTable['subject_type_column'], $userAttributesTable['subject_id_column']]);
        });

        Schema::create('resource_attributes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('resource');
            $table->string('attribute_name');
            $table->string('attribute_value');

            $table->index(['resource', 'attribute_name']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('resource');
            $table->string('operation');

            $table->unique(['resource', 'operation']);
        });

        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->foreignId('permission_id')
                ->constrained('permissions')
                ->cascadeOnDelete();
        });

        Schema::create('policy_conditions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('operator', PolicyOperators::values());
            $table->foreignId('policy_id')
                ->constrained('policies')
                ->cascadeOnDelete();
        });

        Schema::create('policy_condition_attributes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('policy_condition_id')
                ->constrained('policy_conditions')
                ->cascadeOnDelete();
            $table->string('attribute_name');
            $table->string('attribute_value');
        });


    }

    public function down(): void
    {
        $config = app(ConfigurationService::class);
        $userAttributesTable = $config->getUserAttributesTable();

        Schema::dropIfExists($userAttributesTable['name']);
        Schema::dropIfExists('resource_attributes');
        Schema::dropIfExists('policy_condition_attributes');
        Schema::dropIfExists('policy_conditions');
        Schema::dropIfExists('policies');
        Schema::dropIfExists('permissions');
    }
};
