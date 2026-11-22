<?php
// OpenSpeed - main test page
require __DIR__ . '/languages.php';

$servers = require __DIR__ . '/servers.php';
// dead points stay in servers.php but never reach the page
$servers = array_values(array_filter($servers, function ($s) { return !isset($s['enabled']) || $s['enabled']; }));
$serversJson = str_replace('</', '<\\/', json_encode($servers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

$PAGE = array('id' => 'test', 'title' => 'OpenSpeed — ' . L('hero_h1'));
require __DIR__ . '/inc/header.php';
?>

<section class="hero">
  <h1><?php E('hero_h1'); ?></h1>
  <p class="sub"><?php E('hero_sub'); ?></p>
</section>

<section class="gauge-zone">
  <div class="gauge-wrap">
    <svg id="gauge" viewBox="0 0 360 322" aria-hidden="true">
      <defs>
        <linearGradient id="gaugeGrad" x1="0" y1="1" x2="1" y2="0">
          <stop stop-color="#e9ebed"/><stop offset="1" stop-color="#878c92"/>
        </linearGradient>
      </defs>
      <path id="gaugeTrack" class="gtrack" d=""/>
      <path id="gaugeProg" class="gprog" d=""/>
      <g id="gaugeTicks"></g>
      <circle id="gaugeTip" class="gtip" r="6" cx="0" cy="0"/>
    </svg>
    <div class="gauge-center">
      <div id="runVal" class="runval hidden">
        <div id="gaugeValue">0</div>
        <div id="gaugeUnit" class="unit">Mbps</div>
      </div>
      <button id="startBtn" class="go" type="button"><span id="startLabel"><?php E('start'); ?></span></button>
    </div>
  </div>
  <div id="status" class="status"><?php E('st_ready'); ?></div>
  <noscript><p class="status">JavaScript is required for the test.</p></noscript>
</section>

<section class="cards">
  <div class="card"><div class="label"><?php E('card_ping'); ?></div><div class="value" id="rPing">&mdash;</div><div class="u2" data-unit="ms">ms</div></div>
  <div class="card"><div class="label"><?php E('card_jitter'); ?></div><div class="value" id="rJitter">&mdash;</div><div class="u2" data-unit="ms">ms</div></div>
  <div class="card"><div class="label"><?php E('card_dl'); ?></div><div class="value" id="rDl">&mdash;</div><div class="u2" data-unit="mbps">Mbps</div></div>
  <div class="card"><div class="label"><?php E('card_ul'); ?></div><div class="value" id="rUl">&mdash;</div><div class="u2" data-unit="mbps">Mbps</div></div>
</section>

<section class="srvbar" id="srvbar">
  <div class="srv-tags" id="srvTags"></div>
  <div class="srv-main">
    <div class="caption"><?php E('srv_caption'); ?></div>
    <div class="srv-loc" id="srvLoc">&mdash;</div>
    <div id="srvProvider"></div>
  </div>
  <div class="srv-side">
    <button id="openPicker" class="btn-ghost" type="button"><?php E('srv_change'); ?></button>
  </div>
</section>
<div id="connInfo" class="conn"></div>

<div id="pickerMask" class="mask hidden">
  <div class="modal" role="dialog" aria-modal="true" aria-label="<?php E('modal_title'); ?>">
    <div class="modal-head">
      <h2><?php E('modal_title'); ?></h2>
      <button id="closePicker" class="icon-btn" type="button" aria-label="<?php E('modal_close'); ?>">&times;</button>
    </div>
    <input id="srvSearch" class="search" type="text" autocomplete="off" placeholder="<?php E('search_ph'); ?>">
    <div id="srvList" class="srv-list"></div>
  </div>
</div>

<script id="servers-data" type="application/json"><?php echo $serversJson; ?></script>
<script src="speedtest.js"></script>
<script src="assets/app.js"></script>
<?php require __DIR__ . '/inc/footer.php'; ?>
