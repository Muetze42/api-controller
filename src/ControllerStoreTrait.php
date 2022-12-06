<?php

namespace NormanHuth\ApiController;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

trait ControllerStoreTrait
{
    /**
     * @var string
     */
    protected string $storeAction = 'firstOrCreate';

    /**
     * Validation Rules For Store Request
     *
     * @return array
     */
    protected function storeValidationRules(): array
    {
        return [];
    }

    /**
     * Perform Action After Resource Is Stored
     * Do Image Uploads etc.
     *
     * @param Request $request
     * @param mixed $model
     * @return void
     */
    protected function afterStored(Request $request, mixed $model): void
    {
        //
    }

    /**
     * Process only specific keys in a store request
     *
     * @return array
     */
    protected function onlyStore(): array
    {
        return [];
    }

    /**
     * @param Request $request
     * @return void|null
     */
    protected function storeModelIncludeRelation(Request $request)
    {
        return null;
    }

    /**
     * This action is performed before the show request
     *
     * @param Request $request
     */
    protected function beforeStoreAction(Request $request): void
    {
        //
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function store(Request $request): mixed
    {
        $this->beforeAction($request);
        $this->beforeStoreAction($request);

        if (!is_null($this->gate) && method_exists($this->gate, 'create')) {
            $this->authorize('create', $this->model);
        }

        $rules = array_merge($this->validationRules(), $this->storeValidationRules());

        $request->validate($rules);

        $model = $this->storeModelIncludeRelation($request);
        $action = $this->storeAction;
        $statusModel = new $this->model;

        $only = empty($this->only()) || empty($this->onlyStore()) ? $statusModel->getFillable() : array_merge($this->only(), $this->onlyStore());

        $requestData = $request->only($only);

        if ($model) {
            $orm = $model->$action($requestData);
        }

        if (!isset($orm)) {
            $model = app($this->model);

            $orm = $model->$action($requestData);
        }

        $this->afterStored($request, $orm);
        $this->afterStoredUpdated($request, $orm);

        return $orm;
    }
}
