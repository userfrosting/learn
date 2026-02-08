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

use PHPUnit\Framework\TestCase;
use UserFrosting\Learn\Documentation\Version;
use UserFrosting\Learn\Twig\Extensions\VersionedPathExtension;

class VersionedPathExtensionTest extends TestCase
{
    public function testPrefixesRootRelativePathsForVersionedDocs(): void
    {
        $ext = new VersionedPathExtension();
        $version = new Version('5.0', '5.0', false);

        $html = '<a href="/installation">Install</a><img src="/images/logo.png">';
        $converted = $ext->addVersionPrefix($html, $version);

        $this->assertStringContainsString('href="/5.0/installation"', $converted);
        $this->assertStringContainsString('src="/5.0/images/logo.png"', $converted);
    }

    public function testLeavesAlreadyVersionedPathsUntouched(): void
    {
        $ext = new VersionedPathExtension();
        $version = new Version('5.0', '5.0', false);

        $html = '<a href="/5.0/installation">Install</a><img src="/5.0/images/logo.png">';
        $converted = $ext->addVersionPrefix($html, $version);

        $this->assertSame($html, $converted);
    }

    public function testLeavesLatestVersionRootPathsUntouched(): void
    {
        $ext = new VersionedPathExtension();
        $version = new Version('6.0', '6.0', true);

        $html = '<a href="/installation">Install</a><img src="/images/logo.png">';
        $converted = $ext->addVersionPrefix($html, $version);

        $this->assertSame($html, $converted);
    }

    public function testRelativeLinksUnaffectedButWorkWithBase(): void
    {
        $ext = new VersionedPathExtension();
        $version = new Version('5.0', '5.0', false);

        $html = '<a href="guide/setup">Setup</a><img src="images/logo.png">';
        $converted = $ext->addVersionPrefix($html, $version);

        $this->assertSame($html, $converted, 'Relative paths must not be rewritten by the filter');
    }

    public function testRootPathsWithFragmentsOrQueriesAreVersioned(): void
    {
        $ext = new VersionedPathExtension();
        $version = new Version('5.0', '5.0', false);

        $html = '<a href="/installation#toc">TOC</a><img src="/images/logo.png?v=1#hero">';
        $converted = $ext->addVersionPrefix($html, $version);

        $this->assertStringContainsString('href="/5.0/installation#toc"', $converted);
        $this->assertStringContainsString('src="/5.0/images/logo.png?v=1#hero"', $converted);
    }
}
