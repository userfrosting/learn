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
use UserFrosting\Learn\Search\SearchSprunje;
use UserFrosting\Testing\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceStream;

/**
 * Tests for SearchSprunje class.
 */
class SearchSprunjeTest extends TestCase
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

        // Make sure we have 2 results as per size
        $this->assertCount(2, $result['rows']);
        $this->assertSame(2, $result['size']);
        $this->assertSame(1, $result['page']);
    }

    public function testForDefaultSizePageAndVersion(): void
    {
        $searchSprunje = $this->ci->get(SearchSprunje::class);

        // Search with all default parameters via Sprunje
        $searchSprunje->setQuery('page');
        $result = $searchSprunje->getResultSet();

        $this->assertArrayHasKey('rows', $result);

        // Make sure we have 2 results as per size
        $this->assertGreaterThanOrEqual(2, $result['rows']);
        $this->assertSame(10, $result['size']);
        $this->assertSame(1, $result['page']);
    }

    public function testSearchSprunjeWithEmptyIndex(): void
    {
        $searchSprunje = $this->ci->get(SearchSprunje::class);
        $searchIndex = $this->ci->get(SearchIndex::class);
        $cache = $this->ci->get(\Illuminate\Cache\Repository::class);

        // Mock the SearchIndex to return empty array
        $reflection = new \ReflectionClass($searchIndex);
        $getCacheKeyMethod = $reflection->getMethod('getCacheKey');
        $cacheKey = $getCacheKeyMethod->invoke($searchIndex, '6.0');

        // Put empty array in cache to simulate no indexed pages
        $cache->put($cacheKey, [], 60);

        // Set query and version
        $searchSprunje
            ->setQuery('test')
            ->setVersion('6.0');

        // getItems should return empty collection when index is empty
        $items = $searchSprunje->getItems();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $items);
        $this->assertSame(0, $items->count(), 'Should return empty collection when index is empty');
    }

    public function testSearchSprunjeWithNullVersion(): void
    {
        // Version can be null if not defined in config
        $config = $this->ci->get(Config::class);
        $config->set('learn.versions.latest', null);

        $searchSprunje = $this->ci->get(SearchSprunje::class);

        // Search with all default parameters via Sprunje
        $searchSprunje->setQuery('page');
        $result = $searchSprunje->getResultSet();

        $this->assertArrayHasKey('rows', $result);

        // Make sure we have 2 results as per size
        $this->assertSame(0, $result['count']);
        $this->assertCount(0, $result['rows']);
    }

    public function testSearchSprunjeGetVersionDefault(): void
    {
        $searchSprunje = $this->ci->get(SearchSprunje::class);

        // getVersion should be default before setting
        $this->assertSame('6.0', $searchSprunje->getVersion());
    }

    public function testSearchSprunjeGetVersionAfterSet(): void
    {
        $searchSprunje = $this->ci->get(SearchSprunje::class);

        // Set version and verify getVersion returns it
        $searchSprunje->setVersion('5.92');
        $this->assertSame('5.92', $searchSprunje->getVersion());
    }

    public function testSearchSprunjeGetVersionAfterSetNull(): void
    {
        $searchSprunje = $this->ci->get(SearchSprunje::class);

        // Set version to null, should default to latest from config
        $searchSprunje->setVersion(null);
        $this->assertSame('6.0', $searchSprunje->getVersion(), 'Should use latest version from config when set to null');
    }
}
