<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use zennit\ABAC\Enums\Operators\AllOperators;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Enums\PolicyMethod;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abac_actor_additional_attributes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('_id');
            $table->string('key');
            $table->string('value');

            $table->index(['_id']);
        });

        Schema::create('abac_resource_additional_attributes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('model');
            $table->string('_id')->nullable();
            $table->string('key');
            $table->string('value');

            $table->index(['model', '_id']);
        });

        Schema::create('abac_policies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('resource');
            $table->enum('method', PolicyMethod::values());

            $table->unique(['resource', 'method']);
        });

        Schema::create('abac_chains', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('operator', LogicalOperators::values());
            $table->foreignId('chain_id')->nullable()->constrained('abac_chains')->cascadeOnDelete();
            $table->foreignId('policy_id')->unique()->nullable()->constrained('abac_policies')->cascadeOnDelete();
        });

        Schema::create('abac_checks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('chain_id')->constrained('abac_chains')->cascadeOnDelete();
            $table->enum('operator', AllOperators::values([LogicalOperators::class]));
            $table->string('key');
            $table->string('value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abac_resource_additional_attributes');
        Schema::dropIfExists('abac_actor_additional_attributes');
        Schema::dropIfExists('abac_policies');
        Schema::dropIfExists('abac_chains');
        Schema::dropIfExists('abac_checks');
    }
};
