<?php

namespace zennit\ABAC\Benchmarks;

use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\Operators\ArithmeticOperators;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Enums\Operators\StringOperators;
use zennit\ABAC\Enums\PolicyMethod;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacCheck;
use zennit\ABAC\Services\Evaluators\AbacChainEvaluator;
use zennit\ABAC\Services\Evaluators\AbacCheckEvaluator;

/** @noinspection PhpUnused */
class AbacEvaluationBench
{
    private static bool $bootstrapped = false;

    protected int $artificialLatencyMicros = 0;

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
     * @BeforeMethods({"setUpFlatAndStringScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchFlatAndStringResourceChecks(): void
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
     * @BeforeMethods({"setUpNestedOrDeepScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchNestedOrDeepResourceChecks(): void
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

    /**
     * @BeforeMethods({"setUpActorCheckVariantScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchActorAndResourceChecksVariant(): void
    {
        $query = BenchPost::query();

        $this->evaluator->apply($query, $this->rootChain, $this->context)->exists();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveReadSmallScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchReadComprehensiveSmall(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveReadMediumScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchReadComprehensiveMedium(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveReadLargeScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchReadComprehensiveLarge(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveCreateSmallScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchCreateComprehensiveSmall(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveCreateMediumScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchCreateComprehensiveMedium(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveCreateLargeScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchCreateComprehensiveLarge(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveUpdateSmallScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchUpdateComprehensiveSmall(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveUpdateMediumScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchUpdateComprehensiveMedium(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveUpdateLargeScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchUpdateComprehensiveLarge(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveDeleteSmallScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchDeleteComprehensiveSmall(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveDeleteMediumScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchDeleteComprehensiveMedium(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveDeleteLargeScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchDeleteComprehensiveLarge(): void
    {
        $this->runComprehensiveBenchmark();
    }

    public function setUpFlatAndScenario(): void
    {
        $this->setUpEnvironment('admin');
        $this->seedPosts(1500);

        $this->rootChain = AbacChain::query()->create([
            'operator' => LogicalOperators::AND->value,
            '_policy' => 'policy-1',
        ]);

        $this->createCheck($this->rootChain->getKey(), ArithmeticOperators::EQUALS->value, 'resource.category', 'public');
        $this->createCheck($this->rootChain->getKey(), ArithmeticOperators::EQUALS->value, 'resource.region', 'emea');
        $this->createCheck($this->rootChain->getKey(), ArithmeticOperators::GREATER_THAN_EQUALS->value, 'resource.sensitivity', '2');
        $this->createCheck($this->rootChain->getKey(), ArithmeticOperators::NOT_EQUALS->value, 'resource.category', 'archived');
    }

    public function setUpFlatAndStringScenario(): void
    {
        $this->setUpEnvironment('admin');
        $this->seedPosts(2000);

        $this->rootChain = AbacChain::query()->create([
            'operator' => LogicalOperators::AND->value,
            '_policy' => 'policy-1',
        ]);

        $this->createCheck($this->rootChain->getKey(), StringOperators::STARTS_WITH->value, 'resource.category', 'pub');
        $this->createCheck($this->rootChain->getKey(), StringOperators::STARTS_WITH->value, 'resource.region', 'e');
        $this->createCheck($this->rootChain->getKey(), StringOperators::CONTAINS->value, 'resource.owner_id', 'owner-');
        $this->createCheck($this->rootChain->getKey(), StringOperators::NOT_ENDS_WITH->value, 'resource.owner_id', '4');
        $this->createCheck($this->rootChain->getKey(), ArithmeticOperators::LESS_THAN_EQUALS->value, 'resource.sensitivity', '6');
    }

    public function setUpNestedOrScenario(): void
    {
        $this->setUpEnvironment('admin');
        $this->seedPosts(2000);

        $this->rootChain = AbacChain::query()->create([
            'operator' => LogicalOperators::OR->value,
            '_policy' => 'policy-1',
        ]);

        $left = AbacChain::query()->create([
            'operator' => LogicalOperators::AND->value,
            '_chain' => $this->rootChain->getKey(),
        ]);

        $right = AbacChain::query()->create([
            'operator' => LogicalOperators::AND->value,
            '_chain' => $this->rootChain->getKey(),
        ]);

        $middle = AbacChain::query()->create([
            'operator' => LogicalOperators::AND->value,
            '_chain' => $this->rootChain->getKey(),
        ]);

        $this->createCheck($left->getKey(), ArithmeticOperators::EQUALS->value, 'resource.region', 'emea');
        $this->createCheck($left->getKey(), ArithmeticOperators::EQUALS->value, 'resource.category', 'internal');
        $this->createCheck($left->getKey(), ArithmeticOperators::LESS_THAN_EQUALS->value, 'resource.sensitivity', '4');

        $this->createCheck($middle->getKey(), ArithmeticOperators::EQUALS->value, 'resource.owner_id', 'owner-2');
        $this->createCheck($middle->getKey(), ArithmeticOperators::EQUALS->value, 'resource.category', 'restricted');
        $this->createCheck($middle->getKey(), ArithmeticOperators::GREATER_THAN->value, 'resource.sensitivity', '1');

        $this->createCheck($right->getKey(), ArithmeticOperators::EQUALS->value, 'resource.owner_id', 'owner-1');
        $this->createCheck($right->getKey(), StringOperators::NOT_CONTAINS->value, 'resource.category', 'arch');
    }

    public function setUpNestedOrDeepScenario(): void
    {
        $this->setUpEnvironment('admin');
        $this->seedPosts(2500);

        $this->rootChain = AbacChain::query()->create([
            'operator' => LogicalOperators::OR->value,
            '_policy' => 'policy-1',
        ]);

        $leftGroup = AbacChain::query()->create([
            'operator' => LogicalOperators::OR->value,
            '_chain' => $this->rootChain->getKey(),
        ]);

        $rightGroup = AbacChain::query()->create([
            'operator' => LogicalOperators::OR->value,
            '_chain' => $this->rootChain->getKey(),
        ]);

        $leftA = AbacChain::query()->create([
            'operator' => LogicalOperators::AND->value,
            '_chain' => $leftGroup->getKey(),
        ]);

        $leftB = AbacChain::query()->create([
            'operator' => LogicalOperators::AND->value,
            '_chain' => $leftGroup->getKey(),
        ]);

        $rightA = AbacChain::query()->create([
            'operator' => LogicalOperators::AND->value,
            '_chain' => $rightGroup->getKey(),
        ]);

        $rightB = AbacChain::query()->create([
            'operator' => LogicalOperators::AND->value,
            '_chain' => $rightGroup->getKey(),
        ]);

        $this->createCheck($leftA->getKey(), ArithmeticOperators::EQUALS->value, 'resource.region', 'emea');
        $this->createCheck($leftA->getKey(), ArithmeticOperators::EQUALS->value, 'resource.category', 'public');

        $this->createCheck($leftB->getKey(), ArithmeticOperators::EQUALS->value, 'resource.region', 'apac');
        $this->createCheck($leftB->getKey(), ArithmeticOperators::EQUALS->value, 'resource.category', 'internal');

        $this->createCheck($rightA->getKey(), ArithmeticOperators::EQUALS->value, 'resource.owner_id', 'owner-1');
        $this->createCheck($rightA->getKey(), ArithmeticOperators::GREATER_THAN_EQUALS->value, 'resource.sensitivity', '2');

        $this->createCheck($rightB->getKey(), ArithmeticOperators::EQUALS->value, 'resource.owner_id', 'owner-3');
        $this->createCheck($rightB->getKey(), ArithmeticOperators::EQUALS->value, 'resource.category', 'restricted');
    }

    public function setUpActorCheckScenario(): void
    {
        $this->setUpEnvironment('admin');
        $this->seedPosts(1500);

        $this->rootChain = AbacChain::query()->create([
            'operator' => LogicalOperators::AND->value,
            '_policy' => 'policy-1',
        ]);

        $this->createCheck($this->rootChain->getKey(), ArithmeticOperators::EQUALS->value, 'resource.region', 'emea');
        $this->createCheck($this->rootChain->getKey(), ArithmeticOperators::EQUALS->value, 'actor.role', 'admin');
        $this->createCheck($this->rootChain->getKey(), StringOperators::STARTS_WITH->value, 'actor.role', 'adm');
        $this->createCheck($this->rootChain->getKey(), StringOperators::NOT_CONTAINS->value, 'actor.role', 'guest');
    }

    public function setUpActorCheckVariantScenario(): void
    {
        $this->setUpEnvironment('superadmin');
        $this->seedPosts(1500);

        $this->rootChain = AbacChain::query()->create([
            'operator' => LogicalOperators::AND->value,
            '_policy' => 'policy-1',
        ]);

        $this->createCheck($this->rootChain->getKey(), ArithmeticOperators::EQUALS->value, 'resource.category', 'public');
        $this->createCheck($this->rootChain->getKey(), ArithmeticOperators::GREATER_THAN_EQUALS->value, 'resource.sensitivity', '1');
        $this->createCheck($this->rootChain->getKey(), StringOperators::CONTAINS->value, 'resource.owner_id', 'owner-1');
        $this->createCheck($this->rootChain->getKey(), StringOperators::ENDS_WITH->value, 'actor.role', 'admin');
    }

    public function setUpComprehensiveReadSmallScenario(): void
    {
        $this->setUpComprehensiveScenario(PolicyMethod::READ, 1000, 2);
    }

    public function setUpComprehensiveReadMediumScenario(): void
    {
        $this->setUpComprehensiveScenario(PolicyMethod::READ, 4000, 4);
    }

    public function setUpComprehensiveReadLargeScenario(): void
    {
        $this->setUpComprehensiveScenario(PolicyMethod::READ, 10000, 8);
    }

    public function setUpComprehensiveCreateSmallScenario(): void
    {
        $this->setUpComprehensiveScenario(PolicyMethod::CREATE, 1000, 2);
    }

    public function setUpComprehensiveCreateMediumScenario(): void
    {
        $this->setUpComprehensiveScenario(PolicyMethod::CREATE, 4000, 4);
    }

    public function setUpComprehensiveCreateLargeScenario(): void
    {
        $this->setUpComprehensiveScenario(PolicyMethod::CREATE, 10000, 8);
    }

    public function setUpComprehensiveUpdateSmallScenario(): void
    {
        $this->setUpComprehensiveScenario(PolicyMethod::UPDATE, 1000, 2);
    }

    public function setUpComprehensiveUpdateMediumScenario(): void
    {
        $this->setUpComprehensiveScenario(PolicyMethod::UPDATE, 4000, 4);
    }

    public function setUpComprehensiveUpdateLargeScenario(): void
    {
        $this->setUpComprehensiveScenario(PolicyMethod::UPDATE, 10000, 8);
    }

    public function setUpComprehensiveDeleteSmallScenario(): void
    {
        $this->setUpComprehensiveScenario(PolicyMethod::DELETE, 1000, 2);
    }

    public function setUpComprehensiveDeleteMediumScenario(): void
    {
        $this->setUpComprehensiveScenario(PolicyMethod::DELETE, 4000, 4);
    }

    public function setUpComprehensiveDeleteLargeScenario(): void
    {
        $this->setUpComprehensiveScenario(PolicyMethod::DELETE, 10000, 8);
    }

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
