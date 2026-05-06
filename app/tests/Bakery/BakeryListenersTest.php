<?php

declare(strict_types=1);

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Learn\Bakery;

use PHPUnit\Framework\TestCase;
use UserFrosting\Learn\Bakery\BakeCommandListener;
use UserFrosting\Learn\Bakery\DebugCommandListener;
use UserFrosting\Learn\Bakery\DebugVerboseCommandListener;
use UserFrosting\Learn\Bakery\SetupCommandListener;
use UserFrosting\Sprinkle\Core\Bakery\Event\BakeCommandEvent;
use UserFrosting\Sprinkle\Core\Bakery\Event\DebugCommandEvent;
use UserFrosting\Sprinkle\Core\Bakery\Event\DebugVerboseCommandEvent;
use UserFrosting\Sprinkle\Core\Bakery\Event\SetupCommandEvent;

/**
 * Tests for Bakery event listeners.
 */
class BakeryListenersTest extends TestCase
{
    public function testBakeCommandListener(): void
    {
        $event = new BakeCommandEvent();
        $listener = new BakeCommandListener();
        $listener($event);

        $this->assertSame(
            ['debug', 'assets:build', 'clear-cache', 'search:index'],
            $event->getCommands()
        );
    }

    public function testDebugCommandListener(): void
    {
        $event = new DebugCommandEvent();
        $listener = new DebugCommandListener();
        $listener($event);

        $this->assertSame(
            ['debug:version', 'sprinkle:list'],
            $event->getCommands()
        );
    }

    public function testDebugVerboseCommandListener(): void
    {
        $event = new DebugVerboseCommandEvent();
        $listener = new DebugVerboseCommandListener();
        $listener($event);

        $this->assertSame(
            ['debug:locator', 'debug:events', 'debug:twig'],
            $event->getCommands()
        );
    }

    public function testSetupCommandListener(): void
    {
        $event = new SetupCommandEvent();
        $listener = new SetupCommandListener();
        $listener($event);

        $this->assertSame(
            ['setup:env'],
            $event->getCommands()
        );
    }
}
