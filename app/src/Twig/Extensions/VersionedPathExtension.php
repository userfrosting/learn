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
use Twig\TwigFilter;
use UserFrosting\Learn\Documentation\Version;

/**
 * Ensures root-relative href/src generated from Markdown are scoped to the current doc version.
 */
class VersionedPathExtension extends AbstractExtension
{
    /**
     * {@inheritDoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('version_paths', [$this, 'addVersionPrefix'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Prefix root-relative href/src attributes with the current version so links/images resolve correctly.
     */
    public function addVersionPrefix(string $html, Version $version): string
    {
        $prefix = $version->latest ? '' : '/' . trim($version->id, '/') . '/';

        return (string) preg_replace_callback(
            '/\b(href|src)="\/(?!\/)([^"]+)"/i',
            function (array $matches) use ($prefix, $version): string {
                // If already versioned (e.g., /6.0/...), leave untouched
                $path = $matches[2];
                if (!$version->latest && str_starts_with($path, trim($version->id, '/') . '/')) {
                    return $matches[0];
                }

                $normalizedPath = ltrim($path, '/');
                $resolved = ($prefix === '')
                    ? '/' . $normalizedPath
                    : rtrim($prefix, '/') . '/' . $normalizedPath;

                return sprintf('%s="%s"', $matches[1], $resolved);
            },
            $html
        );
    }
}
