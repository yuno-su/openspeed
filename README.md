# OpenSpeed

A lightweight in-browser speed test built on top of [LibreSpeed](https://github.com/librespeed/speedtest) — with its own frontend and an open catalogue of servers.

**[openspeed.bond](https://openspeed.bond)**

## What it is

OpenSpeed measures your internet speed without installing anything and without an account: download, upload, latency and jitter. Everything runs in the browser, and no results are sent anywhere.

The server catalogue is a curated list of independent nodes compatible with the LibreSpeed backend. Every entry lists its city, country, provider and measured speed.

## Features

- download / upload / ping / jitter measurement
- selectable test locations: Russia, CIS, Europe, Asia, America
- public IP and ISP display
- simple configuration: the whole server list lives in one PHP file
- runs on any PHP hosting, no database and no dependencies

## How it works

The frontend is plain PHP + JS. Each node in the catalogue is a separate server exposing the LibreSpeed-compatible backend of three files:

| file | purpose |
|---|---|
| `garbage.php` | streams random data for the download test |
| `empty.php` | accepts data for the upload test and answers pings |
| `getIP.php` | returns the public IP and ISP info |

The backend path differs between nodes: usually `/backend/`, but some serve it from the site root or a subfolder such as `/librespeed/backend/`.

## Own nodes

| node | city | provider |
|---|---|---|
| `openspeed.bond/backend` | Warsaw, Poland | serv00 |
| `md.openspeed.bond` | Chisinau, Moldova | MivoCloud |

## Setup

1. copy the files to any PHP-enabled hosting
2. open `servers.php` and point the list at your own nodes:
   ```php
   'name'     => 'City, Country — domain',
   'city'     => 'City',
   'country'  => 'Country',
   'provider' => 'Provider',
   'server'   => 'backend/',
   ```
3. verify the backend path for every node — it may be the root or a subfolder instead of `/backend/`
4. open the site and run a test

## Requirements

- PHP 5.6 or newer (8.x recommended)
- Apache, nginx or any other web server
- HTTPS with a valid certificate — without it some nodes will refuse to serve data

## Contributing

If you know a working LibreSpeed node that is missing from the catalogue, feel free to send a pull request. The only rule: the node must actually stream megabytes through `garbage.php`, not return a 404 or an HTML page.

## License

The LibreSpeed frontend and backends are licensed under [LGPLv3](https://www.gnu.org/licenses/lgpl-3.0.html). Original project: [librespeed/speedtest](https://github.com/librespeed/speedtest).
