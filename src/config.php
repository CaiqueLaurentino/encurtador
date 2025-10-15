<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad(); 

return [
    'mongo_uri' => $_ENV['MONGO_URI'] ,
    'mongo_db' => $_ENV['MONGO_DB'] ,
    'mongo_collection' => $_ENV['MONGO_COLLECTION'],
    'slug_length' => (int)($_ENV['SLUG_LENGTH'] ),
    'log_file' => $_ENV['LOG_FILE'] ,
];
