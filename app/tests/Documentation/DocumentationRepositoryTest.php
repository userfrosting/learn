<?php

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Learn\Documentation;

use UserFrosting\Config\Config;
use UserFrosting\Learn\Documentation\DocumentationRepository;
use UserFrosting\Learn\Documentation\PageNotFoundException;
use UserFrosting\Learn\PagesManager;
use UserFrosting\Learn\Recipe;
use UserFrosting\Testing\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceStream;

/**
 * Tests for PagesManager.
 */
class DocumentationRepositoryTest extends TestCase
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
            '5.0' => '5.0',
        ]);

        // Use the test pages directory
        /** @var ResourceLocatorInterface $locator */
        $locator = $this->ci->get(ResourceLocatorInterface::class);
        $locator->removeStream('pages');
        $locator->addStream(new ResourceStream('pages', shared: true, readonly: true, path: __DIR__ . '/../pages'));

        // Make sure setup is ok
        $this->assertCount(10, $locator->listResources('pages://'));
    }

    public function testGetTree(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);
        $files = $pagesManager->getTree();

        // Assert tree structure contains 3 top level files
        $this->assertCount(3, $files);
        $this->assertSame([
            'first',
            'second',
            'third'
        ], array_values(array_map(fn ($p) => $p->getSlug(), (array) $files)));
        $this->assertSame(['', '', ''], array_values(array_map(fn ($p) => $p->getParentSlug(), (array) $files)));

        // Assert children count of each top-level file
        $this->assertCount(0, $files[0]->getChildren());
        $this->assertCount(2, $files[1]->getChildren());
        $this->assertCount(2, $files[2]->getChildren());

        // Check children of "second"
        // It has two children: "second/child1" and "second/child2", that don't
        // have children of their own
        $secondChildren = $files[1]->getChildren();
        $this->assertCount(2, $secondChildren);
        $this->assertCount(0, $secondChildren[0]->getChildren());
        $this->assertCount(0, $secondChildren[1]->getChildren());
        $this->assertSame('second/alpha', $secondChildren[0]->getSlug());
        $this->assertSame('second/beta', $secondChildren[1]->getSlug());

        // Check children of "third"
        // It has two children: "third/foo" and "third/bar". foo has two children
        // of its own: "third/foo/grandchild1" and "third/foo/grandchild2"
        $thirdChildren = $files[2]->getChildren();
        $this->assertCount(2, $thirdChildren);
        $this->assertSame('third/foo', $thirdChildren[0]->getSlug());
        $this->assertSame('third/bar', $thirdChildren[1]->getSlug());
        $this->assertCount(2, $thirdChildren[0]->getChildren());
        $this->assertCount(0, $thirdChildren[1]->getChildren());
        $this->assertSame('third/foo/grandchild1', $thirdChildren[0]->getChildren()[0]->getSlug());
        $this->assertSame('third/foo/grandchild2', $thirdChildren[0]->getChildren()[1]->getSlug());
    }

    public function testGetPage(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // Test getting various pages by slug
        $page = $pagesManager->getPage('first');
        $this->assertSame('First page', $page->getTitle());
        $page = $pagesManager->getPage('third/foo/grandchild2');
        $this->assertSame('Foo Bar Page', $page->getTitle());

        // Test getting the home page (empty slug)
        $page = $pagesManager->getPage('');
        $this->assertSame('First page', $page->getTitle());

        // Test both with version
        $page = $pagesManager->getPage('first', '6.0');
        $this->assertSame('First page', $page->getTitle());
    }

    public function testGetPageForNotFound(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // Test getting a non-existing page
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Page not found: non-existing-page (version: 6.0)');
        $pagesManager->getPage('non-existing-page');
    }

    public function testGetPageForNotFoundAndHomePage(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // Test getting a non-existing page
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Page not found: (version: 5.0)');
        $pagesManager->getPage('', '5.0'); // This version does not exist in test pages
    }

    public function testGetAlternateVersions(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);
        $page = $pagesManager->getPage('first');

        // Test getting alternate versions for a page that exists in multiple versions
        $alternates = $pagesManager->getAlternateVersions($page);
        $this->assertCount(2, $alternates);
        $this->assertArrayHasKey('6.0 Beta', $alternates);
        $this->assertArrayNotHasKey('5.1', $alternates);
        $this->assertArrayHasKey('5.0', $alternates);
    }

    /** Use the real file to test the getTemplate */
    public function testGetTemplate(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // Uses the file name 'docs'
        $page = $pagesManager->getPage('first');
        $this->assertSame('docs', $page->getTemplate());

        // Uses the file name 'chapter'
        $page = $pagesManager->getPage('third');
        $this->assertSame('chapter', $page->getTemplate());

        // Uses the template from front-matter (foos) over the file name (chapter)
        $page = $pagesManager->getPage('third/foo');
        $this->assertSame('foos', $page->getTemplate());
    }

    public function testGetBreadcrumbsForPage(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // Test breadcrumbs for a top-level page
        $page = $pagesManager->getPage('first');
        $breadcrumbs = $pagesManager->getBreadcrumbsForPage($page);

        $this->assertCount(2, $breadcrumbs);
        $this->assertSame('Home', $breadcrumbs[0]['label']);
        $this->assertSame('First page', $breadcrumbs[1]['label']);

        // Test breadcrumbs for a nested page
        $page = $pagesManager->getPage('third/foo/grandchild1');
        $breadcrumbs = $pagesManager->getBreadcrumbsForPage($page);

        $this->assertCount(4, $breadcrumbs);
        $this->assertSame('Home', $breadcrumbs[0]['label']);
        $this->assertSame('Third Page', $breadcrumbs[1]['label']);
        $this->assertSame('Foo Page', $breadcrumbs[2]['label']);
        $this->assertSame('Foo Foo Page', $breadcrumbs[3]['label']);

        // Verify URLs are generated
        foreach ($breadcrumbs as $breadcrumb) {
            $this->assertNotEmpty($breadcrumb['url']);
        }
    }

    public function testGetVersionedImage(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // Test successful image retrieval
        $resource = $pagesManager->getVersionedImage('6.0', 'test.jpg');
        $this->assertNotNull($resource);
        $this->assertSame('test.jpg', $resource->getBasename());
    }

    public function testGetVersionedImageWithEmptyVersion(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // Test with empty version string (should use latest)
        $resource = $pagesManager->getVersionedImage('', 'test.jpg');
        $this->assertNotNull($resource);
        $this->assertSame('test.jpg', $resource->getBasename());
    }

    public function testGetVersionedImageNotFound(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Image not found: nonexistent.png (version: 6.0)');
        $pagesManager->getVersionedImage('6.0', 'nonexistent.png');
    }

    public function testCacheKeyGeneration(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($pagesManager);
        $method = $reflection->getMethod('getCacheKey');

        $key = $method->invoke($pagesManager, 'page', 'test-slug');
        $this->assertSame('learn.page.test-slug', $key);

        $key = $method->invoke($pagesManager, 'tree', '6.0');
        $this->assertSame('learn.tree.6.0', $key);
    }

    public function testCacheTtlConfiguration(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($pagesManager);
        $method = $reflection->getMethod('getCacheTtl');

        $ttl = $method->invoke($pagesManager);
        $this->assertIsInt($ttl);
        $this->assertGreaterThan(0, $ttl);
    }

    public function testCacheBypassesWhenDisabled(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        /** @var Config $config */
        $config = $this->ci->get(Config::class);
        $config->set('learn.cache.enabled', false);

        $mockCache = $this->getMockBuilder(\Illuminate\Cache\Repository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['remember'])
            ->getMock();

        $mockCache->expects($this->never())
            ->method('remember');

        $reflection = new \ReflectionClass($pagesManager);
        $cacheProperty = $reflection->getProperty('cache');
        $cacheProperty->setValue($pagesManager, $mockCache);

        $files = $pagesManager->getTree();

        $this->assertNotEmpty($files);
    }

    public function testCacheUsedWhenEnabled(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        /** @var Config $config */
        $config = $this->ci->get(Config::class);
        $config->set('learn.cache.enabled', true);

        $mockCache = $this->getMockBuilder(\Illuminate\Cache\Repository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['remember'])
            ->getMock();

        $mockCache->expects($this->once())
            ->method('remember')
            ->willReturnCallback(fn ($key, $ttl, $callback) => $callback());

        $reflection = new \ReflectionClass($pagesManager);
        $cacheProperty = $reflection->getProperty('cache');
        $cacheProperty->setValue($pagesManager, $mockCache);

        $files = $pagesManager->getTree();

        $this->assertNotEmpty($files);
    }

    public function testGetTreeWithEmptyPages(): void
    {
        /** @var ResourceLocatorInterface $locator */
        $locator = $this->ci->get(ResourceLocatorInterface::class);

        // Remove the test pages stream and add an empty one
        $locator->removeStream('pages');
        $locator->addStream(new ResourceStream('pages', shared: true, readonly: true, path: __DIR__ . '/empty-pages'));

        $pagesManager = $this->ci->get(DocumentationRepository::class);
        $files = $pagesManager->getTree();

        $this->assertIsArray($files);
        $this->assertCount(0, $files);
    }

    public function testGetPageWithSpecificVersion(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // Test getting page with explicit version
        $page = $pagesManager->getPage('second', '6.0');
        $this->assertSame('second', $page->getTitle());
        $this->assertSame('6.0', $page->getVersion()->id);

        // Test getting nested page with version
        $page = $pagesManager->getPage('third', '6.0');
        $this->assertSame('Third Page', $page->getTitle());
        $this->assertSame('6.0', $page->getVersion()->id);
    }

    public function testGetAlternateVersionsWithPath(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // Test alternate versions for a specific path
        $alternates = $pagesManager->getAlternateVersions('first');

        $this->assertIsArray($alternates);
        $this->assertCount(2, $alternates);
        $this->assertArrayHasKey('6.0 Beta', $alternates);
        $this->assertArrayHasKey('5.0', $alternates);

        // Verify URLs are generated for each version
        foreach ($alternates as $url) {
            $this->assertIsString($url);
            $this->assertNotEmpty($url);
        }
    }

    public function testGetTreeSorting(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);
        $files = $pagesManager->getTree();

        // Verify the files are sorted correctly (first, second, third)
        $slugs = array_map(fn ($p) => $p->getSlug(), $files);
        $expectedOrder = ['first', 'second', 'third'];

        $this->assertSame($expectedOrder, $slugs);

        // Test children are also sorted
        $secondChildren = $files[1]->getChildren();
        $childSlugs = array_map(fn ($p) => $p->getSlug(), $secondChildren);
        $expectedChildOrder = ['second/alpha', 'second/beta'];

        $this->assertSame($expectedChildOrder, $childSlugs);
    }

    public function testGetPagesChildren(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($pagesManager);
        $method = $reflection->getMethod('getPagesChildren');

        // Get all pages first
        $getPages = $reflection->getMethod('getPages');

        /** @var \UserFrosting\Learn\Documentation\VersionValidator $versionValidator */
        $versionValidator = $this->ci->get(\UserFrosting\Learn\Documentation\VersionValidator::class);
        $version = $versionValidator->getVersion('6.0');

        $allPages = $getPages->invoke($pagesManager, $version);

        // Test getting children for empty parent (top-level pages)
        $topLevel = $method->invoke($pagesManager, $allPages, '');
        $this->assertCount(3, $topLevel);

        // Test getting children for specific parent
        $secondChildren = $method->invoke($pagesManager, $allPages, 'second');
        $this->assertCount(2, $secondChildren);

        $thirdChildren = $method->invoke($pagesManager, $allPages, 'third');
        $this->assertCount(2, $thirdChildren);
    }

    public function testGetNextPageForPage(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // Test navigation from first page
        $page = $pagesManager->getPage('first');
        $nextPage = $pagesManager->getNextPageForPage($page);
        $this->assertNotNull($nextPage);
        $this->assertSame('second', $nextPage->getSlug());

        // Test navigation from a middle page
        $page = $pagesManager->getPage('second');
        $nextPage = $pagesManager->getNextPageForPage($page);
        $this->assertNotNull($nextPage);
        $this->assertSame('second/alpha', $nextPage->getSlug());

        // Test navigation from a nested page
        $page = $pagesManager->getPage('second/alpha');
        $nextPage = $pagesManager->getNextPageForPage($page);
        $this->assertNotNull($nextPage);
        $this->assertSame('second/beta', $nextPage->getSlug());

        // Test navigation continues to next top-level after nested pages
        $page = $pagesManager->getPage('second/beta');
        $nextPage = $pagesManager->getNextPageForPage($page);
        $this->assertNotNull($nextPage);
        $this->assertSame('third', $nextPage->getSlug());
    }

    public function testGetNextPageForLastPage(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // The last page in the tree should return null
        $page = $pagesManager->getPage('third/bar');
        $nextPage = $pagesManager->getNextPageForPage($page);
        $this->assertNull($nextPage);
    }

    public function testGetPreviousPageForPage(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // Test navigation from second page back to first
        $page = $pagesManager->getPage('second');
        $previousPage = $pagesManager->getPreviousPageForPage($page);
        $this->assertNotNull($previousPage);
        $this->assertSame('first', $previousPage->getSlug());

        // Test navigation within nested pages
        $page = $pagesManager->getPage('second/beta');
        $previousPage = $pagesManager->getPreviousPageForPage($page);
        $this->assertNotNull($previousPage);
        $this->assertSame('second/alpha', $previousPage->getSlug());

        // Test navigation from nested page to parent
        $page = $pagesManager->getPage('second/alpha');
        $previousPage = $pagesManager->getPreviousPageForPage($page);
        $this->assertNotNull($previousPage);
        $this->assertSame('second', $previousPage->getSlug());

        // Test navigation from a deeply nested page
        $page = $pagesManager->getPage('third/foo/grandchild2');
        $previousPage = $pagesManager->getPreviousPageForPage($page);
        $this->assertNotNull($previousPage);
        $this->assertSame('third/foo/grandchild1', $previousPage->getSlug());
    }

    public function testGetPreviousPageForFirstPage(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // The first page in the tree should return null
        $page = $pagesManager->getPage('first');
        $previousPage = $pagesManager->getPreviousPageForPage($page);
        $this->assertNull($previousPage);
    }

    public function testGetFlattenedTree(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($pagesManager);
        $method = $reflection->getMethod('getFlattenedTree');

        $flatPages = $method->invoke($pagesManager, '6.0');

        // Should have all 9 pages in flattened order
        $this->assertCount(9, $flatPages);

        // Verify the order follows depth-first traversal (check array keys)
        $expectedOrder = [
            'first',
            'second',
            'second/alpha',
            'second/beta',
            'third',
            'third/foo',
            'third/foo/grandchild1',
            'third/foo/grandchild2',
            'third/bar',
        ];

        $actualOrder = array_keys($flatPages);
        $this->assertSame($expectedOrder, $actualOrder);

        // Verify each key maps to the correct page
        foreach ($expectedOrder as $slug) {
            $this->assertArrayHasKey($slug, $flatPages);
            $this->assertSame($slug, $flatPages[$slug]->getSlug());
        }
    }

    public function testNavigationSequence(): void
    {
        $pagesManager = $this->ci->get(DocumentationRepository::class);

        // Test complete forward navigation through the tree
        $page = $pagesManager->getPage('first');
        $visited = [$page->getSlug()];

        while ($nextPage = $pagesManager->getNextPageForPage($page)) {
            $visited[] = $nextPage->getSlug();
            $page = $nextPage;
        }

        // Should visit all 9 pages in order
        $this->assertCount(9, $visited);

        // Test backward navigation
        $page = $pagesManager->getPage('third/bar');
        $visitedBackward = [$page->getSlug()];

        while ($previousPage = $pagesManager->getPreviousPageForPage($page)) {
            $visitedBackward[] = $previousPage->getSlug();
            $page = $previousPage;
        }

        // Should visit all 9 pages in reverse order
        $this->assertCount(9, $visitedBackward);
        $this->assertSame(array_reverse($visited), $visitedBackward);
    }
}
