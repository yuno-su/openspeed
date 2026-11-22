/* OpenSpeed shared helpers: i18n, server meta, chips, language switcher */
window.OS = (function () {
  "use strict";

  var data = { lang: "en", langs: {}, s: {} };
  try {
    var el = document.getElementById("i18n-data");
    if (el) data = JSON.parse(el.textContent);
  } catch (e) { /* keep defaults */ }

  function t(k) {
    return (data.s && data.s[k] !== undefined) ? data.s[k] : k;
  }

  function esc(x) {
    var d = document.createElement("div");
    d.textContent = (x === null || x === undefined) ? "" : String(x);
    return d.innerHTML;
  }

  /* ip-api.com enrichment from info.php, keyed by the exact server URL */
  var _META = null, _p = null;
  function loadMeta() {
    if (_p) return _p;
    _p = fetch("info.php?mode=servers", { credentials: "same-origin" })
      .then(function (r) { return r.ok ? r.json() : {}; })
      .then(function (j) { _META = (j && j.servers) ? j.servers : {}; return _META; })
      .catch(function () { _META = {}; return _META; });
    return _p;
  }
  function meta(s) {
    return (_META && _META[s && s.server]) || {};
  }

  /* "61" -> "61 Mbps" (localized unit); strings with units pass as is */
  function speedLabel(s) {
    if (s === undefined || s === null) return "";
    s = String(s).trim();
    if (!s) return "";
    if (/^[0-9]+(?:\.[0-9]+)?$/.test(s)) return s + " " + t("unit_mbit");
    return s;
  }

  /* chips row for a resolved server view {type, speed} */
  function chips(v) {
    var out = [];
    var ty = v.type || "";
    if (ty === "dc") out.push('<span class="chip c-dc">' + esc(t("chip_dc")) + "</span>");
    else if (ty === "res") out.push('<span class="chip c-res">' + esc(t("chip_res")) + "</span>");
    else if (ty === "mobile") out.push('<span class="chip c-mob">' + esc(t("chip_mobile")) + "</span>");
    if (v.speed) out.push('<span class="chip c-speed">' + esc(speedLabel(v.speed)) + "</span>");
    if (v.proxy) out.push('<span class="chip c-px">' + esc(t("srv_proxy")) + "</span>");
    return out.join("");
  }

  /* localized units on elements marked data-unit="mbps" / "ms" */
  function applyUnits() {
    var mb = t("unit_mbps"), ms = t("unit_ms"), els, i;
    els = document.querySelectorAll('[data-unit="mbps"]');
    for (i = 0; i < els.length; i++) els[i].textContent = mb;
    els = document.querySelectorAll('[data-unit="ms"]');
    for (i = 0; i < els.length; i++) els[i].textContent = ms;
  }

  document.addEventListener("DOMContentLoaded", function () {
    var sel = document.getElementById("langSel");
    if (!sel) return;
    sel.addEventListener("change", function () {
      var v = sel.value;
      try { document.cookie = "os_lang=" + v + ";path=/;max-age=31536000"; } catch (e) {}
      location.href = location.pathname + "?lang=" + encodeURIComponent(v);
    });
  });

  return {
    t: t,
    esc: esc,
    lang: function () { return data.lang; },
    loadMeta: loadMeta,
    meta: meta,
    chips: chips,
    speedLabel: speedLabel,
    applyUnits: applyUnits
  };
})();
