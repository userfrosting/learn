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
use UserFrosting\Learn\Documentation\DocumentationRepository;
use UserFrosting\Learn\Recipe;
use UserFrosting\Learn\Search\IndexedPage;
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

        // Should have indexed pages (at least some)
        $this->assertGreaterThan(0, $count, 'Should have indexed at least one page');

        // Verify it matches the number of test pages
        /** @var DocumentationRepository $repository */
        $repository = $this->ci->get(DocumentationRepository::class);
        $flatPages = $repository->getFlattenedTree('6.0');

        $this->assertSame(count($flatPages), $count, 'Index count should match actual page count');
    }

    public function testBuildIndexForAllVersions(): void
    {
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Build index for all versions
        $count = $searchIndex->buildIndex(null);

        // Verify it matches the number of test pages
        /** @var DocumentationRepository $repository */
        $repository = $this->ci->get(DocumentationRepository::class);
        $flatPages = $repository->getFlattenedTree('6.0');

        // Should have indexed pages (at least some)
        $this->assertGreaterThan(0, $count, 'Should have indexed at least one page');

        // Should have more pages than just version 6.0 alone (if multiple versions exist)
        $this->assertSame(count($flatPages), $count, 'Index count should match actual page count across all versions');
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
        $this->assertInstanceOf(IndexedPage::class, $firstPage);
        $this->assertNotEmpty($firstPage->title);
        $this->assertNotEmpty($firstPage->slug);
        $this->assertNotEmpty($firstPage->route);
        $this->assertNotEmpty($firstPage->content);
        $this->assertNotEmpty($firstPage->version);

        // Content should be plain text (no HTML tags)
        $this->assertStringNotContainsString('<', $firstPage->content);
        $this->assertStringNotContainsString('>', $firstPage->content);
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

        // Get cache key
        $reflection = new \ReflectionClass($searchIndex);
        $getCacheKeyMethod = $reflection->getMethod('getCacheKey');
        $cacheKey = $getCacheKeyMethod->invoke($searchIndex, '6.0');

        /** @var \Illuminate\Cache\Repository $cache */
        $cache = $this->ci->get(\Illuminate\Cache\Repository::class);

        // Build index
        $searchIndex->buildIndex('6.0');

        // Make sure index exists
        $index = $cache->get($cacheKey);
        $this->assertIsArray($index);
        $this->assertNotEmpty($index);

        // Clear index
        $searchIndex->clearIndex('6.0');

        // Verify cache is cleared
        $index = $cache->get($cacheKey);
        $this->assertNull($index);
    }

    public function testClearAllIndexes(): void
    {
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Get cache key
        $reflection = new \ReflectionClass($searchIndex);
        $getCacheKeyMethod = $reflection->getMethod('getCacheKey');
        $cacheKey = $getCacheKeyMethod->invoke($searchIndex, '6.0');

        // Build index for all versions
        $searchIndex->buildIndex(null);

        // Make sure index exists
        /** @var \Illuminate\Cache\Repository $cache */
        $cache = $this->ci->get(\Illuminate\Cache\Repository::class);
        $index = $cache->get($cacheKey);
        $this->assertIsArray($index);
        $this->assertNotEmpty($index);

        // Clear all indexes
        $searchIndex->clearIndex(null);
        $index = $cache->get($cacheKey);
        $this->assertNull($index);
    }

    public function testGetIndex(): void
    {
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Build index for version 6.0 so we can retrieve it
        $searchIndex->buildIndex('6.0');

        // Verify it matches the number of test pages
        /** @var DocumentationRepository $repository */
        $repository = $this->ci->get(DocumentationRepository::class);
        $flatPages = $repository->getFlattenedTree('6.0');

        // Retrieve index
        $index = $searchIndex->getIndex('6.0');
        $this->assertIsArray($index);
        $this->assertNotEmpty($index);
        $this->assertSame(count($flatPages), count($index), 'Index should contain the right number of pages');
    }

    public function testGetIndexRebuildsWhenCacheEmpty(): void
    {
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Make sure cache is cleared
        $searchIndex->clearIndex('6.0');

        // getIndex should automatically rebuild when cache is empty
        $index = $searchIndex->getIndex('6.0');

        $this->assertIsArray($index);
        $this->assertNotEmpty($index, 'Should rebuild index when cache is empty');
        $this->assertContainsOnlyInstancesOf(IndexedPage::class, $index);
    }

    public function testExtractMetadataSkipsEmptyFields(): void
    {
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Set metadata fields in config
        /** @var Config $config */
        $config = $this->ci->get(Config::class);
        $config->set('learn.search.metadata_fields', ['description', 'tags', 'category', 'author']);

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($searchIndex);
        $method = $reflection->getMethod('extractMetadata');

        // Test with mixed empty and non-empty fields
        $frontMatter = [
            'description' => 'Test description',
            'tags'        => '',  // Empty string - should be skipped
            'category'    => [],  // Empty array - should be skipped
            'author'      => 'Test Author',
        ];

        $metadata = $method->invoke($searchIndex, $frontMatter);

        // Should only include non-empty fields
        $this->assertSame('Test description Test Author', $metadata);
    }

    public function testExtractMetadataWithAllEmptyFields(): void
    {
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Set metadata fields in config
        /** @var Config $config */
        $config = $this->ci->get(Config::class);
        $config->set('learn.search.metadata_fields', ['description', 'tags']);

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($searchIndex);
        $method = $reflection->getMethod('extractMetadata');

        // Test with all empty or missing fields
        $frontMatter = [
            'description' => '',
            // 'tags' is missing
        ];

        $metadata = $method->invoke($searchIndex, $frontMatter);

        // Should return empty string
        $this->assertSame('', $metadata);
    }

    public function testExtractMetadataWithArrayFields(): void
    {
        $searchIndex = $this->ci->get(SearchIndex::class);

        // Set metadata fields in config
        /** @var Config $config */
        $config = $this->ci->get(Config::class);
        $config->set('learn.search.metadata_fields', ['tags', 'keywords']);

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($searchIndex);
        $method = $reflection->getMethod('extractMetadata');

        // Test with array fields
        $frontMatter = [
            'tags'     => ['php', 'framework', 'web'],
            'keywords' => ['userfrosting', 'tutorial'],
        ];

        $metadata = $method->invoke($searchIndex, $frontMatter);

        // Should join array values with spaces
        $this->assertStringContainsString('php framework web', $metadata);
        $this->assertStringContainsString('userfrosting tutorial', $metadata);
    }

    public function testGetIndexReturnsEmptyArrayWhenCacheCorrupted(): void
    {
        $searchIndex = $this->ci->get(SearchIndex::class);

        /** @var \Illuminate\Cache\Repository $cache */
        $cache = $this->ci->get(\Illuminate\Cache\Repository::class);

        // Mock cache to always return non-array value, even after rebuild attempt
        // Use reflection to get cache key
        $reflection = new \ReflectionClass($searchIndex);
        $getCacheKeyMethod = $reflection->getMethod('getCacheKey');
        $cacheKey = $getCacheKeyMethod->invoke($searchIndex, '6.0');

        // Create a mock cache that always returns corrupted data
        $mockCache = $this->getMockBuilder(\Illuminate\Cache\Repository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'put', 'forget'])
            ->getMock();

        $mockCache->method('get')
            ->with($cacheKey)
            ->willReturn('corrupted-data'); // Always return non-array

        $mockCache->method('put')
            ->willReturn(true);

        // Use reflection to replace the cache instance
        $cacheProperty = $reflection->getProperty('cache');
        $cacheProperty->setValue($searchIndex, $mockCache);

        // getIndex should return empty array when cache consistently returns non-array
        $index = $searchIndex->getIndex('6.0');

        $this->assertIsArray($index);
        $this->assertEmpty($index, 'Should return empty array when cache persistently returns non-array data');
    }
}
