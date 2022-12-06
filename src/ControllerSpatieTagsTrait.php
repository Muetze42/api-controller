<?php

namespace NormanHuth\ApiController;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Tags\Tag;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

trait ControllerSpatieTagsTrait
{
    protected mixed $spatieTagsType = null;

    /**
     * @param Request $request
     * @param $primaryValue
     * @param array|string|null $tags
     * @throws AuthorizationException
     * @return Builder|Model|Collection
     */
    public function attachSpatieTags(Request $request, $primaryValue, array|string $tags = null): Builder|Model|Collection
    {
        return $this->handleSpatieTags($request, $primaryValue, 'attachTag', $tags);
    }

    /**
     * @param Request $request
     * @param $primaryValue
     * @param array|string|null $tags
     * @throws AuthorizationException
     * @return Builder|Model|Collection
     */
    public function detachSpatieTags(Request $request, $primaryValue, array|string|null $tags = null): Builder|Model|Collection
    {
        return $this->handleSpatieTags($request, $primaryValue, 'detachTag', $tags);
    }

    /**
     * @param Request $request
     * @param $primaryValue
     * @return Builder|Model
     */
    protected function getSpatieTagModel(Request $request, $primaryValue): Model|Builder
    {
        $this->model = app($this->model);

        if (!$this->primaryKey) {
            $this->primaryKey = $this->getPrimaryKey();
        }

        $query = ($this->model)->newQueryWithoutRelationships();
        $query = $this->showQuery($query, $request);
        $query = $query->with($this->showRelationships($request));
        $query = $this->trashedQuery($query, $request);

        return $query->where($this->primaryKey, $primaryValue)
            ->firstOrFail();
    }

    /**
     * @param Request $request
     * @param $primaryValue
     * @param $method
     * @param array|string|null $tags
     * @throws AuthorizationException
     * @throws Exception
     * @return Builder|Model|Collection
     */
    public function handleSpatieTags(Request $request, $primaryValue, $method, array|string|null $tags = null): Builder|Model|Collection
    {
        $model = $this->getSpatieTagModel($request, $primaryValue);

        if (!is_null($this->gate) && method_exists($this->gate, $method)) {
            $this->authorize($method, $model);
        }

        $tags = $this->getTagsArray($request, $tags);

        foreach ($tags as $tag) {
            $tagEloquent = \App\Models\Tag::findOrCreate($tag, $this->spatieTagsType);

            $model->{$method}($tagEloquent);
        }

        return $model->tags;
    }

    /**
     * @param Request $request
     * @param $primaryValue
     * @param array|string|null $tags
     * @throws AuthorizationException
     * @throws Exception
     * @return Builder|Model|Collection
     */
    public function syncSpatieTags(Request $request, $primaryValue, array|string|null $tags = null): Builder|Model|Collection
    {
        $model = $this->getSpatieTagModel($request, $primaryValue);

        if (!is_null($this->gate) && method_exists($this->gate, 'attachTag')) {
            $this->authorize('attachTag', $model);
        }
        if (!is_null($this->gate) && method_exists($this->gate, 'detachTag')) {
            $this->authorize('detachTag', $model);
        }

        $tags = $this->getTagsArray($request, $tags);

        $tagsToSync = [];
        foreach ($tags as $tag) {
            $tagsToSync[] = \App\Models\Tag::findOrCreate($tag, $this->spatieTagsType);
        }

        if ($this->spatieTagsType) {
            $model->syncTagsWithType($tagsToSync, $this->spatieTagsType);
        } else {
            $model->syncTags($tagsToSync);
        }

        return $model->tags;
    }

    /**
     * @param Request $request
     * @param string|null $tags
     * @throws Exception
     * @return array
     */
    protected function getTagsArray(Request $request, ?string $tags): array
    {
        if (empty($tags)) {
            $request->validate(['data' => 'required']);
        }

        $tags = !empty($tags) ? $tags : $request->input('data');
        if (is_string($tags)) {
            $tags = isJson($tags) ? json_decode($tags, true) : [$tags];
        }

        if (is_array($tags)) {
            return $tags;
        }

        throw new Exception('Invalid Data Format');
    }
}
