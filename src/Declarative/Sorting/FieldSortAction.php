<?php

namespace Greensight\LaravelElasticQuery\Declarative\Sorting;

use Greensight\LaravelElasticQuery\Declarative\Contracts\SortAction;
use Greensight\LaravelElasticQuery\Raw\Contracts\SortableQuery;

class FieldSortAction implements SortAction
{
    public function __invoke(SortableQuery $query, string $order, ?string $mode, string $field): void
    {
        $query->sortBy($field, $order, $mode);
    }
}