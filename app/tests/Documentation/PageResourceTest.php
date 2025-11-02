<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Learn\Documentation;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Learn\Documentation\PageResource;
use UserFrosting\Learn\Documentation\Version;
use UserFrosting\Learn\Recipe;
use UserFrosting\Sprinkle\Core\Util\RouteParserInterface;
use UserFrosting\Testing\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocationInterface;
use UserFrosting\UniformResourceLocator\ResourceStreamInterface;

class PageResourceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected string $mainSprinkle = Recipe::class;

    /**
     * @dataProvider dataProvider
     */
    public function testPageResource(
        string $path,
        string $expectedSlug,
        string $expectedParent,
        string $expectedRoute
    ): void {
        /** @var ResourceStreamInterface */
        $stream = Mockery::mock(ResourceStreamInterface::class)
            ->shouldReceive('getPath')->andReturn('pages')
            ->getMock();

        /** @var ResourceLocationInterface */
        $location = Mockery::mock(ResourceLocationInterface::class)
            ->shouldReceive('getPath')->andReturn('')
            ->getMock();

        $version = new Version('6.0', '6.0 Beta', true);

        $router = $this->ci->get(RouteParserInterface::class);

        $resource = new PageResource(
            $version,
            $router,
            $stream,
            $location,
            $path,
            ''
        );

        $this->assertEquals($expectedSlug, $resource->getSlug());
        $this->assertEquals($expectedParent, $resource->getParentSlug());
        $this->assertEquals($expectedRoute, $resource->getRoute());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testPageResourceForNonLatestVersion(
        string $path,
        string $expectedSlug,
        string $expectedParent,
        string $expectedRoute
    ): void {
        /** @var ResourceStreamInterface */
        $stream = Mockery::mock(ResourceStreamInterface::class)
            ->shouldReceive('getPath')->andReturn('pages')
            ->getMock();

        /** @var ResourceLocationInterface */
        $location = Mockery::mock(ResourceLocationInterface::class)
            ->shouldReceive('getPath')->andReturn('')
            ->getMock();

        $version = new Version('6.0', '6.0 Beta', false); // Changed to false compared to other test

        $router = $this->ci->get(RouteParserInterface::class);

        $resource = new PageResource(
            $version,
            $router,
            $stream,
            $location,
            $path,
            ''
        );

        // Don't need to test the slug and parent again, just the route
        $this->assertEquals('/6.0' . $expectedRoute, $resource->getRoute());
    }

    /**
     * @return array<array{string, string, string}>
     */
    public static function dataProvider(): array
    {
        return [
            ['pages/6.0/01.quick-start/docs.md', 'quick-start', '', '/quick-start'], // No parent
            ['pages/6.0/02.background/02.foobar/docs.md', 'background/foobar', 'background', '/background/foobar'], // One level deep
            ['pages/6.0/02.background/01.bar/02.foo/docs.md', 'background/bar/foo', 'background/bar', '/background/bar/foo'], // Two levels deep
        ];
    }
}
