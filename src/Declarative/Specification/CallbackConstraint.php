<?php

namespace Greensight\LaravelElasticQuery\Declarative\Specification;

use Closure;
use Greensight\LaravelElasticQuery\Declarative\Contracts\Constraint;
use Greensight\LaravelElasticQuery\Raw\Contracts\BoolQuery;

final class CallbackConstraint implements Constraint
{
    public function __construct(private Closure $callback)
    {
    }

    public function __invoke(BoolQuery $query): void
    {
        ($this->callback)($query);
    }
}