<?php

declare(strict_types=1);

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use UserFrosting\Learn\Documentation\DocumentationRepository;
use UserFrosting\Learn\Documentation\VersionValidator;

/**
 * Route middleware to inject the documentation menu and versions info into Twig
 * globals, based on the current route placeholders.
 */
class TwigGlobals implements MiddlewareInterface
{
    public function __construct(
        protected DocumentationRepository $pagesDirectory,
        protected VersionValidator $versionValidator,
        protected Twig $twig,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Get route object to extract route arguments
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        if ($route !== null) {
            $version = $route->getArgument('version', '');
            $path = $route->getArgument('path', '') ?? '';

            $this->twig->getEnvironment()->addGlobal('menu', $this->pagesDirectory->getTree($version));
            $this->twig->getEnvironment()->addGlobal('version_selector', $this->pagesDirectory->getAlternateVersions($path));
            $this->twig->getEnvironment()->addGlobal('current_version', $this->versionValidator->getVersion($version));
        }

        return $handler->handle($request);
    }
}
