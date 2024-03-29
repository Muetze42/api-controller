<?php

namespace NormanHuth\ApiController;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

trait ControllerAttachTrait
{
    /**
     * This action is performed before the update request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function beforeAttachAction(Request $request): void
    {
        //
    }

    /**
     * @return array
     */
    protected function attachValidationRules(): array
    {
        return [];
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param                          $primaryValue
     * @param string                   $relation
     *
     * @return mixed
     */
    public function attach(Request $request, $primaryValue, string $relation): mixed
    {
        $this->beforeAction($request);
        $this->beforeAttachAction($request);

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

        $rules = array_merge($this->attachValidationRules(), [
            'data' => 'required'
        ]);

        $request->validate($rules);

        $data = $request->input('data');
        if (is_string($data)) {
            $object = json_decode($data, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $model->$relation()->attach($object);
            }
        }

        return $model->$relation()->attach($data);
    }
}
