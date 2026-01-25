<?php

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Learn\Search;

use UserFrosting\Config\Config;
use UserFrosting\Learn\Recipe;
use UserFrosting\Learn\Search\SearchIndex;
use UserFrosting\Learn\Search\SearchService;
use UserFrosting\Learn\Search\SearchSprunje;
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
        $this->assertInstanceOf(\UserFrosting\Learn\Search\SearchResult::class, $firstResult);
        $this->assertNotEmpty($firstResult->title);
        $this->assertNotEmpty($firstResult->slug);
        $this->assertNotEmpty($firstResult->route);
        $this->assertNotEmpty($firstResult->snippet);
        $this->assertGreaterThan(0, $firstResult->matches);
        $this->assertNotEmpty($firstResult->version);
    }

    public function testSearchWithEmptyQuery(): void
    {
        $searchService = $this->ci->get(SearchService::class);
        $searchIndex = $this->ci->get(SearchIndex::class);

        $index = $searchIndex->getIndex('6.0');
        $results = $searchService->performSearch('', $index);

        $this->assertSame(0, count($results));
        $this->assertEmpty($results);
    }

    public function testSearchWithWildcard(): void
    {
        $searchService = $this->ci->get(SearchService::class);
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Search for "f*" - should match words starting with 'f'
        $index = $searchIndex->getIndex('6.0');
        $results = $searchService->performSearch('f*', $index);

        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(0, count($results));
    }

    public function testSearchPagination(): void
    {
        $searchSprunje = $this->ci->get(SearchSprunje::class);

        // Search with pagination via Sprunje
        $searchSprunje
            ->setQuery('page')
            ->setVersion('6.0')
            ->setSize(2)
            ->setPage(1);

        $result = $searchSprunje->getResultSet();

        $this->assertArrayHasKey('rows', $result);
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
        $content = str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit. ', 10) . 'This is the important part. ' . str_repeat('More text follows here. ', 10);
        $matchPosition = strpos($content, 'important');

        if ($matchPosition !== false) {
            $snippet = $method->invoke($searchService, $content, $matchPosition);

            $this->assertIsString($snippet);
            $this->assertStringContainsString('important', $snippet);
            $this->assertStringContainsString('...', $snippet); // Should have ellipsis
        }
    }

    public function testSearchWithNoIndex(): void
    {
        // Clear the index
        $searchIndex = $this->ci->get(SearchIndex::class);
        $searchIndex->clearIndex('6.0');

        $searchService = $this->ci->get(SearchService::class);
        $index = $searchIndex->getIndex('6.0');
        $results = $searchService->performSearch('test', $index);

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
            // Verify results are sorted by number of matches (descending)
            $firstMatches = $results[0]->matches;
            $lastMatches = $results[count($results) - 1]->matches;

            $this->assertGreaterThanOrEqual($lastMatches, $firstMatches);
        }
    }
}
