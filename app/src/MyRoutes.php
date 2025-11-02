<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn;

use Slim\App;
use UserFrosting\Learn\Controller\DocumentationController;
use UserFrosting\Routes\RouteDefinitionInterface;

class MyRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        // TODO : Add to level for version (path is optional below, but throws an error because it can't invoke the class doesn't have a default value)
        // TODO : Same for non-versioned top level route
        $app->get('/{version:\d+\.\d+}/[{path:.*}]', [DocumentationController::class, 'pageVersioned'])->setName('documentation.versioned');
        $app->get('/[{path:.*}]', [DocumentationController::class, 'page'])->setName('documentation');
    }
}
