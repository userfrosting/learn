<?php

declare(strict_types=1);

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Bakery;

use UserFrosting\Sprinkle\Core\Bakery\Event\BakeCommandEvent;

/**
 * Custom bake commands for Learn recipe.
 */
class BakeCommandListener
{
    public function __invoke(BakeCommandEvent $event): void
    {
        $event->setCommands([
            'debug',
            'assets:build',
            'clear-cache'
        ]);
    }
}
