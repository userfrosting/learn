<?php

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Learn\Controller;

use UserFrosting\Config\Config;
use UserFrosting\Learn\Recipe;
use UserFrosting\Testing\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceStream;

/**
 * Tests for DocumentationController class.
 */
class DocumentationControllerTest extends TestCase
{
    protected string $mainSprinkle = Recipe::class;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Config $config */
        $config = $this->ci->get(Config::class);
        $config->set('learn.versions.latest', '6.0');
        $config->set('learn.versions.available', ['6.0' => '6.0 Beta']);

        /** @var ResourceLocatorInterface $locator */
        $locator = $this->ci->get(ResourceLocatorInterface::class);
        $locator->removeStream('pages');
        $locator->addStream(new ResourceStream('pages', shared: true, readonly: true, path: __DIR__ . '/../pages'));
    }

    /**
     * Test a documentation page renders successfully (non-versioned route).
     */
    public function testPageIndex(): void
    {
        $request = $this->createRequest('GET', '/first');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertNotSame('', (string) $response->getBody());
    }

    /**
     * Test a documentation page renders successfully (versioned route).
     */
    public function testPageVersioned(): void
    {
        $request = $this->createRequest('GET', '/6.0/first');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertNotSame('', (string) $response->getBody());
    }

    /**
     * Test serving a JPEG image via the non-versioned route (delegates to imageVersioned).
     */
    public function testImage(): void
    {
        $request = $this->createRequest('GET', '/images/test.jpg');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertSame('image/jpeg', $response->getHeaderLine('Content-Type'));
    }

    /**
     * Test serving a JPEG image (jpg extension).
     */
    public function testImageVersionedJpeg(): void
    {
        $request = $this->createRequest('GET', '/6.0/images/test.jpg');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertSame('image/jpeg', $response->getHeaderLine('Content-Type'));
    }

    /**
     * Test serving a PNG image.
     */
    public function testImageVersionedPng(): void
    {
        $request = $this->createRequest('GET', '/6.0/images/test.png');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertSame('image/png', $response->getHeaderLine('Content-Type'));
    }

    /**
     * Test serving a GIF image.
     */
    public function testImageVersionedGif(): void
    {
        $request = $this->createRequest('GET', '/6.0/images/test.gif');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertSame('image/gif', $response->getHeaderLine('Content-Type'));
    }

    /**
     * Test serving an SVG image.
     */
    public function testImageVersionedSvg(): void
    {
        $request = $this->createRequest('GET', '/6.0/images/test.svg');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertSame('image/svg+xml', $response->getHeaderLine('Content-Type'));
    }

    /**
     * Test serving a WebP image.
     */
    public function testImageVersionedWebp(): void
    {
        $request = $this->createRequest('GET', '/6.0/images/test.webp');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertSame('image/webp', $response->getHeaderLine('Content-Type'));
    }

    /**
     * Test serving a BMP image.
     */
    public function testImageVersionedBmp(): void
    {
        $request = $this->createRequest('GET', '/6.0/images/test.bmp');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertSame('image/bmp', $response->getHeaderLine('Content-Type'));
    }

    /**
     * Test serving an ICO image.
     */
    public function testImageVersionedIco(): void
    {
        $request = $this->createRequest('GET', '/6.0/images/test.ico');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertSame('image/x-icon', $response->getHeaderLine('Content-Type'));
    }

    /**
     * Test serving a file with an unknown extension falls back to octet-stream.
     */
    public function testImageVersionedDefaultMimeType(): void
    {
        $request = $this->createRequest('GET', '/6.0/images/test.bin');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(200, $response);
        $this->assertSame('application/octet-stream', $response->getHeaderLine('Content-Type'));
    }

    /**
     * Test that an unreadable image file returns a 404 response.
     */
    public function testImageVersionedNotReadable(): void
    {
        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            $this->markTestSkipped('Cannot test file permissions as root user.');
        }

        $imagePath = __DIR__ . '/../pages/6.0/images/unreadable.jpg';
        file_put_contents($imagePath, 'data');
        chmod($imagePath, 0000);

        try {
            $request = $this->createRequest('GET', '/6.0/images/unreadable.jpg');
            $response = $this->handleRequest($request);
            $this->assertResponseStatus(404, $response);
        } finally {
            chmod($imagePath, 0644);
            @unlink($imagePath);
        }
    }

    /**
     * Test that a versioned URL with a trailing slash is redirected (301).
     */
    public function testTrailingSlashRedirectVersioned(): void
    {
        $request = $this->createRequest('GET', '/6.0/first/');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(301, $response);
        $this->assertSame('/6.0/first', $response->getHeaderLine('Location'));
    }

    /**
     * Test that a non-versioned URL with a trailing slash is redirected (301).
     */
    public function testTrailingSlashRedirect(): void
    {
        $request = $this->createRequest('GET', '/first/');
        $response = $this->handleRequest($request);

        $this->assertResponseStatus(301, $response);
        $this->assertSame('/first', $response->getHeaderLine('Location'));
    }
}
