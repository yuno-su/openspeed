/* OpenSpeed report page: fills results.php from the saved test result */
(function () {
  "use strict";

  if (!window.OS) return;
  var t = OS.t;
  OS.applyUnits();

  function r(id) { return document.getElementById(id); }

  function fmt(v) {
    if (typeof v !== "number" || !isFinite(v) || v <= 0) return "\u2014";
    return v >= 100 ? String(Math.round(v)) : String(Math.round(v * 10) / 10);
  }

  function getRes() {
    try {
      var s = sessionStorage.getItem("os_result");
      if (s) return JSON.parse(s);
    } catch (e) {}
    try {
      var l = localStorage.getItem("os_last");
      if (l) return JSON.parse(l);
    } catch (e) {}
    return null;
  }

  function verdict(dl) {
    if (dl >= 100) return t("v_top");
    if (dl >= 50) return t("v_high");
    if (dl >= 25) return t("v_mid");
    if (dl >= 10) return t("v_low");
    return t("v_min");
  }

  var res = getRes();
  if (!res) {
    r("resNone").classList.remove("hidden");
    return;
  }

  r("resBody").classList.remove("hidden");
  r("qDl").textContent = fmt(res.dl);
  r("qUl").textContent = fmt(res.ul);
  r("qPing").textContent = fmt(res.ping);
  r("qJitter").textContent = fmt(res.jitter);
  r("verdict").textContent = verdict(res.dl);

  var sv = res.server || {};
  r("qSrvLoc").textContent = (sv.city ? sv.city + ", " : "") + (sv.country || sv.name || "—");
  var pv = r("qSrvProvider");
  if (sv.provider && sv.url) {
    pv.innerHTML = '<a href="' + OS.esc(sv.url) + '" target="_blank" rel="noopener noreferrer">' +
      OS.esc(sv.provider) + '<span class="ext">&#8599;</span></a>';
  } else {
    pv.textContent = sv.provider || "";
  }
  r("qSrvChips").innerHTML = OS.chips(sv);

  if (res.time) {
    var loc = OS.lang() === "ru" ? "ru-RU" : "en-GB";
    r("resTime").textContent = t("res_time") + ": " + new Date(res.time).toLocaleString(loc);
  }

  /* live client info: fresh IP, ISP, AS, geo, connection type */
  fetch("info.php?mode=client", { credentials: "same-origin" })
    .then(function (x) { return x.ok ? x.json() : null; })
    .then(function (j) {
      if (!j) return;
      r("qIp").textContent = j.ip || res.ip || "—";
      r("qIsp").textContent = j.isp || res.isp || "—";
      r("qAs").textContent = j.as ? (j.as + (j.asname ? " (" + j.asname + ")" : "")) : "—";
      r("qLoc").textContent = [j.city, j.country].filter(Boolean).join(", ") || "—";
      var ty = j.hosting ? "dc" : (j.mobile ? "mobile" : (j.ip ? "res" : ""));
      r("qType").innerHTML = ty ? OS.chips({ type: ty }) : "—";
    })
    .catch(function () {
      r("qIp").textContent = res.ip || "—";
      r("qIsp").textContent = res.isp || "—";
    });

  /* server type may have arrived after the test started */
  OS.loadMeta().then(function () {
    var m = OS.meta({ server: sv.server });
    if (m && m.type && !sv.type) {
      sv.type = m.type;
      r("qSrvChips").innerHTML = OS.chips(sv);
    }
  }).catch(function () {});

  /* copy report */
  var cbtn = r("copyBtn");
  cbtn.addEventListener("click", function () {
    var lines = [
      "OpenSpeed — " + (location.host || "openspeed.bond"),
      t("lbl_down") + ": " + fmt(res.dl) + " " + t("unit_mbps"),
      t("lbl_up") + ": " + fmt(res.ul) + " " + t("unit_mbps"),
      t("lbl_ping") + ": " + fmt(res.ping) + " " + t("unit_ms") +
        " · " + t("lbl_jitter") + ": " + fmt(res.jitter) + " " + t("unit_ms"),
      t("lbl_server") + ": " + ((sv.city ? sv.city + ", " : "") + (sv.country || "")) +
        (sv.provider ? " (" + sv.provider + ")" : ""),
      res.ip ? t("lbl_ip") + ": " + res.ip : ""
    ].filter(Boolean);
    var txt = lines.join("\n");
    function done() {
      cbtn.textContent = t("res_copied");
      setTimeout(function () { cbtn.textContent = t("res_copy"); }, 1500);
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(txt).then(done, function () { fallback(); });
    } else {
      fallback();
    }
    /* execCommand works on http pages where the async Clipboard API is absent */
    function fallback() {
      var ta = document.createElement("textarea");
      ta.value = txt;
      ta.setAttribute("readonly", "");
      ta.style.position = "fixed";
      ta.style.opacity = "0";
      document.body.appendChild(ta);
      ta.select();
      try { document.execCommand("copy"); } catch (e) {}
      document.body.removeChild(ta);
      done();
    }
  });
})();
