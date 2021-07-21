<?php

namespace Greensight\LaravelElasticQuery\Raw\Contracts;

interface SearchIndex
{
    /**
     * Returns the name of attribute with unique values in index scope.
     *
     * @return string
     */
    public function tiebreaker(): string;

    /**
     * Perform search query.
     *
     * @param array $dsl
     * @return array
     */
    public function search(array $dsl): array;
}
