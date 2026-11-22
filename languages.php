<?php

/*
 * OpenSpeed i18n loader (PHP 5.6+)
 *
 * Languages live in langs/<code>.json (flat "key": "text" dict, "_name" key
 * holds the native language name shown in the switcher). Drop a new file in
 * langs/ and it appears in the switcher automatically.
 * Current language: ?lang=xx overrides, cookie os_lang remembers, default en.
 */

$OS_LANGS_DIR = __DIR__ . '/langs';

function os_lang_read($file)
{
    $j = json_decode((string) file_get_contents($file), true);
    return is_array($j) ? $j : array();
}

function os_lang_scan($dir)
{
    $out = array();
    $files = glob($dir . '/*.json');
    if ($files) {
        sort($files);
        foreach ($files as $f) {
            $j = os_lang_read($f);
            if (isset($j['_name'])) {
                $out[basename($f, '.json')] = $j['_name'];
            }
        }
    }
    if (!isset($out['en'])) {
        $out['en'] = 'English';
    }
    return $out;
}

$OS_LANGS = os_lang_scan($OS_LANGS_DIR);

$OS_LANG = 'en';
if (isset($_GET['lang']) && isset($OS_LANGS[$_GET['lang']])) {
    $OS_LANG = $_GET['lang'];
    setcookie('os_lang', $OS_LANG, time() + 31536000, '/');
} elseif (isset($_COOKIE['os_lang']) && isset($OS_LANGS[$_COOKIE['os_lang']])) {
    $OS_LANG = $_COOKIE['os_lang'];
}

$L = os_lang_read($OS_LANGS_DIR . '/en.json');
if ($OS_LANG !== 'en') {
    $L = array_merge($L, os_lang_read($OS_LANGS_DIR . '/' . $OS_LANG . '.json'));
}

function L($key)
{
    global $L;
    return isset($L[$key]) ? $L[$key] : $key;
}

function E($key)
{
    echo htmlspecialchars(L($key), ENT_QUOTES, 'UTF-8');
}

$L_JSON = str_replace('</', '<\\/', json_encode(
    array('lang' => $OS_LANG, 'langs' => $OS_LANGS, 's' => $L),
    JSON_UNESCAPED_UNICODE
));
