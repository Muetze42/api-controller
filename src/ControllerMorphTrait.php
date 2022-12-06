<?php

namespace NormanHuth\ApiController;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait ControllerMorphTrait
{
    /**
     * This action is performed before the update request
     *
     * @param Request $request
     */
    protected function morphAction(Request $request): void
    {
        //
    }

    /**
     * @return array
     */
    protected function addValidationRules(): array
    {
        return [];
    }

    /**
     * Process only specific keys in a request
     *
     * @return array
     */
    protected function morphOnly(): array
    {
        return [];
    }

    /**
     * @param Request $request
     * @param $primaryValue
     * @param string $relation
     * @throws AuthorizationException
     * @return mixed
     */
    public function morph(Request $request, $primaryValue, string $relation): mixed
    {
        $this->beforeAction($request);
        $this->morphAction($request);

        if (is_string($this->model)) {
            $this->model = app($this->model);
        }

        if (!$this->primaryKey) {
            $this->primaryKey = $this->getPrimaryKey();
        }

        $query = ($this->model)->newQueryWithoutRelationships();
        $query = $this->showQuery($query, $request);

        $model = $query->where($this->primaryKey, $primaryValue)
            ->firstOrFail();

        $check = Str::ucfirst(Str::singular($relation));

        if (!is_null($this->gate) && method_exists($this->gate, 'add'.$check)) {
            $this->authorize('add'.$check, $model);
        }
        if (!is_null($this->gate) && method_exists($this->gate, 'update')) {
            $this->authorize('update', $model);
        }

        $request->validate($this->addValidationRules());

        $only = $this->morphOnly();
        if (empty($only)) {
            $only = $model->{$relation}()->getRelated()->getFillable();
        }

        return $model->{$relation}()->create($request->only($only));
    }
}
