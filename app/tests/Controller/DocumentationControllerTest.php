<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Learn\Controller;

use UserFrosting\Learn\Recipe;
use UserFrosting\Testing\TestCase;

/**
 * Tests for AppController Class.
 *
 * N.B.: This file is sage to edit or delete.
 */
class DocumentationControllerTest extends TestCase
{
    protected string $mainSprinkle = Recipe::class;

    /**
     * Test index (`/`) page.
     */
    public function testPageIndex(): void
    {
        // Create request with method and url and fetch response
        // $request = $this->createRequest('GET', '/'); TEMP FIX BELOW
        $request = $this->createRequest('GET', '/quick-start');
        $response = $this->handleRequest($request);

        // Asserts
        $this->assertResponseStatus(200, $response);
        $this->assertNotSame('', (string) $response->getBody());
    }
}
