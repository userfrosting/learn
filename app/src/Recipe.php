<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn;

use UserFrosting\Event\EventListenerRecipe;
use UserFrosting\Learn\Bakery\BakeCommandListener;
use UserFrosting\Learn\Bakery\DebugCommandListener;
use UserFrosting\Learn\Bakery\DebugVerboseCommandListener;
use UserFrosting\Learn\Bakery\SetupCommandListener;
use UserFrosting\Learn\Listeners\ResourceLocatorInitiated;
use UserFrosting\Learn\ServicesProvider\MarkdownService;
use UserFrosting\Learn\Twig\Extensions\FileTreeExtension;
use UserFrosting\Sprinkle\Core\Bakery\Event\BakeCommandEvent;
use UserFrosting\Sprinkle\Core\Bakery\Event\DebugCommandEvent;
use UserFrosting\Sprinkle\Core\Bakery\Event\DebugVerboseCommandEvent;
use UserFrosting\Sprinkle\Core\Bakery\Event\SetupCommandEvent;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\Core\Event\ResourceLocatorInitiatedEvent;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\TwigExtensionRecipe;
use UserFrosting\Sprinkle\SprinkleRecipe;

/**
 * The Sprinkle Recipe.
 *
 * @see https://learn.userfrosting.com/sprinkles/recipe
 */
class Recipe implements
    SprinkleRecipe,
    EventListenerRecipe,
    TwigExtensionRecipe
{
    /**
     * Return the Sprinkle name.
     *
     * @see https://learn.userfrosting.com/sprinkles/recipe#name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Learn';
    }

    /**
     * Return the Sprinkle dir path.
     *
     * @see https://learn.userfrosting.com/sprinkles/recipe#path
     *
     * @return string
     */
    public function getPath(): string
    {
        return __DIR__ . '/../';
    }

    /**
     * Return dependent sprinkles.
     *
     * First one will be loaded first, with this sprinkle being last.
     * Dependent sprinkle dependencies will also be loaded recursively.
     *
     * @see https://learn.userfrosting.com/sprinkles/recipe#dependent-sprinkles
     *
     * @return class-string<SprinkleRecipe>[]
     */
    public function getSprinkles(): array
    {
        return [
            // Use our custom Core recipe
            CoreRecipe::class
        ];
    }

    /**
     * Returns a list of routes definition in PHP files.
     *
     * @see https://learn.userfrosting.com/sprinkles/recipe#routes
     *
     * @return class-string<\UserFrosting\Routes\RouteDefinitionInterface>[]
     */
    public function getRoutes(): array
    {
        return [
            MyRoutes::class,
        ];
    }

    /**
     * Returns a list of all PHP-DI services/container definitions class.
     *
     * @see https://learn.userfrosting.com/sprinkles/recipe#services
     *
     * @return class-string<\UserFrosting\ServicesProvider\ServicesProviderInterface>[]
     */
    public function getServices(): array
    {
        return [
            MarkdownService::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getEventListeners(): array
    {
        return [
            BakeCommandEvent::class       => [
                BakeCommandListener::class,
            ],
            DebugVerboseCommandEvent::class => [
                DebugVerboseCommandListener::class,
            ],
            DebugCommandEvent::class        => [
                DebugCommandListener::class,
            ],
            SetupCommandEvent::class        => [
                SetupCommandListener::class,
            ],
            ResourceLocatorInitiatedEvent::class => [
                ResourceLocatorInitiated::class,
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getTwigExtensions(): array
    {
        return [
            FileTreeExtension::class,
        ];
    }
}
