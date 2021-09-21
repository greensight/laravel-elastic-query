# Laravel Elastic Query

Working with Elasticsearch in an Eloquent-like fashion.

## Installation

You can install the package via composer:

1. `composer require greensight/laravel-elastic-query`
2. Set `ELASTICSEARCH_HOSTS` in your `.env` file. `,` can be used as a delimeter.

## Basic usage

Let's create and index class. It's someting like Eloquent model.

```php
use Greensight\LaravelElasticQuery\ElasticIndex;

class ProductsIndex extends ElasticIndex
{
    protected string $name = 'test_products';
    protected string $tiebreaker = 'product_id';
}
```

You should set a unique in document attribute name in `$tiebreaker`. It is used as an additional sort in `search_after`

Now we can get some documents

```php
$searchQuery = ProductsIndex::query();

$hits = $searchQuery
             ->where('rating', '>=', 5)
             ->whereDoesntHave('offers', fn(BoolQuery $query) => $query->where('seller_id', 10)->where('active', false))
             ->sortBy('rating', 'desc')
             ->sortByNested('offers', fn(SortableQuery $query) => $query->where('active', true)->sortBy('price', mode: 'min'))
             ->take(25)
             ->get();
```

### Filtering

```php
$searchQuery->where('field', 'value');
$searchQuery->where('field', '>', 'value'); // supported operators: `=` `!=` `>` `<` `>=` `<=`
$searchQuery->whereNot('field', 'value'); // equals `where('field', '!=', 'value')`
```

```php
$searchQuery->whereIn('field', ['value1', 'value2']);
$searchQuery->whereNotIn('field', ['value1', 'value2']);
```

```php
$searchQuery->whereNull('field');
$searchQuery->whereNotNull('field');
```

```php
$searchQuery->whereIn('field', ['value1', 'value2']);
$searchQuery->whereNotIn('field', ['value1', 'value2']);
```

```php
$searchQuery->whereNull('field');
$searchQuery->whereNotNull('field');
```

```php
$searchQuery->whereHas('nested_field', fn(BoolQuery $subQuery) => $subQuery->where('field_in_nested', 'value'));
$searchQuery->whereDoesntHave(
    'nested_field',
    function (BoolQuery $subQuery) {
        $subQuery->whereHas('nested_field', fn(BoolQuery $subQuery2) => $subQuery2->whereNot('field', 'value'));
    }
);
```

`nested_field` must have `nested` type.
Subqueries cannot use fields of main document only subdocument.

### Sorting

```php
$searchQuery->sortBy('field', 'desc', 'max'); // field is from main document
$searchQuery->sortByNested(
    'nested_field',
    fn(SortableQuery $subQuery) => $subQuery->where('field_in_nested', 'value')->sortBy('field')
);
```

Second attribute is a direction. It supports `asc` and `desc` values. Defaults to `asc`.  
Third attribute - sorting type. List of supporting types: `min, max, avg, sum, median`. Defaults to `min`.

There are also dedicated sort methods for each sort type.

```php
$searchQuery->minSortBy('field', 'asc');
$searchQuery->maxSortBy('field', 'asc');
$searchQuery->avgSortBy('field', 'asc');
$searchQuery->sumSortBy('field', 'asc');
$searchQuery->medianSortBy('field', 'asc');
```

### Pagination

#### Offset Pagination

```php
$page = $searchQuery->paginate(15, 45);
```

Offset pagination returns total documents count as `total` and current position as `size/offset`.

#### Cursor pagination

```php
$page = $searchQuery->cursorPaginate(10);
$pageNext = $searchQuery->cursorPaginate(10, $page->next);
```

 `current`, `next`, `previous` is returned in this case instead of `total`, `size` and `offset`.
 You can check Laravel docs for more info about cursor pagination.

## Aggregation

Aggregation queries can be created like this

```php
$aggQuery = ProductsIndex::aggregate();

/** @var \Illuminate\Support\Collection $aggs */
$aggs = $aggQuery
            ->where('active', true)
            ->terms('codes', 'code')
            ->nested(
                'offers',
                fn(AggregationsBuilder $builder) => $builder->where('seller_id', 10)->minmax('price', 'price')
            );

$aggs
            
```

Type of `$aggs->price` is `MinMax`.
Type of `$aggs->codes` is `BucketCollection`.
Aggregate names must be unique for whole query.


### Aggregate types

Get all variants of attribute values:

```php
$aggQuery->terms('agg_name', 'field');
```

Get min and max attribute values. E.g for date:

```php
$aggQuery->minmax('agg_name', 'field');
```

Aggregation plays nice with nested documents.

```php
$aggQuery->nested('nested_field', function (AggregationsBuilder $builder) {
    $builder->terms('name', 'field_in_nested');
});
```

There is also a special virtual `composite` aggregate on the root level. You can set special conditions using it.

```php
$aggQuery->composite(function (AggregationsBuilder $builder) {
    $builder->where('field', 'value')
        ->whereHas('nested_field', fn(BoolQuery $query) => $query->where('field_in_nested', 'value2'))
        ->terms('field1', 'agg_name1')
        ->minmax('field2', 'agg_name2');
});
```

## Декларативные запросы

### Спецификации

Все виды таких запросов строятся на основе спецификации. В ней содержатся определения доступных фильтров, сортировок и
агрегатов.

```php
use Greensight\LaravelElasticQuery\Declarative\Agregating\AllowedAggregate;
use Greensight\LaravelElasticQuery\Declarative\Filtering\AllowedFilter;
use Greensight\LaravelElasticQuery\Declarative\Sorting\AllowedSort;
use Greensight\LaravelElasticQuery\Declarative\Specification\CompositeSpecification;
use Greensight\LaravelElasticQuery\Declarative\Specification\Specification;

class ProductSpecification extends CompositeSpecification
{
    public function __construct()
    {
        parent::__construct();
        
        $this->allowFilters(
            'package',
            'active',
            AllowedFilter::exact('cashback', 'cashback.active')->default(true)
        );
        
        $this->allowSorts('name', 'rating');
        
        $this->allowAggregates(
            'package',
            AllowedAggregate::minmax('rating')
        );
        
        $this->whereNotNull('package');
        
        $this->addNested('offers', function (Specification $spec) {
            $spec->allowFilters('seller_id', 'active');
            
            $spec->allowAggregates(
                'seller_id',
                AllowedAggregate::minmax('price')
            );
            
            $spec->allowSorts(
                AllowedSort::field('price')->byMin()
            );
        });
    }
}
```

Примеры запросов для данной спецификации.
```json
{
 "sort": ["+price", "-rating"],
 "filter": {
    "active": true,
    "seller_id": 10
 }
}
```
```json
{
 "aggregate": ["price", "rating"],
 "filter": {
    "package": "bottle",
    "seller_id": 10
 }
}
```
Метод `addNested` добавляет спецификации для вложенных документов. Имена фильтров, агрегатов и сортировок из них
экспортируются в глобальную область видимости без добавления каких-либо префиксов. Если для фильтров допустимо иметь
одинаковые имена, то для прочих компонентов нет.

```php
$this->addNested('nested_field', function (Specification $spec) { ... })
$this->addNested('nested_field', new SomeSpecificationImpl());
```

В спецификациях для вложенных документов могут использоваться только поля этих документов.

Допустимо добавлять несколько спецификаций для одного и того же поля типа `nested`.

Ограничения `where*` позволяют устанавливать дополнительные программные условия отбора, которые не могут быть изменены
клиентом. Ограничения, заданные в корневой спецификации, применяются всегда. Ограничения во вложенных спецификациях идут
только как дополнения к добавляемым в запрос фильтрам, агрегатам или сортировкам. Например, если во вложенной
спецификации нет ни одного активного фильтра, то в раздел фильтров запроса к Elasticsearch ограничения из этой
спецификации не попадут.

Метод `allowFilters` определяет доступные для клиента фильтры. Каждый фильтр обязательно содержит уникальное в пределах
спецификации имя. В то же время, в корневой и вложенной спецификациях или в разных вложенных спецификациях, имена могут
повторяться. Все фильтры с одинаковыми именами будут заполнены одним значением из параметров запроса.

Кроме имени самого фильтра можно отдельно задать имя поля в индексе, для которого он применяется, и значение по умолчанию.

```php
$this->allowFilters(AllowedFilter::exact('name', 'field')->default(500));

// the following statements are equivalent
$this->allowFilters('name');
$this->allowFilters(AllowedFilter::exact('name', 'name'));
```

Виды фильтров

```php
AllowedFilter::exact('name', 'field');  // Значение поля проверяется на равенство одному из заданных
AllowedFilter::exists('name', 'field'); // Проверяется, что поле присутствует в документе и имеет ненулевое значение
```

Доступные клиенту сортировки добавляются методом `allowSorts`. Направление сортировки задается в ее имени.
Знак `+` или отсутствие знака соответствует порядку по возрастанию, `-` - порядку по убыванию. 

```php
$this->alllowSorts(AllowedSort::field('name', 'field'));

// the following statements are equivalent
$this->allowSorts('name');
$this->alllowSorts(AllowedSort::field('+name', 'name'));

// set the sorting mode
$this->alllowSorts(AllowedSort::field('name', 'field')->byMin());
$this->alllowSorts(AllowedSort::field('name', 'field')->byMax());
$this->alllowSorts(AllowedSort::field('name', 'field')->byAvg());
$this->alllowSorts(AllowedSort::field('name', 'field')->bySum());
$this->alllowSorts(AllowedSort::field('name', 'field')->byMedian());
```

Для сортировки из вложенной спецификации учитываются все ограничения и активные фильтры из этой же спецификации.

Агрегаты объявляются методом `allowAggregates`. Клиент в параметрах запроса указывает список имен агрегатов, результаты
которых он ожидает в ответе.

```php
$this->allowAggregates(AllowedAggregate::terms('name', 'field'));

// the following statements are equivalent
$this->allowAggregates('name');
$this->allowAggregates(AllowedAggregate::terms('name', 'name'));
```

Виды агрегатов

```php
$this->allowAggregates(AllowedAggregate::terms('name', 'field'));   // Get all variants of attribute values
$this->allowAggregates(AllowedAggregate::minmax('name', 'field'));  // Get min and max attribute values
```

Агрегаты из вложенных спецификаций добавляются в запрос к Elasticsearch со всеми ограничениями и активными фильтрами.

### Поиск документов

```php
use Greensight\LaravelElasticQuery\Declarative\SearchQueryBuilder;
use Greensight\LaravelElasticQuery\Declarative\QueryBuilderRequest;

class ProductsSearchQuery extends SearchQueryBuilder
{
    public function __construct(QueryBuilderRequest $request)
    {
        parent::__construct(ProductsIndex::query(), new ProductSpecification(), $request);
    }
}
```

```php
class ProductsController
{
    // ...
    public function index(ProductsSearchQuery $query)
    {
        return ProductResource::collection($query->get());
    }
}
```

### Расчет сводных показателей

```php
use Greensight\LaravelElasticQuery\Declarative\AggregateQueryBuilder;
use Greensight\LaravelElasticQuery\Declarative\QueryBuilderRequest;

class ProductsAggregateQuery extends AggregateQueryBuilder
{
    public function __construct(QueryBuilderRequest $request)
    {
        parent::__construct(ProductsIndex::aggregate(), new ProductSpecification(), $request);
    }
}
```

```php
class ProductsController
{
    // ...
    public function totals(ProductsAggregateQuery $query)
    {
        return ProductAggregateResource::collection($query->get());
    }
}
```

## Query Log

Just like Eloquent ElasticQuery has its own query log, but you need to enable it manually
Each message contains `indexName`, `query` and `timestamp`

```php
ElasticQuery::enableQueryLog();

/** @var \Illuminate\Support\Collection|Greensight\LaravelElasticQuery\Raw\Debug\QueryLogRecord[] $records */
$records = ElasticQuery::getQueryLog();

ElasticQuery::disableQueryLog();
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

### Testing

1. composer install
2. npm i
3. Start Elasticsearch in your preferred way.
4. Copy `phpunit.xml.dist` to `phpunit.xml` and set correct env variables there
6. composer test

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
