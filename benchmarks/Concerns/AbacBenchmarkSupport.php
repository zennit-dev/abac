<?php

namespace zennit\ABAC\Benchmarks\Concerns;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use zennit\ABAC\Benchmarks\BenchActor;
use zennit\ABAC\Benchmarks\BenchPost;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\Operators\ArithmeticOperators;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Enums\Operators\StringOperators;
use zennit\ABAC\Enums\PolicyMethod;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacCheck;
use zennit\ABAC\Services\Evaluators\AbacChainEvaluator;
use zennit\ABAC\Services\Evaluators\AbacCheckEvaluator;

trait AbacBenchmarkSupport
{
    private static bool $bootstrapped = false;

    protected int $artificialLatencyMicros = 0;

    private AbacChainEvaluator $evaluator;

    private AccessContext $context;

    private AbacChain $rootChain;

    protected function setUpEnvironment(string $actorRole): void
    {
        $this->bootstrapDatabase();

        AbacCheck::query()->delete();
        AbacChain::query()->delete();
        BenchPost::query()->delete();

        $this->evaluator = new AbacChainEvaluator(new AbacCheckEvaluator);

        $actor = new BenchActor;
        $actor->forceFill([
            'id' => 1,
            'role' => $actorRole,
        ]);

        $this->context = new AccessContext(
            method: PolicyMethod::READ,
            resource: BenchPost::query(),
            actor: $actor,
            environment: [],
        );
    }

    protected function setUpComprehensiveScenario(PolicyMethod $method, int $postCount, int $branchCount): void
    {
        $this->setUpEnvironmentForMethod($method, 'admin');
        $this->seedPosts($postCount);

        $this->rootChain = AbacChain::query()->create([
            'operator' => LogicalOperators::OR->value,
            '_policy' => 'policy-1',
        ]);

        for ($i = 0; $i < $branchCount; $i++) {
            $group = AbacChain::query()->create([
                'operator' => $i % 2 === 0 ? LogicalOperators::AND->value : LogicalOperators::OR->value,
                '_chain' => $this->rootChain->getKey(),
            ]);

            $this->attachResourceArithmeticChecks($group->getKey(), $i);
            $this->attachResourceStringChecks($group->getKey(), $i);
            $this->attachActorArithmeticChecks($group->getKey(), $i);
            $this->attachActorStringChecks($group->getKey(), $i);
        }
    }

    protected function setUpEnvironmentForMethod(PolicyMethod $method, string $actorRole): void
    {
        $this->bootstrapDatabase();

        AbacCheck::query()->delete();
        AbacChain::query()->delete();
        BenchPost::query()->delete();

        $this->evaluator = new AbacChainEvaluator(new AbacCheckEvaluator);

        $actor = new BenchActor;
        $actor->forceFill([
            'id' => 1,
            'role' => $actorRole,
        ]);

        $this->context = new AccessContext(
            method: $method,
            resource: BenchPost::query(),
            actor: $actor,
            environment: [],
        );
    }

    protected function runComprehensiveBenchmark(): void
    {
        if ($this->artificialLatencyMicros > 0) {
            usleep($this->artificialLatencyMicros);
        }

        $query = BenchPost::query();

        $this->evaluator->apply($query, $this->rootChain, $this->context)->exists();
    }

    private function bootstrapDatabase(): void
    {
        if (self::$bootstrapped) {
            return;
        }

        $capsule = new Capsule;
        $capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $schema = $capsule->schema();

        $schema->create('abac_chains', function (Blueprint $table): void {
            $table->uuid('_id')->primary();
            $table->string('operator');
            $table->uuid('_chain')->nullable();
            $table->uuid('_policy')->nullable();
            $table->timestamps();
        });

        $schema->create('abac_checks', function (Blueprint $table): void {
            $table->uuid('_id')->primary();
            $table->uuid('_chain');
            $table->string('operator');
            $table->string('key');
            $table->string('value');
            $table->timestamps();
        });

        $schema->create('bench_posts', function (Blueprint $table): void {
            $table->id();
            $table->string('owner_id');
            $table->string('category');
            $table->string('region');
            $table->unsignedTinyInteger('sensitivity');
            $table->timestamps();
        });

        $schema->create('bench_actors', function (Blueprint $table): void {
            $table->id();
            $table->string('role');
            $table->timestamps();
        });

        AbacChain::unsetEventDispatcher();
        AbacCheck::unsetEventDispatcher();

        self::$bootstrapped = true;
    }

    protected function seedPosts(int $count): void
    {
        $rows = [];
        $timestamp = date('Y-m-d H:i:s');
        $owners = ['owner-1', 'owner-2', 'owner-3', 'owner-4'];
        $categories = ['public', 'internal', 'restricted', 'archived'];
        $regions = ['emea', 'na', 'apac', 'latam'];

        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'owner_id' => $owners[$i % count($owners)],
                'category' => $categories[$i % count($categories)],
                'region' => $regions[$i % count($regions)],
                'sensitivity' => $i % 10,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        BenchPost::query()->insert($rows);
    }

    /**
     * @param  list<array{0: string, 1: string, 2: string}>  $checks
     */
    protected function createChecks(string $chainId, array $checks): void
    {
        foreach ($checks as [$operator, $key, $value]) {
            $this->createCheck($chainId, $operator, $key, $value);
        }
    }

    protected function attachResourceArithmeticChecks(string $chainId, int $offset): void
    {
        $this->createChecks($chainId, [
            [ArithmeticOperators::EQUALS->value, 'resource.sensitivity', (string) ($offset % 10)],
            [ArithmeticOperators::NOT_EQUALS->value, 'resource.category', 'archived'],
            [ArithmeticOperators::GREATER_THAN->value, 'resource.sensitivity', '1'],
            [ArithmeticOperators::LESS_THAN->value, 'resource.sensitivity', '9'],
            [ArithmeticOperators::GREATER_THAN_EQUALS->value, 'resource.sensitivity', '2'],
            [ArithmeticOperators::LESS_THAN_EQUALS->value, 'resource.sensitivity', '8'],
        ]);
    }

    protected function attachResourceStringChecks(string $chainId, int $offset): void
    {
        $suffix = (string) ($offset % 4 + 1);

        $this->createChecks($chainId, [
            [StringOperators::CONTAINS->value, 'resource.owner_id', 'owner-'],
            [StringOperators::NOT_CONTAINS->value, 'resource.category', 'missing'],
            [StringOperators::STARTS_WITH->value, 'resource.region', 'e'],
            [StringOperators::ENDS_WITH->value, 'resource.owner_id', $suffix],
            [StringOperators::NOT_STARTS_WITH->value, 'resource.category', 'x'],
            [StringOperators::NOT_ENDS_WITH->value, 'resource.region', 'z'],
        ]);
    }

    protected function attachActorArithmeticChecks(string $chainId, int $offset): void
    {
        $this->createChecks($chainId, [
            [ArithmeticOperators::EQUALS->value, 'actor.id', '1'],
            [ArithmeticOperators::NOT_EQUALS->value, 'actor.id', (string) (100 + $offset)],
            [ArithmeticOperators::GREATER_THAN->value, 'actor.id', '0'],
            [ArithmeticOperators::LESS_THAN->value, 'actor.id', '99'],
            [ArithmeticOperators::GREATER_THAN_EQUALS->value, 'actor.id', '1'],
            [ArithmeticOperators::LESS_THAN_EQUALS->value, 'actor.id', '1'],
        ]);
    }

    protected function attachActorStringChecks(string $chainId, int $offset): void
    {
        $this->createChecks($chainId, [
            [StringOperators::CONTAINS->value, 'actor.role', 'admin'],
            [StringOperators::NOT_CONTAINS->value, 'actor.role', 'guest'],
            [StringOperators::STARTS_WITH->value, 'actor.role', 'ad'],
            [StringOperators::ENDS_WITH->value, 'actor.role', 'min'],
            [StringOperators::NOT_STARTS_WITH->value, 'actor.role', 'guest'],
            [StringOperators::NOT_ENDS_WITH->value, 'actor.role', 'guest'],
        ]);
    }

    protected function createCheck(string $chainId, string $operator, string $key, string $value): void
    {
        AbacCheck::query()->create([
            '_chain' => $chainId,
            'operator' => $operator,
            'key' => $key,
            'value' => $value,
        ]);
    }
}
