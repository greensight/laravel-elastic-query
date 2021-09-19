<?php

namespace Greensight\LaravelElasticQuery\Declarative\Processors;

use Greensight\LaravelElasticQuery\Declarative\Filtering\AllowedFilter;
use Greensight\LaravelElasticQuery\Declarative\Specification\Specification;
use Greensight\LaravelElasticQuery\Declarative\Specification\Visitor;
use Greensight\LaravelElasticQuery\Raw\Contracts\BoolQuery;

class ConstraintProcessor implements Visitor
{
    public function __construct(private BoolQuery $query)
    {
    }

    public function visitRoot(Specification $specification): void
    {
        $this->buildConstraints($this->query, $specification);
    }

    public function visitNested(string $field, Specification $specification): void
    {
        if (!$this->hasActiveFilters($specification)) {
            return;
        }

        $this->query->whereHas(
            $field,
            fn(BoolQuery $query) => $this->buildConstraints($query, $specification)
        );
    }

    public function done(): void
    {
    }

    private function buildConstraints(BoolQuery $query, Specification $specification): void
    {
        foreach ($specification->constraints() as $constraint) {
            $constraint($query);
        }
    }

    private function hasActiveFilters(Specification $specification): bool
    {
        $activeFilter = $specification->filters()
            ->first(fn(AllowedFilter $filter) => $filter->isActive());

        return $activeFilter !== null;
    }
}