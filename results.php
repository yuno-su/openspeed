<?php
// OpenSpeed - detailed test report
require __DIR__ . '/languages.php';

$PAGE = array('id' => 'report', 'title' => L('res_title') . ' — OpenSpeed');
require __DIR__ . '/inc/header.php';
?>

<section class="page-head">
  <h1><?php E('res_title'); ?></h1>
  <p class="sub"><?php E('res_sub'); ?></p>
</section>

<div id="resNone" class="none hidden">
  <h2><?php E('res_none_t'); ?></h2>
  <p><?php E('res_none_p'); ?></p>
  <a class="btn" href="index.php"><?php E('res_none_btn'); ?></a>
</div>

<div id="resBody" class="hidden">
  <section class="res-hero">
    <div class="card bigcard"><div class="label"><?php E('lbl_down'); ?></div><div class="bigval grad" id="qDl">&mdash;</div><div class="u2" data-unit="mbps">Mbps</div></div>
    <div class="card bigcard"><div class="label"><?php E('lbl_up'); ?></div><div class="bigval grad" id="qUl">&mdash;</div><div class="u2" data-unit="mbps">Mbps</div></div>
  </section>

  <section class="cards">
    <div class="card"><div class="label"><?php E('lbl_ping'); ?></div><div class="value" id="qPing">&mdash;</div><div class="u2" data-unit="ms">ms</div></div>
    <div class="card"><div class="label"><?php E('lbl_jitter'); ?></div><div class="value" id="qJitter">&mdash;</div><div class="u2" data-unit="ms">ms</div></div>
  </section>

  <div id="verdict" class="verdict"></div>

  <section class="res-grid">
    <div class="card info">
      <h2><?php E('res_client_t'); ?></h2>
      <div class="kv"><span class="k"><?php E('lbl_ip'); ?></span><span class="v" id="qIp">&mdash;</span></div>
      <div class="kv"><span class="k"><?php E('lbl_isp'); ?></span><span class="v" id="qIsp">&mdash;</span></div>
      <div class="kv"><span class="k"><?php E('lbl_as'); ?></span><span class="v" id="qAs">&mdash;</span></div>
      <div class="kv"><span class="k"><?php E('lbl_loc'); ?></span><span class="v" id="qLoc">&mdash;</span></div>
      <div class="kv"><span class="k"><?php E('lbl_conn_type'); ?></span><span class="v" id="qType">&mdash;</span></div>
    </div>
    <div class="card info">
      <h2><?php E('res_server_t'); ?></h2>
      <div class="kv"><span class="k"><?php E('lbl_server'); ?></span><span class="v" id="qSrvLoc">&mdash;</span></div>
      <div class="kv"><span class="k"><?php E('lbl_isp'); ?></span><span class="v" id="qSrvProvider">&mdash;</span></div>
      <div class="chips" id="qSrvChips"></div>
    </div>
  </section>

  <div class="res-actions">
    <div id="resTime" class="time"></div>
    <button id="copyBtn" class="btn-ghost" type="button"><?php E('res_copy'); ?></button>
    <a class="btn" href="index.php"><?php E('res_again'); ?></a>
  </div>
</div>

<script src="assets/results.js"></script>
<?php require __DIR__ . '/inc/footer.php'; ?>
