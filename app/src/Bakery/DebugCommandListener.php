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

use UserFrosting\Sprinkle\Core\Bakery\Event\DebugCommandEvent;

/**
 * Custom debug commands for Learn recipe.
 */
class DebugCommandListener
{
    public function __invoke(DebugCommandEvent $event): void
    {
        $event->setCommands([
            'debug:version',
            'sprinkle:list',
        ]);
    }
}
