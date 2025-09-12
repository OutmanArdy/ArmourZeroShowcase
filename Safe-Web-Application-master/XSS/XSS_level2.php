<?php
$output = "";
$status_code = 200;
$current_mode = (isset($_GET['mode']) && $_GET['mode'] === 'vulnerable') ? 'vulnerable' : 'safe';

if (isset($_GET["username"])) {
  if (!empty($_GET["username"])) {
    $user = htmlspecialchars($_GET["username"], ENT_QUOTES, 'UTF-8');
    $output = "Your name is " . $user;
  } else {
    $output = "Please enter a value.";
  }
} else {
  $output = "Please enter a value.";
}

http_response_code($status_code);
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>XSS 2</title>
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
            font-weight: 400; /* This is optional but can be used for clarity */
        }
    </style>
</head>
<body>
  <div class="main-content">
    <h1>XSS Vulnerability Exploitation <?= $current_mode === 'safe' ? 'Solved' : 'Demo'; ?></h1>
    <p>
      <b>Try XSS Exploit again by using:</b><br>
      http://example.com/script.php?username=&lt;img src=x onerror=alert('XSS')&gt;<br>
      <?= $current_mode === 'safe'
        ? 'No pop-up alert occurred. Successfully prevented attempted XSS exploitation.'
        : 'This demo shows how reflected XSS can be triggered when user input is not sanitized.'; ?>
    </p>
    <form action="XSS/XSS_level2.php" method="GET">
      <input type="hidden" name="mode" value="<?= $current_mode; ?>">
      <label for="username">Input here:</label>
      <input type="text" name="username" id="username" value="">
      <input type="submit" value="Submit">
    </form>
      </div>
    <div class="output"><?= $output; ?></div>
  </div>
</body>
</html>