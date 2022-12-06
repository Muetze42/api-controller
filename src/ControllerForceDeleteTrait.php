<?php

namespace NormanHuth\ApiController;

use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

trait ControllerForceDeleteTrait
{
    /**
     * This action is performed before the force delete request
     *
     * @param Request $request
     */
    protected function beforeForceDeleteAction(Request $request): void
    {
        //
    }

    /**
     * Perform Action Before Resource Deleted
     * Delete Images etc
     *
     * @param Request $request
     * @param mixed $model
     * @return void
     */
    protected function beforeForceDeleted(Request $request, mixed $model): void
    {
        //
    }

    /**
     * @throws AuthorizationException
     * @throws Exception
     */
    public function forceDelete(Request $request, $primaryValue): Response|Application|ResponseFactory
    {
        $this->beforeAction($request);
        $this->beforeForceDeleteAction($request);
        $this->model = app($this->model);

        if (!$this->primaryKey) {
            $this->primaryKey = $this->getPrimaryKey();
        }
        $query = ($this->model)->newQueryWithoutRelationships();
        $query = $this->showQuery($query, $request);

//        if (!method_exists($query, 'withTrashed')) {
//            throw new Exception('Missing SoftDelete in this Model');
//        }

        $query = $query->withTrashed();
        $model = $query->where($this->primaryKey, $primaryValue)->firstOrFail();

        if (!is_null($this->gate) && method_exists($this->gate, 'forceDelete')) {
            $this->authorize('forceDelete', $model);
        }

        $this->beforeForceDeleted($request, $model);

        $model->forceDelete();

        return response(null, HttpResponse::HTTP_NO_CONTENT);
    }
}
