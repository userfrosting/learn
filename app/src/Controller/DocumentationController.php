<?php

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use UserFrosting\Learn\Documentation\DocumentationRepository;

/**
 * Documentation App Controller
 */
class DocumentationController
{
    public function __construct(
        protected DocumentationRepository $pagesDirectory,
    ) {
    }

    /**
     * Render the documentation page.
     * Request type: GET.
     *
     * @param Response $response
     * @param Twig     $view
     * @param string   $path
     */
    public function page(Response $response, Twig $view, string $path = ''): Response
    {
        return $this->pageVersioned('', $response, $view, $path);
    }

    /**
     * Render the versioned documentation page.
     * Request type: GET.
     *
     * @param string   $version
     * @param Response $response
     * @param Twig     $twig
     * @param string   $path
     */
    public function pageVersioned(string $version, Response $response, Twig $twig, string $path = ''): Response
    {
        $page = $this->pagesDirectory->getPage($path, $version);
        $template = sprintf('pages/%s.html.twig', $page->getTemplate());

        $response = $twig->render($response, $template, [
            'page'         => $page,
            'breadcrumbs'  => $this->pagesDirectory->getBreadcrumbsForPage($page),
            'previousPage' => $this->pagesDirectory->getPreviousPageForPage($page),
            'nextPage'     => $this->pagesDirectory->getNextPageForPage($page),
        ]);

        $maxAge = $this->pagesDirectory->getHttpCacheMaxAge();
        if ($maxAge > 0) {
            $response = $response->withHeader('Cache-Control', "public, max-age={$maxAge}");
        }

        return $response;
    }

    /**
     * Serve an image file.
     * Request type: GET.
     *
     * @param string   $path
     * @param Response $response
     * @param Twig     $view
     */
    public function image(string $path, Response $response, Twig $view): Response
    {
        return $this->imageVersioned('', $path, $response, $view);
    }

    /**
     * Serve a versioned image file.
     * Request type: GET.
     *
     * @param string   $version
     * @param string   $path
     * @param Response $response
     * @param Twig     $view
     */
    public function imageVersioned(string $version, string $path, Response $response, Twig $view): Response
    {
        // Get the versioned image resource from the repository
        $imageResource = $this->pagesDirectory->getVersionedImage($version, $path);

        // Get the image content
        $absolutePath = $imageResource->getAbsolutePath();
        if (!is_readable($absolutePath)) {
            $response->getBody()->write('Image not found');

            return $response->withStatus(404);
        }

        /** @var string $imageContent */
        $imageContent = file_get_contents($absolutePath);

        // Determine MIME type based on file extension
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'svg'         => 'image/svg+xml',
            'webp'        => 'image/webp',
            'bmp'         => 'image/bmp',
            'ico'         => 'image/x-icon',
            default       => 'application/octet-stream',
        };

        // Write the image content to response body
        $response->getBody()->write($imageContent);

        // Set appropriate headers
        return $response
            ->withHeader('Content-Type', $mimeType)
            ->withHeader('Content-Length', (string) strlen($imageContent))
            ->withHeader('Cache-Control', 'public, max-age=3600');
    }
}
