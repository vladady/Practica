<?php

/**
 * @file
 * Administration forms for the LikeBtn module.
 */

/**
 * Menu callback; Displays the administration settings for LikeBtn.
 */
function likebtn_admin_settings() {
  $form = array();

  // Get all available content types.
  $types = node_type_get_types();
  $options = array();
  foreach ($types as $type) {
    $options[$type->type] = $type->name;
  }

  // Get all available entities view modes.
  $view_modes = entity_get_info('node');
  foreach ($view_modes['view modes'] as $view_mode_id => $view_mode_info) {
    $view_modes_options[$view_mode_id] = $view_mode_info['label'];
  }

  $form['likebtn_hint'] = array(
    '#type'          => 'item',
    '#description'   => t('You can set up the Like Button globally on this page, or per content type as a field in <a href="@link-manage_fields">Structure » Content types » Manage fields</a>.') . '<br/>' . t('Keep in mind that only websites upgraded to <a href="http://www.likebtn.com/en/#plans_pricing" target="_blank">PLUS</a> plan or higher are allowed to display more then 3 like buttons per page.',
      array(
        '@link-manage_fields'   => url('admin/structure/types'),
      )
    ),
  );

  $form['likebtn_nodetypes'] = array(
    '#type'          => 'checkboxes',
    '#title'         => t('Enable for the following content types'),
    '#description'   => t('Select the content types for which you want to activate like button.'),
    '#default_value' => variable_get('likebtn_nodetypes', array()),
    '#options'       => $options,
  );

  $form['likebtn_comments_nodetypes'] = array(
    '#type'          => 'checkboxes',
    '#title'         => t('Enable for comments to the following content types'),
    '#description'   => t('Select the content types for comments to which you want to activate like button.'),
    '#default_value' => variable_get('likebtn_comments_nodetypes', array()),
    '#options'       => $options,
    '#disabled'      => !module_exists('comment'),
  );

  $form['likebtn_view_modes'] = array(
    '#type'          => 'checkboxes',
    '#title'         => t('Entities view modes'),
    '#description'   => t('When will the like button be displayed?'),
    '#default_value' => variable_get('likebtn_view_modes', array()),
    '#options'       => $view_modes_options,
  );

  $form['likebtn_weight'] = array(
    '#type'          => 'select',
    '#title'         => t('Position'),
    '#description'   => t('The more the weight, the lower like button position in the entity.'),
    '#default_value' => variable_get('likebtn_weight', 10),
    '#options'       => array(
      -100 => '-100',
      -50  => '-50',
      -20  => '-20',
      -10  => '-10',
      -5   => '-5',
      5    => '5',
      10   => '10',
      20   => '20',
      50   => '50',
      100  => '100',
    ),
  );

  $form['likebtn_plan'] = array(
    '#type'          => 'select',
    '#title'         => t('Website tariff plan'),
    '#description'   => t('Specify your website <a href="http://www.likebtn.com/en/#plans_pricing">plan</a>. The plan specified determines available settings.'),
    '#default_value' => variable_get('likebtn_plan', 0),
    '#options'       => array(
      LIKEBTN_PLAN_FREE  => 'FREE',
      LIKEBTN_PLAN_PLUS  => 'PLUS',
      LIKEBTN_PLAN_PRO   => 'PRO',
      LIKEBTN_PLAN_VIP  => 'VIP',
    ),
  );

  $form['likebtn_account_data'] = array(
    '#type'        => 'fieldset',
    '#title'       => t('Auto-synching likes into local database (PRO, VIP)'),
    '#collapsible' => TRUE,
    '#collapsed'   => TRUE,
  );
  $form['likebtn_account_data']['likebtn_hint_account_data'] = array(
    '#type'          => 'item',
    '#description'   => t('Fill in these fields if you want information on likes to be periodically fetched from LikeBtn.com system into your database. It would allow to sort content in views by vote results using Voting API or LikeBtn field.') . '<br/><br/>' . t('Requirements:') . '<ul><li>' . t('Your website must be upgraded to <a href="http://www.likebtn.com/en/#plans_pricing" target="_blank">PRO</a> or higher on <a href="http://www.likebtn.com/en/#plans_pricing" target="_blank">LikeBtn.com</a>.') . '</li><li>' . t('PHP curl extension must be enabled.') . '</li></ul><br/><strong style="color:green">' . t('Attention: existing vote results of content types for which LikeBtn is enabled will be reseted and replaced with LikeBtn vote results (likes minus dislikes).') . '</strong>',
  );
  $form['likebtn_account_data']['likebtn_account_data_email'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Email'),
    '#default_value' => variable_get('likebtn_account_data_email', ''),
    '#description'   => t('Your LikeBtn.com account email (can be found on <a href="http://www.likebtn.com/en/customer.php/profile/edit" target="_blank">Profile page</a>)'),
    '#states' => array(
      // Disable field.
      'disabled' => array(
        array(':input[name="likebtn_plan"]' => array('value' => LIKEBTN_PLAN_FREE)),
        array(':input[name="likebtn_plan"]' => array('value' => LIKEBTN_PLAN_PLUS)),
      ),
    ),
  );
  $form['likebtn_account_data']['likebtn_account_data_api_key'] = array(
    '#type'          => 'textfield',
    '#title'         => t('API key'),
    '#maxlength'     => 32,
    '#default_value' => variable_get('likebtn_account_data_api_key', ''),
    '#description'   => t('Your website API key on LikeBtn.com (can be requested on <a href="http://www.likebtn.com/en/customer.php/websites" target="_blank">Websites page</a>)'),
    '#states' => array(
      // Disable field.
      'disabled' => array(
        array(':input[name="likebtn_plan"]' => array('value' => LIKEBTN_PLAN_FREE)),
        array(':input[name="likebtn_plan"]' => array('value' => LIKEBTN_PLAN_PLUS)),
      ),
    ),
  );
  $form['likebtn_account_data']['likebtn_sync_inerval'] = array(
    '#type'          => 'select',
    '#title'         => t('Synchronization interval'),
    '#description'   => t('Time interval in minutes in which fetching of likes from LikeBtn.com into your database is being launched. The less the interval the heavier your database load (60 minutes interval is recommended)'),
    '#default_value' => variable_get('likebtn_sync_inerval', 60),
    '#options'       => array(
      5 => '5',
      15 => '15',
      30 => '30',
      60 => '60',
      90 => '90',
      120 => '120',
      120 => '120',
    ),
    '#states' => array(
      // Disable field.
      'disabled' => array(
        array(':input[name="likebtn_plan"]' => array('value' => LIKEBTN_PLAN_FREE)),
        array(':input[name="likebtn_plan"]' => array('value' => LIKEBTN_PLAN_PLUS)),
      ),
    ),
  );

  $form = array_merge($form, _likebtn_settings_form());

  return system_settings_form($form);
}
