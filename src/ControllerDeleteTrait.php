<?php

namespace NormanHuth\ApiController;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

trait ControllerDeleteTrait
{
    /**
     * This action is performed before the update request
     *
     * @param Request $request
     */
    protected function beforeDestroyAction(Request $request): void
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
    protected function beforeDeleted(Request $request, mixed $model): void
    {
        //
    }

    /**
     * @param Request $request
     * @param $primaryValue
     * @throws AuthorizationException
     * @return Response|Application|ResponseFactory
     */
    public function destroy(Request $request, $primaryValue): Response|Application|ResponseFactory
    {
        $this->beforeAction($request);
        $this->beforeDestroyAction($request);

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

        if (!is_null($this->gate) && method_exists($this->gate, 'delete')) {
            $this->authorize('delete', $model);
        }

        $this->beforeDeleted($request, $model);

        $model->delete();

        return response(null, HttpResponse::HTTP_NO_CONTENT);
    }
}
