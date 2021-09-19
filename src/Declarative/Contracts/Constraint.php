<?php

namespace Greensight\LaravelElasticQuery\Declarative\Contracts;

use Greensight\LaravelElasticQuery\Raw\Contracts\BoolQuery;

interface Constraint
{
    public function __invoke(BoolQuery $query): void;
}
