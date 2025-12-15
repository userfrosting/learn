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

use UserFrosting\Sprinkle\Core\Bakery\Event\DebugVerboseCommandEvent;

/**
 * Custom debug:verbose commands for Learn recipe.
 */
class DebugVerboseCommandListener
{
    public function __invoke(DebugVerboseCommandEvent $event): void
    {
        $event->setCommands([
            'debug:locator',
            'debug:events',
            'debug:twig',
        ]);
    }
}
