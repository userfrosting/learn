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
use UserFrosting\Learn\Search\SearchService;

/**
 * Controller for the documentation search API.
 */
class SearchController
{
    public function __construct(
        protected SearchService $searchService,
    ) {
    }

    /**
     * Search documentation pages.
     * Request type: GET.
     *
     * Query parameters:
     * - q: Search query (required)
     * - version: Documentation version to search (optional, defaults to latest)
     * - page: Page number for pagination (optional, default: 1)
     * - size: Number of results per page (optional, default: 10, max: 100)
     *
     * @param Request  $request
     * @param Response $response
     */
    public function search(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        // Get query parameter
        $query = $params['q'] ?? '';

        if (empty($query)) {
            $result = [
                'rows'           => [],
                'count'          => 0,
                'count_filtered' => 0,
            ];

            $response->getBody()->write(json_encode($result, JSON_THROW_ON_ERROR));

            return $response->withHeader('Content-Type', 'application/json');
        }

        // Get pagination parameters
        $page = isset($params['page']) ? max(1, (int) $params['page']) : 1;
        $size = isset($params['size']) ? min(100, max(1, (int) $params['size'])) : 10;

        // Get version parameter
        $version = $params['version'] ?? null;

        // Perform search
        $result = $this->searchService->search($query, $version, $page, $size);

        // Write JSON response
        $response->getBody()->write(json_encode($result, JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
