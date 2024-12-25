<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use zennit\ABAC\Enums\Operators\AllOperators;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Traits\ZennitAbacHasConfigurations;

return new class extends Migration
{
    use ZennitAbacHasConfigurations;

    public function up(): void
    {
        Schema::create('user_attributes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('subject_type')->default($this->getUserAttributeSubjectType());
            $table->unsignedBigInteger('subject_id');
            $table->string('attribute_name');
            $table->string('attribute_value');

            $table->unique(['subject_type', 'subject_id', 'attribute_name']);
            $table->index(['subject_type', 'subject_id']);
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

        Schema::create('policy_collections', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('operator', LogicalOperators::values());
            $table->foreignId('policy_id')
                ->constrained('policies')
                ->cascadeOnDelete();
        });

        Schema::create('policy_conditions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('operator', LogicalOperators::values());
            $table->foreignId('policy_collection_id')
                ->constrained('policy_collections')
                ->cascadeOnDelete();
        });

        Schema::create('policy_condition_attributes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('policy_condition_id')
                ->constrained('policy_conditions')
                ->cascadeOnDelete();
            $table->enum('operator', AllOperators::values(LogicalOperators::cases()));
            $table->string('attribute_name');
            $table->string('attribute_value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_condition_attributes');
        Schema::dropIfExists('policy_conditions');
		Schema::dropIfExists('policy_collections');
        Schema::dropIfExists('policies');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('resource_attributes');
        Schema::dropIfExists('user_attributes');
    }
};
