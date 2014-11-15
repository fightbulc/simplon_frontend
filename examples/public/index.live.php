<?php

$classmap = require __DIR__ . '/../vendor/composer/autoload_classmap.php';

spl_autoload_register(function ($class) use ($classmap)
{
    require $classmap[$class];
});

// start frontend
\Simplon\Frontend\Frontend::start(
    require __DIR__ . '/../src/App/Config/routes.php',
    require __DIR__ . '/../src/App/Config/config.common.php',
    require __DIR__ . '/../src/App/Config/config.live.php'
);