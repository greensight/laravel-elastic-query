<?php

namespace Greensight\LaravelElasticQuery\Declarative;

use Generator;
use Greensight\LaravelElasticQuery\Declarative\Processors\AggregateProcessor;
use Greensight\LaravelElasticQuery\Declarative\Processors\ConstraintProcessor;
use Greensight\LaravelElasticQuery\Declarative\Processors\FilterProcessor;
use Greensight\LaravelElasticQuery\Raw\Aggregating\AggregationsQuery;

/**
 * @mixin AggregationsQuery
 * @extends BaseQueryBuilder<AggregationsQuery>
 */
class AggregateQueryBuilder extends BaseQueryBuilder
{
    /**
     * @inheritDoc
     */
    protected function processors(): Generator
    {
        yield new FilterProcessor($this->parameters->filters());
        yield new ConstraintProcessor($this->query);
        yield new AggregateProcessor($this->query, $this->parameters->aggregates());
    }
}