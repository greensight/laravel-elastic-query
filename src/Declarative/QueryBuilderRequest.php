<?php

namespace Greensight\LaravelElasticQuery\Declarative;

use Greensight\LaravelElasticQuery\Declarative\Concerns\ExtractsQueryParameters;
use Illuminate\Http\Request;

class QueryBuilderRequest extends Request
{
    use ExtractsQueryParameters;

    public static function fromRequest(Request $request): static
    {
        return static::createFrom($request, new static());
    }

    protected function extract(string $key): mixed
    {
        return $this->input($key);
    }
}