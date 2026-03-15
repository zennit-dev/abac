<?php

namespace zennit\ABAC\Benchmarks;

use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\Operators\ArithmeticOperators;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Enums\PolicyMethod;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacCheck;
use zennit\ABAC\Services\Evaluators\AbacChainEvaluator;
use zennit\ABAC\Services\Evaluators\AbacCheckEvaluator;

/** @noinspection PhpUnused */
class AbacEvaluationBench
{
    private static bool $bootstrapped = false;

    private AbacChainEvaluator $evaluator;

    private AccessContext $context;

    private AbacChain $rootChain;

    /**
     * @BeforeMethods({"setUpFlatAndScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchFlatAndResourceChecks(): void
    {
        $query = BenchPost::query();

        $this->evaluator->apply($query, $this->rootChain, $this->context)->exists();
    }

    /**
     * @BeforeMethods({"setUpNestedOrScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchNestedOrResourceChecks(): void
    {
        $query = BenchPost::query();

        $this->evaluator->apply($query, $this->rootChain, $this->context)->exists();
    }

    /**
     * @BeforeMethods({"setUpActorCheckScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchActorAndResourceChecks(): void
    {
        $query = BenchPost::query();

        $this->evaluator->apply($query, $this->rootChain, $this->context)->exists();
    }

    public function setUpFlatAndScenario(): void
    {
        $this->setUpEnvironment();
        $this->seedPosts();

        $this->rootChain = AbacChain::query()->create([
            'operator' => LogicalOperators::AND->value,
            'policy_id' => 1,
        ]);

        $this->createCheck($this->rootChain->id, ArithmeticOperators::EQUALS->value, 'resource.category', 'public');
        $this->createCheck($this->rootChain->id, ArithmeticOperators::EQUALS->value, 'resource.region', 'emea');
        $this->createCheck($this->rootChain->id, ArithmeticOperators::GREATER_THAN_EQUALS->value, 'resource.sensitivity', '2');
    }

    public function setUpNestedOrScenario(): void
    {
        $this->setUpEnvironment();
        $this->seedPosts();

        $this->rootChain = AbacChain::query()->create([
            'operator' => LogicalOperators::OR->value,
            'policy_id' => 1,
        ]);

        $left = AbacChain::query()->create([
            'operator' => LogicalOperators::AND->value,
            'chain_id' => $this->rootChain->id,
        ]);

        $right = AbacChain::query()->create([
            'operator' => LogicalOperators::AND->value,
            'chain_id' => $this->rootChain->id,
        ]);

        $this->createCheck($left->id, ArithmeticOperators::EQUALS->value, 'resource.region', 'emea');
        $this->createCheck($left->id, ArithmeticOperators::EQUALS->value, 'resource.category', 'internal');
        $this->createCheck($right->id, ArithmeticOperators::EQUALS->value, 'resource.owner_id', 'owner-1');
    }

    public function setUpActorCheckScenario(): void
    {
        $this->setUpEnvironment();
        $this->seedPosts();

        $this->rootChain = AbacChain::query()->create([
            'operator' => LogicalOperators::AND->value,
            'policy_id' => 1,
        ]);

        $this->createCheck($this->rootChain->id, ArithmeticOperators::EQUALS->value, 'resource.region', 'emea');
        $this->createCheck($this->rootChain->id, ArithmeticOperators::EQUALS->value, 'actor.role', 'admin');
    }

    private function setUpEnvironment(): void
    {
        $this->bootstrapDatabase();

        AbacCheck::query()->delete();
        AbacChain::query()->delete();
        BenchPost::query()->delete();

        $this->evaluator = new AbacChainEvaluator(new AbacCheckEvaluator);

        $actor = new BenchActor;
        $actor->forceFill([
            'id' => 1,
            'role' => 'admin',
        ]);

        $this->context = new AccessContext(
            method: PolicyMethod::READ,
            resource: BenchPost::query(),
            actor: $actor,
            environment: [],
        );
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
            $table->id();
            $table->string('operator');
            $table->unsignedBigInteger('chain_id')->nullable();
            $table->unsignedBigInteger('policy_id')->nullable();
            $table->timestamps();
        });

        $schema->create('abac_checks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('chain_id');
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

    private function seedPosts(): void
    {
        $rows = [];

        for ($i = 0; $i < 300; $i++) {
            $rows[] = [
                'owner_id' => $i % 2 === 0 ? 'owner-1' : 'owner-2',
                'category' => $i % 3 === 0 ? 'public' : 'internal',
                'region' => $i % 4 === 0 ? 'emea' : 'na',
                'sensitivity' => $i % 5,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        BenchPost::query()->insert($rows);
    }

    private function createCheck(int $chainId, string $operator, string $key, string $value): void
    {
        AbacCheck::query()->create([
            'chain_id' => $chainId,
            'operator' => $operator,
            'key' => $key,
            'value' => $value,
        ]);
    }
}

class BenchPost extends Model
{
    protected $table = 'bench_posts';

    protected $guarded = [];
}

class BenchActor extends Model
{
    protected $table = 'bench_actors';

    protected $guarded = [];

    public $timestamps = false;
}
