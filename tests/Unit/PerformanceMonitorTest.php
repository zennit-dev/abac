<?php

namespace zennit\ABAC\Tests\Unit;

use Illuminate\Support\Facades\Log;
use zennit\ABAC\Services\PerformanceMonitor;
use zennit\ABAC\Tests\TestCase;

class PerformanceMonitorTest extends TestCase
{
    private PerformanceMonitor $monitor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->monitor = new PerformanceMonitor([
            'logging_enabled' => true,
            'log_threshold' => 100,
        ]);
    }

    public function test_measures_execution_time(): void
    {
        Log::shouldReceive('warning')->once();

        $result = $this->monitor->measure('test_operation', function () {
            usleep(200000); // 200ms

            return true;
        });

        $this->assertTrue($result);
    }

    public function test_skips_logging_under_threshold(): void
    {
        Log::shouldReceive('warning')->never();

        $this->monitor->measure('fast_operation', function () {
            usleep(50000); // 50ms

            return true;
        });
    }
}
