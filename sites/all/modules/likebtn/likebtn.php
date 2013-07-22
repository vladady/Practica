<?php
/**
 * @file
 * LikeBtn like button.
 */

define('LIKEBTN_LAST_SUCCESSFULL_SYNC_TIME_OFFSET', 57600);
define('LIKEBTN_LOCALES_SYNC_INTERVAL', 57600);
define('LIKEBTN_API_URL', 'http://www.likebtn.com/api/');

class LikeBtn {

  protected static $synchronized = FALSE;

  /**
   * Constructor.
   */
  public function __construct() {
    // Do nothing.
  }

  /**
   * Running votes synchronization.
   */
  public function runSyncVotes() {
    if (!self::$synchronized && variable_get('likebtn_account_data_email') && variable_get('likebtn_account_data_api_key') && $this->timeToSyncVotes(variable_get('likebtn_sync_inerval', 60) * 60) && function_exists('curl_init')) {
      $this->syncVotes(variable_get('likebtn_account_data_email'), variable_get('likebtn_account_data_api_key'));
    }
  }

  /**
   * Check if it is time to sync votes.
   */
  public function timeToSyncVotes($sync_period) {

    $last_sync_time = variable_get('likebtn_last_sync_time', 0);

    $now = time();
    if (!$last_sync_time) {
      variable_set('likebtn_last_sync_time', $now);
      self::$synchronized = TRUE;
      return TRUE;
    }
    else {
      if ($last_sync_time + $sync_period > $now) {
        return FALSE;
      }
      else {
        variable_set('likebtn_last_sync_time', $now);
        self::$synchronized = TRUE;
        return TRUE;
      }
    }
  }

  /**
   * Retrieve data.
   */
  public function curl($url) {

    $path = drupal_get_path('module', 'likebtn') . '/likebtn.info';
    $info = drupal_parse_info_file($path);
    $drupal_version = VERSION;
    $likebtn_version = $info["core"];
    $php_version = phpversion();
    $useragent = "Drupal $drupal_version; likebtn module $likebtn_version; PHP $php_version";

    try {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_TIMEOUT, 60);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $result = curl_exec($ch);
      curl_close($ch);
    }
    catch(Exception $e) {

    }

    return $result;
  }

  /**
   * Comment sync function.
   */
  public function syncVotes($account_api_key, $site_api_key) {
    $sync_result = TRUE;

    $last_sync_time = number_format(variable_get('likebtn_last_sync_time', 0), 0, '', '');

    $email      = trim(variable_get('likebtn_account_data_email'));
    $api_key    = trim(variable_get('likebtn_account_data_api_key'));
    $parse_url  = parse_url(url(NULL, array('absolute' => TRUE)));
    $domain    = $parse_url['host'];

    $updated_after = '';

    if (variable_get('likebtn_last_successfull_sync_time', 0)) {
      $updated_after = variable_get('likebtn_last_successfull_sync_time') - LIKEBTN_LAST_SUCCESSFULL_SYNC_TIME_OFFSET;
    }

    $url = LIKEBTN_API_URL . "?action=stat&email={$email}&api_key={$api_key}&domain={$domain}&output=json&last_sync_time=" . $last_sync_time;
    if ($updated_after) {
      $url .= '&updated_after=' . $updated_after;
    }

    // Retrieve first page.
    $response_string = $this->curl($url);
    $response = $this->jsonDecode($response_string);
    if (!$this->updateVotes($response)) {
      $sync_result = FALSE;
    }

    // Retrieve all pages.
    if (isset($response['response']['total']) && isset($response['response']['page_size'])) {
      $total_pages = ceil((int) $response['response']['total'] / (int) $response['response']['page_size']);

      for ($page = 2; $page <= $total_pages; $page++) {
        $response_string = $this->curl($url . '&page=' . $page);
        $response = $this->jsonDecode($response_string);

        if (!$this->updateVotes($response)) {
          $sync_result = FALSE;
        }
      }
    }

    if ($sync_result) {
      variable_set('likebtn_last_successfull_sync_time', $last_sync_time);
    }
  }

  /**
   * Decode JSON.
   */
  public function jsonDecode($jsong_string) {
    return json_decode($jsong_string, TRUE);
  }

  /**
   * Update votes in database from API response.
   */
  public function updateVotes($response) {
    $votes = array();

    if (!empty($response['response']['items'])) {
      foreach ($response['response']['items'] as $item) {
        $identifier_parts = explode('_', $item['identifier']);
        $entity_type = '';
        if (!empty($identifier_parts[0])) {
          $entity_type = $identifier_parts[0];
        }
        $entity_id = '';
        if (!empty($identifier_parts[1])) {
          $entity_id = $identifier_parts[1];
          if (!is_numeric($entity_id)) {
            continue;
          }
        }
        $field_id = '';
        if (!empty($identifier_parts[3])) {
          $field_id = $identifier_parts[3];
        }
        $field_index = 0;
        if (!empty($identifier_parts[5])) {
          $field_index = $identifier_parts[5];
        }

        $tag = 'vote';
        if ($field_id) {
          $tag = 'field_' . $field_id . '_index_' . $field_index;
        }
        $likes = 0;
        if (!empty($item['likes'])) {
          $likes = $item['likes'];
        }
        $dislikes = 0;
        if (!empty($item['dislikes'])) {
          $dislikes = $item['dislikes'];
        }

        // If vote with the same tag exists - continue.
        foreach ($votes as $vote) {
          if ($vote['entity_type'] == $entity_type && $vote['entity_id'] == $entity_id && $vote['tag'] == $tag) {
            continue 2;
          }
        }

        // Get entity info.
        try {
          $entity_type_info = entity_get_info($entity_type);
          if (empty($entity_type_info['controller class'])) {
            continue;
          }
        }
        catch(Exception $e) {
          continue;
        }

        // Likes and Disliked stored in Voting API.
        $votes[] = array(
          'entity_type' => $entity_type,
          'entity_id'   => $entity_id,
          'value_type'  => 'points',
          'value'       => $likes,
          'tag'         => $tag,
          'uid'         => 0,
          'vote_source' => '',
        );
        $votes[] = array(
          'entity_type' => $entity_type,
          'entity_id'   => $entity_id,
          'value_type'  => 'points',
          'value'       => $dislikes * (-1),
          'tag'         => $tag,
          'uid'         => 0,
          'vote_source' => '',
        );

        // Remove (backup) votes cast on this entity by other modules.
        $remove_old_votes_fields = array(
          'entity_type' => $entity_type . '_backup',
        );
        try {
          db_update('votingapi_vote')
            ->fields($remove_old_votes_fields)
            ->condition('entity_type', $entity_type)
            ->condition('entity_type', '%_backup', 'NOT LIKE')
            ->condition('vote_source', '', '!=')
            ->execute();
        }
        catch (Exception $e) {

        }

        // Update LikeBtn fields.
        if ($tag) {
          $entities = entity_load($entity_type, array($entity_id));
          if (empty($entities[$entity_id])) {
            continue;
          }
          $entity   = $entities[$entity_id];
          list($tmp_entity_id, $entity_revision_id, $bundle) = entity_extract_ids($entity_type, $entity);

          // Get entity LikeBtn fields.
          $entity_fields = field_info_instances($entity_type, $bundle);

          // Set field value.
          $likes_minus_dislikes = $likes - $dislikes;

          foreach ($entity_fields as $field_name => $field_info) {
            if ($field_info['widget']['module'] != 'likebtn') {
              continue;
            }

            $field_fields_data = array(
              'entity_type'         => $entity_type,
              'bundle'              => $bundle,
              'entity_id'           => $entity_id,
              'revision_id'         => $entity_id,
              'delta'               => $field_index,
              'language'            => $entity->language,
            );
            $field_fields_data[$field_name . '_likebtn_likes']        = $likes;
            $field_fields_data[$field_name . '_likebtn_dislikes']     = $dislikes;
            $field_fields_data[$field_name . '_likebtn_likes_minus_dislikes'] = $likes_minus_dislikes;

            try {
              // Insert value.
              db_insert('field_data_' . $field_name)
                ->fields($field_fields_data)
                ->execute();
            }
            catch(Exception $e) {
              // Update value.
              try {
                db_update('field_data_' . $field_name)
                  ->fields($field_fields_data)
                  ->condition('entity_type', $entity_type)
                  ->condition('bundle', $bundle)
                  ->condition('entity_id', $entity_id)
                  ->execute();
              }
              catch(Exception $e) {

              }
            }
          }
        }
      }

      if ($votes) {
        // Votes must be saved altogether.
        votingapi_set_votes($votes, NULL);
        return TRUE;
      }
      return FALSE;
    }
  }

  /**
   * Run locales synchronization.
   */
  public function runSyncLocales() {
    if ($this->timeToSyncLocales(LIKEBTN_LOCALES_SYNC_INTERVAL) && function_exists('curl_init')) {
      $this->syncLocales();
    }
  }

  /**
   * Check if it is time to sync locales.
   */
  public function timeToSyncLocales($sync_period) {

    $last_sync_time = variable_get('likebtn_last_locale_sync_time', 0);

    $now = time();
    if (!$last_sync_time) {
      variable_set('likebtn_last_locale_sync_time', $now);
      return TRUE;
    }
    else {
      if ($last_sync_time + $sync_period > $now) {
        return FALSE;
      }
      else {
        variable_set('likebtn_last_locale_sync_time', $now);
        return TRUE;
      }
    }
  }

  /**
   * Locales sync function.
   */
  public function syncLocales() {

    $last_sync_time = number_format(variable_get('likebtn_last_locale_sync_time', 0), 0, '', '');

    $parse_url  = parse_url(url(NULL, array('absolute' => TRUE)));
    $domain     = $parse_url['host'];

    $url = LIKEBTN_API_URL . "?action=locale";

    $response_string = $this->curl($url);
    $response = $this->jsonDecode($response_string);

    if (isset($response['result']) && $response['result'] == 'success' && isset($response['response']) && count($response['response'])) {
      variable_set('likebtn_locales', $response['response']);
    }
  }

}
