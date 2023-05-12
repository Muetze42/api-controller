<?php

return [
    'limit'  => [
        'default' => 50,
        'min'     => 10,
        'max'     => 100,
    ],
    'pageName' => 'page',
    'fallback-resource' => \Illuminate\Http\Resources\Json\JsonResource::class,
];
