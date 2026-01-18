<?php

declare(strict_types=1);

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Config\Config;
use UserFrosting\Learn\Search\SearchService;
use UserFrosting\Learn\Search\SearchSprunje;

/**
 * Controller for the documentation search API.
 */
class SearchController
{
    public function __construct(
        protected SearchService $searchService,
        protected Config $config,
    ) {
    }

    /**
     * Search documentation pages.
     * Request type: GET.
     *
     * Query parameters:
     * - q: Search query (required, min length from config)
     * - version: Documentation version to search (optional, defaults to latest)
     * - page: Page number for pagination (optional, from config)
     * - size: Number of results per page (optional, from config, max from config)
     *
     * @param Request  $request
     * @param Response $response
     */
    public function search(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        // Get query parameter
        $query = $params['q'] ?? '';

        // Create Sprunje which validates query length in its constructor
        try {
            // Prepare options for Sprunje
            $sprunjeOptions = [
                'query'   => $query,
                'version' => $params['version'] ?? null,
                'page'    => isset($params['page']) ? (int) $params['page'] : null,
                'size'    => $params['size'] ?? null,
                'format'  => 'json',
            ];

            // Create and execute Sprunje (validates query length in constructor)
            $sprunje = new SearchSprunje($this->searchService, $this->config, $sprunjeOptions);

            // Return response via Sprunje
            return $sprunje->toResponse($response);
        } catch (\InvalidArgumentException $e) {
            // Handle validation errors consistently
            $result = [
                'rows'           => [],
                'count'          => 0,
                'count_filtered' => 0,
                'error'          => $e->getMessage(),
            ];

            $response->getBody()->write(json_encode($result, JSON_THROW_ON_ERROR));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }
}
