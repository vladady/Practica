<?php
$p = profile2_load_by_user($node->uid);
$p = reset($p);
$d = date('d/m/Y H:i:s', $node->created);

$profile = profile2_view($p, 'teaser');
$profile = $profile['profile2'][$p->pid];
?>
<div class="modal" style="position:relative;margin-top: 50px;z-index:0;">
  <div class="modal-header">
    <?php print render($profile['field_poza']); ?>
    <span class="user-name">
    <h3>
      <?php 
        echo $p->field_prenume['und'][0]['value'] , ' ' 
              , $p->field_nume['und'][0]['value']; 
      ?>
    </h3>
    </span>
      <span class="date_create"><?php echo 'Posted on ' . $d; ?>
      </span>
  </div>
    <div class="modal-body">
    <p>
      <?php print render($content['body']); ?>
      <?php print render($content['field_poza']); ?>
    </p>
  </div>
  <div class="modal-footer">
    <span class="pull-left"><?php print render($content['links']); ?></span>
    <a href="#" class="btn btn-primary">Like</a>
  </div>
</div>
