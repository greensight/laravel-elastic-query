<?php

namespace Greensight\LaravelElasticQuery\Declarative\Contracts;

use Greensight\LaravelElasticQuery\Raw\Contracts\SortableQuery;

interface Sort
{
    public function __invoke(SortableQuery $query, ?string $order): void;
}