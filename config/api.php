<?php

return [
    'limit'  => [
        'default' => 50,
        'min'     => 10,
        'max'     => 100,
    ],
    'pageName' => 'page',
    'fallback-resource' => \NormanHuth\HelpersLaravel\App\Http\Resources\Json\JsonResource::class,
];
