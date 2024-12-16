<?php

namespace zennit\ABAC\Tests\Feature;

use Illuminate\Support\Facades\Log;
use Mockery;
use stdClass;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Logging\AuditLogger;
use zennit\ABAC\Tests\TestCase;

class AuditLoggingTest extends TestCase
{
    private AuditLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = new AuditLogger([
            'enabled' => true,
            'channel' => 'testing',
            'detailed' => true,
        ]);
    }

    public function test_logs_access_evaluation(): void
    {
        Log::shouldReceive('channel')
            ->with('testing')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->with(
                'Access evaluation',
                Mockery::on(
                    fn ($args) => isset($args['resource']) &&
                    isset($args['operation']) &&
                    isset($args['granted'])
                )
            );

        $subject = new stdClass();
        $subject->id = 1;

        $context = new AccessContext(
            subject: $subject,
            resource: 'posts',
            operation: PermissionOperations::INDEX->value
        );

        $this->logger->logAccess($context, true, ['reason' => 'Policy matched']);
    }
}
