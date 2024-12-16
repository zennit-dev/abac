<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use zennit\ABAC\Enums\PolicyOperators;

return new class () extends Migration
{
    public function up(): void
    {
        $config = config('abac.tables.user_attributes');

        Schema::create($config['name'], function (Blueprint $table) use ($config) {
            $table->id();
            $table->timestamps();

            $table->string($config['subject_type_column']);
            $table->unsignedBigInteger($config['subject_id_column']);
            $table->string($config['attribute_name_column']);
            $table->string($config['attribute_value_column']);

            $table->index([
                $config['subject_type_column'],
                $config['subject_id_column'],
            ]);
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

        Schema::create('resource_attributes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('resource');
            $table->string('attribute_name');
            $table->string('attribute_value');

            $table->index(['resource', 'attribute_name']);
        });
    }

    public function down(): void
    {
        $tables = config('abac.tables');

        Schema::dropIfExists($tables['resource_attributes']['name']);
        Schema::dropIfExists('policy_condition_attributes');
        Schema::dropIfExists('policy_conditions');
        Schema::dropIfExists('policies');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists($tables['user_attributes']['name']);
    }
};
