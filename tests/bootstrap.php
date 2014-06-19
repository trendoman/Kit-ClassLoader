<?php

require __DIR__ . '/constraints/TestCase.php';
require __DIR__ . '/constraints/ClassLoaderLoads.php';

require __DIR__ . '/../src/ClassLoader.php';
require __DIR__ . '/../src/CacheListClassLoader.php';
require __DIR__ . '/../src/FileCacheClassLoader.php';

define('CLASS_BASE', __DIR__ . DIRECTORY_SEPARATOR . 'classes');

function path(array $path, $add = false)
{
    return implode(DIRECTORY_SEPARATOR, $path) . ($add ? DIRECTORY_SEPARATOR : '');
}
