<?php

declare(strict_types=1);

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Search;

use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;

/**
 * Implements a simple version of Sprunje for paginating a static collection.
 *
 * @template TItem
 */
abstract class StaticSprunje
{
    /** @var int|null Number of results per page. Null means return all results. */
    protected ?int $size = null;

    /** @var int Page number (1-based). */
    protected int $page = 1;

    /**
     * @var string[] Fields to show in output. Empty array will load all.
     */
    protected array $columns = [];

    /** @var string Array key for the total unfiltered object count. */
    protected string $countKey = 'count';

    /** @var string Array key for the actual result set. */
    protected string $rowsKey = 'rows';

    /** @var string Array key for the actual result set. */
    protected string $sizeKey = 'size';

    /** @var string Array key for the actual result set. */
    protected string $pageKey = 'page';

    /**
     * Set the page size.
     *
     * @param int|null $size Number of results per page, or null for all results
     *
     * @throws ValidationException
     *
     * @return static
     */
    public function setSize(?int $size): static
    {
        if ($size !== null && $size < 1) {
            $e = new ValidationException();
            $e->addErrors(['size' => ['Size must be null or at least 1']]);

            throw $e;
        }

        $this->size = $size;

        return $this;
    }

    /**
     * Get the page size.
     *
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * Set the page number (1-based).
     *
     * @param int $page Page number
     *
     * @throws ValidationException
     *
     * @return static
     */
    public function setPage(int $page): static
    {
        if ($page < 1) {
            $e = new ValidationException();
            $e->addErrors(['page' => ['Page must be at least 1']]);

            throw $e;
        }

        $this->page = $page;

        return $this;
    }

    /**
     * Get the page number.
     *
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Execute the query and build the results, and append them in the appropriate format to the response.
     *
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function toResponse(ResponseInterface $response): ResponseInterface
    {
        $payload = json_encode($this->getResultSet(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Execute processing to get the complete result set with metadata.
     *
     * Returns an array containing `count` (total items), `size` (page size),
     * `page` (current page), and `rows` (the filtered result set).
     *
     * @return array<string, mixed>
     */
    public function getResultSet(): array
    {
        $collection = $this->getItems();

        // Count unfiltered total
        $count = $this->count($collection);

        // Paginate
        $collection = $this->applyPagination($collection);

        // Execute query - only apply select if not wildcard/empty
        if ($this->columns !== []) {
            $collection = $collection->select($this->columns); // @phpstan-ignore-line
        }

        $collection = collect($collection);

        // Perform any additional transformations on the dataset
        $collection = $this->applyTransformations($collection);

        // Return complete result set with metadata
        return [
            $this->countKey => $count,
            $this->sizeKey  => $this->size ?? 0,
            $this->pageKey  => $this->page,
            $this->rowsKey  => $collection->values()->toArray(),
        ];
    }

    /**
     * Get the base collection of items to process.
     *
     * @return Collection<int, TItem>
     */
    abstract public function getItems(): Collection;

    /**
     * Apply pagination based on the `page` and `size` options.
     *
     * @param Collection<int, TItem> $collection
     *
     * @return Collection<int, TItem>
     */
    public function applyPagination(Collection $collection): Collection
    {
        if ($this->size !== null) {
            // Page is 1-based, so subtract 1 for offset calculation
            $offset = $this->size * ($this->page - 1);
            $collection = $collection->skip($offset)->take($this->size);
        }

        return $collection;
    }

    /**
     * Set fields to show in output.
     *
     * @param string[] $columns
     *
     * @return static
     */
    public function setColumns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Set any transformations you wish to apply to the collection, after the query is executed.
     * This method is meant to be customized in child class.
     *
     * @param Collection<int, TItem> $collection
     *
     * @return Collection<int, TItem>
     */
    protected function applyTransformations(Collection $collection): Collection
    {
        return $collection;
    }

    /**
     * Get the unpaginated count of items in the collection.
     *
     * @param Collection<int, TItem> $collection
     *
     * @return int
     */
    protected function count(Collection $collection): int
    {
        return $collection->count();
    }
}
