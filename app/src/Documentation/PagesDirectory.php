<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Documentation;

use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Util\RouteParserInterface;
use UserFrosting\UniformResourceLocator\ResourceInterface;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Manage pages.
 */
class PagesDirectory
{
    public function __construct(
        protected ResourceLocatorInterface $locator,
        protected VersionValidator $versionValidator,
        protected RouteParserInterface $router,
    ) {
    }

    /**
     * Undocumented function
     *
     * @param string|null $version
     *
     * @return PageResource[]
     */
    public function getTree(?string $version = null): array
    {
        // Get version object (throws exception if invalid)
        $versionObj = $this->versionValidator->getVersion($version);

        // TODO : Cache the result, with a config to turn off in non-production environments

        // Get all files for the version
        $pages = $this->getPages($versionObj);

        // Sort the files by relative path
        usort($pages, fn ($a, $b) => strcmp($a->getBasePath(), $b->getBasePath()));

        // Transform the flat list of pages into a tree structure
        $tree = $this->getPagesChildren($pages);

        return $tree;
    }

    /**
     * Return children pages for a given parent slug.
     *
     * @param PageResource[] $pages
     * @param string         $parentSlug
     *
     * @return PageResource[]
     */
    protected function getPagesChildren(array $pages, string $parentSlug = ''): array
    {
        // Get all pages that have this parent
        $children = array_filter($pages, fn ($p) => $p->getParentSlug() === $parentSlug);

        // For each child, get its own children
        foreach ($children as $child) {
            $child->children = $this->getPagesChildren($pages, $child->getSlug());
        }

        // Reset keys
        $children = array_values($children);

        return $children;
    }

    // TODO : getPage

    // TODO : Get page by slug and version

    // TODO : Page not found exception

    /**
     * Get a list of all the files found across all active sprinkles
     *
     * @return PageResource[] An array of absolute paths
     */
    protected function getPages(Version $version): array
    {
        // Get all pages
        $resources = $this->locator->listResources("pages://{$version->id}/");

        // Convert each to our custom "PageResource" objects
        $resources = array_map(
            fn (ResourceInterface $res) => new PageResource(
                $version,
                $this->router,
                $res->getStream(),
                $res->getLocation(),
                $res->getPath(),
                $res->getLocatorBasePath()
            ),
            $resources
        );

        return $resources;
    }
}
