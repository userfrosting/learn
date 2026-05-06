<?php

declare(strict_types=1);

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Learn;

use UserFrosting\Learn\Bakery\SearchIndexCommand;
use UserFrosting\Learn\Recipe;
use UserFrosting\Testing\TestCase;

/**
 * Tests for Recipe class.
 */
class RecipeTest extends TestCase
{
    protected string $mainSprinkle = Recipe::class;

    public function testGetBakeryCommands(): void
    {
        $recipe = new Recipe();

        $this->assertSame([SearchIndexCommand::class], $recipe->getBakeryCommands());
    }
}
