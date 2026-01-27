<?php

declare(strict_types=1);

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Learn\Bakery;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use UserFrosting\Learn\Bakery\SearchIndexCommand;
use UserFrosting\Learn\Recipe;
use UserFrosting\Learn\Search\SearchIndex;
use UserFrosting\Testing\TestCase;

/**
 * Tests for SearchIndexCommand.
 */
class SearchIndexCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected string $mainSprinkle = Recipe::class;

    /**
     * Test building index for all versions (no options).
     */
    public function testBuildIndexAllVersions(): void
    {
        // Mock SearchIndex
        $searchIndex = Mockery::mock(SearchIndex::class);
        $searchIndex->shouldReceive('buildIndex')
            ->once()
            ->with(null)
            ->andReturn(42);

        // Create command and tester
        $command = new SearchIndexCommand($searchIndex);
        $tester = new CommandTester($command);

        // Execute command
        $exitCode = $tester->execute([]);

        // Assertions
        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Building search index for all versions', $tester->getDisplay());
        $this->assertStringContainsString('Indexed 42 pages', $tester->getDisplay());
    }

    /**
     * Test building index for specific version.
     */
    public function testBuildIndexSpecificVersion(): void
    {
        // Mock SearchIndex
        $searchIndex = Mockery::mock(SearchIndex::class);
        $searchIndex->shouldReceive('buildIndex')
            ->once()
            ->with('6.0')
            ->andReturn(15);

        // Create command and tester
        $command = new SearchIndexCommand($searchIndex);
        $tester = new CommandTester($command);

        // Execute command with version option
        $exitCode = $tester->execute(['--doc-version' => '6.0']);

        // Assertions
        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Building search index for version 6.0', $tester->getDisplay());
        $this->assertStringContainsString('Indexed 15 pages', $tester->getDisplay());
    }

    /**
     * Test clearing index before building.
     */
    public function testClearIndexBeforeBuilding(): void
    {
        // Mock SearchIndex
        $searchIndex = Mockery::mock(SearchIndex::class);
        $searchIndex->shouldReceive('clearIndex')
            ->once()
            ->with(null);
        $searchIndex->shouldReceive('buildIndex')
            ->once()
            ->with(null)
            ->andReturn(30);

        // Create command and tester
        $command = new SearchIndexCommand($searchIndex);
        $tester = new CommandTester($command);

        // Execute command with clear option
        $exitCode = $tester->execute(['--clear' => true]);

        // Assertions
        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Clearing search index', $tester->getDisplay());
        $this->assertStringContainsString('Search index cleared', $tester->getDisplay());
        $this->assertStringContainsString('Indexed 30 pages', $tester->getDisplay());
    }

    /**
     * Test clearing and building for specific version.
     */
    public function testClearAndBuildSpecificVersion(): void
    {
        // Mock SearchIndex
        $searchIndex = Mockery::mock(SearchIndex::class);
        $searchIndex->shouldReceive('clearIndex')
            ->once()
            ->with('5.1');
        $searchIndex->shouldReceive('buildIndex')
            ->once()
            ->with('5.1')
            ->andReturn(20);

        // Create command and tester
        $command = new SearchIndexCommand($searchIndex);
        $tester = new CommandTester($command);

        // Execute command with both options
        $exitCode = $tester->execute([
            '--doc-version' => '5.1',
            '--clear'       => true,
        ]);

        // Assertions
        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Clearing search index', $tester->getDisplay());
        $this->assertStringContainsString('Building search index for version 5.1', $tester->getDisplay());
        $this->assertStringContainsString('Indexed 20 pages', $tester->getDisplay());
    }

    /**
     * Test handling exception during index building.
     */
    public function testBuildIndexException(): void
    {
        // Mock SearchIndex to throw exception
        $searchIndex = Mockery::mock(SearchIndex::class);
        $searchIndex->shouldReceive('buildIndex')
            ->once()
            ->with(null)
            ->andThrow(new \RuntimeException('Index build failed'));

        // Create command and tester
        $command = new SearchIndexCommand($searchIndex);
        $tester = new CommandTester($command);

        // Execute command
        $exitCode = $tester->execute([]);

        // Assertions
        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Failed to build search index', $tester->getDisplay());
        $this->assertStringContainsString('Index build failed', $tester->getDisplay());
    }

    /**
     * Test command configuration.
     */
    public function testCommandConfiguration(): void
    {
        $searchIndex = Mockery::mock(SearchIndex::class);
        $command = new SearchIndexCommand($searchIndex);

        $this->assertSame('search:index', $command->getName());
        $this->assertStringContainsString('Build or rebuild', $command->getDescription());

        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasOption('doc-version'));
        $this->assertTrue($definition->hasOption('clear'));
    }
}
