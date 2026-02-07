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
            if ($this->isCacheEnabled()) {
                $this->cache->put(
                    $this->getCacheKey($versionObj->id),
                    $pages,
                    $this->getCacheTtl()
                );
            }
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
        if (!$this->isCacheEnabled()) {
            $versionObj = $this->versionValidator->getVersion($version);

            return $this->indexVersion($versionObj);
        }

        $keyFormat = $this->config->getString('learn.search.index.key', '');
        $cacheKey = sprintf($keyFormat, $version);

        $index = $this->cache->get($cacheKey);

        // If cache is empty, try to build the index first
        if (!is_array($index)) {
            $this->buildIndex($version);
            $index = $this->cache->get($cacheKey);
        }

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
        $pages = $this->repository->getFlattenedTree($version->id);

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
        $frontMatter = $page->getFrontMatter();

        return new IndexedPage(
            title: $page->getTitle(),
            slug: $page->getSlug(),
            route: $page->getRoute(),
            content: $this->stripHtmlTags($page->getContent()),
            version: $page->getVersion()->id,
            keywords: $this->extractFieldAsString($frontMatter, 'keywords'),
            metadata: $this->extractMetadata($frontMatter),
        );
    }

    /**
     * Extract a frontmatter field as string.
     *
     * @param array<string, mixed> $frontMatter
     * @param string               $field
     *
     * @return string
     */
    protected function extractFieldAsString(array $frontMatter, string $field): string
    {
        if (!isset($frontMatter[$field])) {
            return '';
        }

        $value = $frontMatter[$field];

        return is_array($value) ? implode(' ', $value) : (string) $value;
    }

    /**
     * Extract metadata fields as concatenated string.
     *
     * @param array<string, mixed> $frontMatter
     *
     * @return string
     */
    protected function extractMetadata(array $frontMatter): string
    {
        $fields = $this->config->get('learn.search.metadata_fields', []);
        $values = [];

        foreach ($fields as $field) {
            $value = $this->extractFieldAsString($frontMatter, $field);
            if ($value !== '') {
                $values[] = $value;
            }
        }

        return implode(' ', $values);
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
        // Remove script/style tags with content
        $html = preg_replace('/<(script|style)[^>]*>.*?<\/\1>/is', '', $html) ?? $html;

        // Add spaces around block elements to prevent word concatenation
        $html = preg_replace('/<(div|p|h[1-6]|li|pre|code|blockquote)[^>]*>/i', ' ', $html) ?? $html;

        // Strip all remaining HTML tags and decode entities
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize whitespace
        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
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
        if (!$this->isCacheEnabled()) {
            return;
        }

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

    /**
     * Determine if search index caching is enabled.
     */
    protected function isCacheEnabled(): bool
    {
        return $this->config->getBool('learn.search.index.enabled', true);
    }
}
