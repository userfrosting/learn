<?php

declare(strict_types=1);

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Bakery;

use UserFrosting\Sprinkle\Core\Bakery\Event\SetupCommandEvent;

/**
 * Custom setup commands for Learn recipe.
 */
class SetupCommandListener
{
    public function __invoke(SetupCommandEvent $event): void
    {
        $event->setCommands([
            'setup:env'
        ]);
    }
}
