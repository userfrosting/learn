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

        // Get minimum length from config
        $minLength = $this->config->get('learn.search.min_length', 3);

        // Validate query length
        if ($query === '' || mb_strlen($query) < $minLength) {
            $result = [
                'rows'           => [],
                'count'          => 0,
                'count_filtered' => 0,
                'error'          => "Query must be at least {$minLength} characters long",
            ];

            $response->getBody()->write(json_encode($result, JSON_THROW_ON_ERROR));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        // Get pagination parameters from config with fallbacks
        $defaultPage = $this->config->get('learn.search.default_page', 1);
        $defaultSize = $this->config->get('learn.search.default_size', 10);
        $maxSize = $this->config->get('learn.search.max_size', 100);
        
        $page = isset($params['page']) ? max(1, (int) $params['page']) : $defaultPage;
        $size = isset($params['size']) ? min($maxSize, max(1, (int) $params['size'])) : $defaultSize;

        // Get version parameter
        $version = $params['version'] ?? null;

        // Perform search
        $result = $this->searchService->search($query, $version, $page, $size);

        // Write JSON response
        $response->getBody()->write(json_encode($result, JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
