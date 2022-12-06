<?php

namespace NormanHuth\ApiController;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

trait ControllerDetachTrait
{
    /**
     * This action is performed before the update request
     *
     * @param Request $request
     */
    protected function beforeDetachAction(Request $request): void
    {
        //
    }

    /**
     * @return array
     */
    protected function detachValidationRules(): array
    {
        return [];
    }

    /**
     * @throws AuthorizationException
     */
    public function detach(Request $request, $primaryValue, string $relation)
    {
        $this->beforeAction($request);
        $this->beforeDetachAction($request);

        $this->model = app($this->model);

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

        $rules = array_merge($this->detachValidationRules(), [
            'data' => 'required'
        ]);

        $request->validate($rules);

        $data = $request->input('data');
        if (is_string($data)) {
            $object = json_decode($data, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $model->$relation()->detach($object);
            }
        }

        return $model->$relation()->detach($data);
    }
}
