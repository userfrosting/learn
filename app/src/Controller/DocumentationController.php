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

/**
 * Documentation App Controller
 */
class DocumentationController
{
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
     * @param Twig     $view
     */
    public function pageVersioned(string $version, string $path, Response $response, Twig $view): Response
    {
        // TODO : Load the actual page content from the PagesDirectory service

        return $view->render($response, 'pages/doc.html.twig', [
            'version' => $version,
            'path'    => $path
        ]);
    }
}
