<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Documentation;

use League\CommonMark\ConverterInterface;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Util\MarkdownFile;
use UserFrosting\Sprinkle\Core\Util\RouteParserInterface;
use UserFrosting\Support\Exception\FileNotFoundException;
use UserFrosting\UniformResourceLocator\ResourceInterface;

/**
 * Factory service for creating PageResource objects.
 *
 * This service handles the creation of PageResource objects and manages
 * the dependencies (Router & Markdown converter) so that PageResource
 * objects can focus on being data containers.
 */
class PageFactory
{
    public function __construct(
        protected ConverterInterface $markdown,
        protected RouteParserInterface $router,
        protected Config $config,
    ) {
    }

    /**
     * Create a PageResource from a ResourceInterface.
     *
     * @param Version           $version
     * @param ResourceInterface $resource
     *
     * @return PageResource
     */
    public function createFromResource(Version $version, ResourceInterface $resource): PageResource
    {
        // Read and process the markdown file
        $markdownFile = $this->readMarkdownFile($resource);

        // Create the PageResource with processed data
        $page = new PageResource(
            $version,
            $resource->getStream(),
            $resource->getLocation(),
            $resource->getPath(),
            $resource->getLocatorBasePath(),
            $markdownFile,
        );

        // Generate the route based on version and slug
        if ($version->latest) {
            $route = $this->router->urlFor('documentation', ['path' => $page->getSlug()]);
        } else {
            $route = $this->router->urlFor('documentation.versioned', [
                'version' => $version->id,
                'path'    => $page->getSlug(),
            ]);
        }
        $page->setRoute($route);

        // Generate the GitHub URL
        $githubBaseUrl = rtrim($this->config->get('learn.github.url'), '/');
        $githubPath = ltrim($this->config->get('learn.github.path'), '/');
        $githubBranch = $this->config->get('learn.github.branch', 'main');
        $githubUrl = sprintf('%s/blob/%s/%s/%s', $githubBaseUrl, $githubBranch, $githubPath, ltrim($resource->getPath(), '/'));
        $page->setGithub($githubUrl);

        return $page;
    }

    /**
     * Read and process a markdown file from a resource.
     *
     * @param ResourceInterface $resource
     *
     *
     * @throws FileNotFoundException
     * @return MarkdownFile
     */
    protected function readMarkdownFile(ResourceInterface $resource): MarkdownFile
    {
        $path = $resource->getAbsolutePath();
        if (($content = @file_get_contents($path)) === false) {
            throw new FileNotFoundException("The file '$path' could not be read.");
        }

        $result = $this->markdown->convert($content);
        $frontMatter = method_exists($result, 'getFrontMatter') ? $result->getFrontMatter() : [];

        return new MarkdownFile($result, $frontMatter);
    }
}
