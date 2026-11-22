<?php
// OpenSpeed - "add your server" page
require __DIR__ . '/languages.php';

$PAGE = array('id' => 'add', 'title' => L('add_title') . ' — OpenSpeed');
require __DIR__ . '/inc/header.php';
?>

<section class="page-head">
  <h1><?php E('add_title'); ?></h1>
  <p class="sub"><?php E('add_lead'); ?></p>
</section>

<div class="prose">
  <h2><?php E('req_t'); ?></h2>
  <ul>
    <li><?php E('req1'); ?></li>
    <li><?php E('req2'); ?></li>
    <li><?php E('req3'); ?></li>
    <li><?php E('req4'); ?></li>
    <li><?php E('req5'); ?></li>
  </ul>

  <h2><?php E('steps_t'); ?></h2>
  <div class="steps">
    <div class="card step">
      <div class="num">1</div>
      <h3><?php E('step1_t'); ?></h3>
      <p><?php E('step1_p'); ?></p>
    </div>
    <div class="card step">
      <div class="num">2</div>
      <h3><?php E('step2_t'); ?></h3>
      <p><?php E('step2_p'); ?></p>
    </div>
    <div class="card step">
      <div class="num">3</div>
      <h3><?php E('step3_t'); ?></h3>
      <p><?php E('step3_p'); ?></p>
    </div>
  </div>

  <h2><?php E('fields_t'); ?></h2>
  <p><?php E('fields_note'); ?></p>

  <table class="ftable">
    <tr><th>field</th><th>meaning</th></tr>
    <tr><td><code>name</code></td><td><?php E('fd_name'); ?></td></tr>
    <tr><td><code>city</code></td><td><?php E('fd_city'); ?></td></tr>
    <tr><td><code>country</code></td><td><?php E('fd_country'); ?></td></tr>
    <tr><td><code>provider</code></td><td><?php E('fd_provider'); ?></td></tr>
    <tr><td><code>url</code></td><td><?php E('fd_url'); ?></td></tr>
    <tr><td><code>server</code></td><td><?php E('fd_server'); ?></td></tr>
    <tr><td><code>speed</code></td><td><?php E('fd_speed'); ?></td></tr>
    <tr><td><code>type</code></td><td><?php E('fd_type'); ?></td></tr>
  </table>

  <p class="code-cap"><?php E('sample_t'); ?></p>
<pre class="code">[
    'name'     =&gt; 'Frankfurt, Germany — MyHost',
    'city'     =&gt; 'Frankfurt',
    'country'  =&gt; 'Germany',
    'provider' =&gt; 'MyHost',
    'url'      =&gt; 'https://myhost.com/',
    'server'   =&gt; 'https://speed.example.com/',
    'dlURL'    =&gt; 'garbage.php',
    'ulURL'    =&gt; 'empty.php',
    'pingURL'  =&gt; 'empty.php',
    'getIpURL' =&gt; 'getIP.php',
    'speed'    =&gt; '1 Gbps',
    'type'     =&gt; '',
],</pre>

  <h2><?php E('tip_t'); ?></h2>
  <p><?php E('tip_p'); ?></p>
  <p><?php E('tip2_p'); ?></p>

  <a class="btn repo-btn" href="https://github.com/yuno-su/openspeed" target="_blank" rel="noopener"><?php E('repo_btn'); ?></a>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
