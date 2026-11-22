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
  <a href="#run-your-own-node">Host a node</a>
  &nbsp;·&nbsp;
  <a href="#sponsors">Sponsors</a>
  &nbsp;·&nbsp;
  <a href="LICENSE">MIT</a>
</p>

<p align="center">
  <img alt="License: MIT" src="https://img.shields.io/badge/license-MIT-3b82f6?style=flat-square">
  <img alt="PHP 5.6+" src="https://img.shields.io/badge/PHP-5.6%2B-777bb4?style=flat-square">
  <img alt="Languages: 6" src="https://img.shields.io/badge/languages-6-success?style=flat-square">
  <img alt="No dependencies" src="https://img.shields.io/badge/dependencies-none-success?style=flat-square">
</p>

---

## What it is

OpenSpeed measures your connection **right in the browser** — download, upload, latency and jitter — against a catalogue of independent servers across Russia, the CIS, Europe, Asia and North America. No installs, no accounts, no tracking.

The whole site is a **single PHP application with zero build steps**, and every test point speaks the [LibreSpeed](https://github.com/librespeed/speedtest) backend protocol — so a two-dollar VPS with nginx and PHP is enough to host a node.

## Features

- **Download, upload, ping and jitter** — measured in the browser, streamed by the node itself.
- **Server picker** with city, country, provider, a measured speed hint and an optional sponsor link.
- **IP and ISP display** through each node's `getIP.php` backend.
- **HTTP-only by design** — no mixed content, no proxy hop, honest numbers. See [below](#why-the-site-is-http-only).
- **Six interface languages** — English, Russian, Spanish, German, French, Polish ([authors](langs/AUTHORS.md)).
- **Zero dependencies** — no database, no Composer, no bundler.
- **Self-hostable** — drop the files on any PHP hosting and edit one array.

## How it works

The frontend is plain PHP + JavaScript written for this project. Only the **backend** comes from LibreSpeed, and it is three files:

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

## Run your own node

Running a test point is genuinely easy — the backend is three PHP files and a few kilobytes of traffic. Anything below is enough:

| Option | What you need |
| --- | --- |
| **Weak VPS** | Apache or nginx + PHP-FPM, 256 MB of RAM is plenty |
| **Shared hosting** | PHP hosting that allows `fetch`/XHR to your own domain (CORS) and does not cap the transfer volume |
| **Free PHP hosting** | e.g. [anonhosting.io](https://anonhosting.io) (where `md.openspeed.bond` runs), [serv00.com](https://serv00.com), [ct8.pl](https://ct8.pl) |

Steps:

1. Upload the `backend/` folder from this repository to your host.
2. Check that `https://your-host/backend/getIP.php` answers with JSON (a 404 or an HTML page means the path is wrong).
3. Add a block to `servers.php` — or open a pull request so the node lands in the public catalogue.

## Criteria for a node

A node is added to the public list only if all of this holds:

- **Stable throughput above 10 Mbit/s** — measured repeatedly, not a single lucky run.
- **General stability** — the node answers `empty.php`, `garbage.php` and `getIP.php` day after day, not just right after it was set up.
- **Honest bandwidth** — no throttling the test files, no traffic caps that die after a few hundred megabytes.
- **HTTPS is welcome, HTTP is fine too** — the site is HTTP-only, so plain `http://` nodes work out of the box.

Nodes that stop answering are hidden with `'enabled' => false` and kept in the file for later.

## Adding a server

```php
[
    'name'     => 'Chisinau, Moldova — md.example.com',
    'city'     => 'Chisinau',
    'country'  => 'Moldova',
    'provider' => 'MivoCloud',
    'url'      => 'https://mivocloud.com/',
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

## Sponsors

**We want your servers.** If you run a machine with a clean pipe, fork the repository, add your block and open a pull request — we test it, and if it holds up it goes live for everyone.

And the credit goes to **you**, not to your provider:

- your name (or nickname) goes into the node's `provider` field, exactly as you want it written;
- the `url` field turns into a clickable **sponsor link** under the location in the server picker — your site, your profile, your project;
- the same name stays in the git history of `servers.php`, so the credit is permanent.

Datacenter VPS, home fibre, student lab, ISP backbone — all welcome, as long as it passes the [criteria](#criteria-for-a-node).

## Own nodes

All three are datacenter nodes, so the numbers are honest rather than a home line fighting for air.

| Server | Location | Sponsor | Speed |
| --- | --- | --- | --- |
| `openspeed.bond/backend` | Warsaw, Poland | [OpenSpeed](https://openspeed.bond) | — |
| `md.openspeed.bond` | Chisinau, Moldova | [MivoCloud](https://mivocloud.com) | 37 Mbps |
| `cz.openspeed.bond/backend` | Prague, Czechia | [invogram.biz](https://invogram.biz) | 27 Mbps |

## Project layout

```
index.php          entry point, hero and test UI
servers.php        the whole server catalogue
results.php        result page
add.php            submit a server
info.php           IP / ISP enrichment with a local cache
inc/header.php     shared header and the HTTP-only redirect
inc/footer.php     shared footer
backend/           LibreSpeed-compatible backend (upstream files)
assets/            CSS, JS, fonts
langs/             one JSON per language + AUTHORS.md
```

## Languages

| Code | Language | Author |
| --- | --- | --- |
| `en` | English | Danil "yuno" Zhuzhich |
| `ru` | Русский | Danil "yuno" Zhuzhich |
| `es` | Español | OpenSpeed maintainers |
| `de` | Deutsch | OpenSpeed maintainers |
| `fr` | Français | OpenSpeed maintainers |
| `pl` | Polski | OpenSpeed maintainers |

Adding one is a single file: copy `langs/en.json`, translate the values, keep the keys, set `"_name"` and `"_author"` — the switcher picks it up automatically. Full details in [`langs/AUTHORS.md`](langs/AUTHORS.md).

## FAQ

<details>
<summary><b>Why does the site redirect from HTTPS to HTTP?</b></summary>

Because a page loaded over HTTPS cannot talk to plain-HTTP nodes — the browser blocks it as mixed content. A large part of the catalogue, including some of the fastest servers, only speaks HTTP. Redirecting to HTTP makes every node reachable directly, without a proxy and without lying about latency.
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

Yes. Follow the [criteria](#criteria-for-a-node), add an entry to `servers.php` and open a pull request — and read the [Sponsors](#sponsors) section, because the credit goes to you personally.
</details>

<details>
<summary><b>Do I need a database, Composer or a build step?</b></summary>

No. Copy the files, serve them with PHP, done.
</details>

<details>
<summary><b>Is the site open source?</b></summary>

The frontend, the catalogue and the translations are MIT. Only the backend files in `backend/` are upstream LibreSpeed and stay LGPLv3 — see [License](#license).
</details>

## Contributing

Pull requests are welcome: new working nodes, translation fixes, UI improvements, backend edge cases. If you add a server, include its city, country, sponsor name and measured speed so the catalogue stays useful.

## License

The OpenSpeed frontend, server catalogue and translations are released under the [MIT License](LICENSE).

Only the backend files in `backend/` (`garbage.php`, `empty.php`, `getIP.php` and helpers) come from [LibreSpeed](https://github.com/librespeed/speedtest), stay under **LGPLv3** and are not relicensed here.

## Credits

- [LibreSpeed](https://github.com/librespeed/speedtest) — the backend protocol this project speaks.
- [JetBrains Mono](https://www.jetbrains.com/lp/mono/) — the typeface used in the UI and the logo.
- Every volunteer who keeps a node running — the catalogue exists because of them.
