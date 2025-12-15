<?php

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn;

use Slim\App;
use UserFrosting\Learn\Controller\DocumentationController;
use UserFrosting\Learn\Middleware\TwigGlobals;
use UserFrosting\Routes\RouteDefinitionInterface;

class MyRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        // Route for versioned and non-versioned images
        $app->get('/{version:\d+\.\d+}/images/{path:.*}', [DocumentationController::class, 'imageVersioned'])
            ->add(TwigGlobals::class)
            ->setName('documentation.image.versioned');
        $app->get('/images/{path:.*}', [DocumentationController::class, 'image'])
            ->add(TwigGlobals::class)
            ->setName('documentation.image');

        // Route for versioned and non-versioned documentation pages
        $app->get('/{version:\d+\.\d+}[/{path:.*}]', [DocumentationController::class, 'pageVersioned'])
            ->add(TwigGlobals::class)
            ->setName('documentation.versioned');
        $app->get('[/{path:.*}]', [DocumentationController::class, 'page'])
            ->add(TwigGlobals::class)
            ->setName('documentation');
    }
}
