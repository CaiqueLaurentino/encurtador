<?php
require_once __DIR__ . '/src/functions.php';

try {
    $slug = saveLink('https://www.php.net/releases/8.4/pt_BR.php');
    echo "Slug criado: $slug";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
