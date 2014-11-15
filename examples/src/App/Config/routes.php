<?php

return [
    [
        'pattern'    => '^/hello/(.*?)$',
        'controller' => 'App\Controllers\WelcomeController::helloAction',
    ],
    [
        'pattern'    => '^/foo$',
        'controller' => 'App\Controllers\WelcomeController::fooAction',
    ],
    [
        'pattern'    => '^/*$',
        'controller' => 'App\Controllers\WelcomeController::defaultAction',
    ],
];
