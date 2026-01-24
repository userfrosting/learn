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
use Valitron\Validator;

/**
 * Implements a simple version of Sprunje for paginating a static collection.
 *
 * @template TOptions of array{
 *     size: string|int|null,
 *     page: string|int|null,
 * }
 * @template TItem
 */
abstract class StaticSprunje
{
    /**
     * @var TOptions Default HTTP request parameters.
     */
    protected array $options = [
        'size'    => 'all',
        'page'    => null,
    ];

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
     * Set Sprunje options.
     *
     * @param array<string, mixed> $options Partial TOptions
     *
     * @return static
     */
    public function setOptions(array $options): static
    {
        $options = $this->validateOptions($options);

        // @phpstan-ignore-next-line - Can't make array_replace_recursive hint at TOptions
        $this->options = array_replace_recursive($this->options, $options);

        return $this;
    }

    /**
     * Validate option using Validator. Can also mutate options as needed,
     * e.g., setting defaults.
     *
     * @param array<string, mixed> $options
     *
     * @throws ValidationException
     *
     * @return array<string, mixed>
     */
    protected function validateOptions(array $options): array
    {
        // Validation on input data
        $v = new Validator($options);
        $v->rule('regex', 'size', '/all|[0-9]+/i');
        $v->rule('integer', 'page');

        if (!$v->validate()) {
            $e = new ValidationException();
            $e->addErrors($v->errors()); // @phpstan-ignore-line errors returns array with no arguments

            throw $e;
        }

        return $options;
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
        $payload = json_encode($this->getArray(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Executes the sprunje query, applying all sorts, filters, and pagination.
     *
     * Returns an array containing `count` (the total number of rows, before filtering),
     * and `rows` (the filtered result set).
     *
     * @return array<string, mixed>
     */
    public function getArray(): array
    {
        list($count, $rows) = $this->getModels();

        // Return sprunjed results
        return [
            $this->countKey => $count,
            $this->sizeKey  => (int) $this->options['size'],
            $this->pageKey  => (int) $this->options['page'],
            $this->rowsKey  => $rows->values()->toArray(),
        ];
    }

    /**
     * Executes the sprunje query, applying all sorts, filters, and pagination.
     *
     * Returns the filtered, paginated result set and the counts.
     *
     * @return array{int, Collection<int, TItem>}
     */
    public function getModels(): array
    {
        $query = $this->getQuery();

        // Count unfiltered total
        $count = $this->count($query);

        // Paginate
        $query = $this->applyPagination($query);

        // Execute query - only apply select if not wildcard/empty
        if ($this->columns !== []) {
            $query = $query->select($this->columns); // @phpstan-ignore-line
        }

        $query = collect($query);

        // Perform any additional transformations on the dataset
        $query = $this->applyTransformations($query);

        return [$count, $query];
    }

    /**
     * Get the underlying queryable object in its current state.
     *
     * @return Collection<int, TItem>
     */
    abstract public function getQuery(): Collection;

    /**
     * Apply pagination based on the `page` and `size` options.
     *
     * @param Collection<int, TItem> $query
     *
     * @return Collection<int, TItem>
     */
    public function applyPagination(Collection $query): Collection
    {
        $page = $this->options['page'];
        $size = $this->options['size'];

        if (!is_null($page) && !is_null($size) && $size !== 'all') {
            $offset = (int) $size * (int) $page;
            $query = $query->skip($offset)->take((int) $size);
        }

        return $query;
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
     * Get the unpaginated count of items (before filtering) in this query.
     *
     * @param Collection<int, TItem> $query
     *
     * @return int
     */
    protected function count(Collection $query): int
    {
        return $query->count();
    }
}
