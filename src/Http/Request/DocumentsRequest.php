<?php

declare(strict_types=1);

namespace CoderSapient\JsonApi\Http\Request;

use CoderSapient\JsonApi\Criteria\Filter;
use CoderSapient\JsonApi\Criteria\FilterOperator;
use CoderSapient\JsonApi\Criteria\Filters;
use CoderSapient\JsonApi\Criteria\Order;
use CoderSapient\JsonApi\Criteria\Orders;
use CoderSapient\JsonApi\Criteria\OrderType;
use CoderSapient\JsonApi\Document\Builder\DocumentsQuery;
use CoderSapient\JsonApi\Utils;

trait DocumentsRequest
{
    use JsonApiRequest;

    protected string $queryPage = 'page';
    protected string $queryPerPage = 'per_page';
    protected string $queryFilter = 'filter';
    protected string $querySort = 'sort';

    protected string $filterDelimiter = ',';
    protected string $sortDelimiter = ',';

    protected string $sortPrefix = '-';

    public function toQuery(): DocumentsQuery
    {
        $this->ensureQueryParamsIsValid();

        return (new DocumentsQuery($this->resourceType()))
            ->setFilters($this->filters())
            ->setOrders($this->orders())
            ->setIncludes($this->includes())
            ->setPage($this->page())
            ->setPerPage($this->perPage());
    }

    protected function supportedQueryParams(): array
    {
        return [
            $this->queryPage,
            $this->queryPerPage,
            $this->queryFilter,
            $this->querySort,
            $this->queryInclude,
        ];
    }

    /**
     * [
     *   'foo' => ['eq', 'gt', 'lt'],
     *   'bar' => ['like', 'lt'],
     * ].
     */
    protected function supportedFilters(): array
    {
        return [];
    }

    /**
     * ['foo', 'bar'].
     */
    protected function supportedSorting(): array
    {
        return [];
    }

    protected function page(): int
    {
        $page = $this->queryParam($this->queryPage, DocumentsQuery::DEFAULT_PAGE);

        $this->ensurePageIsValid($page);

        return (int) $page;
    }

    protected function perPage(): int
    {
        $perPage = $this->queryParam($this->queryPerPage, DocumentsQuery::DEFAULT_PER_PAGE);

        $this->ensurePerPageIsValid($perPage);

        return (int) $perPage;
    }

    protected function filters(): Filters
    {
        $filter = $this->queryParam($this->queryFilter, []);

        $this->ensureFilterIsValid($filter);

        $collect = new Filters();

        foreach ($this->normalizeFilter($filter) as $field => $filter) {
            foreach ($filter as $operator => $value) {
                $collect->add(Filter::fromValues($field, $operator, $value));
            }
        }

        return $collect;
    }

    protected function orders(): Orders
    {
        $sort = $this->queryParam($this->querySort, '');

        $this->ensureSortIsValid($sort);

        $collect = new Orders();

        foreach (Utils::explodeIfNotEmpty($sort, $this->sortDelimiter) as $field) {
            $by = Utils::subStrFirst($field, $this->sortPrefix);
            $type = $by === $field ? OrderType::ASC : OrderType::DESC;

            $collect->add(Order::fromValues($by, $type));
        }

        return $collect;
    }

    protected function ensurePageIsValid(mixed $page): void
    {
        if (! is_numeric($page) || (int) $page < 0) {
            $this->throwBadRequestException(
                sprintf('%s must be a non-negative integer', $this->queryPage),
                $this->queryPage,
            );
        }
    }

    protected function ensurePerPageIsValid(mixed $perPage): void
    {
        if (! is_numeric($perPage) || (int) $perPage < 1) {
            $this->throwBadRequestException(
                sprintf('%s must be a positive integer', $this->queryPerPage),
                $this->queryPerPage,
            );
        }
    }

    protected function ensureFilterIsValid(mixed $filter): void
    {
        $this->ensureQueryParamIsArray($this->queryFilter, $filter);

        $this->ensureFilterHasAValidStructure($filter);

        $this->ensureFilterIsSupported($filter);
    }

    protected function ensureSortIsValid(mixed $sort): void
    {
        $this->ensureQueryParamIsString($this->querySort, $sort);

        $sort = array_map(
            fn (string $value) => Utils::subStrFirst($value, $this->sortPrefix),
            Utils::explodeIfNotEmpty($sort, $this->sortDelimiter),
        );

        $this->ensureQueryParamIsSupported($this->querySort, $sort, $this->supportedSorting());
    }

    protected function ensureFilterHasAValidStructure(array $filter): void
    {
        foreach ($filter as $value) {
            foreach ((array) $value as $item) {
                if (is_array($item)) {
                    $this->throwBadRequestException(
                        sprintf(
                            "%s should have the following structure [?%s[field][operator]=value]",
                            $this->queryFilter,
                            $this->queryFilter,
                        ),
                        $this->queryFilter,
                    );
                }
            }
        }
    }

    protected function ensureFilterIsSupported(array $filter): void
    {
        foreach ($this->normalizeFilter($filter) as $field => $data) {
            foreach ($data as $operator => $value) {
                if (
                    ! isset($this->supportedFilters()[$field])
                    || ! in_array($operator, $this->supportedFilters()[$field], true)
                ) {
                    $this->throwBadRequestException(
                        sprintf(
                            "Not supported %s operator [%s] for field [%s]",
                            $this->queryFilter,
                            $operator,
                            $field,
                        ),
                        $this->queryFilter,
                    );
                }
            }
        }
    }

    protected function normalizeFilter(array $filter): array
    {
        $result = [];

        foreach ($filter as $field => $condition) {
            if (is_array($condition)) {
                foreach ($condition as $operator => $value) {
                    $result[$field][$operator] = Utils::explodeIfContains(
                        $value,
                        $this->filterDelimiter,
                    );
                }
            } else {
                $result[$field][FilterOperator::EQUAL] = Utils::explodeIfContains(
                    $condition,
                    $this->filterDelimiter,
                );
            }
        }

        return $result;
    }
}
