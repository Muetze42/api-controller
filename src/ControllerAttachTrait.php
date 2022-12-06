<?php

namespace NormanHuth\ApiController;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

trait ControllerAttachTrait
{
    /**
     * This action is performed before the update request
     *
     * @param Request $request
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
     * @throws AuthorizationException
     */
    public function attach(Request $request, $primaryValue, string $relation)
    {
        $this->beforeAction($request);
        $this->beforeAttachAction($request);

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
