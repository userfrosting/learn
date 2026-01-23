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
        protected SearchSprunje $sprunje,
    ) {
    }

    /**
     * Search documentation pages.
     * Request type: GET.
     *
     * Query parameters:
     * - q: Search query (required, min length from config)
     * - page: Page number for pagination (optional, from config)
     * - size: Number of results per page (optional, from config, max from config)
     *
     * @param Request  $request
     * @param Response $response
     */
    public function search(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        $this->sprunje->setOptions([
            'query' => $params['q'] ?? '',
            'page'  => $params['page'] ?? null,
            'size'  => $params['size'] ?? null,
        ]);

        return $this->sprunje->toResponse($response);
    }
}
