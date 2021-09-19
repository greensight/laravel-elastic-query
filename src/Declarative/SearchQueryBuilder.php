<?php

namespace Greensight\LaravelElasticQuery\Declarative;

use Generator;
use Greensight\LaravelElasticQuery\Declarative\Processors\ConstraintProcessor;
use Greensight\LaravelElasticQuery\Declarative\Processors\FilterProcessor;
use Greensight\LaravelElasticQuery\Declarative\Processors\SortProcessor;
use Greensight\LaravelElasticQuery\Raw\Search\SearchQuery;

/**
 * @mixin SearchQuery
 * @extends BaseQueryBuilder<SearchQuery>
 */
class SearchQueryBuilder extends BaseQueryBuilder
{
    protected function processors(): Generator
    {
        yield new FilterProcessor($this->parameters->filters());
        yield new ConstraintProcessor($this->query);
        yield new SortProcessor($this->query, $this->parameters->sorts());
    }
}
