<?php

declare(strict_types=1);

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Listeners;

use UserFrosting\Sprinkle\Core\Event\ResourceLocatorInitiatedEvent;
use UserFrosting\UniformResourceLocator\ResourceStream;

/**
 * Register the required streams with the resource locator.
 */
class ResourceLocatorInitiated
{
    /**
     * Add all defined streams.
     *
     * @param ResourceLocatorInitiatedEvent $event
     */
    public function __invoke(ResourceLocatorInitiatedEvent $event): void
    {
        foreach ($this->getStreams() as $stream) {
            $event->locator->addStream($stream);
        }
    }

    /**
     * Returns all ResourceStream to register.
     *
     * @return ResourceStream[]
     */
    protected function getStreams(): array
    {
        return [
            new ResourceStream('pages', readonly: true)
        ];
    }
}
