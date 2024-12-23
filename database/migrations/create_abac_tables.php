<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use zennit\ABAC\Enums\PolicyOperators;
use zennit\ABAC\Traits\HasConfigurations;

return new class () extends Migration
{
    use HasConfigurations;

    public function up(): void
    {
        Schema::create('user_attributes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string($this->getSubjectType());
            $table->unsignedBigInteger($this->getSubjectId());
            $table->string('attribute_name');
            $table->string('attribute_value');

            $table->index([$this->getSubjectType(), $this->getSubjectId()]);
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
        Schema::dropIfExists('policy_condition_attributes');
        Schema::dropIfExists('policy_conditions');
        Schema::dropIfExists('policies');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('resource_attributes');
        Schema::dropIfExists('user_attributes');
    }
};
