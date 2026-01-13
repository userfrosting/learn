<?php

declare(strict_types=1);

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Search;

use Illuminate\Cache\Repository as Cache;
use UserFrosting\Config\Config;
use UserFrosting\Learn\Documentation\DocumentationRepository;
use UserFrosting\Learn\Documentation\PageResource;
use UserFrosting\Learn\Documentation\Version;
use UserFrosting\Learn\Documentation\VersionValidator;

/**
 * Service for indexing documentation pages for search.
 *
 * Builds a searchable index of all documentation content by:
 * - Parsing markdown to plain text
 * - Extracting page metadata
 * - Storing content with position information for snippet generation
 */
class SearchIndex
{
    public function __construct(
        protected DocumentationRepository $repository,
        protected VersionValidator $versionValidator,
        protected Cache $cache,
        protected Config $config,
    ) {
    }

    /**
     * Build the search index for a specific version or all versions.
     *
     * @param string|null $version The version to index, or null for all versions
     *
     * @return int Number of pages indexed
     */
    public function buildIndex(?string $version = null): int
    {
        $versions = [];

        if ($version === null) {
            // Index all available versions
            $available = $this->config->get('learn.versions.available', []);
            foreach (array_keys($available) as $versionId) {
                $versions[] = $this->versionValidator->getVersion($versionId);
            }
        } else {
            // Index specific version
            $versions[] = $this->versionValidator->getVersion($version);
        }

        $totalPages = 0;

        foreach ($versions as $versionObj) {
            $pages = $this->indexVersion($versionObj);
            $totalPages += count($pages);

            // Store in cache
            $this->cache->put(
                $this->getCacheKey($versionObj->id),
                $pages,
                $this->getCacheTtl()
            );
        }

        return $totalPages;
    }

    /**
     * Index all pages for a specific version.
     *
     * @param Version $version
     *
     * @return array<int, array{title: string, slug: string, route: string, content: string, version: string}>
     */
    protected function indexVersion(Version $version): array
    {
        $tree = $this->repository->getTree($version->id);
        $pages = $this->flattenTree($tree);

        $indexed = [];

        foreach ($pages as $page) {
            $indexed[] = $this->indexPage($page);
        }

        return $indexed;
    }

    /**
     * Index a single page.
     *
     * @param PageResource $page
     *
     * @return array{title: string, slug: string, route: string, content: string, version: string}
     */
    protected function indexPage(PageResource $page): array
    {
        // Get the HTML content and strip HTML tags to get plain text
        $htmlContent = $page->getContent();
        $plainText = $this->stripHtmlTags($htmlContent);

        return [
            'title'   => $page->getTitle(),
            'slug'    => $page->getSlug(),
            'route'   => $page->getRoute(),
            'content' => $plainText,
            'version' => $page->getVersion()->id,
        ];
    }

    /**
     * Strip HTML tags from content to get searchable plain text.
     * Preserves code blocks and adds spacing for better search results.
     *
     * @param string $html
     *
     * @return string
     */
    protected function stripHtmlTags(string $html): string
    {
        // Convert HTML to plain text, preserving code blocks
        // Add space before/after block elements to prevent word concatenation
        $html = (string) preg_replace('/<(div|p|h[1-6]|li|pre|code|blockquote)[^>]*>/i', ' $0', $html);
        $html = (string) preg_replace('/<\/(div|p|h[1-6]|li|pre|code|blockquote)>/i', '$0 ', $html);

        // Remove script and style tags with their content
        $html = (string) preg_replace('/<(script|style)[^>]*>.*?<\/\1>/is', '', $html);

        // Strip remaining HTML tags
        $text = strip_tags($html);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize whitespace
        $text = (string) preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Flatten a tree structure into a flat array of pages.
     *
     * @param PageResource[] $tree
     *
     * @return PageResource[]
     */
    protected function flattenTree(array $tree): array
    {
        $flat = [];

        foreach ($tree as $page) {
            $flat[] = $page;
            if ($page->getChildren()) {
                $flat = array_merge($flat, $this->flattenTree($page->getChildren()));
            }
        }

        return $flat;
    }

    /**
     * Get the cache key for the search index of a specific version.
     *
     * @param string $version
     *
     * @return string
     */
    protected function getCacheKey(string $version): string
    {
        $keyFormat = $this->config->get('learn.cache.key', '%s.%s');

        return sprintf($keyFormat, 'search-index', $version);
    }

    /**
     * Get the cache TTL for the search index.
     *
     * @return int The cache TTL in seconds
     */
    protected function getCacheTtl(): int
    {
        // Use a longer TTL for search index since it's expensive to rebuild
        return $this->config->get('learn.cache.ttl', 3600) * 24; // 24 hours by default
    }

    /**
     * Clear the search index for a specific version or all versions.
     *
     * @param string|null $version The version to clear, or null for all versions
     */
    public function clearIndex(?string $version = null): void
    {
        if ($version === null) {
            // Clear all versions
            $available = $this->config->get('learn.versions.available', []);
            foreach (array_keys($available) as $versionId) {
                $this->cache->forget($this->getCacheKey($versionId));
            }
        } else {
            // Clear specific version
            $this->cache->forget($this->getCacheKey($version));
        }
    }
}
