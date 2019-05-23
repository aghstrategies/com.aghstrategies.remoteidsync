<?php

require_once 'remoteidsync.civix.php';
use CRM_Remoteidsync_ExtensionUtil as E;
use GuzzleHttp\Client;

/**
 * Rest API Call using guzzle
 *
 * @param string $url
 * @param float $timeout
 *
 * @return bool
 */
function apiCall($url, $timeout = 0.50) {
  $fileExists = FALSE;
  try {
    $guzzleClient = new GuzzleHttp\Client();
    $guzzleResponse = $guzzleClient->request('POST', $url, array(
      'timeout' => $timeout,
    ));
    $fileExists = ($guzzleResponse->getStatusCode() == 200);
  }
  catch (Exception $e) {
    // At this stage we are not checking for variants of not being able to receive it.
    // However, we might later enhance this to distinguish forbidden from a 500 error.
  }
  return $fileExists;
}

function remoteidsync_civicrm_summary($contactID, &$content, &$contentPlacement) {
  $remoteID = NULL;
  $contentPlacement = CRM_Utils_Hook::SUMMARY_ABOVE;
  $settings = CRM_Remoteidsync_Form_Settings::getSettings([]);
  if (!empty($settings['remoteidsync_customfield']) && !empty($settings['remoteidsync_baseurl'])) {
    $customField = 'custom_' . $settings['remoteidsync_customfield'];
    try {
      $remoteIDCall = civicrm_api3('Contact', 'getsingle', array(
        'id' => $contactID,
        'return' => $customField,
        'sequential' => 1,
      ));
    }
    catch (CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
      CRM_Core_Error::debug_log_message(ts('API Error %1', array(
        'domain' => 'com.aghstrategies.remoteidsync',
        1 => $error,
      )));
    }
    if (!empty($remoteIDCall[$customField])) {
      $remoteID = $remoteIDCall[$customField];
    }
    // TODO abstract out url
    $content = "<div>
      <div class='crm-label'>
        Remote ID: <a href='{$settings['remoteidsync_baseurl']}{$remoteID}'>$remoteID</a>
      </div>
    </div>";
  }
}

function remoteidsync_civicrm_custom($op, $groupID, $entityID, $params) {
  if ($op == 'create' || $op == 'edit') {
    $settings = CRM_Remoteidsync_Form_Settings::getSettings([]);
    if (!empty($settings['remoteidsync_customfield'])) {
      foreach ($params as $key => $values) {
        if (!empty($values['value']) && $values['custom_field_id'] == $settings['remoteidsync_customfield']) {
          $contactIdInOtherDB = $values['value'];
          $contactIdInThisDB = $entityID;
          $apiCall = "{$settings['remoteidsync_apiendpoint']}?entity=Contact&action=create&api_key={$settings['remoteidsync_apikey']}&key={$settings['remoteidsync_sitekey']}&json=1&id={$contactIdInOtherDB}&custom_{$settings['remoteidsync_customfield']}={$contactIdInThisDB}";
          $result = apiCall($apiCall);
          if ($result) {
            CRM_Core_Session::setStatus(ts('Remote ID synced'), ts('Remote ID'), 'success');
          }
        }
      }
    }
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function remoteidsync_civicrm_config(&$config) {
  _remoteidsync_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function remoteidsync_civicrm_xmlMenu(&$files) {
  _remoteidsync_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function remoteidsync_civicrm_install() {
  $groupID = NULL;
  // Check if custom field group exsists
  try {
    $groupCheck = civicrm_api3('CustomGroup', 'getsingle', [
      'name' => 'Remote_ID',
    ]);
  }
  catch (CiviCRM_API3_Exception $e) {
    $error = $e->getMessage();
    CRM_Core_Error::debug_log_message(ts('API Error %1', array(
      'domain' => 'com.aghstrategies.remoteidsync',
      1 => $error,
    )));
  }
  // If found save
  if (!empty($groupCheck['id'])) {
    $groupID = $groupCheck['id'];
  }
  // If no existing custom field group create one
  else {
    try {
      $group = civicrm_api3('CustomGroup', 'create', [
        'title' => "Remote ID",
        'name' => 'Remote_ID',
        'extends' => "Contact",
      ]);
    }
    catch (CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
      CRM_Core_Error::debug_log_message(ts('API Error %1', array(
        'domain' => 'com.aghstrategies.remoteidsync',
        1 => $error,
      )));
    }
    if (!empty($group['id'])) {
      $groupID = $group['id'];
    }
  }
  // Now we should have a group id lets check for a field
  if ($groupID) {
    try {
      $fieldCheck = civicrm_api3('CustomField', 'getsingle', [
        'custom_group_id' => $groupID,
        // 'label' => "Remote ID",
        'name' => "Remote_Id",
        // "data_type" => "Int",
        // "html_type" => "Text",
        // "is_active" => "1",
        // "is_view" => "0",
        // "text_length" => "255",
      ]);
    }
    catch (CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
      CRM_Core_Error::debug_log_message(ts('API Error %1', array(
        'domain' => 'com.aghstrategies.remoteidsync',
        1 => $error,
      )));
    }
    if (empty($fieldCheck['id'])) {
      try {
        $fieldCheck = civicrm_api3('CustomField', 'create', [
          'custom_group_id' => $groupID,
          'label' => "Remote ID",
          'name' => "Remote_Id",
          "data_type" => "Int",
          "html_type" => "Text",
          "is_active" => "1",
          "is_view" => "0",
          "text_length" => "255",
        ]);
      }
      catch (CiviCRM_API3_Exception $e) {
        $error = $e->getMessage();
        CRM_Core_Error::debug_log_message(ts('API Error %1', array(
          'domain' => 'com.aghstrategies.remoteidsync',
          1 => $error,
        )));
      }
    }
  }

  _remoteidsync_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function remoteidsync_civicrm_postInstall() {
  _remoteidsync_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function remoteidsync_civicrm_uninstall() {
  _remoteidsync_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function remoteidsync_civicrm_enable() {
  _remoteidsync_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function remoteidsync_civicrm_disable() {
  _remoteidsync_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function remoteidsync_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _remoteidsync_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function remoteidsync_civicrm_managed(&$entities) {
  _remoteidsync_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function remoteidsync_civicrm_caseTypes(&$caseTypes) {
  _remoteidsync_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function remoteidsync_civicrm_angularModules(&$angularModules) {
  _remoteidsync_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function remoteidsync_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _remoteidsync_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function remoteidsync_civicrm_entityTypes(&$entityTypes) {
  _remoteidsync_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function remoteidsync_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function remoteidsync_civicrm_navigationMenu(&$menu) {
  _remoteidsync_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _remoteidsync_civix_navigationMenu($menu);
} // */
