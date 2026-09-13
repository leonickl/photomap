<?php

use PXP\Data\DB;

require __DIR__.'/vendor/autoload.php';

$db = DB::init();

$db->create('markers', [
    'title' => 'string not null',
    'author' => 'string not null',
    'file' => 'string not null',
]);

$db->addColumns('markers', [
    'lat' => 'float',
    'lon' => 'float',
]);
