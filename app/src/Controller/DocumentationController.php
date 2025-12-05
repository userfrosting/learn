<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
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
     * @param string   $path
     * @param Response $response
     * @param Twig     $view
     */
    public function page(string $path, Response $response, Twig $view): Response
    {
        return $this->pageVersioned('', $path, $response, $view);
    }

    /**
     * Render the versioned documentation page.
     * Request type: GET.
     *
     * @param string   $version
     * @param string   $path
     * @param Response $response
     * @param Twig     $twig
     */
    public function pageVersioned(string $version, string $path, Response $response, Twig $twig): Response
    {
        $page = $this->pagesDirectory->getPage($path, $version);
        $template = sprintf('pages/%s.html.twig', $page->getTemplate());

        return $twig->render($response, $template, [
            'page' => $page,
        ]);
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
        $imageContent = file_get_contents($imageResource->getAbsolutePath());

        if ($imageContent === false) {
            $response->getBody()->write('Image not found');

            return $response->withStatus(404);
        }

        // Determine MIME type based on file extension
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'   => 'image/png',
            'gif'   => 'image/gif',
            'svg'   => 'image/svg+xml',
            'webp'  => 'image/webp',
            'bmp'   => 'image/bmp',
            'ico'   => 'image/x-icon',
            default => 'application/octet-stream',
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
