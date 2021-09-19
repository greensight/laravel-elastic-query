<?php

namespace Greensight\LaravelElasticQuery\Declarative\Filtering;

use Greensight\LaravelElasticQuery\Declarative\Contracts\FilterAction;
use Greensight\LaravelElasticQuery\Raw\Contracts\BoolQuery;

class ExistsFilterAction implements FilterAction
{
    public function __invoke(BoolQuery $query, mixed $value, string $field): void
    {
        $value === true
            ? $query->whereNotNull($field)
            : $query->whereNull($field);
    }
}
