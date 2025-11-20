<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Documentation;

use Illuminate\Cache\Repository as Cache;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Util\RouteParserInterface;
use UserFrosting\UniformResourceLocator\ResourceInterface;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Repository for managing documentation pages across different versions.
 * 
 * Handles retrieval, caching, and organization of documentation content
 * from various sources using the resource locator system.
 */
class DocumentationRepository
{
    public function __construct(
        protected ResourceLocatorInterface $locator,
        protected VersionValidator $versionValidator,
        protected PageFactory $pageFactory,
        protected Config $config,
        protected Cache $cache,
        protected RouteParserInterface $router,
    ) {
    }

    /**
     * Return the documentation tree for a given version, using cache if enabled.
     *
     * @param string|null $version
     *
     * @return PageResource[]
     */
    public function getTree(?string $version = null): array
    {
        return $this->cache->remember(
            $this->getCacheKey('tree', $version ?? 'latest'),
            $this->getCacheTtl(),
            function () use ($version) {
                // Get version object (throws exception if invalid)
                $versionObj = $this->versionValidator->getVersion($version);

                // Get all files for the version
                $pages = $this->getPages($versionObj);

                // Sort the files by relative path
                usort($pages, fn ($a, $b) => strcmp($a->getBasePath(), $b->getBasePath()));

                // Transform the flat list of pages into a tree structure
                return $this->getPagesChildren($pages);
            }
        );
    }

    /**
     * Return the list of all variables version, with the link to the provided
     * page alternate versions. Uses cache if enabled.
     *
     * @param string $path
     *
     * @return array<string, string> Array containing Label => Route
     */
    public function getAlternateVersions(string $path): array
    {
        return $this->cache->remember(
            $this->getCacheKey('versions', $path),
            $this->getCacheTtl(),
            function () use ($path) {
                $available = $this->config->get('site.versions.available', []);

                // $available contains a list of version => name
                // We need for the dropdown to have name => path
                return array_map(
                    fn ($v) => $this->router->urlFor('documentation.versioned', [
                        'version' => $v,
                        'path'    => $path,
                    ]),
                    array_flip($available)
                );
            }
        );
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
            $child->setChildren($this->getPagesChildren($pages, $child->getSlug()));
        }

        // Reset keys
        $children = array_values($children);

        return $children;
    }

    /**
     * Get a single page by version and slug.
     *
     * @param string      $slug
     * @param string|null $version
     *
     *
     * @throws PageNotFoundException
     * @return PageResource
     */
    public function getPage(string $slug, ?string $version = null): PageResource
    {
        return $this->cache->remember(
            $this->getCacheKey('page', $slug . ($version ?? 'latest')),
            $this->getCacheTtl(),
            function () use ($slug, $version) {
                // Get version object (throws exception if invalid)
                $versionObj = $this->versionValidator->getVersion($version);

                // Get all pages for the version
                $pages = $this->getPages($versionObj);

                // If page slug is empty, we want the "home" page, aka the first page found
                if ($slug === '') {
                    return array_key_first($pages) !== null
                        ? $pages[array_key_first($pages)]
                        : throw new PageNotFoundException("Page not found: (version: {$versionObj->id})");
                }

                // Find the page with the matching slug
                foreach ($pages as $page) {
                    if ($page->getSlug() === $slug) {
                        return $page;
                    }
                }

                throw new PageNotFoundException("Page not found: {$slug} (version: {$versionObj->id})");
            }
        );
    }

    /**
     * Get a list of all the files found across all active sprinkles
     *
     * @return PageResource[] An array of absolute paths
     */
    protected function getPages(Version $version): array
    {
        return $this->cache->remember(
            $this->getCacheKey('pages', $version->id),
            $this->getCacheTtl(),
            function () use ($version) {
                // Get all pages
                $resources = $this->locator->listResources("pages://{$version->id}/");

                // Keep only markdown files
                $resources = array_filter(
                    $resources,
                    fn (ResourceInterface $res) => $res->getExtension() === 'md'
                );

                // Convert each to our custom "PageResource" objects using the factory
                return array_map(
                    fn (ResourceInterface $res) => $this->pageFactory->createFromResource($version, $res),
                    $resources
                );
            }
        );
    }

    /**
     * Get the cache key for documentation items.
     *
     * @param string $type       The type of item (e.g., 'tree', 'page', 'versions', 'pages')
     * @param string $identifier The identifier for the item (e.g., version, slug)
     *
     * @return string The cache key
     */
    protected function getCacheKey(string $type, string $identifier): string
    {
        $keyFormat = $this->config->get('learn.cache.key', '%s.%s');

        return sprintf($keyFormat, $type, $identifier);
    }

    /**
     * Get the cache TTL for documentation items.
     *
     * @return int The cache TTL in seconds
     */
    protected function getCacheTtl(): int
    {
        return $this->config->get('learn.cache.ttl', 3600);
    }
}
