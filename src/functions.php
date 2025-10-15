<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
$config = require __DIR__ . '/config.php';

function generateSlug(int $length): string {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $max = strlen($chars) - 1;
    $slug = '';
    for ($i = 0; $i < $length; $i++) {
        $slug .= $chars[random_int(0, $max)];
    }
    return $slug;
}

function normalizeUrl(string $url): string {
    return trim($url);
}

function isValidUrl(string $url): bool {
    if (!preg_match('#^https?://#i', $url)) return false;

    if (preg_match('#^(javascript|data|file|ftp):#i', trim($url))) return false;

    $host = parse_url($url, PHP_URL_HOST);
    if (!$host) return false;

    $scheme = parse_url($url, PHP_URL_SCHEME);
    if (!in_array($scheme, ['http', 'https'], true)) return false;

    $port = parse_url($url, PHP_URL_PORT);
    if ($port !== null && ($port < 1 || $port > 65535)) return false;

    if (strlen($url) > 2000) return false;

    if (strtolower($host) === 'localhost') return false;

    $ip = @gethostbyname($host);
    if ($ip === false || $ip === $host) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false; 
    }

    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}


function safeRedirect(string $url): void {
    $url = str_replace(["\r", "\n"], '', $url);

    $host = parse_url($url, PHP_URL_HOST);
    if (!$host) {
        http_response_code(400);
        exit('Bad redirect URL');
    }

    $ip = @gethostbyname($host);
    if ($ip === false) {
        http_response_code(400);
        exit('Bad redirect host');
    }

    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        http_response_code(403);
        exit('Redirect to private IP not allowed');
    }

    header('Location: ' . $url, true, 302);
    exit;
}

function saveLink(string $url): string {
    global $collection, $config;

    $url = normalizeUrl($url);

    if (!isValidUrl($url)) {
        throw new Exception('URL inválida ou não permitida');
    }

    $attempts = 0;
    do {
        if ($attempts++ > 10) {
            throw new Exception('Não foi possível gerar slug único, tente novamente');
        }
        $slug = generateSlug($config['slug_length']);
        $exists = $collection->findOne(['slug' => $slug]);
    } while ($exists !== null);

    $doc = [
        'slug' => $slug,
        'url' => $url,
        'clicks' => 0,
        'created_at' => new MongoDB\BSON\UTCDateTime(),
        'last_clicked' => null
    ];

    try {
        $collection->insertOne($doc);
    } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
        throw new Exception('Erro ao salvar link. Tente novamente.');
    }

    return $slug;
}


function getLink(string $slug): ?array {
    global $collection;

    if (!preg_match('/^[0-9A-Za-z]{4,}$/', $slug)) {
        return null;
    }

    $doc = $collection->findOne(['slug' => $slug]);
    if (!$doc) return null;

    $collection->updateOne(
        ['_id' => $doc->_id],
        ['$inc' => ['clicks' => 1], '$set' => ['last_clicked' => new MongoDB\BSON\UTCDateTime()]]
    );

    return [
        'url' => $doc->url,
        'clicks' => $doc->clicks + 1,
        'created_at' => $doc->created_at,
        'last_clicked' => new MongoDB\BSON\UTCDateTime()
    ];
}

function logAccess(string $message): void {
    global $config;
    $time = date('Y-m-d H:i:s');
    $line = "[$time] $message\n";

    $logFile = $config['log_file'] ?? __DIR__ . '/../logs/access.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}
