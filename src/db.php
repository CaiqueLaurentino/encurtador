<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$config = require __DIR__ . '/config.php';

$mongoClient = new MongoDB\Client($config['mongo_uri']);
$db = $mongoClient->{$config['mongo_db']};
$collection = $db->{$config['mongo_collection']};
