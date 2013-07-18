<?php

/**
 * @file template.php
 */

  
 function social_preprocess_node(&$variables) {
  $node = $variables['node'];
   // Clean up name so there are no underscores.
  if ($variables['view_mode']) {
    // Add theme suggestion for view mode, replacing hyphens with underscores.
    $variables['theme_hook_suggestions'][] = 'node__' . $variables['view_mode'];
  }
   $variables['theme_hook_suggestions'][] = 'node__' . $node->type;
  if ($variables['view_mode']) {
    // Add theme suggestion for view mode, replacing hyphens with underscores.
    $variables['theme_hook_suggestions'][] = 'node__' . $node->type . '__' . $variables['view_mode'];
  }
  $variables['theme_hook_suggestions'][] = 'node__' . $node->nid;
}
