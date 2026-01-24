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

        // Should have some results
        $this->assertGreaterThan(0, $data['count']);
        $this->assertNotEmpty($data['rows']);

        // Check structure of first result
        if (count($data['rows']) > 0) {
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

        // Returns 500 because InvalidArgumentException is not caught
        $this->assertResponseStatus(500, $response);
    }

    /**
     * Test search API endpoint with query too short.
     */
    public function testSearchEndpointQueryTooShort(): void
    {
        // Create request with query too short (less than min_length)
        $request = $this->createRequest('GET', '/api/search?q=ab');
        $response = $this->handleRequest($request);

        // Returns 500 because InvalidArgumentException is not caught
        $this->assertResponseStatus(500, $response);
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
        $this->assertArrayHasKey('count', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('size', $data);
        $this->assertArrayHasKey('rows', $data);

        // Should return at most 2 results
        $this->assertLessThanOrEqual(2, count($data['rows']));

        // Check that page and size are correct
        $this->assertSame(1, $data['page']);
        $this->assertSame(2, $data['size']);
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
        if (count($data['rows']) > 0) {
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
        // Create request with wildcard query that meets minimum length
        $request = $this->createRequest('GET', '/api/search?q=fir*');
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

    /**
     * Test search API endpoint with no version and no default version in config.
     */
    public function testSearchEndpointNoVersion(): void
    {
        // Temporarily unset the default version
        /** @var Config $config */
        $config = $this->ci->get(Config::class);
        $originalVersion = $config->get('learn.versions.latest');
        $config->set('learn.versions.latest', null);

        // Create request without version parameter
        $request = $this->createRequest('GET', '/api/search?q=test');
        $response = $this->handleRequest($request);

        // Restore original version
        $config->set('learn.versions.latest', $originalVersion);

        // Assert successful response but empty results
        $this->assertResponseStatus(200, $response);

        // Parse JSON response
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('rows', $data);
        $this->assertArrayHasKey('count', $data);

        // Should have no results when version is null
        $this->assertSame(0, $data['count']);
        $this->assertEmpty($data['rows']);
    }

    /**
     * Test search API endpoint with no indexed pages for version.
     */
    public function testSearchEndpointNoIndexedPages(): void
    {
        // Clear the index for version 6.0
        $searchIndex = $this->ci->get(SearchIndex::class);
        $searchIndex->clearIndex('6.0');

        // Create request to search
        $request = $this->createRequest('GET', '/api/search?q=test&version=6.0');
        $response = $this->handleRequest($request);

        // Assert successful response but empty results
        $this->assertResponseStatus(200, $response);

        // Parse JSON response
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('rows', $data);
        $this->assertArrayHasKey('count', $data);

        // Should have no results when index is empty
        $this->assertSame(0, $data['count']);
        $this->assertEmpty($data['rows']);

        // Rebuild index for other tests
        $searchIndex->buildIndex('6.0');
    }
}
