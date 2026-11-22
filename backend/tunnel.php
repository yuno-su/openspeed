<?php

/*
 * OpenSpeed - mixed-content tunnel
 *
 * When this site is served over https, browsers block direct requests to
 * http:// test points (mixed content). The frontend then routes those
 * requests through this script:  browser -> this host -> http test point.
 * Enabled server points get the "Proxy" tag; on an http page everything
 * goes directly, this file is not used.
 *
 * Usage:  tunnel.php?u=<backend base url>&f=empty.php|garbage.php|getIP.php[&...]
 * All other query parameters are passed to the upstream backend.
 * Requires allow_url_fopen = On.
 */

error_reporting(0);
@ini_set('zlib.output_compression', 'Off');
@ini_set('output_buffering', 'Off');
@set_time_limit(0);

$FILES = array('empty.php', 'garbage.php', 'getIP.php');
$base = isset($_GET['u']) ? $_GET['u'] : '';
$file = isset($_GET['f']) ? $_GET['f'] : '';

if (!in_array($file, $FILES) || !preg_match('#^https?://[^/]+(/.*)?$#i', $base)) {
    header('HTTP/1.1 400 Bad Request');
    exit('tunnel: bad request');
}

$host = strtolower((string) parse_url($base, PHP_URL_HOST));
if (
    $host === '' || $host === 'localhost' || $host === '::1' || $host === '0.0.0.0'
    || preg_match('/^(127\.|10\.|192\.168\.|169\.254\.|0\.)/', $host)
    || preg_match('/^172\.(1[6-9]|2\d|3[01])\./', $host)
) {
    header('HTTP/1.1 403 Forbidden');
    exit('tunnel: blocked host');
}

$base = rtrim($base, '/');
$q = $_GET;
unset($q['u'], $q['f']);
$url = $base . '/' . $file . ($q ? '?' . http_build_query($q) : '');

$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
$opts = array('http' => array(
    'method'        => $isPost ? 'POST' : 'GET',
    'timeout'       => ($file === 'garbage.php') ? 120 : 20,
    'user_agent'    => 'OpenSpeed-Tunnel/1',
    'ignore_errors' => true,
    'follow_location' => 0,
));
if ($isPost) {
    $opts['http']['content'] = file_get_contents('php://input');
    if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] !== '') {
        $opts['http']['header'] = "Content-Type: " . $_SERVER['CONTENT_TYPE'] . "\r\n";
    }
}

$ctx = stream_context_create($opts);
$fp = @fopen($url, 'rb', false, $ctx);
if (!$fp) {
    header('HTTP/1.1 502 Bad Gateway');
    exit('tunnel: upstream unreachable');
}

$status = 'HTTP/1.1 200 OK';
foreach ($http_response_header as $line) {
    if (preg_match('#^HTTP/#i', $line)) { $status = $line; continue; }
    if (preg_match('#^(Content-Type|Content-Length):#i', $line)) header($line);
}
header($status);
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

while (ob_get_level() > 0) { @ob_end_flush(); }
while (!feof($fp)) {
    echo fread($fp, 65536);
    if (connection_aborted()) break;
    @flush();
}
fclose($fp);
