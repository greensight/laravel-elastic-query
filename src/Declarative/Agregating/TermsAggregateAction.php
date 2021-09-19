<?php

namespace Greensight\LaravelElasticQuery\Declarative\Agregating;

use Greensight\LaravelElasticQuery\Declarative\Contracts\AggregateAction;
use Greensight\LaravelElasticQuery\Raw\Contracts\AggregationsBuilder;

class TermsAggregateAction implements AggregateAction
{
    public function __invoke(AggregationsBuilder $builder, string $name, string $field): void
    {
        $builder->terms($name, $field);
    }
}