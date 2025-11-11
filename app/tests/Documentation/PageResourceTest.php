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
use UserFrosting\Sprinkle\Core\Util\MarkdownFile;
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
        string $expectedRoute,
        bool $isLatest = true
    ): void {
        /** @var ResourceStreamInterface */
        $stream = Mockery::mock(ResourceStreamInterface::class)
            ->shouldReceive('getPath')->andReturn('pages')->times(2)
            ->getMock();

        /** @var ResourceLocationInterface */
        $location = Mockery::mock(ResourceLocationInterface::class)
            ->shouldReceive('getPath')->andReturn('')->times(2)
            ->getMock();

        $version = new Version('6.0', '6.0 Beta', $isLatest);
        $markdown = new MarkdownFile('foo bar', ['title' => 'Foo Bar']);

        $resource = new PageResource(
            $version,
            $stream,
            $location,
            $path,
            '',
            $markdown,
            $expectedRoute,
        );

        $this->assertEquals($expectedSlug, $resource->getSlug());
        $this->assertEquals($expectedParent, $resource->getParentSlug());
        $this->assertEquals($expectedRoute, $resource->getRoute());
        $this->assertSame('foo bar', $resource->getContent());
        $this->assertSame(['title' => 'Foo Bar'], $resource->getFrontMatter());
        $this->assertSame('Foo Bar', $resource->getTitle());
        $this->assertSame($version, $resource->getVersion());

        // Set a new route and check it
        $newRoute = $expectedRoute . '/new';
        $resource->setRoute($newRoute);
        $this->assertEquals($newRoute, $resource->getRoute());
    }

    /**
     * @return array<array{string, string, string}>
     */
    public static function dataProvider(): array
    {
        return [
            ['pages/6.0/01.quick-start/docs.md', 'quick-start', '', '/quick-start', true], // No parent
            ['pages/6.0/02.background/02.foobar/docs.md', 'background/foobar', 'background', '/background/foobar', true], // One level deep
            ['pages/6.0/02.background/01.bar/02.foo/docs.md', 'background/bar/foo', 'background/bar', '/background/bar/foo', true], // Two levels deep
            ['pages/6.0/01.quick-start/docs.md', 'quick-start', '', '/quick-start', false], // No parent - not latest
            ['pages/6.0/02.background/02.foobar/docs.md', 'background/foobar', 'background', '/background/foobar', false], // One level deep - not latest
            ['pages/6.0/02.background/01.bar/02.foo/docs.md', 'background/bar/foo', 'background/bar', '/background/bar/foo', false], // Two levels deep - not latest
        ];
    }
}
