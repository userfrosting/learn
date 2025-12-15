<?php

declare(strict_types=1);

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Twig\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use UserFrosting\Learn\Documentation\PageResource;

/**
 * Helper function for the sidebar file tree.
 */
class FileTreeExtension extends AbstractExtension
{
    /**
     * Adds Twig functions `isChildActive`.
     *
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('isChildActive', [$this, 'isChildActive'])
        ];
    }

    /**
     * Check if any child in the array is active (matches current page).
     *
     * @param PageResource[] $children    Array of child pages
     * @param PageResource   $currentPage Current page object
     *
     * @return bool True if any child is active, false otherwise
     */
    public function isChildActive(array $children, ?PageResource $currentPage): bool
    {
        if (is_null($currentPage)) {
            return false;
        }

        foreach ($children as $child) {
            if ($currentPage->getSlug() == $child->getSlug()) {
                return true;
            }
            if (!is_null($child->getChildren()) && $child->getChildren() !== [] && $this->isChildActive($child->getChildren(), $currentPage)) {
                return true;
            }
        }

        return false;
    }
}
