<?php
/**
 * Created by PhpStorm.
 * User: haru
 * Date: 17-9-18
 * Time: 下午8:14
 */
require_once __DIR__ . '/Core/Common.php';

/**
 * 
 * @param $class
 */
function hj_autoloader($class) {
    $class=str_replace('\\','/',$class);
    $file= __DIR__ . '/extend/' . $class . '.php';

    if (file_exists($file)){
        include_once $file;
    }

}
spl_autoload_register('hj_autoloader');
