<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/functions.php';

header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Referrer-Policy: no-referrer");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");

$slug = trim($_GET['s'] ?? '', '/');

// CORREÇÃO: Se a slug estiver vazia (acesso à raiz '/'), redireciona para a página do formulário (index.php).
// if ($slug === '') { 
//     header('Location: index.php', true, 302);
//     exit;
// }

$link = getLink($slug);
if ($link === null) { 
    http_response_code(404); 
    echo "404 — Link não encontrado."; 
    exit; 
}

logAccess("Redirecionado: $slug -> {$link['url']}");
header('Location: ' . $link['url'], true, 302);
exit;