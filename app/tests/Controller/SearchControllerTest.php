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
     * Test search API endpoint with query.
     */
    public function testSearchEndpoint(): void
    {
        // Create request to search API
        $request = $this->createRequest('GET', '/api/search?q=first');
        $response = $this->handleRequest($request);

        // Assert successful response
        $this->assertResponseStatus(200, $response);

        // Parse JSON response
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('rows', $data);
        $this->assertArrayHasKey('count', $data);
        $this->assertArrayHasKey('count_filtered', $data);

        // Should have some results
        $this->assertGreaterThan(0, $data['count_filtered']);
        $this->assertNotEmpty($data['rows']);

        // Check structure of first result
        if (!empty($data['rows'])) {
            $firstResult = $data['rows'][0];
            $this->assertArrayHasKey('title', $firstResult);
            $this->assertArrayHasKey('slug', $firstResult);
            $this->assertArrayHasKey('route', $firstResult);
            $this->assertArrayHasKey('snippet', $firstResult);
            $this->assertArrayHasKey('matches', $firstResult);
            $this->assertArrayHasKey('version', $firstResult);
        }
    }

    /**
     * Test search API endpoint with empty query.
     */
    public function testSearchEndpointEmptyQuery(): void
    {
        // Create request without query
        $request = $this->createRequest('GET', '/api/search');
        $response = $this->handleRequest($request);

        // Should return 400 Bad Request for invalid query
        $this->assertResponseStatus(400, $response);

        // Parse JSON response
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertIsArray($data);
        $this->assertSame(0, $data['count_filtered']);
        $this->assertEmpty($data['rows']);
        $this->assertArrayHasKey('error', $data);
    }
    
    /**
     * Test search API endpoint with query too short.
     */
    public function testSearchEndpointQueryTooShort(): void
    {
        // Create request with query too short (less than min_length)
        $request = $this->createRequest('GET', '/api/search?q=ab');
        $response = $this->handleRequest($request);

        // Should return 400 Bad Request for query too short
        $this->assertResponseStatus(400, $response);

        // Parse JSON response
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('at least', $data['error']);
    }

    /**
     * Test search API endpoint with pagination.
     */
    public function testSearchEndpointPagination(): void
    {
        // Create request with pagination parameters
        $request = $this->createRequest('GET', '/api/search?q=page&page=1&size=2');
        $response = $this->handleRequest($request);

        // Assert successful response
        $this->assertResponseStatus(200, $response);

        // Parse JSON response
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertIsArray($data);

        // Should return at most 2 results
        $this->assertLessThanOrEqual(2, count($data['rows']));
    }

    /**
     * Test search API endpoint with version parameter.
     */
    public function testSearchEndpointWithVersion(): void
    {
        // Create request with version parameter
        $request = $this->createRequest('GET', '/api/search?q=first&version=6.0');
        $response = $this->handleRequest($request);

        // Assert successful response
        $this->assertResponseStatus(200, $response);

        // Parse JSON response
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertIsArray($data);

        // Verify results are from correct version
        if (!empty($data['rows'])) {
            foreach ($data['rows'] as $result) {
                $this->assertSame('6.0', $result['version']);
            }
        }
    }

    /**
     * Test search API endpoint with wildcard query.
     */
    public function testSearchEndpointWildcard(): void
    {
        // Create request with wildcard query
        $request = $this->createRequest('GET', '/api/search?q=f*');
        $response = $this->handleRequest($request);

        // Assert successful response
        $this->assertResponseStatus(200, $response);

        // Parse JSON response
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('rows', $data);
    }

    /**
     * Test that response is valid JSON.
     */
    public function testSearchEndpointReturnsJson(): void
    {
        $request = $this->createRequest('GET', '/api/search?q=test');
        $response = $this->handleRequest($request);

        // Check content type header
        $this->assertTrue($response->hasHeader('Content-Type'));
        $contentType = $response->getHeaderLine('Content-Type');
        $this->assertStringContainsString('application/json', $contentType);
    }
}
