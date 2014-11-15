<?php

require __DIR__ . '/../vendor/autoload.php';

// start frontend
\Simplon\Frontend\Frontend::start(
    require __DIR__ . '/../src/App/Config/routes.php',
    require __DIR__ . '/../src/App/Config/config.common.php'
);