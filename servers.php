<?php

/*
 * OpenSpeed - test point list (servers)
 *
 * Fields:
 *   name     - internal label; shown only when city/country are empty
 *   city, country - the row title, rendered as "City, Country"
 *   provider - sponsor / ISP / hosting, shown under the title
 *   url      - provider website, shown as a clickable link (empty = plain text)
 *   server   - backend base URL, must end with "/"
 *              ("backend/" = same origin as this site, always works)
 *   dlURL / ulURL / pingURL / getIpURL - backend files on that server
 *   speed    - server speed: a bare number in Mbps ("61" -> "61 Mbps",
 *              localized unit); a string like "1 Gbps" is shown as is;
 *              empty = hidden
 *   type     - "dc" = datacenter, "res" = residential
 *              (check via ip-api.com)
 *
 * Community list and instructions: https://github.com/yuno-su/openspeed
 *
 * To add a server: copy a block, change the values, open a pull request.
 */

return [
    // ------- OpenSpeed servers -------
[
        'name'     => 'Warsaw, Poland',
        'city'     => 'Warsaw',
        'country'  => 'Poland',
        'provider' => 'OpenSpeed',
        'url'      => 'https://openspeed.bond/',
        'server'   => 'backend/',
        'dlURL'    => 'garbage.php',
        'ulURL'    => 'empty.php',
        'pingURL'  => 'empty.php',
        'getIpURL' => 'getIP.php',
        'speed'    => '',
        'type'     => 'dc',
        'enabled'  => true,
    ],
[
        'name'     => 'Chisinau, Moldova',
        'city'     => 'Chisinau',
        'country'  => 'Moldova',
        'provider' => 'MivoCloud',
        'url'      => 'https://mivocloud.com/',
        'server'   => 'http://md.openspeed.bond/',
        'dlURL'    => 'garbage.php',
        'ulURL'    => 'empty.php',
        'pingURL'  => 'empty.php',
        'getIpURL' => 'getIP.php',
        'speed'    => '37',
        'type'     => 'dc',
        'enabled'  => true,
    ],
[
        'name'     => 'Prague, Czechia',
        'city'     => 'Prague',
        'country'  => 'Czechia',
        'provider' => 'invogram.biz',
        'url'      => 'https://invogram.biz',
        'server'   => 'http://cz.openspeed.bond/backend/',
        'dlURL'    => 'garbage.php',
        'ulURL'    => 'empty.php',
        'pingURL'  => 'empty.php',
        'getIpURL' => 'getIP.php',
        'speed'    => '27',
        'type'     => 'dc',
        'enabled'  => true,
    ],
];