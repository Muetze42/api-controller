<?php

namespace NormanHuth\ApiController;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

trait ControllerUpdateTrait
{

    /**
     * Validation Rules For Store Request
     *
     * @return array
     */
    protected function updateValidationRules(): array
    {
        return [];
    }

    /**
     * Process only specific keys in a update request
     *
     * @return array
     */
    protected function onlyUpdate(): array
    {
        return [];
    }

    /**
     * Perform Action After Resource Is Updated
     * Do Image Uploads etc.
     *
     * @param Request $request
     * @param mixed $model
     * @return void
     */
    protected function afterUpdated(Request $request, mixed $model): void
    {
        //
    }

    /**
     * This action is performed before the update request
     *
     * @param Request $request
     */
    protected function beforeUpdateAction(Request $request): void
    {
        //
    }

    /**
     * @param Request $request
     * @param $primaryValue
     * @return mixed
     * @throws AuthorizationException
     */
    public function update(Request $request, $primaryValue): mixed
    {
        $this->beforeAction($request);
        $this->beforeUpdateAction($request);

        $statusModel = new $this->model;
        if (is_string($this->model)) {
            $this->model = app($this->model);
        }

        if (!$this->primaryKey) {
            $this->primaryKey = $this->getPrimaryKey();
        }

        $query = ($this->model)->newQueryWithoutRelationships();
        $query = $this->showQuery($query, $request);
        $query = $this->trashedQuery($query, $request);

        $model = $query->where($this->primaryKey, $primaryValue)
            ->firstOrFail()
            ->makeHidden(array_merge($this->makeHiddenShow, $this->makeHidden))
            ->makeVisible(array_merge($this->makeVisibleShow, $this->makeVisible));

        if (!is_null($this->gate) && method_exists($this->gate, 'update')) {
            $this->authorize('update', $model);
        }

        $rules = array_merge($this->validationRules(), $this->updateValidationRules());

        $request->validate($rules);

        $only = empty($this->only()) || empty($this->onlyUpdate()) ? $statusModel->getFillable() : array_merge($this->only(), $this->onlyUpdate());

        $model->update($request->only($only));

        $this->afterUpdated($request, $model);
        $this->afterStoredUpdated($request, $model);

        return new $this->resource($model);
    }
}
