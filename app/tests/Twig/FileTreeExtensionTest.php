<?php

declare(strict_types=1);

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Learn\Twig;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Learn\Documentation\PageResource;
use UserFrosting\Learn\Twig\Extensions\FileTreeExtension;

/**
 * Tests for FileTreeExtension.
 */
class FileTreeExtensionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetFunctions(): void
    {
        $ext = new FileTreeExtension();
        $functions = $ext->getFunctions();

        $this->assertCount(1, $functions);
        $this->assertSame('isChildActive', $functions[0]->getName());
    }

    /**
     * Covers the early return when $currentPage is null (line 47).
     */
    public function testIsChildActiveWithNullCurrentPage(): void
    {
        $ext = new FileTreeExtension();
        $child = Mockery::mock(PageResource::class);

        $this->assertFalse($ext->isChildActive([$child], null));
    }

    /**
     * Covers the direct slug match return (line 52).
     */
    public function testIsChildActiveDirectMatch(): void
    {
        $ext = new FileTreeExtension();

        $currentPage = Mockery::mock(PageResource::class);
        $currentPage->shouldReceive('getSlug')->andReturn('matching-page');

        $child = Mockery::mock(PageResource::class);
        $child->shouldReceive('getSlug')->andReturn('matching-page');

        $this->assertTrue($ext->isChildActive([$child], $currentPage));
    }

    /**
     * Covers the recursive match return (line 55).
     */
    public function testIsChildActiveRecursiveMatch(): void
    {
        $ext = new FileTreeExtension();

        $currentPage = Mockery::mock(PageResource::class);
        $currentPage->shouldReceive('getSlug')->andReturn('grandchild');

        $grandchild = Mockery::mock(PageResource::class);
        $grandchild->shouldReceive('getSlug')->andReturn('grandchild');

        $child = Mockery::mock(PageResource::class);
        $child->shouldReceive('getSlug')->andReturn('child-slug');
        $child->shouldReceive('getChildren')->andReturn([$grandchild]);

        $this->assertTrue($ext->isChildActive([$child], $currentPage));
    }

    /**
     * Covers the final return false when no child matches.
     */
    public function testIsChildActiveNoMatch(): void
    {
        $ext = new FileTreeExtension();

        $currentPage = Mockery::mock(PageResource::class);
        $currentPage->shouldReceive('getSlug')->andReturn('other-page');

        $child = Mockery::mock(PageResource::class);
        $child->shouldReceive('getSlug')->andReturn('child-slug');
        $child->shouldReceive('getChildren')->andReturn(null);

        $this->assertFalse($ext->isChildActive([$child], $currentPage));
    }
}
