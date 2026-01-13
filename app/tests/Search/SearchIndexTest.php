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
use UserFrosting\Testing\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceStream;

/**
 * Tests for SearchIndex class.
 */
class SearchIndexTest extends TestCase
{
    protected string $mainSprinkle = Recipe::class;

    public function setUp(): void
    {
        parent::setUp();

        // Load test config to force the default version
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
    }

    public function testBuildIndexForVersion(): void
    {
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Build index for version 6.0
        $count = $searchIndex->buildIndex('6.0');

        // Should have indexed 9 pages (based on test data structure)
        $this->assertSame(9, $count);
    }

    public function testBuildIndexForAllVersions(): void
    {
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Build index for all versions
        $count = $searchIndex->buildIndex(null);

        // Should have indexed 9 pages (only 6.0 has test data)
        $this->assertSame(9, $count);
    }

    public function testIndexPageContent(): void
    {
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Build index
        $searchIndex->buildIndex('6.0');

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($searchIndex);
        $getCacheKeyMethod = $reflection->getMethod('getCacheKey');

        // Get cache key and retrieve index
        $cacheKey = $getCacheKeyMethod->invoke($searchIndex, '6.0');

        /** @var \Illuminate\Cache\Repository $cache */
        $cache = $this->ci->get(\Illuminate\Cache\Repository::class);
        $index = $cache->get($cacheKey);

        $this->assertIsArray($index);
        $this->assertNotEmpty($index);

        // Check first page structure
        $firstPage = $index[0];
        $this->assertArrayHasKey('title', $firstPage);
        $this->assertArrayHasKey('slug', $firstPage);
        $this->assertArrayHasKey('route', $firstPage);
        $this->assertArrayHasKey('content', $firstPage);
        $this->assertArrayHasKey('version', $firstPage);

        // Content should be plain text (no HTML tags)
        $this->assertStringNotContainsString('<', $firstPage['content']);
        $this->assertStringNotContainsString('>', $firstPage['content']);
    }

    public function testStripHtmlTags(): void
    {
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($searchIndex);
        $method = $reflection->getMethod('stripHtmlTags');

        // Test with HTML content
        $html = '<h1>Title</h1><p>This is a <strong>test</strong> paragraph.</p><pre><code>some code</code></pre>';
        $plain = $method->invoke($searchIndex, $html);

        $this->assertStringNotContainsString('<h1>', $plain);
        $this->assertStringNotContainsString('<p>', $plain);
        $this->assertStringNotContainsString('<strong>', $plain);
        $this->assertStringContainsString('Title', $plain);
        $this->assertStringContainsString('test', $plain);
        $this->assertStringContainsString('some code', $plain);
    }

    public function testClearIndex(): void
    {
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Build index
        $searchIndex->buildIndex('6.0');

        // Clear index
        $searchIndex->clearIndex('6.0');

        // Verify cache is cleared
        $reflection = new \ReflectionClass($searchIndex);
        $getCacheKeyMethod = $reflection->getMethod('getCacheKey');
        $cacheKey = $getCacheKeyMethod->invoke($searchIndex, '6.0');

        /** @var \Illuminate\Cache\Repository $cache */
        $cache = $this->ci->get(\Illuminate\Cache\Repository::class);
        $index = $cache->get($cacheKey);

        $this->assertNull($index);
    }

    public function testClearAllIndexes(): void
    {
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Build index for all versions
        $searchIndex->buildIndex(null);

        // Clear all indexes
        $searchIndex->clearIndex(null);

        // Verify cache is cleared
        $reflection = new \ReflectionClass($searchIndex);
        $getCacheKeyMethod = $reflection->getMethod('getCacheKey');
        $cacheKey = $getCacheKeyMethod->invoke($searchIndex, '6.0');

        /** @var \Illuminate\Cache\Repository $cache */
        $cache = $this->ci->get(\Illuminate\Cache\Repository::class);
        $index = $cache->get($cacheKey);

        $this->assertNull($index);
    }

    public function testFlattenTree(): void
    {
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Build index to get tree
        $searchIndex->buildIndex('6.0');

        // Use reflection to access the repository and get tree
        /** @var \UserFrosting\Learn\Documentation\DocumentationRepository $repository */
        $repository = $this->ci->get(\UserFrosting\Learn\Documentation\DocumentationRepository::class);
        $tree = $repository->getTree('6.0');

        // Use reflection to test flattenTree
        $reflection = new \ReflectionClass($searchIndex);
        $method = $reflection->getMethod('flattenTree');

        $flat = $method->invoke($searchIndex, $tree);

        // Should have 9 pages total
        $this->assertCount(9, $flat);

        // Verify they're all PageResource objects
        foreach ($flat as $page) {
            $this->assertInstanceOf(\UserFrosting\Learn\Documentation\PageResource::class, $page);
        }
    }
}
