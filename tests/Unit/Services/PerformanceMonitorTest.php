<?php

namespace zennit\ABAC\Tests\Unit\Services;

use Mockery;
use zennit\ABAC\Services\PerformanceMonitor;
use zennit\ABAC\Tests\TestCase;

class PerformanceMonitorTest extends TestCase
{
    private PerformanceMonitor $monitor;

    public function test_measures_operation_duration(): void
    {
        $this->monitor->start('test_operation');
        usleep(50000); // 50ms
        $duration = $this->monitor->end('test_operation');

        $this->assertGreaterThan(45, $duration); // Allow for small variations
        $this->assertLessThan(55, $duration);
    }

    public function test_handles_missing_start_time(): void
    {
        $duration = $this->monitor->end('nonexistent_operation');
        $this->assertEquals(0, $duration);
    }

    public function test_measure_method(): void
    {
        $result = $this->monitor->measure('test_measure', function () {
            usleep(50000); // 50ms

            return 'result';
        });

        $this->assertEquals('result', $result);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->monitor = new PerformanceMonitor([
            'threshold' => 100, // 100ms threshold
            'enabled' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
