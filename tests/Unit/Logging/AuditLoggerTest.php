<?php

namespace zennit\ABAC\Tests\Unit\Logging;

use Illuminate\Support\Facades\Log;
use Mockery;
use stdClass;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Logging\AuditLogger;
use zennit\ABAC\Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    private AuditLogger $logger;

    public function test_logs_access_decision(): void
    {
        Log::shouldReceive('channel')
            ->with('testing')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->with(
                'Access evaluation',  // Updated message to match implementation
                Mockery::on(
                    fn ($args) => isset($args['resource']) &&
                    isset($args['operation']) &&
                    isset($args['granted']) &&
                    isset($args['metadata_key'])
                )
            );

        $subject = new stdClass();
        $subject->id = 1;

        $context = new AccessContext(
            subject: $subject,
            resource: 'posts',
            operation: PermissionOperations::INDEX->value
        );

        $this->logger->logAccess($context, true, ['metadata_key' => 'value']);

        $this->assertTrue(true);
    }

    public function test_respects_disabled_logging(): void
    {
        $logger = new AuditLogger(['enabled' => false]);

        Log::shouldReceive('channel')->never();
        Log::shouldReceive('info')->never();

        $subject = new stdClass();
        $subject->id = 1;

        $context = new AccessContext(
            subject: $subject,
            resource: 'posts',
            operation: PermissionOperations::INDEX->value
        );

        $logger->logAccess($context, true);

        $this->assertTrue(true);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = new AuditLogger([
            'enabled' => true,
            'channel' => 'testing',
            'detailed' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
