<?php
declare(strict_types=1);
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'use_strict_mode' => true,
    'cookie_samesite' => 'Lax',
]);

require_once __DIR__ . '/SecretManager.php';
$manager = new SecretManager();

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// $masked = htmlspecialchars($manager->getMasked(), ENT_QUOTES | ENT_HTML5);
$apiKeyHash = htmlspecialchars($manager->getHash(), ENT_QUOTES | ENT_HTML5);


// Handle POST (secure the key)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(400);

    } else {
        $manager->rotateAndHide();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // rotate CSRF
        // $masked = htmlspecialchars($manager->getMasked(), ENT_QUOTES | ENT_HTML5);
        $apiKeyHash = htmlspecialchars($manager->getHash(), ENT_QUOTES | ENT_HTML5);

    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="../Resources/hmbct.png" />
  <title>Private Key Exposure - Secured</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Figtree:wght@300..900&display=swap"
    rel="stylesheet"
  />
  <style>
    body {
      background: var(--black);
      color: var(--white);
      font-family: 'Figtree', sans-serif;
      padding: 20px;
      font-size: 20px;
      font-weight: 400; 
    }
    .output {
      overflow-wrap: anywhere;
    }
  </style>
</head>
<body>
  <div class="main-content">
    <h1>Private Key Exposure - Secured</h1>
    <p><b>Exploit Example:</b> The API was leaked, but you can secure it.</p>
    <form method="POST" action="Secrets/KeyExposureSafe.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES | ENT_HTML5); ?>">
        <?php if (!empty($manager->getMeta()['rotated'])): ?>
            <input type="submit" value="Secured" disabled>
            <?php else: ?>
            <input type="submit" value="Secure the Key">
        <?php endif; ?>
    </form>
  </div>
  <div class="output">
    <?php 
    $meta = $manager->getMeta();
    if (!empty($meta['rotated'])): ?>
        <strong>API Key (hashed):</strong> <?php echo $apiKeyHash; ?>
    <?php else: ?>
        <strong>Key Status:</strong> <?php echo "YOUR_LEAKED_API_KEY"; ?>
    <?php endif; ?>
  </div>
</body>
</html>