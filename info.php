<?php

/*
 * OpenSpeed - enrichment endpoint (server-side proxy for ip-api.com), PHP 5.6+
 *
 *   info.php?mode=servers -> {servers: {serverUrl: {ip,isp,as,asname,city,country,type}}}
 *                            type: dc / res / mobile (hosting flag from ip-api.com)
 *   info.php?mode=client  -> same shape for the visitor: IP, ISP, AS, geo, type
 *
 * Answers are cached in cache/ipinfo.json (12 h for servers, 6 h for clients).
 * If ip-api.com is unreachable the endpoint still answers 200 with empty
 * data - the UI falls back to the static values from servers.php.
 */

error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store');

define('OS_CACHE_FILE', __DIR__ . '/cache/ipinfo.json');
define('OS_TTL_SERVERS', 43200);
define('OS_TTL_CLIENT', 21600);
define('OS_API', 'http://ip-api.com/');
define('OS_FIELDS', 'query,status,country,city,isp,org,as,asname,hosting,mobile,proxy');

function os_cache_load()
{
    if (!is_file(OS_CACHE_FILE)) {
        return array();
    }
    $j = json_decode((string) file_get_contents(OS_CACHE_FILE), true);
    return is_array($j) ? $j : array();
}

function os_cache_save($data)
{
    $dir = dirname(OS_CACHE_FILE);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    if (isset($data['clients']) && count($data['clients']) > 300) {
        $data['clients'] = array_slice($data['clients'], -150, null, true);
    }
    @file_put_contents(OS_CACHE_FILE, json_encode($data), LOCK_EX);
}

function os_http($url, $postJson = null, $timeout = 4)
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
        ));
        if ($postJson !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postJson);
        }
        $body = curl_exec($ch);
        curl_close($ch);
        return is_string($body) ? $body : false;
    }
    $ctx = stream_context_create(array('http' => array(
        'method'  => $postJson !== null ? 'POST' : 'GET',
        'header'  => $postJson !== null ? "Content-Type: application/json\r\n" : '',
        'content' => $postJson !== null ? $postJson : '',
        'timeout' => $timeout,
        'ignore_errors' => true,
    )));
    return @file_get_contents($url, false, $ctx);
}

// 2 s probe so a dead network never hangs the page
function os_online()
{
    $c = os_http(OS_API . 'json/?fields=status', null, 2);
    return is_string($c) && $c !== '' && strpos($c, '"status"') !== false;
}

function os_ip_of_host($host)
{
    $ip = @gethostbyname($host);
    if ($ip !== $host && filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
    }
    return '';
}

function os_client_ip()
{
    foreach (array('HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR') as $k) {
        if (!empty($_SERVER[$k])) {
            $v = $_SERVER[$k];
            if (strpos($v, ',') !== false) {
                $v = trim(substr($v, 0, strpos($v, ',')));
            }
            if (filter_var($v, FILTER_VALIDATE_IP)) {
                return $v;
            }
        }
    }
    $v = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    return filter_var($v, FILTER_VALIDATE_IP) ? $v : '';
}

function os_row($r)
{
    if (!is_array($r) || (isset($r['status']) && $r['status'] !== 'success')) {
        return null;
    }
    $type = 'res';
    if (!empty($r['hosting'])) {
        $type = 'dc';
    } elseif (!empty($r['mobile'])) {
        $type = 'mobile';
    }
    return array(
        'ip'      => isset($r['query']) ? $r['query'] : '',
        'isp'     => isset($r['isp']) ? $r['isp'] : '',
        'org'     => isset($r['org']) ? $r['org'] : '',
        'as'      => isset($r['as']) ? $r['as'] : '',
        'asname'  => isset($r['asname']) ? $r['asname'] : '',
        'city'    => isset($r['city']) ? $r['city'] : '',
        'country' => isset($r['country']) ? $r['country'] : '',
        'type'    => $type,
    );
}

function os_batch($ips)
{
    $raw = os_http(OS_API . 'batch?fields=' . OS_FIELDS, json_encode(array_values($ips)), 6);
    if (!is_string($raw) || $raw === '') {
        return array();
    }
    $j = json_decode($raw, true);
    return is_array($j) ? $j : array();
}

function os_host_of($serverUrl)
{
    $u = (string) $serverUrl;
    if (strpos($u, '//') === 0) {
        $u = 'http:' . $u;
    } elseif (strpos($u, '://') === false) {
        $u = 'http://' . $u;
    }
    $host = parse_url($u, PHP_URL_HOST);
    if (!is_string($host) || $host === '' || strpos($host, '.') === false) {
        return '';
    }
    return $host;
}

$cache = os_cache_load();
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'servers';

if ($mode === 'client') {
    $ip = os_client_ip();
    $key = 'ip:' . $ip;
    $out = array('ip' => $ip);
    if ($ip !== '' && isset($cache['clients'][$key]['ts']) && (time() - $cache['clients'][$key]['ts']) < OS_TTL_CLIENT) {
        $out = $cache['clients'][$key]['data'];
    } elseif ($ip !== '' && os_online()) {
        $raw = os_http(OS_API . 'json/' . $ip . '?fields=' . OS_FIELDS, null, 4);
        $j = is_string($raw) ? json_decode($raw, true) : null;
        $row = os_row(is_array($j) ? $j : array());
        if ($row !== null) {
            $out = $row;
            $cache['clients'][$key] = array('ts' => time(), 'data' => $out);
            os_cache_save($cache);
        }
    }
    echo json_encode(is_array($out) ? $out : array('ip' => $ip));
    exit;
}

// mode=servers
if (isset($cache['servers_ts']) && isset($cache['servers']) && (time() - $cache['servers_ts']) < OS_TTL_SERVERS) {
    echo json_encode(array('servers' => $cache['servers']));
    exit;
}

$servers = require __DIR__ . '/servers.php';
$hosts = array();
foreach ($servers as $s) {
    $h = os_host_of(isset($s['server']) ? $s['server'] : '');
    if ($h !== '') {
        $hosts[$h] = true;
    }
}

$out = array();
if (count($hosts) && os_online()) {
    $res = array();
    foreach (array_keys($hosts) as $h) {
        $ip = os_ip_of_host($h);
        if ($ip !== '') {
            $res[$h] = $ip;
        }
    }
    if (count($res)) {
        $rows = os_batch(array_values($res));
        $byIp = array();
        foreach ($rows as $rr) {
            $r2 = os_row($rr);
            if ($r2 !== null) {
                $byIp[$r2['ip']] = $r2;
            }
        }
        foreach ($servers as $s) {
            $h = os_host_of($s['server']);
            if ($h !== '' && isset($res[$h]) && isset($byIp[$res[$h]])) {
                $out[$s['server']] = $byIp[$res[$h]];
            }
        }
    }
}

if (count($out)) {
    $cache['servers'] = $out;
    $cache['servers_ts'] = time();
    os_cache_save($cache);
}

echo json_encode(array('servers' => $out));
