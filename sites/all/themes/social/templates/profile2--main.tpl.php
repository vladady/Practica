<?php 
//print_r($content);
//print_r(array_keys($content));
?>

<div class="<?php print $classes; ?> clearfix"<?php print $attributes; ?>>

  <?php if (!$page): ?>
    <h2<?php print $title_attributes; ?>>
      <?php if ($url): ?>
        <a href="<?php print $url; ?>"><?php echo  $title; ?></a>
      <?php else: ?>
        <?php print $title; ?>
      <?php endif; ?>
    </h2>
  <?php endif; ?>

  <div class="content container "<?php print $content_attributes; ?>>
    <div class="row">
      <div class="span4 title-profile2"><?php 
      $name = ' <h2>' . $content['field_nume'][0]['#markup'] . ' ';
      $name .= $content['field_prenume'][0]['#markup'] . '<h2>';
      echo render($name);
      ?>
      </div>
    </div>
    <div class="row">
      <div class="span3 thumbnail">
        <?php
        echo render($content['field_poza']);

         ?>  

      </div>
      <div class="span6 offset1">
      <div class="row">
      <div class="span8"></div>
      <div id='text-profile2' class="span9">
        <?php
        //   #0088CC
       $date =  '<i class="icon-gift"></i> ' ;
       $date .= '<strong>' . $content['field_data_nasterii']['#title'] . ': </strong>';
       $date .=  $content['field_data_nasterii'][0]['#markup'] ;
       echo render($date) . '<br />';

       $loc =  '<i class="icon-map-marker"></i> ' ;
       $loc .= '<strong>' . $content['field_localitate']['#title'] . ': </strong>';
       $loc .=  $content['field_localitate'][0]['#markup'] ;
       echo render($loc). '<br />';
             

       $sex =  '<i class="icon-user"></i> ' ;
       $sex .= '<strong>' . $content['field_sex']['#title'] . ': </strong>';
       $sex .=  $content['field_sex'][0]['#markup'] ;
       echo render($sex). '<br />';

       $ocupation =  '<i class="icon-briefcase"></i> ' ;
       $ocupation .= '<strong>' . $content['field_ocupatie']['#title'] . ': </strong>';
       $ocupation .=  $content['field_ocupatie'][0]['#markup'] ;
       echo render($ocupation). '<br />';
       ?>      
      </div>      
     </div> 

    </div>
  </div>
</div>