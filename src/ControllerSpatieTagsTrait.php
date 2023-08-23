<?php

namespace NormanHuth\ApiController;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

trait ControllerSpatieTagsTrait
{
    protected mixed $spatieTagsType = null;

    /**
     * @param \Illuminate\Http\Request $request
     * @param                          $primaryValue
     * @param array|string|null        $tags
     *
     * @throws \Exception
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection
     */
    public function attachSpatieTags(
        Request $request,
        $primaryValue,
        array|string $tags = null
    ): Builder|Model|Collection {
//        if (!is_null($this->gate) && method_exists($this->gate, 'addTag')) {
//            $this->authorize('addTag', $this->model);
//        }
//        if (!is_null($this->gate) && method_exists($this->gate, 'attachTag')) {
//            $this->authorize('attachTag', $this->model);
//        }
        if (!is_null($this->gate) && method_exists($this->gate, 'attachAnyTag')) {
            $this->authorize('attachAnyTag', $this->model);
        }

        return $this->handleSpatieTags($request, $primaryValue, 'attachTag', $tags);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param                          $primaryValue
     * @param array|string|null        $tags
     *
     * @throws \Exception
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection
     */
    public function detachSpatieTags(
        Request $request,
        $primaryValue,
        array|string|null $tags = null
    ): Builder|Model|Collection {
//        if (!is_null($this->gate) && method_exists($this->gate, 'detachTag')) {
//            $this->authorize('detachTag', $this->model);
//        }
        if (!is_null($this->gate) && method_exists($this->gate, 'attachAnyTag')) {
            $this->authorize('attachAnyTag', $this->model);
        }

        return $this->handleSpatieTags($request, $primaryValue, 'detachTag', $tags);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param                          $primaryValue
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder
     */
    protected function getSpatieTagModel(Request $request, $primaryValue): Model|Builder
    {
        if (is_string($this->model)) {
            $this->model = app($this->model);
        }

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
     * @param \Illuminate\Http\Request $request
     * @param                          $primaryValue
     * @param                          $method
     * @param array|string|null        $tags
     *
     * @throws \Exception
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection
     */
    public function handleSpatieTags(
        Request $request,
        $primaryValue,
        $method,
        array|string|null $tags = null
    ): Builder|Model|Collection {
        $model = $this->getSpatieTagModel($request, $primaryValue);

        $tags = $this->getTagsArray($request, $tags);

        foreach ($tags as $tag) {
            $tagEloquent = \App\Models\Tag::findOrCreate($tag, $this->spatieTagsType);

            $model->{$method}($tagEloquent);
        }

        return $model->tags;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param                          $primaryValue
     * @param array|string|null        $tags
     *
     * @throws \Exception
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection
     */
    public function syncSpatieTags(
        Request $request,
        $primaryValue,
        array|string|null $tags = null
    ): Builder|Model|Collection {
        $model = $this->getSpatieTagModel($request, $primaryValue);

//        if (!is_null($this->gate) && method_exists($this->gate, 'detachTag')) {
//            $this->authorize('detachTag', $this->model);
//        }
        if (!is_null($this->gate) && method_exists($this->gate, 'attachTag')) {
            $this->authorize('attachTag', $this->model);
        }
        if (!is_null($this->gate) && method_exists($this->gate, 'addTag')) {
            $this->authorize('addTag', $this->model);
        }
        if (!is_null($this->gate) && method_exists($this->gate, 'attachAnyTag')) {
            $this->authorize('attachAnyTag', $this->model);
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
     * @param \Illuminate\Http\Request $request
     * @param string|array|null        $tags
     *
     * @throws \Exception
     * @return array
     */
    protected function getTagsArray(Request $request, string|array|null $tags): array
    {
        if (empty($tags)) {
            $request->validate(['tags' => 'required']);
        }

        $tags = !empty($tags) ? $tags : $request->input('tags');
        if (is_string($tags)) {
            $tags = isJson($tags) ? json_decode($tags, true) : [$tags];
        }

        if (is_array($tags)) {
            return $tags;
        }

        throw new Exception('Invalid Data Format');
    }
}
