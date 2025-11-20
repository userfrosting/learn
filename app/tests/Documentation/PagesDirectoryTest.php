<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Learn\Documentation;

use UserFrosting\Config\Config;
use UserFrosting\Learn\Documentation\PageNotFoundException;
use UserFrosting\Learn\Documentation\DocumentationRepository;
use UserFrosting\Learn\PagesManager;
use UserFrosting\Learn\Recipe;
use UserFrosting\Testing\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceStream;

/**
 * Tests for PagesManager.
 */
class PagesDirectoryTest extends TestCase
{
    protected string $mainSprinkle = Recipe::class;

    public function setUp(): void
    {
        parent::setUp();

        // Load test config to force the default version
        /** @var Config $config */
        $config = $this->ci->get(Config::class);
        $config->set('site.versions.latest', '6.0');
        $config->set('site.versions.available', [
            '6.0' => '6.0 Beta',
            '5.0' => '5.0',
        ]);

        // Use the test pages directory
        /** @var ResourceLocatorInterface $locator */
        $locator = $this->ci->get(ResourceLocatorInterface::class);
        $locator->removeStream('pages');
        $locator->addStream(new ResourceStream('pages', shared: true, readonly: true, path: __DIR__ . '/../pages'));

        // Make sure setup is ok
        $this->assertCount(9, $locator->listResources('pages://'));
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
}
