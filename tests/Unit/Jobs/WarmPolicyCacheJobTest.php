<?php

namespace zennit\ABAC\Tests\Unit\Jobs;

use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Jobs\WarmPolicyCacheJob;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Repositories\PolicyRepository;
use zennit\ABAC\Services\CacheService;

class WarmPolicyCacheJobTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function test_job_handle(): void
    {
        // Create mocks
        $repository = $this->createMock(PolicyRepository::class);
        $cache = $this->createMock(CacheService::class);

        // Create a Collection instance instead of an array
        $policies = new Collection([
            new Policy(['id' => 1]),
            new Policy(['id' => 2]),
        ]);

        // Set up repository mock expectations
        $repository->expects($this->once())
            ->method('getPoliciesFor')
            ->with('posts', 'all')
            ->willReturn($policies);

        // Set up cache mock expectations
        $cache->expects($this->exactly(2))
            ->method('remember')
            ->willReturn(true);

        // Create and execute job
        $job = new WarmPolicyCacheJob('posts');
        $job->handle($repository, $cache);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function test_job_handle_without_resource(): void
    {
        // Create mocks
        $repository = $this->createMock(PolicyRepository::class);
        $cache = $this->createMock(CacheService::class);

        // Create a Collection instance
        $policies = new Collection([
            new Policy(['id' => 1]),
            new Policy(['id' => 2]),
        ]);

        // Set up repository mock expectations
        $repository->expects($this->once())
            ->method('all')
            ->willReturn($policies);

        // Set up cache mock expectations
        $cache->expects($this->exactly(2))
            ->method('remember')
            ->willReturn(true);

        // Create and execute job
        $job = new WarmPolicyCacheJob;
        $job->handle($repository, $cache);
    }
}
