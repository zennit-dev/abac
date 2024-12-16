<?php

namespace zennit\ABAC\Tests\Unit\Jobs;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionClass;
use zennit\ABAC\Jobs\InvalidatePolicyCacheJob;
use zennit\ABAC\Services\CacheService;

class InvalidatePolicyCacheJobTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function test_job_handle(): void
    {
        // Create mock for CacheService
        $cache = $this->createMock(CacheService::class);

        // Set up expectations
        $policyId = 123;
        $cache->expects($this->once())
            ->method('forget')
            ->with("policy:{$policyId}");

        // Create job with integer policy ID
        $job = new InvalidatePolicyCacheJob($policyId);

        // Execute job
        $job->handle($cache);
    }

    public function test_job_constructor(): void
    {
        $policyId = 123;
        $job = new InvalidatePolicyCacheJob($policyId);

        // Use reflection to test private property
        $reflection = new ReflectionClass($job);
        $property = $reflection->getProperty('policyId');

        $this->assertEquals($policyId, $property->getValue($job));
    }
}
