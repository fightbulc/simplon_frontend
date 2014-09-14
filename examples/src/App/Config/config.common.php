<?php

return [
    'routes'    => [
        [
            'pattern'    => '^/*(.*?)$',
            'controller' => 'App\Controllers\WelcomeController::hello',
        ],
    ],

    // ------------------------------------------

    'paths'     => [
        'src'    => __DIR__ . '/../../App/',
        'public' => __DIR__ . '/../../../public/',
    ],

    // ------------------------------------------

    'templates' => [
        'isNative' => true,
        'locale'   => [
            'default'   => 'en',
            'available' => [],
        ],
    ],
];