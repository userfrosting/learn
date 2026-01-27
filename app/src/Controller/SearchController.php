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
use UserFrosting\Learn\Search\SearchSprunje;

/**
 * Controller for the documentation search API.
 */
class SearchController
{
    public function __construct(
        protected SearchSprunje $sprunje,
    ) {
    }

    /**
     * Search documentation pages.
     * Request type: GET.
     *
     * Query parameters:
     * - q: Search query (required, min length from config)
     * - page: Page number for pagination (optional, default 1)
     * - size: Number of results per page (optional, default from config, null means all results)
     * - version: Documentation version (optional, defaults to latest)
     *
     * @param Request  $request
     * @param Response $response
     */
    public function search(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        $this->sprunje
            ->setQuery($params['q'] ?? '')
            ->setVersion($params['version'] ?? null)
            ->setPage((int) ($params['page'] ?? 1));

        // Only set size if explicitly provided
        if (isset($params['size'])) {
            $this->sprunje->setSize((int) $params['size']);
        }

        return $this->sprunje->toResponse($response);
    }
}
