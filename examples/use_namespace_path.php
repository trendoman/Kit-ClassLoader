<?php

include __DIR__ . '/../src/Riimu/Kit/ClassLoader/BasePathLoader.php';

$loader = new Riimu\Kit\ClassLoader\BasePathLoader();
$loader->addNamespacePath('Vendor', __DIR__ . '/class/');
$loader->register();

var_dump(new Vendor\SimpleClass());
