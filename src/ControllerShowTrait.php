<?php

namespace NormanHuth\ApiController;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

trait ControllerShowTrait
{
    /**
     * @var array
     */
    protected array $showAllowInclude = [];
    /**
     * @var array
     */
    protected array $makeHiddenShow = [];
    /**
     * @var array
     */
    protected array $makeVisibleShow = [];

    /**
     * @var array
     */
    protected array $autoloadRelationsShow = [];

    /**
     * @param \Illuminate\Http\Request $request
     * @param                          $primaryValue
     *
     * @return mixed
     */
    public function show(Request $request, $primaryValue): mixed
    {
        $this->beforeAction($request);
        $this->beforeShowAction($request);

        if (is_string($this->model)) {
            $this->model = app($this->model);
        }

        if (!$this->primaryKey) {
            $this->primaryKey = $this->getPrimaryKey();
        }

        $query = ($this->model)->newQueryWithoutRelationships();
        $query = $this->showQuery($query, $request);
        $query = $query->with($this->showRelationships($request));
        $query = $this->trashedQuery($query, $request);

        $model = $query->where($this->primaryKey, $primaryValue)
            ->firstOrFail()
            ->makeHidden(array_merge($this->makeHiddenShow, $this->makeHidden))
            ->makeVisible(array_merge($this->makeVisibleShow, $this->makeVisible));

        if (!is_null($this->gate) && method_exists($this->gate, 'view')) {
            $this->authorize('view', $model);
        }

        return new $this->resource($model);
    }

    /**
     * This action is performed before the show request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function beforeShowAction(Request $request): void
    {
        //
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request              $request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function showQuery(Builder $query, Request $request): Builder
    {
        return $query;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function showRelationships(Request $request): array
    {
        $requestRelations = explode(',', $request->input('include'));

        $array = [];

        foreach ($requestRelations as $relation) {
            if (
                in_array($relation, $this->showAllowInclude) ||
                in_array($relation, $this->allowInclude)
            ) {
                $array[] = $relation;
            }
        }

        return array_merge(
            $array,
            $this->autoloadRelations,
            $this->autoloadRelationsShow
        );
    }
}
