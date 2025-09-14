<?php

declare(strict_types=1);

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Twig\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use UserFrosting\Config\Config;

/**
 * Core Twig Extensions.
 */
class VersionExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        protected Config $config,
    ) {
    }

    /**
     * Adds Twig global variables `versions` containing documentation versions
     * information.
     *
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        $available = $this->config->get('site.versions.available', []);
        $latest = $this->config->get('site.versions.latest', '');

        // $latest is the version number, we need the name
        // Must do this before we mutate $available!
        if (isset($available[$latest])) {
            $latest = $available[$latest];
        }

        // $available contains a list of version => name
        // We need for the dropdown to have name => path
        $available = array_map(fn ($v) => $v . '/', array_flip($available));

        return [
            'versions' => [
                'available' => $available,
                'latest'    => $latest,
            ]
        ];
    }
}
