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
                $versions[] = $this->versionValidator->getVersion((string) $versionId);
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
     * Get the search index for a specific version from cache.
     * Public method for use by SearchSprunje.
     *
     * @param string $version
     *
     * @return list<IndexedPage>
     */
    public function getIndex(string $version): array
    {
        $keyFormat = $this->config->getString('learn.search.index.key', '');
        $cacheKey = sprintf($keyFormat, $version);

        // TODO : If the cache key is empty, it should build the index first
        $index = $this->cache->get($cacheKey);

        // Ensure we return an array even if cache returns null or unexpected type
        if (!is_array($index)) {
            return [];
        }

        return $index;
    }

    /**
     * Index all pages for a specific version.
     *
     * @param Version $version
     *
     * @return list<IndexedPage>
     */
    protected function indexVersion(Version $version): array
    {
        $tree = $this->repository->getTree($version->id);
        $pages = $this->flattenTree($tree);

        /** @var list<IndexedPage> */
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
     * @return IndexedPage
     */
    protected function indexPage(PageResource $page): IndexedPage
    {
        // Get the HTML content and strip HTML tags to get plain text
        $htmlContent = $page->getContent();
        $plainText = $this->stripHtmlTags($htmlContent);

        // Get frontmatter
        $frontMatter = $page->getFrontMatter();

        // Extract keywords if present
        $keywords = '';
        if (isset($frontMatter['keywords'])) {
            if (is_array($frontMatter['keywords'])) {
                $keywords = implode(' ', $frontMatter['keywords']);
            } elseif (is_string($frontMatter['keywords'])) {
                $keywords = $frontMatter['keywords'];
            }
        }

        // Extract other relevant metadata (description, tags, etc.)
        $metadata = [];
        $metadataFields = $this->config->get('learn.search.metadata_fields', ['description', 'tags', 'category', 'author']);
        foreach ($metadataFields as $field) {
            if (isset($frontMatter[$field])) {
                if (is_array($frontMatter[$field])) {
                    $metadata[] = implode(' ', $frontMatter[$field]);
                } elseif (is_string($frontMatter[$field])) {
                    $metadata[] = $frontMatter[$field];
                }
            }
        }
        $metadataString = implode(' ', $metadata);

        return new IndexedPage(
            title: $page->getTitle(),
            slug: $page->getSlug(),
            route: $page->getRoute(),
            content: $plainText,
            version: $page->getVersion()->id,
            keywords: $keywords,
            metadata: $metadataString,
        );
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
        // Combined regex: Add space before/after block elements to prevent word concatenation
        $result = preg_replace([
            '/<(div|p|h[1-6]|li|pre|code|blockquote)[^>]*>/i',  // Opening tags
            '/<\/(div|p|h[1-6]|li|pre|code|blockquote)>/i',     // Closing tags
            '/<(script|style)[^>]*>.*?<\/\1>/is',               // Remove script/style with content
        ], [
            ' $0',  // Space before opening tags
            '$0 ',  // Space after closing tags
            '',     // Remove script/style entirely
        ], $html);

        // Check if preg_replace failed
        if ($result === null) {
            // Fallback to original HTML if regex fails
            $result = $html;
        }

        // Strip remaining HTML tags
        $text = strip_tags($result);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Check if preg_replace failed
        if ($text === null) {
            // Fallback: at least decode entities from stripped HTML
            $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

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
            $children = $page->getChildren();
            if ($children !== null && count($children) > 0) {
                $flat = array_merge($flat, $this->flattenTree($children));
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
        $keyFormat = $this->config->get('learn.search.index.key', 'learn.search-index.%1$s');

        return sprintf($keyFormat, $version);
    }

    /**
     * Get the cache TTL for the search index.
     *
     * @return int The cache TTL in seconds
     */
    protected function getCacheTtl(): int
    {
        return $this->config->get('learn.search.index.ttl', 86400 * 7);
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
                $this->cache->forget($this->getCacheKey((string) $versionId));
            }
        } else {
            // Clear specific version
            $this->cache->forget($this->getCacheKey($version));
        }
    }
}
