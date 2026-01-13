<?php

declare(strict_types=1);

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\ServicesProvider;

use UserFrosting\Learn\Search\SearchIndex;
use UserFrosting\Learn\Search\SearchService;
use UserFrosting\ServicesProvider\ServicesProviderInterface;

/**
 * Services provider for documentation search.
 */
class SearchServicesProvider implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            SearchIndex::class   => \DI\autowire(),
            SearchService::class => \DI\autowire(),
        ];
    }
}
