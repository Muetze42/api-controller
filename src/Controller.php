<?php

namespace NormanHuth\ApiController;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Gate;

class Controller extends BaseController
{
    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests,
        ControllerMorphTrait,
        ControllerAttachTrait,
        ControllerDeleteTrait,
        ControllerSpatieTagsTrait,
        ControllerDetachTrait,
        ControllerForceDeleteTrait,
        ControllerRestoreTrait,
        ControllerIndexTrait,
        ControllerShowTrait,
        ControllerStoreTrait,
        ControllerUpdateTrait;

    /**
     * The Model Instance
     *
     * @var mixed|string|null
     */
    protected mixed $model = null;

    /**
     * The Resource Instance
     *
     * @var mixed|string|null
     */
    protected mixed $resource = null;

    /**
     * Models Namespace
     *
     * @var string
     */
    protected string $modelNamespace = '\App\Models\\';

    /**
     * Resources Namespace
     *
     * @var string
     */
    protected string $resourceNamespace = '\App\Http\Resources\\';

    /**
     * @var array
     */
    protected array $makeHidden = [];

    /**
     * @var array
     */
    protected array $makeVisible = [];

    /**
     * @var array
     */
    protected array $autoloadRelations = [];

    /**
     * Per Page Limit
     *
     * @var int|null
     */
    protected ?int $limit = null;

    /**
     * Per Page Max Limit
     *
     * @var int|null
     */
    protected ?int $limitMax = null;

    /**
     * @var array
     */
    protected array $allowInclude = [];

    /**
     * Thr Gate Instance
     *
     * @var mixed
     */
    protected mixed $gate;

    /**
     * @var string|null
     */
    protected ?string $orderBy = null;

    /**
     * @var string|null
     */
    protected ?string $pageName = null;

    /**
     * @var string|null
     */
    protected ?string $primaryKey = null;

    /**
     * @var string
     */
    protected string $orderDirection = 'asc';

    public function __construct()
    {
        if (!$this->model) {
            $this->model = $this->modelNamespace.str_replace('Controller', '', class_basename(get_class($this)));
        }

        if (!$this->resource) {
            $this->resource = $this->resourceNamespace.str_replace('Controller', '', class_basename(get_class($this))).'Resource';
        }

        if (!$this->pageName) {
            $this->pageName = config('api.pageName', 'page');
        }

        $this->gate = Gate::getPolicyFor($this->model);
    }

    /**
     * This action is performed before each request
     *
     * @param Request $request
     */
    protected function beforeAction(Request $request): void
    {
        //
    }

    /**
     * @param Request $request
     * @return int
     */
    protected function getLimit(Request $request): int
    {
        $input = $request->input('limit');
        $limit = $this->limit > 0 ? $this->limit : config('api.limit.default', 50);

        if ($input) {
            $min = config('api.limit.min', 10);
            $max = $this->limitMax > 0 ? $this->limitMax : config('api.limit.max', 100);

            if ($input > $max) {
                $limit = $max;
            } elseif ($input < $min) {
                $limit = $min;
            } else {
                $limit = $input;
            }
        }

        return $limit;
    }

    /**
     * @param Builder $query
     * @param Request $request
     * @return Builder
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function trashedQuery(Builder $query, Request $request): Builder
    {
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->model))) {
            if ($request->input('withTrashed')) {
                return $query->withTrashed();
            } elseif ($request->input('onlyTrashed')) {
                return $query->onlyTrashed();
            }
        }

        return $query;
    }

    /**
     * Perform Action After Resource Is Stored Or Updated
     * Do Image Uploads etc.
     *
     * @param Request $request
     * @param mixed $model
     * @return void
     */
    protected function afterStoredUpdated(Request $request, mixed $model): void
    {
        //
    }

    /**
     * @return string
     */
    protected function getPrimaryKey(): string
    {
        return $this->model->getKeyName();
    }

    /**
     * Validation Rules For Each Request
     *
     * @return array
     */
    protected function validationRules(): array
    {
        return [];
    }

    /**
     * Process only specific keys in a request
     *
     * @return array
     */
    protected function only(): array
    {
        return [];
    }
}
