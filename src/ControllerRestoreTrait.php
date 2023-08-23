<?php

namespace NormanHuth\ApiController;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Exception;

trait ControllerRestoreTrait
{
    /**
     * This action is performed before the update request.
     *
     * @param \Illuminate\Http\Request $request
     */
    protected function beforeRestoreAction(Request $request): void
    {
        //
    }

    /**
     * Perform Action After Resource Is Restored. (Image Uploads etc)
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed                    $model
     *
     * @return void
     */
    protected function afterRestored(Request $request, mixed $model): void
    {
        //
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param                          $primaryValue
     *
     * @return mixed
     */
    public function restore(Request $request, $primaryValue): mixed
    {
        $this->beforeAction($request);
        $this->beforeRestoreAction($request);

        if (is_string($this->model)) {
            $this->model = app($this->model);
        }

        if (!$this->primaryKey) {
            $this->primaryKey = $this->getPrimaryKey();
        }

        $query = ($this->model)->newQueryWithoutRelationships();
        $query = $this->showQuery($query, $request);

//        if (!method_exists($query, 'withTrashed')) {
//            throw new Exception('Missing SoftDelete in this Model');
//        }

        $query = $query->withTrashed();

        $model = $query->where($this->primaryKey, $primaryValue)
            ->firstOrFail()
            ->makeHidden(array_merge($this->makeHiddenShow, $this->makeHidden))
            ->makeVisible(array_merge($this->makeVisibleShow, $this->makeVisible));

        if (!is_null($this->gate) && method_exists($this->gate, 'restore')) {
            $this->authorize('restore', $model);
        }

        $model->restore();

        $this->afterRestored($request, $model);

        return new $this->resource($model);
    }
}
