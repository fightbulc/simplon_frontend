<?php

use Simplon\Frontend\Frontend;
use Simplon\Frontend\Utils\SessionStorage;

require __DIR__ . '/../vendor/autoload.php';

$sessionStorage = (new SessionStorage())->start();

Frontend::setSessionStorage($sessionStorage);

if (Frontend::hasFlash())
{
    echo '<h3>READ/DEL FLASH...</h3>';
    var_dump([htmlspecialchars(Frontend::getFlash()), Frontend::hasFlash()]);
}
else
{
    echo '<h3>SET FLASH...</h3>';
    Frontend::setFlashSuccess('SUCCESS');
}
