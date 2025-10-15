<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../src/functions.php';
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$success = null;
$error = null;

header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Referrer-Policy: no-referrer");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $original = trim($_POST['url']);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown'; 

    if ($original === '') {
        $error = 'URL vazia.';
    } else {
        try {
            $slug = saveLink($original, $ip);
            $success = rtrim($baseUrl, '/') . '/' . $slug;
            logAccess("Link criado: $slug -> $original (IP: $ip)");
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Encurtador de links</title>
<style>
body{font-family:system-ui,Segoe UI,Roboto,Helvetica,Arial;background:#f7f7f8;color:#111;padding:20px;max-width:800px;margin:20px auto}
form{display:grid;gap:8px}
input[type=url]{padding:8px;font-size:16px}
button{padding:10px 14px;font-size:16px}
.box{background:#fff;padding:16px;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.06)}
.success{background:#e6ffed;border-left:4px solid #2ca36b;padding:10px;margin-top:8px}
.error{background:#ffecec;border-left:4px solid #d9534f;padding:10px;margin-top:8px}
</style>
</head>
<body>
<h1>Encurtador de links</h1>
<div class="box">
<form method="post" action="">
<label>URL a encurtar
<input type="url" name="url" placeholder="https://exemplo.com" required>
</label>
<div><button type="submit">Encurtar</button></div>
</form>

<?php if(!empty($error)): ?><div class="error"><?=h($error)?></div><?php endif; ?>
<?php if(!empty($success)): ?><div class="success"><strong>Encurtado:</strong><div><a href="<?=h($success)?>" target="_blank"><?=h($success)?></a></div></div><?php endif; ?>
</div>
</body>
</html>
