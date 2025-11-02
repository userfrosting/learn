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
use Twig\TwigFunction;
use UserFrosting\Config\Config;
use UserFrosting\Learn\Documentation\PagesDirectory;

/**
 * Core Twig Extensions.
 */
class FileTreeExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        protected Config $config,
        protected PagesDirectory $pagesDirectory,
    ) {
    }

    /**
     * Adds Twig functions `getAlerts`.
     *
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('pagesTree', function (?string $version = null) {
                return $this->pagesDirectory->getTree($version);
            })
        ];
    }

    /**
     * Adds Twig global variables `versions` containing documentation versions
     * information.
     *
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        return [
            'fileTree' => []
        ];
    }
}
