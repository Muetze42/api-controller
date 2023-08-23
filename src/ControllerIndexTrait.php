<?php

namespace NormanHuth\ApiController;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ControllerIndexTrait
{
    /**
     * @var array
     */
    protected array $indexFilter = [];

    /**
     * @var array
     */
    protected array $indexLikeFilter = [];

    /**
     * @var array
     */
    protected array $indexHasFilter = [];

    /**
     * @var array
     */
    protected array $indexHasLikeFilter = [];

    /**
     * @var array
     */
    protected array $indexAllowInclude = [];

    /**
     * @var array
     */
    protected array $makeHiddenIndex = [];

    /**
     * @var array
     */
    protected array $makeVisibleIndex = [];

    /**
     * @var array
     */
    protected array $autoloadRelationsIndex = [];

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request              $request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function indexQuery(Builder $query, Request $request): Builder
    {
        return $query;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request              $request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function indexFilter(Builder $query, Request $request): Builder
    {
        $filters = $request->input('filter');
        foreach ($this->indexFilter as $key) {
            if (is_array($filters) && array_key_exists($key, $filters)) {
                $query->where($key, $filters[$key]);
            }
        }
        foreach ($this->indexLikeFilter as $key) {
            if (is_array($filters) && array_key_exists($key, $filters)) {
                $query->where($key, 'like', '%' . $filters[$key] . '%');
            }
        }

        $filters = $request->input('has');

        foreach ($this->indexHasFilter as $key) {
            if (is_array($filters) && array_key_exists($key, $filters)) {
                $parts = explode('.', $key);

                $relation = $parts[0];
                $column = $parts[1];
                $value = $filters[$key];

                $query->whereHas($relation, function (Builder $query) use ($column, $value) {
                    $query->where($column, $value);
                });
            }
        }

        foreach ($this->indexHasLikeFilter as $key) {
            if (is_array($filters) && array_key_exists($key, $filters)) {
                $parts = explode('.', $key);

                $relation = $parts[0];
                $column = $parts[1];
                $value = $filters[$key];

                $query->whereHas($relation, function (Builder $query) use ($column, $value) {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                });
            }
        }

        return $query;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $this->beforeAction($request);
        $this->beforeIndexAction($request);

        if (!is_null($this->gate) && method_exists($this->gate, 'viewAny')) {
            $this->authorize('viewAny', $this->model);
        }

        $model = app($this->model);
        $query = $model->newQueryWithoutRelationships();
        $query = $this->indexQuery($query, $request);
        $query = $this->indexFilter($query, $request);
        $query = $query->with($this->indexRelationships($request));
        $query = $this->trashedQuery($query, $request);

        if (empty($this->orderBy)) {
            $this->orderBy = $model->getKeyName();
        }

        $limit = $this->getLimit($request);

        $paginator = $query->orderBy($this->orderBy, $this->orderDirection)->paginate($limit, ['*'], $this->pageName);

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $paginator->setCollection($paginator->getCollection()
            ->makeHidden(array_merge($this->makeHiddenIndex, $this->makeHidden))
            ->makeVisible(array_merge($this->makeVisibleIndex, $this->makeVisible)));

        return $this->resource::collection($paginator);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function indexRelationships(Request $request): array
    {
        $requestRelations = explode(',', $request->input('include'));

        $array = [];

        foreach ($requestRelations as $relation) {
            if (
                in_array($relation, $this->indexAllowInclude) ||
                in_array($relation, $this->allowInclude)
            ) {
                $array[] = $relation;
            }
        }

        return array_merge(
            $array,
            $this->autoloadRelations,
            $this->autoloadRelationsIndex
        );
    }

    /**
     * This action is performed before the index request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function beforeIndexAction(Request $request): void
    {
        //
    }
}
