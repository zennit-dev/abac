<?php

namespace zennit\ABAC\Tests\Unit\Commands;

use PHPUnit\Framework\TestCase;
use zennit\ABAC\Commands\PublishAbacCommand;

class PublishAbacCommandTest extends TestCase
{
    /**
     * @test
     */
    public function it_publishes_configs_without_force_option(): void
    {
        $command = $this->getMockBuilder(PublishAbacCommand::class)
            ->onlyMethods(['call', 'option', 'info'])
            ->getMock();

        // Expect option('force') to be called twice and return false
        $command->expects($this->exactly(2))
            ->method('option')
            ->with('force')
            ->willReturn(false);

        // Expect calls to vendor:publish
        $command->expects($this->exactly(2))
            ->method('call')
            ->willReturnCallback(function ($command, $params) {
                static $callNumber = 0;
                $callNumber++;

                if ($callNumber === 1) {
                    $this->assertEquals('vendor:publish', $command);
                    $this->assertEquals([
                        '--tag' => 'abac-config',
                        '--force' => false,
                    ], $params);
                } elseif ($callNumber === 2) {
                    $this->assertEquals('vendor:publish', $command);
                    $this->assertEquals([
                        '--tag' => 'abac-migrations',
                        '--force' => false,
                    ], $params);
                }

                return 0;
            });

        // Expect info() to be called with success message
        $command->expects($this->once())
            ->method('info')
            ->with('ABAC files published successfully!');

        $result = $command->handle();
        $this->assertEquals(0, $result); // Command::SUCCESS = 0
    }

    /**
     * @test
     */
    public function it_publishes_configs_with_force_option(): void
    {
        $command = $this->getMockBuilder(PublishAbacCommand::class)
            ->onlyMethods(['call', 'option', 'info'])
            ->getMock();

        // Expect option('force') to be called twice and return true
        $command->expects($this->exactly(2))
            ->method('option')
            ->with('force')
            ->willReturn(true);

        // Expect calls to vendor:publish
        $command->expects($this->exactly(2))
            ->method('call')
            ->willReturnCallback(function ($command, $params) {
                static $callNumber = 0;
                $callNumber++;

                if ($callNumber === 1) {
                    $this->assertEquals('vendor:publish', $command);
                    $this->assertEquals([
                        '--tag' => 'abac-config',
                        '--force' => true,
                    ], $params);
                } elseif ($callNumber === 2) {
                    $this->assertEquals('vendor:publish', $command);
                    $this->assertEquals([
                        '--tag' => 'abac-migrations',
                        '--force' => true,
                    ], $params);
                }

                return 0;
            });

        // Expect info() to be called with success message
        $command->expects($this->once())
            ->method('info')
            ->with('ABAC files published successfully!');

        $result = $command->handle();
        $this->assertEquals(0, $result); // Command::SUCCESS = 0
    }
}
