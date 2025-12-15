<?php

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Documentation;

/**
 * Represents a documentation version.
 */
class Version
{
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly bool $latest = false
    ) {
    }

    public function uri(): string
    {
        return $this->latest ? '' : $this->id;
    }
}
