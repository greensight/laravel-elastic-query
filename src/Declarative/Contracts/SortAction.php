<?php

namespace Greensight\LaravelElasticQuery\Declarative\Contracts;

use Greensight\LaravelElasticQuery\Raw\Contracts\SortableQuery;

interface SortAction
{
    public function __invoke(SortableQuery $query, string $order, ?string $mode, string $field): void;
}