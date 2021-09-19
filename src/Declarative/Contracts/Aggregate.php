<?php

namespace Greensight\LaravelElasticQuery\Declarative\Contracts;

use Greensight\LaravelElasticQuery\Raw\Contracts\AggregationsBuilder;

interface Aggregate
{
    public function __invoke(AggregationsBuilder $builder): void;
}