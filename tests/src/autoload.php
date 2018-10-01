<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 29.09.2018
 * Time: 21:26
 */

spl_autoload_register(function ($class) {
    $file = 'tests' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $class . '.php';
    if (file_exists($file)) {
        var_dump($file);
        include $file;
    }
    return true;
}, true);