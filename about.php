<?php
// OpenSpeed - about page
require __DIR__ . '/languages.php';

$PAGE = array('id' => 'about', 'title' => L('about_title') . ' — OpenSpeed');
require __DIR__ . '/inc/header.php';
?>

<section class="page-head">
  <h1><?php E('about_title'); ?></h1>
  <p class="sub"><?php E('about_lead'); ?></p>
</section>

<div class="prose">
  <h2><?php E('what_t'); ?></h2>
  <p><?php E('what_p'); ?></p>

  <h2><?php E('how_t'); ?></h2>
  <p><?php E('how_p1'); ?></p>
  <p><?php E('how_p2'); ?></p>

  <h2><?php E('priv_t'); ?></h2>
  <p><?php E('priv_p'); ?></p>

  <h2><?php E('cred_t'); ?></h2>
  <p><?php E('cred_p1'); ?></p>
  <p><?php E('cred_p2'); ?></p>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
