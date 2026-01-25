<?php

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Learn\Search;

use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use UserFrosting\Learn\Recipe;
use UserFrosting\Learn\Search\StaticSprunje;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;
use UserFrosting\Testing\TestCase;

/**
 * Tests for StaticSprunje class.
 */
class StaticSprunjeTest extends TestCase
{
    protected string $mainSprinkle = Recipe::class;

    /**
     * Create a concrete implementation of StaticSprunje for testing.
     */
    protected function createSprunje(): TestableStaticSprunje
    {
        return new TestableStaticSprunje();
    }

    public function testSetSizeAndPage(): void
    {
        $sprunje = $this->createSprunje();

        $sprunje->setSize(10)->setPage(2);

        $result = $sprunje->getResultSet();

        $this->assertSame(10, $result['size']);
        $this->assertSame(2, $result['page']);
    }

    public function testGettersAndSetters(): void
    {
        $sprunje = $this->createSprunje();

        $sprunje->setSize(25)->setPage(3);

        $this->assertSame(25, $sprunje->getSize());
        $this->assertSame(3, $sprunje->getPage());
    }

    public function testDefaultValues(): void
    {
        $sprunje = $this->createSprunje();

        // Don't set any options
        $result = $sprunje->getResultSet();

        $this->assertSame(0, $result['size']); // null converts to 0
        $this->assertSame(1, $result['page']); // Default is 1
    }

    public function testSetSizeWithValidInteger(): void
    {
        $sprunje = $this->createSprunje();

        $sprunje->setSize(25);
        $result = $sprunje->getResultSet();

        $this->assertSame(25, $result['size']);
    }

    public function testSetSizeWithNull(): void
    {
        $sprunje = $this->createSprunje();

        $sprunje->setSize(null);
        $result = $sprunje->getResultSet();

        // null should convert to 0 in output (meaning all results)
        $this->assertSame(0, $result['size']);
    }

    public function testSetSizeWithInvalidValue(): void
    {
        $sprunje = $this->createSprunje();

        $this->expectException(ValidationException::class);
        $sprunje->setSize(0);
    }

    public function testSetSizeWithNegativeValue(): void
    {
        $sprunje = $this->createSprunje();

        $this->expectException(ValidationException::class);
        $sprunje->setSize(-1);
    }

    public function testSetPageWithValidInteger(): void
    {
        $sprunje = $this->createSprunje();

        $sprunje->setPage(3);
        $result = $sprunje->getResultSet();

        $this->assertSame(3, $result['page']);
    }

    public function testSetPageWithInvalidValue(): void
    {
        $sprunje = $this->createSprunje();

        $this->expectException(ValidationException::class);
        $sprunje->setPage(0);
    }

    public function testSetPageWithNegativeValue(): void
    {
        $sprunje = $this->createSprunje();

        $this->expectException(ValidationException::class);
        $sprunje->setPage(-1);
    }

    public function testGetResultSet(): void
    {
        $sprunje = $this->createSprunje();

        $result = $sprunje->getResultSet();

        $this->assertIsArray($result); // @phpstan-ignore-line
        $this->assertArrayHasKey('count', $result);
        $this->assertArrayHasKey('size', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('rows', $result);
    }

    public function testGetResultSetWithData(): void
    {
        $sprunje = $this->createSprunje();

        $result = $sprunje->getResultSet();

        $this->assertSame(5, $result['count']); // Test data has 5 items
        $this->assertIsArray($result['rows']);
        $this->assertCount(5, $result['rows']);
    }

    public function testApplyPaginationWithPageAndSize(): void
    {
        $sprunje = $this->createSprunje();

        $sprunje->setSize(2)->setPage(2); // Second page (1-based)

        $result = $sprunje->getResultSet();

        // Should return 2 items starting from offset 2
        $this->assertCount(2, $result['rows']);
        $this->assertSame('Item 3', $result['rows'][0]['name']); // Third item (0-indexed array)
    }

    public function testApplyPaginationWithFirstPage(): void
    {
        $sprunje = $this->createSprunje();

        $sprunje->setSize(2)->setPage(1); // First page (1-based)

        $result = $sprunje->getResultSet();

        $this->assertCount(2, $result['rows']);
        $this->assertSame('Item 1', $result['rows'][0]['name']);
        $this->assertSame('Item 2', $result['rows'][1]['name']);
    }

    public function testApplyPaginationWithNullSize(): void
    {
        $sprunje = $this->createSprunje();

        $sprunje->setSize(null)->setPage(1);

        $result = $sprunje->getResultSet();

        // Should return all items when size is null
        $this->assertCount(5, $result['rows']);
    }

    public function testSetColumns(): void
    {
        $sprunje = $this->createSprunje();

        $sprunje->setColumns(['name']);

        // Columns don't affect static collections (no select() support)
        // but we can verify the method works
        $result = $sprunje->getResultSet();

        $this->assertIsArray($result['rows']);
    }

    public function testApplyTransformations(): void
    {
        $sprunje = new TestableStaticSprunjeWithTransformation();

        $result = $sprunje->getResultSet();

        // Transformation adds '_transformed' to each name
        $this->assertStringEndsWith('_transformed', $result['rows'][0]['name']);
    }

    public function testCount(): void
    {
        $sprunje = $this->createSprunje();

        $result = $sprunje->getResultSet();

        $this->assertSame(5, $result['count']);
    }

    public function testToResponse(): void
    {
        $sprunje = $this->createSprunje();

        // Create a response using Slim's ResponseFactory
        $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
        $response = $responseFactory->createResponse();

        $response = $sprunje->toResponse($response);

        $this->assertInstanceOf(ResponseInterface::class, $response); // @phpstan-ignore-line
        $this->assertTrue($response->hasHeader('Content-Type'));
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));

        // Verify JSON is valid - need to rewind the stream after writing
        $response->getBody()->rewind();
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('count', $data);
        $this->assertArrayHasKey('rows', $data);
    }

    public function testToResponseWithPrettyPrint(): void
    {
        $sprunje = $this->createSprunje();

        // Create a response using Slim's ResponseFactory
        $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
        $response = $responseFactory->createResponse();
        $response = $sprunje->toResponse($response);

        // Rewind the stream after writing
        $response->getBody()->rewind();
        $body = (string) $response->getBody();

        // Should have pretty print formatting (newlines and indentation)
        $this->assertStringContainsString("\n", $body);
    }

    public function testPaginationBeyondAvailableItems(): void
    {
        $sprunje = $this->createSprunje();

        $sprunje->setSize(2)->setPage(10); // Way beyond available items

        $result = $sprunje->getResultSet();

        // Should return empty rows when page is beyond available items
        $this->assertEmpty($result['rows']);
        $this->assertSame(5, $result['count']); // Total count should still be 5
    }
}

/**
 * Concrete implementation of StaticSprunje for testing.
 *
 * @extends StaticSprunje<array{
 *     id: int,
 *     name: string
 * }>
 */
class TestableStaticSprunje extends StaticSprunje
{
    public function getItems(): Collection
    {
        // Return a simple collection for testing
        return collect([
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
            ['id' => 3, 'name' => 'Item 3'],
            ['id' => 4, 'name' => 'Item 4'],
            ['id' => 5, 'name' => 'Item 5'],
        ]);
    }
}

/**
 * Concrete implementation with custom transformation for testing.
 */
class TestableStaticSprunjeWithTransformation extends TestableStaticSprunje
{
    protected function applyTransformations(Collection $collection): Collection
    {
        return $collection->map(function ($item) {
            $item['name'] .= '_transformed';

            return $item;
        });
    }
}
