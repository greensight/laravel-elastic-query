<?php

namespace Greensight\LaravelElasticQuery\Declarative\Contracts;

use Greensight\LaravelElasticQuery\Raw\Contracts\BoolQuery;

interface FilterAction
{
    public function __invoke(BoolQuery $query, mixed $value, string $field): void;
}