<?php

use CRM_Remoteidsync_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Remoteidsync_Form_Settings extends CRM_Core_Form {

  public function getCustomFieldForThisDB() {
    $customFieldInfo = [
      'custom_field_id' => NULL,
      'custom_group_id' => NULL,
    ];
    try {
      $customField = civicrm_api3('CustomGroup', 'getsingle', [
        'name' => "Remote_ID",
        'api.CustomField.getsingle' => ['custom_group_id' => "\$value.id", 'name' => "Remote_Id"],
      ]);
    }
    catch (CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
      CRM_Core_Error::debug_log_message(ts('API Error: %1', array(1 => $error, 'domain' => 'com.aghstrategies.remoteidsync')));
    }
    if (!empty($customField["api.CustomField.getsingle"]['id']) && !empty($customField['id'])) {
      $customFieldInfo['custom_field_id'] = $customField["api.CustomField.getsingle"]['id'];
      $customFieldInfo['custom_group_id'] = $customField['id'];
    }
    return $customFieldInfo;
  }

  public function buildQuickForm() {
    $settingFields = self::settingsFields();
    foreach ($settingFields as $name => $label) {
      $this->add(
        'text',
        $name,
        ts($label)
      );
    }
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    $customField = self::getCustomFieldForThisDB();
    if (!empty($customField['custom_field_id'])) {
      $this->assign('customField', $customField['custom_field_id']);
    }
    $defaults = self::getSettings($settingFields);
    $this->setDefaults($defaults);

    parent::buildQuickForm();
  }

  /**
   * Get Default Values
   * @param  array $settingFields  settings fields
   * @return array                 default values
   */
  public function getSettings($settingFields = []) {
    $defaults = array();

    if ($settingFields == []) {
      $settingFields = self::settingsFields();
    }
    try {
      $existingSetting = civicrm_api3('Setting', 'get', array(
        'sequential' => 1,
        'return' => array_keys($settingFields),
      ));
    }
    catch (CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
      CRM_Core_Error::debug_log_message(ts('API Error: %1', array(1 => $error, 'domain' => 'com.aghstrategies.remoteidsync')));
    }
    foreach ($settingFields as $name => $label) {
      if (!empty($existingSetting['values'][0][$name])) {
        $defaults[$name] = $existingSetting['values'][0][$name];
      }
    }
    // print_r($error); die();
    return $defaults;
  }

  /**
   * Settings Fields
   * @return array setting field name => setting field label
   */
  public function settingsFields() {
    return [
      'remoteidsync_sitekey' => 'Site Key',
      'remoteidsync_apikey' => 'API Key',
      'remoteidsync_apiendpoint' => 'Endpoint for API',
      'remoteidsync_baseurl' => 'Base URL',
      'remoteidsync_customfield' => 'Custom Field ID',
    ];
  }

  public function postProcess() {
    $values = $this->exportValues();
    $settingFields = self::settingsFields();
    $params = array();
    foreach ($settingFields as $name => $label) {
      if (!empty($values[$name])) {
        $params[$name] = $values[$name];
      }
    }
    if (!empty($params)) {
      try {
        $existingSetting = civicrm_api3('Setting', 'create', $params);
        CRM_Core_Session::setStatus(ts('Settings Successfully Saved'), ts('Remote ID Sync'), 'success');
      }
      catch (CiviCRM_API3_Exception $e) {
        $error = $e->getMessage();
        CRM_Core_Error::debug_log_message(
          ts('API Error: %1', array(1 => $error, 'domain' => 'com.aghstrategies.remoteidsync'))
        );
      }
    }
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
