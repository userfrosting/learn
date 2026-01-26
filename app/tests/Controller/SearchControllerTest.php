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
use UserFrosting\Learn\Search\SearchIndex;
use UserFrosting\Testing\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceStream;

/**
 * Tests for SearchController Class.
 */
class SearchControllerTest extends TestCase
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

    /**
     * Test search API endpoint returns successful response with valid structure.
     */
    public function testSearchEndpoint(): void
    {
        // Create request to search API
        $request = $this->createRequest('GET', '/api/search?q=first');
        $response = $this->handleRequest($request);

        // Assert successful response
        $this->assertResponseStatus(200, $response);

        // Check content type header
        $this->assertTrue($response->hasHeader('Content-Type'));
        $contentType = $response->getHeaderLine('Content-Type');
        $this->assertStringContainsString('application/json', $contentType);

        // Parse JSON response
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        // Verify response structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('rows', $data);
        $this->assertArrayHasKey('count', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('size', $data);
    }

    /**
     * Test search API endpoint with size parameter.
     */
    public function testSearchEndpointWithSize(): void
    {
        // Create request with size parameter
        $request = $this->createRequest('GET', '/api/search?q=page&size=2');
        $response = $this->handleRequest($request);

        // Assert successful response
        $this->assertResponseStatus(200, $response);

        // Parse JSON response
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        // Verify size is correctly set and respected
        $this->assertSame(2, $data['size']);

        // If there are results, should not exceed size
        if ($data['count'] > 0) {
            $this->assertLessThanOrEqual(2, count($data['rows']));
        }
    }
}
