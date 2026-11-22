<p align="center">
  <img src="logo.svg" alt="OpenSpeed" width="560">
</p>

<p align="center">
  <b>A fast, lightweight internet speed test with an open catalogue of servers.</b>
</p>

<p align="center">
  <a href="https://openspeed.bond"><b>openspeed.bond</b></a>
  &nbsp;·&nbsp;
  <a href="#faq">FAQ</a>
  &nbsp;·&nbsp;
  <a href="#adding-a-server">Add a server</a>
  &nbsp;·&nbsp;
  <a href="LICENSE">MIT</a>
</p>

<p align="center">
  <img alt="License: MIT" src="https://img.shields.io/badge/license-MIT-3b82f6?style=flat-square">
  <img alt="PHP 5.6+" src="https://img.shields.io/badge/PHP-5.6%2B-777bb4?style=flat-square">
  <img alt="No dependencies" src="https://img.shields.io/badge/dependencies-none-success?style=flat-square">
  <img alt="Status: live" src="https://img.shields.io/badge/status-live-success?style=flat-square">
</p>

---

## What it is

OpenSpeed measures your connection **right in the browser** — download, upload, latency and jitter — against a catalogue of independent servers across Russia, the CIS, Europe, Asia and North America. No installs, no accounts, no tracking.

It speaks the [LibreSpeed](https://github.com/librespeed/speedtest) backend protocol, so any compatible node can be plugged into the catalogue, and the frontend itself is a single PHP application with zero build steps.

## Features

- **Download, upload, ping and jitter** — measured in the browser, streamed by the node itself.
- **Server picker** with city, country, provider and a measured speed hint for every entry.
- **IP and ISP display** through each node's `getIP.php` backend.
- **HTTP-only by design** — no mixed content, no proxy hop, honest numbers. See [below](#why-the-site-is-http-only).
- **Zero dependencies** — no database, no Composer, no bundler.
- **Bilingual interface** — English and Russian out of the box (`langs/`).
- **Self-hostable** — drop the files on any PHP hosting and edit one array.

## How it works

The frontend is plain PHP + JavaScript. Every node exposes the LibreSpeed backend of three files:

| File | Purpose |
| --- | --- |
| `garbage.php` | streams random data for the download test |
| `empty.php` | accepts upload data and answers latency probes |
| `getIP.php` | returns the client's public IP and ISP |

Backend paths are *not* standardised: most nodes serve them from `/backend/`, some from the site root, some from a subfolder such as `/librespeed/backend/`. The path is stored per node in `servers.php` in the `server` field.

## Why the site is HTTP-only

Browsers refuse **mixed content**: a page served over HTTPS cannot send requests to plain `http://` hosts. Most nodes in the catalogue have no TLS at all — several serve their backend on a non-standard port that no certificate covers — so on an HTTPS page the fastest test points would simply be unreachable.

OpenSpeed solves that by running on HTTP and answering every HTTPS request with a **permanent 301 redirect back to HTTP** (`inc/header.php`). Every node is then reachable directly: no proxy hop, no downgrade, and honest latency and bandwidth numbers.

The trade-off is explicit and deliberate: the browser marks the page as *Not secure*. If you would rather keep HTTPS:

1. remove the redirect from `inc/header.php`;
2. route HTTP-only nodes through `backend/tunnel.php` in `servers.php`;
3. accept that their numbers will be lower — and label them as proxied in the UI.

> [!IMPORTANT]
> While this build is HTTP-only, do **not** enable HSTS, "Always Use HTTPS", or a Cloudflare redirect rule — the redirect will loop and the site will become unreachable.

## Quick start

1. Copy the files to any hosting with PHP.
2. Point the web server at the folder — `index.php` is the entry point.
3. Open `servers.php` and adjust the catalogue if you want your own nodes.
4. Open the site and run a test.

Requirements: **PHP 5.6+** (8.x recommended), any web server, no database.

## Adding a server

```php
[
    'name'     => 'Chisinau, Moldova — md.example.com',
    'city'     => 'Chisinau',
    'country'  => 'Moldova',
    'provider' => 'MivoCloud',
    'server'   => 'backend/',
    'dlURL'    => 'garbage.php',
    'ulURL'    => 'empty.php',
    'pingURL'  => 'empty.php',
    'getIpURL' => 'getIP.php',
    'speed'    => '',
    'type'     => 'dc',
    'enabled'  => true,
],
```

A node qualifies only if `garbage.php` actually streams megabytes. A 404 page, an HTML error or a kilobyte response is not a node.

## Own nodes

| Node | Location | Provider |
| --- | --- | --- |
| `openspeed.bond/backend` | Warsaw, Poland | serv00 |
| `ru.openspeed.bond:88` | Elektrostal, Russia | Beeline |
| `md.openspeed.bond` | Chisinau, Moldova | MivoCloud |

## Project layout

```
index.php          entry point, hero and test UI
servers.php        the whole server catalogue
results.php        result page
add.php            submit a server
inc/header.php     shared header and the HTTP-only redirect
inc/footer.php     shared footer
backend/           LibreSpeed-compatible backend
assets/            CSS, JS, fonts
langs/             en.json, ru.json
```

## FAQ

<details>
<summary><b>Why does the site redirect from HTTPS to HTTP?</b></summary>

Because a page loaded over HTTPS cannot talk to plain-HTTP nodes — the browser blocks it as mixed content. Roughly a quarter of the catalogue, including some of the fastest servers, only speaks HTTP. Redirecting to HTTP makes every node reachable directly, without a proxy and without lying about latency.
</details>

<details>
<summary><b>Why does my browser say the page is not secure?</b></summary>

That is the direct consequence of the answer above. The page carries no user data, no cookies and no login — it only streams test payloads — but the "not secure" badge is the price of HTTP-only operation.
</details>

<details>
<summary><b>Do you store my results?</b></summary>

No. Everything is calculated in your browser from the bytes the node sent. The backend only streams data and reports the IP and ISP of the request, which it must know anyway in order to serve it.
</details>

<details>
<summary><b>Why is the speed lower than on Ookla or Speedtest?</b></summary>

Three common reasons: the test uses a single TCP stream, so a distant node limits the result more than a multi-threaded test would; some nodes are volunteer-run and capped by their uplink; and mobile networks throttle differently per host. Pick the node closest to you for the fairest number.
</details>

<details>
<summary><b>What does the "Proxy" label on a server mean?</b></summary>

That node is HTTP-only, so the request goes through `backend/tunnel.php` on this site instead of directly. It works, but the number you see is limited by both the tunnel and the node.
</details>

<details>
<summary><b>How do I switch the whole site back to HTTPS?</b></summary>

Remove the redirect from `inc/header.php`, serve the site over HTTPS again, and route HTTP-only nodes through the tunnel. Expect slower numbers on those nodes.
</details>

<details>
<summary><b>Why does the same test give different results every run?</b></summary>

Wi-Fi, background traffic, CPU load, node congestion and whichever route your ISP picks this minute. Run three tests and look at the median, not at the single best one.
</details>

<details>
<summary><b>Can I add my own server to the catalogue?</b></summary>

Yes — add an entry to `servers.php` (format above) and open a pull request. The only requirement is that the node actually streams data through `garbage.php`.
</details>

<details>
<summary><b>Do I need a database, Composer or a build step?</b></summary>

No. Copy the files, serve them with PHP, done.
</details>

## Contributing

Pull requests are welcome: new working nodes, translation fixes, UI improvements, backend edge cases. If you add a server, include its city, country, provider and measured speed so the catalogue stays useful.

## License

The OpenSpeed frontend and the server catalogue are released under the [MIT License](LICENSE).

The LibreSpeed backend files in `backend/` (`garbage.php`, `empty.php`, `getIP.php` and helpers) remain under **LGPLv3** and are not relicensed here. Original project: [librespeed/speedtest](https://github.com/librespeed/speedtest).

## Credits

- [LibreSpeed](https://github.com/librespeed/speedtest) — the backend protocol and the original idea.
- [JetBrains Mono](https://www.jetbrains.com/lp/mono/) — the typeface used in the UI and the logo.
- Every volunteer who keeps a node running — the catalogue exists because of them.
