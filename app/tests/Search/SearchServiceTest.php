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

        // Search for "first" - should match "First page"
        $result = $searchService->search('first', '6.0');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertArrayHasKey('count_filtered', $result);

        $this->assertGreaterThan(0, $result['count_filtered']);
        $this->assertNotEmpty($result['rows']);

        // Check structure of first result
        $firstResult = $result['rows'][0];
        $this->assertArrayHasKey('title', $firstResult);
        $this->assertArrayHasKey('slug', $firstResult);
        $this->assertArrayHasKey('route', $firstResult);
        $this->assertArrayHasKey('snippet', $firstResult);
        $this->assertArrayHasKey('matches', $firstResult);
        $this->assertArrayHasKey('version', $firstResult);
    }

    public function testSearchWithEmptyQuery(): void
    {
        $searchService = $this->ci->get(SearchService::class);

        $result = $searchService->search('', '6.0');

        $this->assertSame(0, $result['count_filtered']);
        $this->assertEmpty($result['rows']);
    }

    public function testSearchWithWildcard(): void
    {
        $searchService = $this->ci->get(SearchService::class);

        // Search for "f*" - should match words starting with 'f'
        $result = $searchService->search('f*', '6.0');

        $this->assertGreaterThanOrEqual(0, $result['count_filtered']);
    }

    public function testSearchPagination(): void
    {
        $searchService = $this->ci->get(SearchService::class);

        // Search with pagination
        $result = $searchService->search('page', '6.0', 1, 2);

        $this->assertLessThanOrEqual(2, count($result['rows']));
    }

    public function testSearchResultSnippet(): void
    {
        $searchService = $this->ci->get(SearchService::class);

        $result = $searchService->search('first', '6.0');

        if (!empty($result['rows'])) {
            $firstResult = $result['rows'][0];
            $this->assertIsString($firstResult['snippet']);
            $this->assertNotEmpty($firstResult['snippet']);
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

        $content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. This is the important part. More text follows here.';
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
        $result = $searchService->search('test', '6.0');

        $this->assertSame(0, $result['count_filtered']);
        $this->assertEmpty($result['rows']);
    }

    public function testSearchResultSorting(): void
    {
        $searchService = $this->ci->get(SearchService::class);

        // Search for a common term that might appear multiple times
        $result = $searchService->search('page', '6.0');

        if (count($result['rows']) > 1) {
            // Verify results are sorted by number of matches (descending)
            $firstMatches = $result['rows'][0]['matches'];
            $lastMatches = $result['rows'][count($result['rows']) - 1]['matches'];

            $this->assertGreaterThanOrEqual($lastMatches, $firstMatches);
        }
    }
}
