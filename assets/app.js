/* OpenSpeed test page: circular gauge, server picker, wiring to the LibreSpeed engine */
(function () {
  "use strict";

  if (!window.OS) return;
  var t = OS.t, esc = OS.esc;
  OS.applyUnits();

  var SERVERS = JSON.parse(document.getElementById("servers-data").textContent);

  /* circular gauge: 270 deg sweep (135 -> 405), non-linear scale to 1000 Mbps */
  var CX = 180, CY = 176, R = 128;
  var A0 = 135, A1 = 405;
  var STOPS = [0, 1, 5, 10, 50, 100, 250, 500, 1000];
  var NS = "http://www.w3.org/2000/svg";

  function r(id) { return document.getElementById(id); }
  var track = r("gaugeTrack"), prog = r("gaugeProg"), ticks = r("gaugeTicks");
  var tip = r("gaugeTip"), valEl = r("gaugeValue"), unitEl = r("gaugeUnit");
  var runVal = r("runVal"), btn = r("startBtn"), startLabel = r("startLabel");
  var statusEl = r("status"), connEl = r("connInfo");
  var mask = r("pickerMask"), list = r("srvList"), search = r("srvSearch");
  var cards = { ping: r("rPing"), jitter: r("rJitter"), dl: r("rDl"), ul: r("rUl") };

  var arcLen = 0, test = null, running = false;
  var selected = -1;
  var pings = {};   /* index -> ms | null */
  var last = { dl: 0, ul: 0, ping: 0, jitter: 0, ip: "", isp: "" };
  var started = false;

  /* ---------- gauge ---------- */

  function polar(a, rad) {
    var x = a * Math.PI / 180;
    return [CX + rad * Math.cos(x), CY + rad * Math.sin(x)];
  }

  function frac(v) {
    if (typeof v !== "number" || !isFinite(v) || v < 0) v = 0;
    if (v >= STOPS[STOPS.length - 1]) return 1;
    for (var i = 0; i < STOPS.length - 1; i++) {
      if (v <= STOPS[i + 1]) {
        return (i + (v - STOPS[i]) / (STOPS[i + 1] - STOPS[i])) / (STOPS.length - 1);
      }
    }
    return 1;
  }

  function setGauge(v) {
    var f = frac(v);
    prog.style.strokeDashoffset = String(arcLen * (1 - f));
    var p = polar(A0 + (A1 - A0) * f, R);
    tip.setAttribute("cx", p[0].toFixed(2));
    tip.setAttribute("cy", p[1].toFixed(2));
    tip.style.opacity = f > 0.003 ? "1" : "0";
  }

  function buildGauge() {
    var s = polar(A0, R), e = polar(A1, R);
    var d = "M " + s[0].toFixed(2) + " " + s[1].toFixed(2) +
      " A " + R + " " + R + " 0 1 1 " + e[0].toFixed(2) + " " + e[1].toFixed(2);
    track.setAttribute("d", d);
    prog.setAttribute("d", d);
    arcLen = track.getTotalLength();
    prog.style.strokeDasharray = String(arcLen);
    prog.style.strokeDashoffset = String(arcLen);
    for (var i = 0; i < STOPS.length; i++) {
      var a = A0 + (A1 - A0) * (i / (STOPS.length - 1));
      var p1 = polar(a, R - 14), p2 = polar(a, R - 8), lp = polar(a, R - 30);
      var ln = document.createElementNS(NS, "line");
      ln.setAttribute("x1", p1[0].toFixed(1)); ln.setAttribute("y1", p1[1].toFixed(1));
      ln.setAttribute("x2", p2[0].toFixed(1)); ln.setAttribute("y2", p2[1].toFixed(1));
      ln.setAttribute("class", "tickl");
      ticks.appendChild(ln);
      var tx = document.createElementNS(NS, "text");
      tx.setAttribute("x", lp[0].toFixed(1)); tx.setAttribute("y", lp[1].toFixed(1));
      tx.setAttribute("class", "tickt");
      tx.setAttribute("text-anchor", "middle");
      tx.setAttribute("dominant-baseline", "middle");
      tx.textContent = String(STOPS[i]);
      ticks.appendChild(tx);
    }
  }

  function fmt(v) {
    if (typeof v !== "number" || !isFinite(v) || v < 0) return "\u2014";
    return v >= 100 ? String(Math.round(v)) : String(Math.round(v * 10) / 10);
  }

  function setStatus(s) { statusEl.textContent = s; }
  function setUnit(u) { unitEl.textContent = u; }

  function resetCards() {
    cards.ping.textContent = "\u2014";
    cards.jitter.textContent = "\u2014";
    cards.dl.textContent = "\u2014";
    cards.ul.textContent = "\u2014";
  }

  function showRun(on) {
    runVal.classList.toggle("hidden", !on);
    btn.classList.toggle("hidden", on);
  }

  /* ---------- mixed content tunnel ---------- */

  /* on an https page, http:// test points are blocked by the browser:
     route them through backend/tunnel.php on this host */
  function prox(s) {
    return location.protocol === "https:" && String(s.server || "").indexOf("http://") === 0;
  }
  /* The engine builds request URLs as s.server + s.<x>URL itself, so the four
     fields must stay exactly as written in servers.php (relative file names).
     Making them absolute here produced URLs like
     http://host/backend/http://host/backend/empty.php, every engine ping then
     failed, selectServer returned null and start() crashed on null.server -
     that was the frozen "Connecting" state on the live site. */
  function tp(s) {
    var c = {}, k;
    for (k in s) c[k] = s[k];
    return c;
  }

  /* ---------- server views ---------- */

  function srvView(i) {
    var s = SERVERS[i];
    if (!s) return null;
    var m = OS.meta(s);
    return {
      name: s.name || "",
      city: s.city || m.city || "",
      country: s.country || m.country || "",
      provider: s.provider || m.isp || "",
      url: s.url || "",
      proxy: prox(s),
      type: s.type || m.type || "",
      speed: s.speed || ""
    };
  }

  function locOf(v) {
    return (v.city && v.country) ? v.city + ", " + v.country : (v.city || v.country || v.name);
  }

  function providerHtml(v) {
    var name = v.provider || "";
    if (!name) return "";
    if (v.url) {
      return '<a class="sr-sub" href="' + esc(v.url) + '" target="_blank" rel="noopener noreferrer">' +
        esc(name) + '<span class="ext">&#8599;</span></a>';
    }
    return '<span class="sr-sub">' + esc(name) + "</span>";
  }

  function tagsHtml(v, ping) {
    var out = "";
    if (typeof ping === "number") out += '<span class="tg"><b>' + esc(fmt(ping)) + "</b> ms</span>";
    else out += '<span class="tg na">' + esc(t("srv_ping_na")) + "</span>";
    if (v.type === "dc") out += '<span class="tg tg-dc">' + esc(t("chip_dc")) + "</span>";
    else if (v.type === "res") out += '<span class="tg tg-res">' + esc(t("chip_res")) + "</span>";
    else if (v.type === "mobile") out += '<span class="tg">' + esc(t("chip_mobile")) + "</span>";
    var sp = OS.speedLabel(v.speed);
    if (sp) out += '<span class="tg">' + esc(sp) + "</span>";
    if (v.proxy) out += '<span class="tg tg-px">' + esc(t("srv_proxy")) + "</span>";
    return out;
  }

  function renderBar() {
    var v = srvView(selected);
    if (!v) return;
    r("srvLoc").textContent = locOf(v);
    r("srvProvider").innerHTML = providerHtml(v);
    r("srvTags").innerHTML = tagsHtml(v, pings[selected]);
  }

  /* ---------- ping probes (picker + auto select) ---------- */

  function probe(i) {
    var s = SERVERS[i];
    var base = s.server + s.pingURL;
    base += (base.indexOf("?") < 0 ? "?" : "&") + "cors=true";
    var times = [];
    function once() {
      return new Promise(function (res) {
        var xhr = new XMLHttpRequest();
        var t0 = performance.now(), fin = false;
        xhr.open("GET", base + "&r=" + Math.random(), true);
        xhr.timeout = 4000;
        function done(ok) {
          if (fin) return;
          fin = true;
          if (ok && xhr.status === 200) times.push(performance.now() - t0);
          res();
        }
        xhr.onload = function () { done(true); };
        xhr.onerror = xhr.ontimeout = xhr.onabort = function () { done(false); };
        try { xhr.send(null); } catch (e) { done(false); }
      });
    }
    var p = once().then(once).then(once);
    return p.then(function () {
      if (!times.length) return null;
      times.sort(function (a, b) { return a - b; });
      return Math.round(times[0] * 10) / 10;
    });
  }

  function probeAll() {
    setStatus(t("st_probe"));
    var ps = SERVERS.map(function (s, i) {
      return probe(i).then(function (ms) { pings[i] = ms; });
    });
    return Promise.all(ps).then(function () {
      var best = 0, bv = Infinity, any = false, k;
      for (k in pings) {
        var v = pings[k];
        if (typeof v === "number" && v < bv) { bv = v; best = parseInt(k, 10); any = true; }
      }
      if (!any) best = 0;
      return best;
    });
  }

  /* ---------- picker modal ---------- */

  function openPicker() {
    mask.classList.remove("hidden");
    search.value = "";
    renderList("");
    setTimeout(function () { search.focus(); }, 50);
  }
  function closePicker() { mask.classList.add("hidden"); }

  function renderList(q) {
    q = q.trim().toLowerCase();
    list.innerHTML = "";
    var n = 0;
    for (var i = 0; i < SERVERS.length; i++) {
      var v = srvView(i);
      var hay = (v.city + " " + v.country + " " + v.provider + " " + v.name).toLowerCase();
      if (q && hay.indexOf(q) < 0) continue;
      n++;
      var row = document.createElement("div");
      row.className = "srvrow" + (i === selected ? " sel" : "") + (pings[i] === null ? " dead" : "");
      row.setAttribute("data-i", String(i));
      row.setAttribute("role", "button");
      row.setAttribute("tabindex", "0");
      row.innerHTML =
        '<div class="sr-tags">' + tagsHtml(v, pings[i]) + "</div>" +
        '<div class="sr-main">' +
          '<div class="sr-loc">' + esc(locOf(v)) + "</div>" +
          providerHtml(v) +
        "</div>";
      var pick = function () {
        selected = parseInt(row.getAttribute("data-i"), 10);
        renderBar();
        closePicker();
      };
      row.addEventListener("click", function (e) {
        if (e.target && e.target.closest && e.target.closest("a")) return;
        pick();
      });
      row.addEventListener("keydown", function (e) {
        if (e.key === "Enter" || e.key === " ") { e.preventDefault(); pick(); }
      });
      list.appendChild(row);
    }
    if (!n) list.innerHTML = '<div class="empty">' + esc(t("list_empty")) + "</div>";
  }

  r("openPicker").addEventListener("click", openPicker);
  r("closePicker").addEventListener("click", closePicker);
  mask.addEventListener("click", function (e) { if (e.target === mask) closePicker(); });
  document.addEventListener("keydown", function (e) { if (e.key === "Escape") closePicker(); });
  search.addEventListener("input", function () { renderList(search.value); });

  /* ---------- test flow ---------- */

  function num(x) { var v = parseFloat(x); return isFinite(v) ? v : 0; }

  function onupdate(d) {
    /* worker sends statuses as strings - normalize */
    d.dlStatus = num(d.dlStatus);
    d.ulStatus = num(d.ulStatus);
    d.pingStatus = num(d.pingStatus);
    d.jitterStatus = num(d.jitterStatus);
    if (d.clientIp) {
      var s = String(d.clientIp), idx = s.indexOf(" - ");
      if (idx > 0) {
        last.ip = s.slice(0, idx);
        last.isp = s.slice(idx + 3).replace(/\s*\([^)]*km\)\s*$/i, "");
      } else {
        last.ip = s;
      }
      connEl.textContent = t("lbl_you") + ": " + last.ip + (last.isp ? " \u00b7 " + last.isp : "");
    }
    var st = d.testState;
    if (st === 0) {
      setUnit(t("unit_mbps"));
      valEl.textContent = "0";
      setGauge(0);
      setStatus(t("st_connect"));
    } else if (st === 1) {
      setUnit(t("unit_mbps"));
      valEl.textContent = fmt(d.dlStatus);
      setGauge(d.dlStatus);
      cards.dl.textContent = fmt(d.dlStatus);
      setStatus(t("st_dl") + " \u2014 " + Math.round((d.dlProgress || 0) * 100) + "%");
    } else if (st === 2) {
      setUnit(t("unit_ms"));
      valEl.textContent = fmt(d.pingStatus);
      setGauge(d.pingStatus);
      cards.ping.textContent = fmt(d.pingStatus);
      cards.jitter.textContent = fmt(d.jitterStatus);
      setStatus(t("st_ping"));
    } else if (st === 3) {
      setUnit(t("unit_mbps"));
      valEl.textContent = fmt(d.ulStatus);
      setGauge(d.ulStatus);
      cards.ul.textContent = fmt(d.ulStatus);
      setStatus(t("st_ul") + " \u2014 " + Math.round((d.ulProgress || 0) * 100) + "%");
    }
    if (typeof d.dlStatus === "number" && isFinite(d.dlStatus) && d.dlStatus > 0) last.dl = d.dlStatus;
    if (typeof d.ulStatus === "number" && isFinite(d.ulStatus) && d.ulStatus > 0) last.ul = d.ulStatus;
    if (typeof d.pingStatus === "number" && isFinite(d.pingStatus) && d.pingStatus > 0) last.ping = d.pingStatus;
    if (typeof d.jitterStatus === "number" && isFinite(d.jitterStatus) && d.jitterStatus > 0) last.jitter = d.jitterStatus;
  }

  function onend(aborted) {
    running = false;
    test = null;
    showRun(false);
    startLabel.textContent = t("start");
    btn.classList.remove("stop");
    setUnit(t("unit_mbps"));
    valEl.textContent = fmt(last.dl);
    setGauge(last.dl);
    if (aborted) {
      setStatus(t("st_abort"));
      return;
    }
    setStatus(t("st_done"));
    var v = srvView(selected);
    var res = {
      dl: last.dl, ul: last.ul, ping: last.ping, jitter: last.jitter,
      ip: v.proxy ? "" : last.ip, isp: v.proxy ? "" : last.isp,
      server: {
        name: v.name, city: v.city, country: v.country, provider: v.provider, url: v.url,
        type: v.type, speed: v.speed, proxy: !!v.proxy, server: SERVERS[selected].server
      },
      time: Date.now(),
      lang: OS.lang()
    };
    try {
      sessionStorage.setItem("os_result", JSON.stringify(res));
      localStorage.setItem("os_last", JSON.stringify(res));
    } catch (e) { /* private mode etc. */ }
    setTimeout(function () { location.href = "results.php"; }, 900);
  }

  function failStart() {
    running = false;
    test = null;
    started = false;
    showRun(false);
    setStatus(t("st_error"));
  }

  function startTest() {
    if (selected < 0) { selected = 0; renderBar(); }
    var s = new Speedtest();
    var q = new URLSearchParams(location.search);
    if (q.get("dl")) s.setParameter("time_dl_max", parseFloat(q.get("dl")));
    if (q.get("ul")) s.setParameter("time_ul_max", parseFloat(q.get("ul")));
    s.setParameter("telemetry_level", false);
    s.onupdate = onupdate;
    s.onend = onend;
    resetCards();
    last = { dl: 0, ul: 0, ping: 0, jitter: 0, ip: last.ip, isp: last.isp };
    s.addTestPoints([tp(SERVERS[selected])]);
    running = true;
    started = false;
    showRun(true);
    valEl.textContent = "\u2026";
    setUnit(t("unit_mbps"));
    setStatus(t("st_connect"));
    /* if the pick turns out unreachable, selectServer answers null or never
       answers at all - show an error instead of hanging forever */
    var wd = setTimeout(function () {
      if (running && !started) failStart();
    }, 30000);
    try {
      s.selectServer(function (picked) {
        clearTimeout(wd);
        if (!picked) { failStart(); return; }
        started = true;
        s.start();
      });
    } catch (e) { clearTimeout(wd); failStart(); }
    test = s;
  }

  btn.addEventListener("click", function () {
    if (running && test) { test.abort(); return; }
    if (!running) startTest();
  });

  /* ---------- init ---------- */

  buildGauge();
  setGauge(0);

  OS.loadMeta().then(renderBar).catch(function () {});

  probeAll().then(function (best) {
    selected = best;
    renderBar();
    setStatus(t("st_ready"));
    document.body.classList.add("probed");
  });
})();
