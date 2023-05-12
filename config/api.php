<?php

return [
    'limit'  => [
        'default' => 50,
        'min'     => 10,
        'max'     => 100,
    ],
    'pageName' => 'page',
    'fallback-resource' => \Illuminate\Http\Resources\Json\JsonResource::class,
    'locale' => null,

    'controller-less' => [
//        'users' => [
//            'index' => [
//                'indexLikeFilter' => ['name', 'login'],
//            ],
//        ],
//        'channels' => [
//            'index' => [
//                'indexLikeFilter' => ['name'],
//            ],
//        ],
//        'videos' => [
//            'index' => [
//                'indexLikeFilter' => ['title', 'description'],
//                'indexFilter' => ['channel_id'],
//            ],
//        ],
    ],
];
