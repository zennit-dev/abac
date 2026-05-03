<?php

namespace zennit\ABAC\Benchmarks;

use Exception;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use zennit\ABAC\Benchmarks\Concerns\AbacBenchmarkSupport;
use zennit\ABAC\Enums\PolicyMethod;

/** @noinspection PhpUnused */
class AbacLatencyEvaluationBench
{
    use AbacBenchmarkSupport;

    /**
     * @BeforeMethods({"setUpComprehensiveReadSmallLatencyScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchReadComprehensiveSmallLatency(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveReadMediumLatencyScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchReadComprehensiveMediumLatency(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveReadLargeLatencyScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchReadComprehensiveLargeLatency(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveCreateSmallLatencyScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchCreateComprehensiveSmallLatency(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveCreateMediumLatencyScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchCreateComprehensiveMediumLatency(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveCreateLargeLatencyScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchCreateComprehensiveLargeLatency(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveUpdateSmallLatencyScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchUpdateComprehensiveSmallLatency(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveUpdateMediumLatencyScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchUpdateComprehensiveMediumLatency(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveUpdateLargeLatencyScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchUpdateComprehensiveLargeLatency(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveDeleteSmallLatencyScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchDeleteComprehensiveSmallLatency(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveDeleteMediumLatencyScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchDeleteComprehensiveMediumLatency(): void
    {
        $this->runComprehensiveBenchmark();
    }

    /**
     * @BeforeMethods({"setUpComprehensiveDeleteLargeLatencyScenario"})
     *
     * @Revs(100)
     *
     * @Iterations(5)
     *
     * @throws Exception
     */
    public function benchDeleteComprehensiveLargeLatency(): void
    {
        $this->runComprehensiveBenchmark();
    }

    public function setUpComprehensiveReadSmallLatencyScenario(): void
    {
        $this->setUpComprehensiveLatencyScenario(PolicyMethod::READ, 1000, 2);
    }

    public function setUpComprehensiveReadMediumLatencyScenario(): void
    {
        $this->setUpComprehensiveLatencyScenario(PolicyMethod::READ, 4000, 4);
    }

    public function setUpComprehensiveReadLargeLatencyScenario(): void
    {
        $this->setUpComprehensiveLatencyScenario(PolicyMethod::READ, 10000, 8);
    }

    public function setUpComprehensiveCreateSmallLatencyScenario(): void
    {
        $this->setUpComprehensiveLatencyScenario(PolicyMethod::CREATE, 1000, 2);
    }

    public function setUpComprehensiveCreateMediumLatencyScenario(): void
    {
        $this->setUpComprehensiveLatencyScenario(PolicyMethod::CREATE, 4000, 4);
    }

    public function setUpComprehensiveCreateLargeLatencyScenario(): void
    {
        $this->setUpComprehensiveLatencyScenario(PolicyMethod::CREATE, 10000, 8);
    }

    public function setUpComprehensiveUpdateSmallLatencyScenario(): void
    {
        $this->setUpComprehensiveLatencyScenario(PolicyMethod::UPDATE, 1000, 2);
    }

    public function setUpComprehensiveUpdateMediumLatencyScenario(): void
    {
        $this->setUpComprehensiveLatencyScenario(PolicyMethod::UPDATE, 4000, 4);
    }

    public function setUpComprehensiveUpdateLargeLatencyScenario(): void
    {
        $this->setUpComprehensiveLatencyScenario(PolicyMethod::UPDATE, 10000, 8);
    }

    public function setUpComprehensiveDeleteSmallLatencyScenario(): void
    {
        $this->setUpComprehensiveLatencyScenario(PolicyMethod::DELETE, 1000, 2);
    }

    public function setUpComprehensiveDeleteMediumLatencyScenario(): void
    {
        $this->setUpComprehensiveLatencyScenario(PolicyMethod::DELETE, 4000, 4);
    }

    public function setUpComprehensiveDeleteLargeLatencyScenario(): void
    {
        $this->setUpComprehensiveLatencyScenario(PolicyMethod::DELETE, 10000, 8);
    }

    protected function setUpComprehensiveLatencyScenario(PolicyMethod $method, int $postCount, int $branchCount): void
    {
        $this->setUpComprehensiveScenario($method, $postCount, $branchCount);
        $this->artificialLatencyMicros = 100000;
    }
}
