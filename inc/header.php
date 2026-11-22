<?php
// OpenSpeed - common page header. Requires languages.php and $PAGE first.

/*
 * The site is deliberately http-only: every https request to a page gets a
 * permanent 301 redirect back to http. This keeps all http:// test points
 * directly reachable from the page - no mixed content, no proxy tunnel,
 * honest ping and speeds. Do NOT enable HSTS or "Always Use HTTPS"
 * (Cloudflare) in front of this site: that would loop with this redirect.
 */
$osHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && strtolower($_SERVER['HTTPS']) !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
    || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
if ($osHttps) {
    $osHost = isset($_SERVER['HTTP_HOST']) ? trim($_SERVER['HTTP_HOST']) : 'openspeed.bond';
    if (!preg_match('/^[A-Za-z0-9.\-]+(:[0-9]+)?$/', $osHost)) {
        $osHost = 'openspeed.bond';
    }
    $osUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: http://' . $osHost . $osUri);
    exit;
}

if (!isset($PAGE)) {
    $PAGE = array('id' => '', 'title' => 'OpenSpeed');
}
?><!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($OS_LANG, ENT_QUOTES, 'UTF-8'); ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($PAGE['title'], ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="description" content="OpenSpeed - free and open internet speed test powered by LibreSpeed.">
<meta name="theme-color" content="#0a0b0c">
  <link rel="preload" href="assets/fonts/JetBrainsMono-Regular.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="assets/fonts/JetBrainsMono-Medium.woff2" as="font" type="font/woff2" crossorigin>
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Cpath d='M4.5 22a11.5 11.5 0 1 1 23 0' fill='none' stroke='%23e9ebed' stroke-width='3' stroke-linecap='round'/%3E%3Cpath d='M16 22l7-10' stroke='%23e8ecf4' stroke-width='3' stroke-linecap='round'/%3E%3C/svg%3E">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <div class="wrap topbar-in">
    <a class="logo" href="index.php">
      <svg viewBox="0 0 32 32" fill="none" aria-hidden="true">
        <path d="M4.5 22a11.5 11.5 0 1 1 23 0" stroke="url(#lg1)" stroke-width="3" stroke-linecap="round"/>
        <path d="M16 22l7-10" stroke="#e8ecf4" stroke-width="3" stroke-linecap="round"/>
        <defs>
          <linearGradient id="lg1" x1="4" y1="22" x2="28" y2="22" gradientUnits="userSpaceOnUse">
            <stop stop-color="#e9ebed"/><stop offset="1" stop-color="#878c92"/>
          </linearGradient>
        </defs>
      </svg>
      <span>open<b>speed</b></span>
    </a>
    <nav class="nav">
      <a href="index.php"<?php echo $PAGE['id'] === 'test' ? ' class="on"' : ''; ?>><?php E('nav_test'); ?></a>
      <a href="results.php"<?php echo $PAGE['id'] === 'report' ? ' class="on"' : ''; ?>><?php E('nav_report'); ?></a>
      <a href="about.php"<?php echo $PAGE['id'] === 'about' ? ' class="on"' : ''; ?>><?php E('nav_about'); ?></a>
      <a href="add.php"<?php echo $PAGE['id'] === 'add' ? ' class="on"' : ''; ?>><?php E('nav_add'); ?></a>
    </nav>
    <div class="top-actions">
      <select id="langSel" aria-label="<?php E('lang_label'); ?>">
<?php foreach ($OS_LANGS as $code => $label) { ?>
        <option value="<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $code === $OS_LANG ? ' selected' : ''; ?>><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
<?php } ?>
      </select>
      <a class="gh-btn" href="https://github.com/yuno-su/openspeed" target="_blank" rel="noopener" aria-label="GitHub">
        <svg viewBox="0 0 16 16" aria-hidden="true"><path fill="currentColor" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8Z"/></svg>
      </a>
    </div>
  </div>
</header>
<script id="i18n-data" type="application/json"><?php echo $L_JSON; ?></script>
<script src="assets/site.js"></script>
<main class="wrap">
