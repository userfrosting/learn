<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Documentation;

use UserFrosting\Sprinkle\Core\Util\MarkdownFile;
use UserFrosting\UniformResourceLocator\Resource;
use UserFrosting\UniformResourceLocator\ResourceLocationInterface;
use UserFrosting\UniformResourceLocator\ResourceStreamInterface;

/**
 * The representation of a Documentation Page as a Resource.
 */
class PageResource extends Resource
{
    // TODO : Make protected, and add getter and setter
    /** @var PageResource[] */
    public ?array $children = null;

    /**
     * @param Version                        $version
     * @param ResourceStreamInterface        $stream
     * @param null|ResourceLocationInterface $location
     * @param string                         $path
     * @param string                         $locatorBasePath
     * @param MarkdownFile                   $markdownFile
     * @param string                         $route
     */
    public function __construct(
        protected readonly Version $version,
        protected ResourceStreamInterface $stream,
        protected ?ResourceLocationInterface $location,
        protected string $path,
        protected string $locatorBasePath,
        protected readonly MarkdownFile $markdownFile,
        protected string $route = ''
    ) {
        parent::__construct($stream, $location, $path, $locatorBasePath);
    }

    /**
     * Get the route to this page.
     *
     * @return string The route to this page
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Set the route for this page.
     *
     * @param string $route The route to set
     */
    public function setRoute(string $route): void
    {
        $this->route = $route;
    }

    /**
     * Return the parent of a given page slug.
     *
     * @return string The item parent slug
     */
    public function getParentSlug(): string
    {
        $fragments = explode('/', $this->getSlug());
        array_pop($fragments);

        return implode('/', $fragments);
    }

    /**
     * Get the slug for this page.
     *
     * The slug is the path to the page, without the version or sequence numbers.
     * For example, for a page with path `pages/6.0/02.background/02.the-client-conversation/docs.md`,
     * the slug would be `background/the-client-conversation`.
     *
     * @return string The page slug
     */
    public function getSlug(): string
    {
        $path = pathinfo($this->getBasePath(), PATHINFO_DIRNAME);

        // Remove the version
        $path = (string) preg_replace('#' . preg_quote($this->version->id, '#') . '/#', '', $path);

        // Remove the sequence number form the path to get the slug
        $dirFragments = explode('/', $path);
        foreach ($dirFragments as $key => $fragment) {
            $fragmentList = explode('.', $fragment);
            $dirFragments[$key] = end($fragmentList);
        }

        // Glue the fragments back together
        return implode('/', $dirFragments);
    }

    public function getContent(): string
    {
        return $this->markdownFile->content;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFrontMatter(): array
    {
        return $this->markdownFile->frontMatter;
    }

    public function getTitle(): string
    {
        return $this->markdownFile->frontMatter['title'] ?? $this->getSlug();
    }

    public function getVersion(): Version
    {
        return $this->version;
    }
}
