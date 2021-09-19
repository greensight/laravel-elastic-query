<?php

namespace Greensight\LaravelElasticQuery\Declarative\Contracts;

use Greensight\LaravelElasticQuery\Raw\Contracts\AggregationsBuilder;

interface AggregateAction
{
    public function __invoke(AggregationsBuilder $builder, string $name, string $field): void;
}