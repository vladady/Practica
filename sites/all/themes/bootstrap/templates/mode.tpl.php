<article id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?> clearfix"<?php print $attributes; ?>>


  <header>
    <?php print render($title_prefix); ?>
    <?php if (!$page && $title): ?>
      <h2<?php print $title_attributes; ?>><a href="<?php print $node_url; ?>"><?php print $title; ?></a></h2>
    <?php endif; ?>
    <?php print render($title_suffix); ?>

    <?php if ($display_submitted): ?>
      <span class="submitted">
        <?php print $user_picture; ?>
        <?php print $submitted; ?>
      </span>
    <?php endif; ?>
  </header>

  <?php
<<<<<<< HEAD:sites/all/themes/bootstrap/templates/mode.tpl.php
echo "hello";
echo "Text adaugat Bozga Razvan";
   echo "Text adaugat Georgi ";
=======
     echo "test adaugat de dany ban"
echo "Text adaugat Bozga Razvan";

>>>>>>> 7221620cabdc0f4226ddb57a7b9b30e63022ad2b:sites/all/themes/bootstrap/templates/node.tpl.php
	echo "Text adaugat de Andra";
  echo "Text adaugat de Deny";

    // Hide comments, tags, and links now so that we can render them later.
    hide($content['comments']);
    hide($content['links']);
    hide($content['field_tags']);
    print render($content);
  ?>

  <?php if (!empty($content['field_tags']) || !empty($content['links'])): ?>
    <footer>
      <?php print render($content['field_tags']); ?>
      <?php print render($content['links']); ?>
    </footer>
  <?php endif; ?>

  <?php print render($content['comments']); ?>

</article> <!-- /.node -->

