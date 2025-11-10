<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Learn\Documentation;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Config\Config;
use UserFrosting\Learn\Documentation\PageFactory;
use UserFrosting\Learn\Documentation\Version;
use UserFrosting\Learn\Recipe;
use UserFrosting\Support\Exception\FileNotFoundException;
use UserFrosting\Testing\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceStream;

class PageFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected string $mainSprinkle = Recipe::class;

    public function setUp(): void
    {
        parent::setUp();

        // Load test config to force the default version
        /** @var Config $config */
        $config = $this->ci->get(Config::class);
        $config->set('learn.documentation.default_version', '6.0');

        // Use the test pages directory
        /** @var ResourceLocatorInterface $locator */
        $locator = $this->ci->get(ResourceLocatorInterface::class);
        $locator->removeStream('pages');
        $locator->addStream(new ResourceStream('pages', shared: true, readonly: true, path: __DIR__ . '/../pages'));

        // Make sure setup is ok
        $this->assertCount(9, $locator->listResources('pages://'));
    }

    public function testCreateFromResourceWithLatestVersion(): void
    {
        // Get the factory from CI
        /** @var PageFactory $pageFactory */
        $pageFactory = $this->ci->get(PageFactory::class);

        // Get Locator
        /** @var ResourceLocatorInterface $locator */
        $locator = $this->ci->get(ResourceLocatorInterface::class);

        // Setup version and resource to create a page for
        $version = new Version('6.0', '6.0', true);
        $resource = $locator->getResource('pages://6.0/01.first/docs.md');
        $this->assertNotNull($resource);

        // Get page
        $page = $pageFactory->createFromResource($version, $resource);

        // Assertions
        $this->assertSame('/first', $page->getRoute());
        $this->assertSame('first', $page->getSlug());
        $this->assertSame('First page', $page->getTitle());
        $this->assertSame('First page description', $page->getFrontMatter()['metadata']['description']);
    }

    public function testCreateFromResourceWithVersionedRoute(): void
    {
        // Get the factory from CI
        /** @var PageFactory $pageFactory */
        $pageFactory = $this->ci->get(PageFactory::class);

        // Get Locator
        /** @var ResourceLocatorInterface $locator */
        $locator = $this->ci->get(ResourceLocatorInterface::class);

        // Setup version and resource to create a page for
        $version = new Version('6.0', '6.0', false);
        $resource = $locator->getResource('pages://6.0/01.first/docs.md');
        $this->assertNotNull($resource);

        // Get page
        $page = $pageFactory->createFromResource($version, $resource);

        // Assertions
        $this->assertSame('/6.0/first', $page->getRoute());
        $this->assertSame('first', $page->getSlug());
        $this->assertSame('First page', $page->getTitle());
        $this->assertSame('First page description', $page->getFrontMatter()['metadata']['description']);
    }

    public function testCreateFromResourceWithNoFrontMatter(): void
    {
        // Get the factory from CI
        /** @var PageFactory $pageFactory */
        $pageFactory = $this->ci->get(PageFactory::class);

        // Get Locator
        /** @var ResourceLocatorInterface $locator */
        $locator = $this->ci->get(ResourceLocatorInterface::class);

        // Setup version and resource to create a page for
        $version = new Version('6.0', '6.0', true);
        $resource = $locator->getResource('pages://6.0/02.second/docs.md');
        $this->assertNotNull($resource);

        // Get page
        $page = $pageFactory->createFromResource($version, $resource);

        // Assertions
        $this->assertSame('/second', $page->getRoute());
        $this->assertSame('second', $page->getSlug());
        $this->assertSame('second', $page->getTitle());
        $this->assertSame([], $page->getFrontMatter());
    }

    public function testCreateFromResourceWithFileNotFound(): void
    {
        // Get the factory from CI
        /** @var PageFactory $pageFactory */
        $pageFactory = $this->ci->get(PageFactory::class);

        // Get Locator
        /** @var ResourceLocatorInterface $locator */
        $locator = $this->ci->get(ResourceLocatorInterface::class);

        // Setup version and resource to create a page for
        $version = new Version('6.0', '6.0', true);
        $resource = $locator->getResource('pages://6.0/doesNotExist/docs.md', true);
        $this->assertNotNull($resource);

        // Expect exception
        $this->expectException(FileNotFoundException::class);

        // Get page
        $page = $pageFactory->createFromResource($version, $resource);
    }
}
