<?php

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Learn\Search;

use InvalidArgumentException;
use UserFrosting\Config\Config;
use UserFrosting\Learn\Recipe;
use UserFrosting\Learn\Search\SearchIndex;
use UserFrosting\Learn\Search\SearchResult;
use UserFrosting\Learn\Search\SearchService;
use UserFrosting\Testing\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceStream;

/**
 * Tests for SearchService class.
 */
class SearchServiceTest extends TestCase
{
    protected string $mainSprinkle = Recipe::class;

    public function setUp(): void
    {
        parent::setUp();

        // Load test config
        /** @var Config $config */
        $config = $this->ci->get(Config::class);
        $config->set('learn.versions.latest', '6.0');
        $config->set('learn.versions.available', [
            '6.0' => '6.0 Beta',
        ]);

        // Use the test pages directory
        /** @var ResourceLocatorInterface $locator */
        $locator = $this->ci->get(ResourceLocatorInterface::class);
        $locator->removeStream('pages');
        $locator->addStream(new ResourceStream('pages', shared: true, readonly: true, path: __DIR__ . '/../pages'));

        // Build index for testing
        $searchIndex = $this->ci->get(SearchIndex::class);
        $searchIndex->buildIndex('6.0');
    }

    public function testSearchWithPlainText(): void
    {
        $searchService = $this->ci->get(SearchService::class);
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Get index and search for "first" - should match "First page"
        $index = $searchIndex->getIndex('6.0');
        $results = $searchService->performSearch('first', $index);

        $this->assertIsArray($results);
        $this->assertGreaterThan(0, count($results));

        // Check structure of first result
        $firstResult = $results[0];
        $this->assertInstanceOf(SearchResult::class, $firstResult);
        $this->assertNotEmpty($firstResult->title);
        $this->assertNotEmpty($firstResult->slug);
        $this->assertNotEmpty($firstResult->route);
        $this->assertNotEmpty($firstResult->snippet);
        $this->assertGreaterThan(0, $firstResult->score);
        $this->assertNotEmpty($firstResult->version);
    }

    public function testSearchWithEmptyQuery(): void
    {
        $searchService = $this->ci->get(SearchService::class);
        $searchIndex = $this->ci->get(SearchIndex::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query must be at least 3 characters long');

        $index = $searchIndex->getIndex('6.0');
        $searchService->performSearch('', $index);
    }

    public function testSearchWithShortQuery(): void
    {
        $searchService = $this->ci->get(SearchService::class);
        $searchIndex = $this->ci->get(SearchIndex::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query must be at least 3 characters long');

        $index = $searchIndex->getIndex('6.0');
        $searchService->performSearch('ab', $index); // Only 2 characters
    }

    public function testSearchWithWildcard(): void
    {
        $searchService = $this->ci->get(SearchService::class);
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Search for "pag*" - should match words starting with 'pag'
        $index = $searchIndex->getIndex('6.0');
        $results = $searchService->performSearch('pag*', $index);

        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(0, count($results));
    }

    public function testSearchResultSnippet(): void
    {
        $searchService = $this->ci->get(SearchService::class);
        $searchIndex = $this->ci->get(SearchIndex::class);

        $index = $searchIndex->getIndex('6.0');
        $results = $searchService->performSearch('first', $index);

        if (count($results) > 0) {
            $firstResult = $results[0];
            $this->assertIsString($firstResult->snippet);
            $this->assertNotEmpty($firstResult->snippet);
        }
    }

    public function testSearchPlainMethod(): void
    {
        $searchService = $this->ci->get(SearchService::class);

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($searchService);
        $method = $reflection->getMethod('searchPlain');

        $content = 'This is a test content with multiple test words.';
        $matches = $method->invoke($searchService, 'test', $content);

        $this->assertIsArray($matches);
        $this->assertCount(2, $matches); // Should find 2 matches
    }

    public function testGenerateSnippet(): void
    {
        $searchService = $this->ci->get(SearchService::class);

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($searchService);
        $method = $reflection->getMethod('generateSnippet');

        // Create long content that exceeds snippet length (default 150 chars)
        $content = str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit. ', 10) .
                   'This is the important part. ' . str_repeat('More text follows here. ', 10);
        $matchPosition = strpos($content, 'important');

        if ($matchPosition !== false) {
            $snippet = $method->invoke($searchService, $content, $matchPosition, 'important');

            $this->assertIsString($snippet);
            $this->assertStringContainsString('important', $snippet);
            $this->assertStringContainsString('...', $snippet); // Should have ellipsis
        }
    }

    public function testSearchWithEmptyIndex(): void
    {
        $searchService = $this->ci->get(SearchService::class);
        $results = $searchService->performSearch('first', []);

        $this->assertSame(0, count($results));
        $this->assertEmpty($results);
    }

    public function testSearchResultSorting(): void
    {
        $searchService = $this->ci->get(SearchService::class);
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Search for a common term that might appear multiple times
        $index = $searchIndex->getIndex('6.0');
        $results = $searchService->performSearch('page', $index);

        if (count($results) > 1) {
            // Verify results are sorted by score (descending)
            $firstScore = $results[0]->score;
            $lastScore = $results[count($results) - 1]->score;

            $this->assertGreaterThanOrEqual($lastScore, $firstScore);
        }

        // First tree pages should be in order : Alpha Page, Beta Page & First Page
        $this->assertSame('Alpha Page', $results[0]->title);
        $this->assertSame('Beta Page', $results[1]->title);
        $this->assertSame('First page', $results[2]->title);
    }

    public function testSelectSnippetSourceWithNoMatches(): void
    {
        $searchService = $this->ci->get(SearchService::class);

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($searchService);
        $method = $reflection->getMethod('selectSnippetSource');

        // Create a page with some content
        $page = new \UserFrosting\Learn\Search\IndexedPage(
            title: 'Test Page',
            slug: 'test-page',
            route: '/test-page',
            content: 'Test content',
            version: '6.0',
            keywords: 'test keywords',
            metadata: 'test metadata',
        );

        // Create matches array with all empty arrays (no matches)
        $matches = [
            'title'    => [],
            'keywords' => [],
            'metadata' => [],
            'content'  => [],
        ];

        $result = $method->invoke($searchService, $page, $matches);

        // Should return empty content and zero position as fallback
        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('position', $result);
        $this->assertSame('', $result['content']);
        $this->assertSame(0, $result['position']);
    }

    public function testSearchWithWildcardMethod(): void
    {
        $searchService = $this->ci->get(SearchService::class);

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($searchService);
        $method = $reflection->getMethod('searchWithWildcard');

        // Test normal wildcard search
        $content = 'This is a test with testing and tested words like attest and attestation.';
        $regex = '/test.*/i'; // Matches test, testing, tested, attest, attestation

        $matches = $method->invoke($searchService, $regex, $content);

        $this->assertIsArray($matches);
        $this->assertSame(5, count($matches));
    }

    public function testSearchWithWildcardEmptyContent(): void
    {
        $searchService = $this->ci->get(SearchService::class);

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($searchService);
        $method = $reflection->getMethod('searchWithWildcard');

        // Test with empty content - should not fail and return empty array
        $content = '';
        $regex = '/test.*/i';

        $matches = $method->invoke($searchService, $regex, $content);

        $this->assertIsArray($matches);
        $this->assertEmpty($matches, 'Should return empty array for empty content');
    }

    public function testSearchWithWildcardPregSplitFailure(): void
    {
        $searchService = $this->ci->get(SearchService::class);

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($searchService);
        $method = $reflection->getMethod('searchWithWildcard');

        // Save current PCRE settings
        $originalBacktrackLimit = ini_get('pcre.backtrack_limit');

        // Set a very low backtrack limit to force preg_split to fail
        ini_set('pcre.backtrack_limit', '1');

        // Create content that would exceed the backtrack limit
        $content = str_repeat('a ', 1000); // 1000 words of 'a'
        $regex = '/test.*/i';

        $matches = $method->invoke($searchService, $regex, $content);

        // Restore original setting
        if ($originalBacktrackLimit !== false) {
            ini_set('pcre.backtrack_limit', $originalBacktrackLimit);
        }

        // Should return empty array when preg_split fails
        $this->assertIsArray($matches);
        $this->assertEmpty($matches, 'Should return empty array when preg_split fails');
    }
}
